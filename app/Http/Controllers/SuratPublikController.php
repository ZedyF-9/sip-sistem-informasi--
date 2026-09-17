<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Services\PengirimanSuratPublik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SuratPublikController extends Controller
{
    public function create()
    {
        return view('publik.kirim');
    }

    public function store(
        Request $request,
        PengirimanSuratPublik $pengiriman
    ) {
        $validator = Validator::make($request->all(), [
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'jenis_surat' => ['required', 'string', 'max:100'],
            'sifat_surat' => [
                'required',
                Rule::in(['biasa', 'penting', 'rahasia']),
            ],
            'asal_surat' => ['required', 'string', 'max:255'],
            'nama_pengirim' => ['required', 'string', 'max:255'],
            'email_pengirim' => ['nullable', 'email', 'max:255'],
            'perihal' => ['required', 'string', 'max:255'],
            'tanggal_surat' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'file_surat' => [
                'required',
                'file',
                'mimes:pdf',
                'extensions:pdf',
                'max:5120',
            ],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute melebihi batas yang diizinkan.',
            'file_surat.mimes' => 'Berkas harus berupa PDF.',
            'file_surat.extensions' => 'Ekstensi berkas harus .pdf.',
            'file_surat.max' => 'Ukuran PDF maksimal 5 MB.',
            'file_surat.uploaded' =>
                'Unggahan gagal. Periksa ukuran PDF.',
            'tanggal_surat.before_or_equal' =>
                'Tanggal surat tidak boleh melewati hari ini.',
        ], [
            'nomor_surat' => 'Nomor surat',
            'jenis_surat' => 'Jenis surat',
            'sifat_surat' => 'Sifat surat',
            'asal_surat' => 'Asal surat',
            'nama_pengirim' => 'Nama pengirim',
            'email_pengirim' => 'Email pengirim',
            'perihal' => 'Perihal',
            'tanggal_surat' => 'Tanggal surat',
            'file_surat' => 'PDF surat',
        ]);

        $hasil = $pengiriman->kirim($request, $validator);

        if ($hasil['jenis'] === 'dibatasi') {
            return response()
                ->view('publik.dibatasi', $hasil, 429)
                ->header('Retry-After', (string) $hasil['detik'])
                ->header('Cache-Control', 'no-store');
        }

        if ($hasil['jenis'] === 'validasi') {
            return redirect()
                ->route('publik.kirim')
                ->withErrors($hasil['errors'])
                ->withInput($request->only([
                    'nomor_surat',
                    'jenis_surat',
                    'sifat_surat',
                    'asal_surat',
                    'nama_pengirim',
                    'email_pengirim',
                    'perihal',
                    'tanggal_surat',
                ]));
        }

        $request->session()->put(
            'nomor_surat_terakhir',
            $hasil['nomor_registrasi']
        );

        return redirect()->route('publik.berhasil');
    }

    public function berhasil(Request $request)
    {
        $nomor = $request->session()->get('nomor_surat_terakhir');

        if (! $nomor) {
            return redirect()->route('publik.kirim');
        }

        return response()
            ->view('publik.berhasil', compact('nomor'))
            ->header('Cache-Control', 'no-store');
    }

    public function lacakForm()
    {
        return response()
            ->view('publik.lacak', [
                'hasil' => null,
                'dicari' => false,
                'nomor' => '',
            ])
            ->header('Cache-Control', 'no-store');
    }

    public function lacak(Request $request)
    {
        $input = $request->input('nomor_registrasi');

        if (is_string($input)) {
            $request->merge([
                'nomor_registrasi' => strtoupper(trim($input)),
            ]);
        }

        $data = $request->validate([
            'nomor_registrasi' => [
                'required',
                'string',
                'regex:/^SM-[0-9A-HJKMNP-TV-Z]{26}$/',
            ],
        ], [
            'nomor_registrasi.required' => 'Nomor registrasi wajib diisi.',
            'nomor_registrasi.regex' =>
                'Format nomor registrasi tidak sesuai. Salin nomor lengkap dari bukti pengiriman.',
        ]);

        $nomor = $data['nomor_registrasi'];

        $surat = SuratMasuk::query()
            ->where('nomor_registrasi', $nomor)
            ->first(['nomor_registrasi', 'status']);

        $hasil = $surat ? [
            'nomor' => $surat->nomor_registrasi,
            'status' => match ($surat->status) {
                'menunggu_verifikasi' => 'Menunggu verifikasi Tata Usaha',
                'diverifikasi' => 'Telah diverifikasi',
                'disposisi' => 'Sedang ditindaklanjuti',
                'selesai' => 'Selesai',
                'ditolak', 'spam' => 'Tidak dapat diproses',
                default => 'Sedang diperiksa',
            },
        ] : null;

        return response()
            ->view('publik.lacak', [
                'hasil' => $hasil,
                'dicari' => true,
                'nomor' => $nomor,
            ])
            ->header('Cache-Control', 'no-store');
    }
}
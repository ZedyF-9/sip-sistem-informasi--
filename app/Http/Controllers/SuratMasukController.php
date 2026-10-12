<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($validated['q'] ?? '');

        $surats = SuratMasuk::query()
            ->with('disposisis.penerima')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search
                        ->where('nomor_registrasi', 'like', "%{$q}%")
                        ->orWhere('nomor_agenda', 'like', "%{$q}%")
                        ->orWhere('nomor_surat', 'like', "%{$q}%")
                        ->orWhere('asal_surat', 'like', "%{$q}%")
                        ->orWhere('keterangan', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('surat-masuk.index', compact('surats', 'q'));
    }

    public function create()
    {
        return view('surat-masuk.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_surat' => [
                'nullable',
                'string',
                'max:100',
            ],
            'asal_surat' => [
                'required',
                'string',
                'max:255',
            ],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            'perihal' => [
                'required',
                'string',
                'max:255',
            ],
            'tanggal_surat' => [
                'required',
                'date',
            ],
            'tanggal_diterima' => [
                'required',
                'date',
                'after_or_equal:tanggal_surat',
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
            'keterangan.max' => 'Keterangan maksimal 2.000 karakter.',
            'file_surat.file' => 'Unggahan harus berupa berkas.',
            'file_surat.mimes' => 'Berkas harus berupa PDF.',
            'file_surat.extensions' => 'Ekstensi berkas harus .pdf.',
            'file_surat.max' => 'Ukuran PDF maksimal 5 MB.',
            'file_surat.uploaded' => 'Unggahan gagal. Periksa ukuran berkas dan batas upload PHP.',
            'tanggal_diterima.after_or_equal' =>
                'Tanggal diterima tidak boleh sebelum tanggal surat.',
            'tanggal_diterima.before_or_equal' =>
                'Tanggal diterima tidak boleh melewati hari ini.',
        ], [
            'nomor_surat' => 'Nomor surat',
            'asal_surat' => 'Asal surat',
            'keterangan' => 'Keterangan',
            'perihal' => 'Perihal',
            'tanggal_surat' => 'Tanggal surat',
            'tanggal_diterima' => 'Tanggal diterima',
            'file_surat' => 'Berkas surat',
        ]);

        $path = $request->file('file_surat')
            ->store('surat-masuk', 'local');

        if (! $path) {
            return back()
                ->withInput($request->except('file_surat'))
                ->withErrors([
                    'file_surat' => 'Berkas gagal disimpan. Coba kembali.',
                ]);
        }

        $data['file_surat'] = $path;
        $data['nomor_registrasi'] = 'SM-'.Str::ulid();
        $data['jenis_surat'] = 'Surat Masuk';
        $data['sifat_surat'] = 'biasa';
        $data['nama_pengirim'] = $data['asal_surat'];
        $data['email_pengirim'] = null;
        $data['metode_pengiriman'] = 'fisik';
        $data['status'] = 'diverifikasi';
        $data['created_by'] = $request->user()->id;

        try {
            $surat = DB::transaction(function () use ($data) {
                $tahun = (int) date('Y', strtotime($data['tanggal_diterima']));
                DB::table('nomor_agenda_tahunans')->insertOrIgnore([
                    'tahun' => $tahun,
                    'nomor_terakhir' => 0,
                ]);
                $sequence = DB::table('nomor_agenda_tahunans')
                    ->where('tahun', $tahun)
                    ->lockForUpdate()
                    ->first();
                $nomor = $sequence->nomor_terakhir + 1;
                DB::table('nomor_agenda_tahunans')
                    ->where('tahun', $tahun)
                    ->update(['nomor_terakhir' => $nomor]);

                $data['nomor_agenda'] = sprintf('%03d/TU/%d', $nomor, $tahun);
                return SuratMasuk::create($data);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return redirect()
            ->route('tu.surat-masuk.show', $surat)
            ->with('status', 'Surat masuk berhasil dicatat ke arsip.');
    }

    public function show(SuratMasuk $suratMasuk)
    {
        $suratMasuk->load(['pencatat', 'disposisis.penerima']);

        return view('surat-masuk.show', [
            'surat' => $suratMasuk,
        ]);
    }

    public function download(SuratMasuk $suratMasuk)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless(
            $suratMasuk->file_surat &&
            $disk->exists($suratMasuk->file_surat),
            404,
            'Berkas surat tidak ditemukan.'
        );

        return $disk->download(
            $suratMasuk->file_surat,
            $suratMasuk->nomor_registrasi.'.pdf',
            [
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}

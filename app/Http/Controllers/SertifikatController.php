<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Sertifikat;
use App\Services\PenerbitSertifikat;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SertifikatController extends Controller
{
    public function store(
        Request $request,
        Kegiatan $kegiatan,
        PenerbitSertifikat $penerbit
    ) {
        $data = $request->validate([
            'kode_peserta' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9._-]+$/',
            ],
            'nama_penerima' => [
                'required',
                'string',
                'max:100',
            ],
            'peran' => [
                'required',
                'string',
                'max:100',
            ],
            'publikasi' => ['accepted'],
        ], [
            'kode_peserta.regex' => (
                'Kode peserta hanya boleh berisi huruf, angka, '
                .'titik, garis bawah, dan tanda minus.'
            ),
            'publikasi.accepted' => (
                'Konfirmasi data yang ditampilkan pada verifikasi publik.'
            ),
        ]);

        $penerbit->terbitkan(
            $kegiatan,
            $data,
            $request->user()->id
        );

        return redirect()
            ->route('tu.kegiatan.show', $kegiatan)
            ->with('status', 'Sertifikat PDF berhasil diterbitkan.');
    }

    public function download(Sertifikat $sertifikat)
    {
        abort_unless($sertifikat->status === 'aktif', 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless(
            $sertifikat->file_pdf
            && $disk->exists($sertifikat->file_pdf),
            404,
            'PDF sertifikat tidak ditemukan.'
        );

        return $disk->download(
            $sertifikat->file_pdf,
            'sertifikat-'.$sertifikat->id.'.pdf',
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    public function cabut(Request $request, Sertifikat $sertifikat)
    {
        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $sertifikat, $data) {
            // Urutan penguncian sama dengan penerbitan.
            Kegiatan::query()
                ->lockForUpdate()
                ->findOrFail($sertifikat->kegiatan_id);

            $target = Sertifikat::query()
                ->lockForUpdate()
                ->findOrFail($sertifikat->id);

            if ($target->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'sertifikat' => 'Sertifikat sudah dicabut.',
                ]);
            }

            $target->status = 'dicabut';
            $target->dicabut_oleh = $request->user()->id;
            $target->dicabut_at = now();
            $target->alasan_pencabutan = $data['alasan'];
            $target->save();
        });

        return redirect()
            ->route('tu.kegiatan.show', $sertifikat->kegiatan_id)
            ->with('status', 'Sertifikat dicabut dan tidak berlaku.');
    }

    public function form()
    {
        return response()
            ->view('sertifikat.verifikasi', [
                'hasil' => null,
                'kode' => '',
            ])
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function cari(Request $request)
    {
        $data = $request->validate([
            'kode' => [
                'required',
                'string',
                'regex:/^[a-f0-9]{64}$/',
            ],
        ], [
            'kode.regex' => 'Format kode verifikasi tidak sesuai.',
        ]);

        return redirect()->route(
            'publik.sertifikat.hasil',
            ['kode' => $data['kode']]
        );
    }

    public function hasil(string $kode)
    {
        $sertifikat = Sertifikat::query()
            ->where('kode_verifikasi', $kode)
            ->first();

        $hasil = [
            'status' => 'tidak_ditemukan',
        ];

        if ($sertifikat !== null) {
            $hasil = [
                'status' => $sertifikat->status,
            ];

            if ($sertifikat->status === 'aktif') {
                $hasil += [
                    'nomor' => $sertifikat->nomor,
                    'nama' => $sertifikat->snapshot['nama_penerima'],
                    'kegiatan' => $sertifikat->snapshot['kegiatan'],
                    'peran' => $sertifikat->snapshot['peran'],
                    'tanggal' => $sertifikat->tanggal_terbit
                        ->format('d-m-Y'),
                ];
            }
        }

        return response()
            ->view('sertifikat.verifikasi', compact('hasil', 'kode'))
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
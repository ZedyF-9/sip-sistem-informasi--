<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Sertifikat;
use App\Models\PesertaPkl;
use App\Services\PenerbitSertifikat;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SertifikatController extends Controller
{
    public function store(
        Request $request,
        Kegiatan $kegiatan,
        PesertaPkl $pesertaPkl,
        PenerbitSertifikat $penerbit
    ) {
        abort_unless((int) $pesertaPkl->kegiatan_id === (int) $kegiatan->id, 404);
        abort_unless($pesertaPkl->status === 'selesai', 409, 'PKL siswa belum ditandai selesai.');

        $data = [
            'kode_peserta' => $pesertaPkl->nis,
            'nama_penerima' => $pesertaPkl->nama,
            'peran' => 'Siswa PKL',
            'industri' => $pesertaPkl->nama_industri,
            'kelas' => $pesertaPkl->kelas,
            'jurusan' => $pesertaPkl->jurusan,
        ];

        $penerbit->terbitkan(
            $kegiatan,
            $data,
            $request->user()->id
        );

        return redirect()
            ->route('tu.kegiatan.show', $kegiatan)
            ->with('status', 'Sertifikat PDF berhasil diterbitkan.');
    }

    public function bulkStore(
        Request $request,
        Kegiatan $kegiatan,
        PenerbitSertifikat $penerbit
    ) {
        abort_if($kegiatan->tanggal_selesai->isFuture(), 422, 'Sertifikat dapat diterbitkan setelah periode PKL selesai.');
        $group = $request->validate([
            'berdasarkan' => ['required', Rule::in(['kelas', 'jurusan'])],
            'nilai' => ['required', 'string', 'max:100'],
        ]);

        $peserta = $kegiatan->pesertaPkls()
            ->where('status', 'selesai')
            ->where($group['berdasarkan'], $group['nilai'])
            ->get();
        abort_if($peserta->isEmpty(), 422, 'Tidak ada siswa selesai pada kelompok ini.');
        $sudahTerbit = $kegiatan->sertifikats()
            ->where('status', 'aktif')
            ->whereIn('kode_peserta', $peserta->pluck('nis'))
            ->pluck('kode_peserta')
            ->all();
        $jumlah = 0;

        foreach ($peserta as $item) {
            if (in_array($item->nis, $sudahTerbit, true)) {
                continue;
            }

            $penerbit->terbitkan($kegiatan, [
                'kode_peserta' => $item->nis,
                'nama_penerima' => $item->nama,
                'peran' => 'Siswa PKL',
                'industri' => $item->nama_industri,
                'kelas' => $item->kelas,
                'jurusan' => $item->jurusan,
            ], $request->user()->id);
            $jumlah++;
        }

        return redirect()->route('tu.kegiatan.show', $kegiatan)
            ->with('status', $jumlah
                ? $jumlah.' sertifikat berhasil diterbitkan untuk '.$group['berdasarkan'].' '.$group['nilai'].'.'
                : 'Semua siswa pada kelompok ini sudah memiliki sertifikat aktif.');
    }

    public function downloadZip(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'kelas' => ['sometimes', 'required', 'string', 'max:100'],
        ]);
        $files = $kegiatan->sertifikats()
            ->where('status', 'aktif')
            ->whereNotNull('file_pdf');
        if (isset($data['kelas'])) {
            $files->whereIn('kode_peserta', $kegiatan->pesertaPkls()
                ->where('kelas', $data['kelas'])->select('nis'));
        }
        $files = $files
            ->get();
        abort_if($files->isEmpty(), 404, 'Belum ada sertifikat aktif untuk diunduh.');

        $zipPath = storage_path('app/exports/sertifikat-pkl-'.$kegiatan->id.'-'.uniqid().'.zip');
        if (! is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0770, true);
        }
        $zip = new \ZipArchive();
        abort_unless($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true, 500);
        $disk = Storage::disk('local');
        $added = 0;
        foreach ($files as $file) {
            if ($disk->exists($file->file_pdf)) {
                $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->kode_peserta.'-'.$file->nomor).'.pdf';
                $zip->addFile($disk->path($file->file_pdf), $name);
                $added++;
            }
        }
        $zip->close();
        if ($added === 0) {
            @unlink($zipPath);
            abort(404, 'File PDF sertifikat tidak ditemukan.');
        }

        $kelasFile = isset($data['kelas']) ? '-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $data['kelas']) : '-semua';
        return response()->download($zipPath, 'sertifikat-'.$kegiatan->id.$kelasFile.'.zip')
            ->deleteFileAfterSend(true);
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
                'regex:/^(?:[0-9]{8}|[a-f0-9]{64})$/',
            ],
        ], [
            'kode.regex' => 'Masukkan kode angka 8 digit atau pindai QR sertifikat.',
        ]);

        return redirect()->route(
            'publik.sertifikat.hasil',
            ['kode' => $data['kode']]
        );
    }

    public function hasil(string $kode)
    {
        $sertifikat = ctype_digit($kode) && strlen($kode) === 8
            ? Sertifikat::query()->where('kode_cetak', $kode)->first()
            : Sertifikat::query()->where('kode_verifikasi', $kode)->first();

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
                    'industri' => $sertifikat->snapshot['industri'] ?? null,
                    'kelas' => $sertifikat->snapshot['kelas'] ?? null,
                    'jurusan' => $sertifikat->snapshot['jurusan'] ?? null,
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

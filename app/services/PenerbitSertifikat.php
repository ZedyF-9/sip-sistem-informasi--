<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\Sertifikat;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PenerbitSertifikat
{
    public function terbitkan(
        Kegiatan $kegiatan,
        array $data,
        int $userId
    ): Sertifikat {
        $path = null;

        try {
            return DB::transaction(function () use (
                $kegiatan,
                $data,
                $userId,
                &$path
            ) {
                $kegiatan = Kegiatan::query()
                    ->lockForUpdate()
                    ->findOrFail($kegiatan->id);

                $hariIni = now('Asia/Jakarta')->toDateString();

                if (
                    $kegiatan->tanggal_selesai->toDateString()
                    > $hariIni
                ) {
                    throw ValidationException::withMessages([
                        'kegiatan' => (
                            'Sertifikat diterbitkan setelah kegiatan selesai.'
                        ),
                    ]);
                }

                $sudahAda = $kegiatan->sertifikats()
                    ->where('kode_peserta', $data['kode_peserta'])
                    ->where('status', 'aktif')
                    ->exists();

                if ($sudahAda) {
                    throw ValidationException::withMessages([
                        'kode_peserta' => (
                            'Peserta ini sudah memiliki sertifikat aktif '
                            .'pada kegiatan tersebut.'
                        ),
                    ]);
                }

                $sertifikat = new Sertifikat();
                $sertifikat->kegiatan_id = $kegiatan->id;
                $sertifikat->kode_peserta = $data['kode_peserta'];
                $sertifikat->nama_penerima = $data['nama_penerima'];
                $sertifikat->peran = $data['peran'];
                $sertifikat->kode_verifikasi = bin2hex(random_bytes(32));
                $sertifikat->kode_cetak = $this->kodeCetakUnik();
                $sertifikat->tanggal_terbit = $hariIni;
                $sertifikat->status = 'aktif';
                $sertifikat->created_by = $userId;

                $sertifikat->snapshot = [
                    'sekolah' => 'SMKN 4 Tanjungpinang',
                    'kegiatan' => $kegiatan->nama,
                    'lokasi' => $kegiatan->lokasi,
                    'tanggal_mulai' => $kegiatan->tanggal_mulai
                        ->format('d-m-Y'),
                    'tanggal_selesai' => $kegiatan->tanggal_selesai
                        ->format('d-m-Y'),
                    'nama_penerima' => $data['nama_penerima'],
                    'peran' => $data['peran'],
                    'industri' => $data['industri'] ?? null,
                    'kelas' => $data['kelas'] ?? null,
                    'jurusan' => $data['jurusan'] ?? null,
                ];

                $sertifikat->save();

                $sertifikat->nomor = sprintf(
                    'SERT/%s/%06d',
                    now('Asia/Jakarta')->format('Y'),
                    $sertifikat->id
                );

                $sertifikat->save();

                $verificationUrl = rtrim(
                    config('app.url'),
                    '/'
                ).'/verifikasi-sertifikat/'
                    .$sertifikat->kode_verifikasi;

                $renderer = new ImageRenderer(
                    new RendererStyle(240, 4),
                    new SvgImageBackEnd()
                );

                $writer = new Writer($renderer);
                $svg = $writer->writeString($verificationUrl);

                $qr = 'data:image/svg+xml;base64,'
                    .base64_encode($svg);

                $template = 'data:image/png;base64,'
                    .base64_encode(file_get_contents(
                        public_path(
                            'reference/serifikat/'
                            .'Gambar ChatGPT 28 Sep 2026, 14.00.25-1.png'
                        )
                    ));
                $asset = fn (string $file) => 'data:image/png;base64,'
                    .base64_encode(file_get_contents(public_path(
                        'reference/serifikat/'.$file
                    )));

                $pdf = Pdf::loadView('sertifikat.pdf', [
                    'sertifikat' => $sertifikat,
                    'snapshot' => $sertifikat->snapshot,
                    'qr' => $qr,
                    'template' => $template,
                    'logoKepri' => $asset(
                        'Gambar ChatGPT 28 Sep 2026, 14.00.27-3.png'
                    ),
                    'logoSekolah' => $asset(
                        'Gambar ChatGPT 28 Sep 2026, 14.00.28-4.png'
                    ),
                    'tutWuri' => $asset(
                        'Gambar ChatGPT 28 Sep 2026, 14.00.29-5.png'
                    ),
                    'stempel' => $asset(
                        'Gambar ChatGPT 28 Sep 2026, 14.00.30-6.png'
                    ),
                    'motto' => $asset(
                        'Gambar ChatGPT 28 Sep 2026, 14.00.26-2.png'
                    ),
                ])
                    ->setPaper([0, 0, 841.89, 563.15])
                    ->setOption('isRemoteEnabled', false)
                    ->setOption('isPhpEnabled', false)
                    ->setOption('isJavascriptEnabled', false);

                $path = 'sertifikat/'
                    .$sertifikat->kode_verifikasi.'.pdf';

                if (! Storage::disk('local')->put($path, $pdf->output())) {
                    throw new RuntimeException(
                        'PDF sertifikat gagal disimpan.'
                    );
                }

                $sertifikat->file_pdf = $path;
                $sertifikat->save();

                return $sertifikat;
            });
        } catch (Throwable $exception) {
            if ($path !== null) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }
    }

    private function kodeCetakUnik(): string
    {
        do {
            $kode = (string) random_int(10000000, 99999999);
        } while (Sertifikat::query()->where('kode_cetak', $kode)->exists());

        return $kode;
    }
}

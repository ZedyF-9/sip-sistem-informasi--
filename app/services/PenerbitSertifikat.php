<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\PesertaPkl;
use App\Models\Sertifikat;
use Carbon\CarbonImmutable;
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
    private const VERIFICATION_URL_BASE = 'https://sip.tefa.smkn4tpi.sch.id/verifikasi-sertifikat/';

    public const PREDIKAT = [
        'BIASA' => 'Biasa',
        'BAIK' => 'Baik',
        'SANGAT BAIK' => 'Sangat Baik',
    ];

    public function terbitkan(
        Kegiatan $kegiatan,
        array $data,
        int $userId
    ): Sertifikat {
        $predikat = $data['predikat'] ?? null;

        if (
            ! is_string($predikat)
            || ! array_key_exists($predikat, self::PREDIKAT)
        ) {
            throw ValidationException::withMessages([
                'predikat' => 'Pilih predikat Biasa, Baik, atau Sangat Baik sebelum menerbitkan sertifikat.',
            ]);
        }

        $path = null;

        try {
            return DB::transaction(function () use (
                $kegiatan,
                $data,
                $predikat,
                $userId,
                &$path
            ) {
                $kegiatan = Kegiatan::query()
                    ->lockForUpdate()
                    ->findOrFail($kegiatan->id);

                // Data lahir pada peserta yang sudah tersimpan menjadi sumber utama
                // agar tanggal hasil impor ikut masuk ke snapshot sertifikat.
                $peserta = PesertaPkl::query()
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('nis', $data['kode_peserta'])
                    ->first();

                if ($peserta !== null) {
                    if (! empty($peserta->tempat_lahir)) {
                        $data['tempat_lahir'] = $peserta->tempat_lahir;
                    }

                    if (! empty($peserta->tanggal_lahir)) {
                        $data['tanggal_lahir'] = $peserta->tanggal_lahir;
                    }
                }

                if (empty($data['tempat_lahir']) || empty($data['tanggal_lahir'])) {
                    throw ValidationException::withMessages([
                        'tanggal_lahir' => 'Data tempat/tanggal lahir belum tersimpan. Impor ulang data peserta sebelum menerbitkan sertifikat.',
                    ]);
                }

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
                    'predikat' => $predikat,
                    'tempat_lahir' => $data['tempat_lahir'] ?? null,
                    'tanggal_lahir' => ! empty($data['tanggal_lahir'])
                        ? CarbonImmutable::parse($data['tanggal_lahir'])
                            ->locale('id')
                            ->translatedFormat('d F Y')
                        : null,
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

                $path = 'sertifikat/'
                    .$sertifikat->kode_verifikasi.'.pdf';

                if (! Storage::disk('local')->put(
                    $path,
                    $this->renderPdf($sertifikat)
                )) {
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

    public function perbaruiPdf(Sertifikat $sertifikat): void
    {
        if (! $sertifikat->file_pdf) {
            throw new RuntimeException(
                'Lokasi file PDF sertifikat tidak tersedia.'
            );
        }

        if (! Storage::disk('local')->put(
            $sertifikat->file_pdf,
            $this->renderPdf($sertifikat)
        )) {
            throw new RuntimeException(
                'PDF sertifikat gagal diperbarui.'
            );
        }
    }

    public function sinkronkanDataLahir(Sertifikat $sertifikat): bool
    {
        $peserta = PesertaPkl::query()
            ->where('kegiatan_id', $sertifikat->kegiatan_id)
            ->where('nis', $sertifikat->kode_peserta)
            ->first();

        if ($peserta === null) {
            return false;
        }

        $snapshot = $sertifikat->snapshot ?? [];
        $berubah = false;

        if (
            empty($snapshot['tempat_lahir'])
            && ! empty($peserta->tempat_lahir)
        ) {
            $snapshot['tempat_lahir'] = $peserta->tempat_lahir;
            $berubah = true;
        }

        if (
            empty($snapshot['tanggal_lahir'])
            && ! empty($peserta->tanggal_lahir)
        ) {
            $snapshot['tanggal_lahir'] = CarbonImmutable::parse(
                $peserta->tanggal_lahir
            )->locale('id')->translatedFormat('d F Y');
            $berubah = true;
        }

        if ($berubah) {
            $sertifikat->snapshot = $snapshot;
            $sertifikat->save();
        }

        return $berubah;
    }

    private function renderPdf(Sertifikat $sertifikat): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(240, 4),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $verificationUrl = self::VERIFICATION_URL_BASE.$sertifikat->kode_verifikasi;
        $svg = $writer->writeString($verificationUrl);
        $qr = 'data:image/svg+xml;base64,'.base64_encode($svg);
        $template = 'data:image/png;base64,'.base64_encode(file_get_contents(
            public_path(
                'reference/serifikat/'
                .'Gambar ChatGPT 28 Sep 2026, 14.00.25-1.png'
            )
        ));
        $asset = fn (string $file) => 'data:image/png;base64,'
            .base64_encode(file_get_contents(public_path(
                'reference/serifikat/'.$file
            )));

        return Pdf::loadView('sertifikat.pdf', [
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
                'logo_trasnparan_dibelaang_tulisan.png'
            ),
            'motto' => $asset(
                'Gambar ChatGPT 28 Sep 2026, 14.00.26-2.png'
            ),
        ])
            ->setPaper([0, 0, 841.89, 588.76])
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', false)
            ->setOption('isJavascriptEnabled', false)
            ->output();
    }

    private function kodeCetakUnik(): string
    {
        do {
            $kode = (string) random_int(10000000, 99999999);
        } while (Sertifikat::query()->where('kode_cetak', $kode)->exists());

        return $kode;
    }
}

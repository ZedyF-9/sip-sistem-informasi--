<?php

namespace App\Services;

use App\Models\SuratKeluar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use Throwable;

class FinalisasiSuratKeluar
{
    public function proses(
        int $suratId,
        User $aktor,
        int $version
    ): SuratKeluar {
        abort_unless(
            $aktor->is_active
            && $aktor->role === 'koordinator_tu',
            403
        );

        $pathBaru = null;

        try {
            return DB::transaction(function () use (
                $suratId,
                $aktor,
                $version,
                &$pathBaru
            ) {
                $surat = SuratKeluar::query()
                    ->lockForUpdate()
                    ->findOrFail($suratId);

                if ($surat->status !== 'disetujui') {
                    throw ValidationException::withMessages([
                        'surat' => (
                            'Penomoran dan PDF final hanya dibuat '
                            .'untuk surat yang sudah disetujui.'
                        ),
                    ]);
                }

                /*
                 * Jika sebelumnya sudah berhasil, klik ulang tidak
                 * membuat nomor atau PDF baru.
                 */
                if ($this->siap($surat)) {
                    return $surat;
                }

                if ((int) $surat->version !== $version) {
                    throw ValidationException::withMessages([
                        'surat' => (
                            'Data surat sudah berubah. '
                            .'Muat ulang halaman lalu coba kembali.'
                        ),
                    ]);
                }

                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk('local');

                if (
                    ! $surat->file_surat
                    || ! $disk->exists($surat->file_surat)
                ) {
                    throw ValidationException::withMessages([
                        'surat' => 'PDF asli surat tidak ditemukan.',
                    ]);
                }

                $sumber = $disk->path($surat->file_surat);
                $hashSumber = hash_file('sha256', $sumber);

                if ($hashSumber === false) {
                    throw new RuntimeException('PDF asli tidak dapat dibaca.');
                }

                /*
                 * Surat lama yang sudah memiliki nomor menggunakan
                 * nomor yang sama saat dibuatkan PDF final.
                 */
                if (! $surat->nomor_surat) {
                    $surat->nomor_surat = $this->nomorBerikutnya($surat);
                }

                $nomor = (string) $surat->nomor_surat;

                if (
                    strlen($nomor) > 100
                    || ! preg_match('/^[\x20-\x7E]+$/', $nomor)
                ) {
                    throw ValidationException::withMessages([
                        'nomor_surat' => (
                            'Nomor harus maksimal 100 karakter '
                            .'dan menggunakan huruf/angka/simbol ASCII.'
                        ),
                    ]);
                }

                $isiPdf = $this->buatPdf($sumber, $nomor);

                $pathBaru = 'surat-keluar-final/'
                    .$surat->id.'-'.Str::uuid().'.pdf';

                if (! $disk->put($pathBaru, $isiPdf)) {
                    throw new RuntimeException('PDF final gagal disimpan.');
                }

                $surat->file_final = $pathBaru;
                $surat->hash_sumber_final = $hashSumber;
                $surat->nomor_pada_pdf = $nomor;
                $surat->finalized_at = now();
                $surat->version = (int) $surat->version + 1;
                $surat->save();

                DB::table('riwayat_surat_keluars')->insert([
                    'surat_keluar_id' => $surat->id,
                    'user_id' => $aktor->id,
                    'aksi' => 'finalisasi',
                    'status_sebelum' => 'disetujui',
                    'status_sesudah' => 'disetujui',
                    'catatan' => (
                        'Nomor dan PDF final dibuat. Nomor: '.$nomor
                    ),
                    'file_surat' => $pathBaru,
                    'snapshot' => json_encode(
                        $surat->getAttributes(),
                        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                    ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $surat;
            });
        } catch (Throwable $exception) {
            if ($pathBaru !== null) {
                Storage::disk('local')->delete($pathBaru);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            throw ValidationException::withMessages([
                'pdf' => (
                    'PDF final gagal dibuat. Pastikan PDF dapat dibuka, '
                    .'tidak diproteksi password, dan formatnya didukung. '
                    .'Nomor baru tidak disimpan jika proses gagal. '
                    .'Detail teknis tersedia di storage/logs/laravel.log.'
                ),
            ]);
        }
    }

    public function siap(SuratKeluar $surat): bool
    {
        if (
            ! $surat->file_final
            || ! $surat->file_surat
            || ! $surat->hash_sumber_final
            || ! $surat->nomor_surat
            || $surat->nomor_pada_pdf !== $surat->nomor_surat
        ) {
            return false;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (
            ! $disk->exists($surat->file_final)
            || ! $disk->exists($surat->file_surat)
        ) {
            return false;
        }

        $hash = hash_file(
            'sha256',
            $disk->path($surat->file_surat)
        );

        return is_string($hash)
            && hash_equals($surat->hash_sumber_final, $hash);
    }

    public function download(SuratKeluar $surat)
    {
        abort_unless(
            in_array($surat->status, ['disetujui', 'dikirim'], true),
            404
        );

        abort_unless(
            $this->siap($surat),
            409,
            'PDF final belum tersedia atau tidak cocok dengan data surat.'
        );

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk->download(
            $surat->file_final,
            'surat-keluar-'.$surat->id.'-final.pdf',
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    private function nomorBerikutnya(SuratKeluar $surat): string
    {
        $tanggal = Carbon::parse($surat->tanggal_surat);
        $tahun = (int) $tanggal->format('Y');

        $format = (string) config('penomoran.format');
        $digit = (int) config('penomoran.digit', 4);
        $awal = (int) config('penomoran.awal', 1);

        if (
            ! str_contains($format, '{urut}')
            || ! str_contains($format, '{tahun}')
            || $digit < 1
            || $digit > 10
            || $awal < 1
        ) {
            throw ValidationException::withMessages([
                'konfigurasi' => (
                    'Konfigurasi penomoran tidak valid. '
                    .'Format wajib memuat {urut} dan {tahun}.'
                ),
            ]);
        }

        DB::table('nomor_surat_tahunans')->insertOrIgnore([
            'tahun' => $tahun,
            'nomor_terakhir' => $awal - 1,
        ]);

        $penghitung = DB::table('nomor_surat_tahunans')
            ->where('tahun', $tahun)
            ->lockForUpdate()
            ->first();

        if ($penghitung === null) {
            throw new RuntimeException('Penghitung nomor tidak ditemukan.');
        }

        $urutan = max(
            (int) $penghitung->nomor_terakhir,
            $awal - 1
        );

        $romawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        /*
         * Jika ada nomor lama yang sama, lanjutkan urutan.
         * Batasi pencarian untuk mendeteksi penghitung yang salah.
         */
        for ($percobaan = 0; $percobaan < 10000; $percobaan++) {
            $urutan++;

            $nomor = strtr($format, [
                '{urut}' => str_pad(
                    (string) $urutan,
                    $digit,
                    '0',
                    STR_PAD_LEFT
                ),
                '{tahun}' => (string) $tahun,
                '{bulan}' => $tanggal->format('m'),
                '{romawi}' => $romawi[(int) $tanggal->format('n')],
            ]);

            if (
                str_contains($nomor, '{')
                || str_contains($nomor, '}')
            ) {
                throw ValidationException::withMessages([
                    'konfigurasi' => 'Ada placeholder nomor yang tidak dikenal.',
                ]);
            }

            $sudahAda = SuratKeluar::query()
                ->where('nomor_surat', $nomor)
                ->exists();

            if (! $sudahAda) {
                DB::table('nomor_surat_tahunans')
                    ->where('tahun', $tahun)
                    ->update([
                        'nomor_terakhir' => $urutan,
                    ]);

                return $nomor;
            }
        }

        throw ValidationException::withMessages([
            'nomor_surat' => (
                'Urutan nomor perlu disesuaikan dengan data yang sudah ada.'
            ),
        ]);
    }

    private function buatPdf(string $sumber, string $nomor): string
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $jumlah = $pdf->setSourceFile($sumber);
        $maksimal = (int) config('penomoran.maksimal_halaman', 50);

        if ($jumlah < 1 || $jumlah > $maksimal) {
            throw ValidationException::withMessages([
                'pdf' => "PDF harus berisi 1–{$maksimal} halaman.",
            ]);
        }

        $bidang = (float) config(
            'penomoran.tinggi_bidang_nomor',
            16
        );

        if ($bidang < 16 || $bidang > 30) {
            throw ValidationException::withMessages([
                'konfigurasi' => 'Tinggi bidang nomor harus 16–30 mm.',
            ]);
        }

        for ($halaman = 1; $halaman <= $jumlah; $halaman++) {
            $template = $pdf->importPage($halaman);
            $ukuran = $pdf->getTemplateSize($template);

            $lebar = (float) $ukuran['width'];
            $tinggiAsli = (float) $ukuran['height'];
            $tinggiFinal = $tinggiAsli + $bidang;

            if ($lebar < 80 || $tinggiAsli < 80) {
                throw ValidationException::withMessages([
                    'pdf' => 'Ukuran halaman PDF terlalu kecil untuk surat.',
                ]);
            }

            $orientasi = $lebar > $tinggiFinal ? 'L' : 'P';

            $pdf->AddPage(
                $orientasi,
                [$lebar, $tinggiFinal]
            );

            /*
             * Halaman asli diletakkan di bawah bidang baru,
             * dengan ukuran asli dan tanpa ditimpa.
             */
            $pdf->useTemplate(
                $template,
                0,
                $bidang,
                $lebar,
                $tinggiAsli
            );

            $pdf->SetTextColor(15, 23, 42);
            $pdf->SetFont('Helvetica', 'B', 10);

            $teks = 'Nomor: '.$nomor;
            $font = 10;

            while (
                $pdf->GetStringWidth($teks) > $lebar - 16
                && $font > 6
            ) {
                $font -= 0.5;
                $pdf->SetFont('Helvetica', 'B', $font);
            }

            if ($pdf->GetStringWidth($teks) > $lebar - 16) {
                throw ValidationException::withMessages([
                    'nomor_surat' => 'Nomor terlalu panjang untuk halaman PDF.',
                ]);
            }

            $pdf->SetXY(8, 3);
            $pdf->Cell($lebar - 16, 5, $teks);

            $pdf->SetFont('Helvetica', '', 7);
            $pdf->SetXY(8, 9);
            $pdf->Cell(
                $lebar - 16,
                3,
                "Halaman {$halaman} dari {$jumlah}"
            );

            $pdf->SetDrawColor(203, 213, 225);
            $pdf->Line(8, $bidang - 2, $lebar - 8, $bidang - 2);
        }

        return $pdf->Output('S');
    }
}
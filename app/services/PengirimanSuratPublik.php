<?php

namespace App\Services;

use App\Models\PembatasPengiriman;
use App\Models\SuratMasuk;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PengirimanSuratPublik
{
    public function kirim(Request $request, Validator $validator): array
    {
        $ipKey = hash_hmac(
            'sha256',
            (string) $request->ip(),
            (string) config('app.key')
        );

        DB::table('pembatas_pengiriman')->insertOrIgnore([
            'ip_key' => $ipKey,
            'pelanggaran' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $path = null;

        try {
            return DB::transaction(function () use (
                $request,
                $validator,
                $ipKey,
                &$path
            ) {
                $pembatas = PembatasPengiriman::query()
                    ->where('ip_key', $ipKey)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $pembatas->diblokir_sampai &&
                    $pembatas->diblokir_sampai->isFuture()
                ) {
                    return $this->blockedResult(
                        $pembatas->diblokir_sampai->getTimestamp()
                    );
                }

                if ($pembatas->diblokir_sampai) {
                    $pembatas->diblokir_sampai = null;
                    $pembatas->pelanggaran = 0;
                    $pembatas->save();
                }

                $bolehKirimAt = $pembatas->terakhir_berhasil_at
                    ? $pembatas->terakhir_berhasil_at->copy()->addMinutes(10)
                    : null;

                if ($bolehKirimAt && $bolehKirimAt->isFuture()) {
                    $pembatas->pelanggaran++;

                    if ($pembatas->pelanggaran > 5) {
                        $pembatas->diblokir_sampai = now()->addHours(24);
                        $pembatas->save();

                        return $this->blockedResult(
                            $pembatas->diblokir_sampai->getTimestamp()
                        );
                    }

                    $pembatas->save();

                    return [
                        'jenis' => 'dibatasi',
                        'pesan' =>
                            'Pengiriman sebelumnya masih dalam masa tunggu. '
                            .'Jangan mengirim ulang sebelum waktu tunggu selesai.',
                        'detik' => max(
                            1,
                            $bolehKirimAt->getTimestamp() - now()->getTimestamp()
                        ),
                    ];
                }

                if ($validator->fails()) {
                    return [
                        'jenis' => 'validasi',
                        'errors' => $validator->errors()->toArray(),
                    ];
                }

                $data = $validator->validated();

                $path = $request->file('file_surat')
                    ->store('surat-masuk', 'local');

                if (! $path) {
                    return [
                        'jenis' => 'validasi',
                        'errors' => [
                            'file_surat' => [
                                'PDF gagal disimpan. Silakan coba kembali.',
                            ],
                        ],
                    ];
                }

                $data['file_surat'] = $path;
                $data['nomor_registrasi'] = 'SM-'.Str::ulid();
                $data['metode_pengiriman'] = 'online';
                $data['tanggal_diterima'] = now()->toDateString();
                $data['status'] = 'menunggu_verifikasi';
                $data['created_by'] = null;
                $data['nomor_agenda'] = null;
                $data['lokasi_arsip_fisik'] = null;

                $surat = SuratMasuk::create($data);

                $pembatas->terakhir_berhasil_at = now();
                $pembatas->pelanggaran = 0;
                $pembatas->diblokir_sampai = null;
                $pembatas->save();

                return [
                    'jenis' => 'berhasil',
                    'nomor_registrasi' => $surat->nomor_registrasi,
                ];
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }
    }

    private function blockedResult(int $until): array
    {
        return [
            'jenis' => 'dibatasi',
            'pesan' =>
                'Pengiriman dari jaringan ini diblokir sementara '
                .'karena percobaan berulang selama masa tunggu.',
            'detik' => max(1, $until - now()->getTimestamp()),
        ];
    }
}
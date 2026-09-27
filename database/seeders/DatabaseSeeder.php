<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\password;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
      $daftarAkun = [
    [
        'name' => 'Koordinator TU',
        'email' => 'tu@smkn4.com',
        'role' => 'koordinator_tu',
    ],
    [
        'name' => 'Kepala Sekolah',
        'email' => 'kepala_sekolah@smkn4.com',
        'role' => 'kepala_sekolah',
    ],
    [
        'name' => 'Penerima Disposisi',
        'email' => 'penerima_disposisi@smkn4.com',
        'role' => 'penerima_disposisi',
    ],
    [
        'name' => 'Waka Humas',
        'email' => 'waka_humas@smkn4.com',
        'role' => 'waka_humas',
    ],
];

        // Pastikan semua role dikenali oleh sistem login.
        foreach ($daftarAkun as $data) {
            if (! array_key_exists($data['role'], User::ROLES)) {
                throw new \RuntimeException(
                    "Role '{$data['role']}' tidak ditemukan di User::ROLES. "
                    . 'Tidak ada akun yang diubah.'
                );
            }
        }

        $passwordBaru = password(
            label: 'Ketik password baru untuk semua akun:',
            required: true,
        );

        $konfirmasi = password(
            label: 'Ketik ulang password baru:',
            required: true,
        );

        if (strlen($passwordBaru) < 12 || strlen($passwordBaru) > 72) {
            throw new \RuntimeException(
                'Password harus sepanjang 12–72 byte. '
                . 'Tidak ada akun yang diubah.'
            );
        }

        if ($passwordBaru !== $konfirmasi) {
            throw new \RuntimeException(
                'Konfirmasi password tidak sama. '
                . 'Tidak ada akun yang diubah.'
            );
        }

        // Jika salah satu akun gagal disimpan,
        // semua perubahan dalam transaksi dibatalkan.
        DB::transaction(function () use ($daftarAkun, $passwordBaru) {
            foreach ($daftarAkun as $data) {
                $akun = User::firstOrNew([
                    'email' => $data['email'],
                ]);

                $akun->name = $data['name'];
                $akun->role = $data['role'];
                $akun->password = Hash::make($passwordBaru);
                $akun->is_active = true;
                $akun->save();
            }
        });

        foreach ($daftarAkun as $data) {
            $this->command->info(
                "Siap: {$data['email']} ({$data['role']})"
            );
        }

        $this->command->info(
            'Selesai! Semua akun dalam daftar aktif dan memakai password baru.'
        );
    }
}

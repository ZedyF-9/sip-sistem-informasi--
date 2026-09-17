<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateAllInternalUsers extends Command
{
    protected $signature = 'sip:buat-semua-akun';

    protected $description = 'Membuat seluruh akun internal untuk pengujian lokal SIP';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error(
                'Perintah ini hanya dapat digunakan pada lingkungan local.'
            );

            return self::FAILURE;
        }

        $accounts = [
            [
                'name' => 'Kepala Sekolah',
                'email' => 'kepala_sekolah@smkn4.com',
                'role' => 'kepala_sekolah',
            ],
            [
                'name' => 'Koordinator TU',
                'email' => 'tu@smkn4.com',
                'role' => 'koordinator_tu',
            ],
            [
                'name' => 'Waka Kurikulum',
                'email' => 'waka_kurikulum@smkn4.com',
                'role' => 'penerima_disposisi',
            ],
            [
                'name' => 'Waka Kesiswaan',
                'email' => 'waka_kesiswaan@smkn4.com',
                'role' => 'penerima_disposisi',
            ],
            [
                'name' => 'Waka Sapras',
                'email' => 'waka_sapras@smkn4.com',
                'role' => 'penerima_disposisi',
            ],
            [
                'name' => 'Waka Humas',
                'email' => 'waka_humas@smkn4.com',
                'role' => 'penerima_disposisi',
            ],
            [
                'name' => 'Pembina OSIS',
                'email' => 'pembina_osis@smkn4.com',
                'role' => 'penerima_disposisi',
            ],
            [
                'name' => 'Bendahara BOS',
                'email' => 'bendahara_bos@smkn4.com',
                'role' => 'penerima_disposisi',
            ],
            [
                'name' => 'Bendahara SPP',
                'email' => 'bendahara_spp@smkn4.com',
                'role' => 'penerima_disposisi',
            ],
        ];

        $rows = DB::transaction(function () use ($accounts) {
            $results = [];

            foreach ($accounts as $account) {
                $existing = User::where(
                    'email',
                    $account['email']
                )->first();

                if ($existing) {
                    $results[] = [
                        $existing->name,
                        $existing->email,
                        $existing->roleLabel(),
                        'Gunakan password lama',
                        'Sudah ada',
                    ];

                    continue;
                }

                $password = Str::random(16);

                $user = new User();
                $user->name = $account['name'];
                $user->email = $account['email'];
                $user->role = $account['role'];
                $user->is_active = true;
                $user->password = $password;
                $user->save();

                $results[] = [
                    $user->name,
                    $user->email,
                    $user->roleLabel(),
                    $password,
                    'Dibuat',
                ];
            }

            return $results;
        });

        $this->info('Pembuatan akun selesai.');

        $this->table(
            ['Nama', 'Email', 'Role', 'Password', 'Status'],
            $rows
        );

        $this->warn(
            'Simpan password akun baru sebelum menutup terminal. '
            .'Password tersebut hanya ditampilkan saat akun dibuat.'
        );

        return self::SUCCESS;
    }
}
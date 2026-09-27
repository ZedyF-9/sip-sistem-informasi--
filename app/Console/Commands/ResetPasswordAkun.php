<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordAkun extends Command
{
    protected $signature = 'sip:reset-password';

    protected $description = 'Mengganti password semua akun internal SIP lokal';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('Perintah ini hanya untuk lingkungan local.');

            return self::FAILURE;
        }

        $users = User::query()
            ->whereIn('role', [
                'koordinator_tu',
                'kepala_sekolah',
                'waka_humas',
                'penerima_disposisi',
            ])
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            $this->error('Tidak ada akun internal yang ditemukan.');

            return self::FAILURE;
        }

        $this->table(
            ['Nama', 'Email'],
            $users->map(fn (User $user) => [
                $user->name,
                $user->email,
            ])->all()
        );

        $this->warn('Password yang diketik akan terlihat di terminal.');
        $this->line('Semua akun di atas akan memakai password baru yang sama.');

        $password = (string) $this->ask(
            'Ketik password baru (12–72 karakter)'
        );

        $confirmation = (string) $this->ask(
            'Ketik ulang password baru'
        );

        if (strlen($password) < 12 || strlen($password) > 72) {
            $this->error('Password harus sepanjang 12–72 karakter.');

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->error('Kedua password berbeda. Jalankan ulang perintah.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($users, $password) {
            foreach ($users as $user) {
                $user->password = Hash::make($password);
                $user->remember_token = Str::random(60);
                $user->save();
            }
        });

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->whereIn('user_id', $users->modelKeys())
                ->delete();
        }

        $this->info(
            'Password '.$users->count().' akun berhasil diganti.'
        );

        $this->line('Data surat, email, role, dan status akun tetap sama.');
        $this->line('Buka halaman login baru untuk masuk kembali.');

        return self::SUCCESS;
    }
}

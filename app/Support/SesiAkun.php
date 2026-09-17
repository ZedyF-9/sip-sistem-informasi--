<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SesiAkun
{
    public static function pastikanDatabase(): void
    {
        if (config('session.driver') !== 'database') {
            throw ValidationException::withMessages([
                'akun' => (
                    'Pengelolaan akun ini membutuhkan '
                    .'SESSION_DRIVER=database. '
                    .'Periksa .env, lalu jalankan php artisan config:clear.'
                ),
            ]);
        }
    }

    public static function hapus(int $userId): void
    {
        self::pastikanDatabase();

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->delete();
    }
}
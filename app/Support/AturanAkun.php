<?php

namespace App\Support;

use Closure;

final class AturanAkun
{
    public const ROLES = [
        'koordinator_tu' => 'Koordinator TU',
        'kepala_sekolah' => 'Kepala Sekolah',
        'waka_humas' => 'Waka Humas',
        'penerima_disposisi' => 'Penerima Disposisi',
    ];

    public static function password(): array
    {
        return [
            'bail',
            'required',
            'string',
            'min:12',
            'max:72',
            'confirmed',
            function (
                string $attribute,
                mixed $value,
                Closure $fail
            ): void {
                if (strlen($value) > 72) {
                    $fail('Password maksimal 72 byte.');
                }
            },
        ];
    }
}

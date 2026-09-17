<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembatasPengiriman extends Model
{
    protected $table = 'pembatas_pengiriman';

    protected $fillable = [
        'ip_key',
        'terakhir_berhasil_at',
        'pelanggaran',
        'diblokir_sampai',
    ];

    protected function casts(): array
    {
        return [
            'terakhir_berhasil_at' => 'datetime',
            'diblokir_sampai' => 'datetime',
            'pelanggaran' => 'integer',
        ];
    }
}
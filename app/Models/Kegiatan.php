<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    protected $fillable = [
        'nama',
        'lokasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function sertifikats(): HasMany
    {
        return $this->hasMany(Sertifikat::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MitraIndustri extends Model
{
    protected $table = 'mitra_industris';

    protected $fillable = [
        'nama_mitra',
        'bidang_usaha',
        'alamat',
        'telepon',
        'email',
        'website',
        'logo',
        'deskripsi',
        'status',
    ];

    public function kerjasamas(): HasMany
    {
        return $this->hasMany(Kerjasama::class, 'mitra_industri_id');
    }
}   
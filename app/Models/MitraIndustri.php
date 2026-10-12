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
        'status_persetujuan',
        'catatan_persetujuan',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected function casts(): array
    {
        return ['disetujui_pada' => 'datetime'];
    }

    public function kerjasamas(): HasMany
    {
        return $this->hasMany(Kerjasama::class, 'mitra_industri_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(AgendaMitra::class, 'mitra_industri_id');
    }

    public function pesertaPkls(): HasMany
    {
        return $this->hasMany(PesertaPkl::class, 'mitra_industri_id');
    }
}

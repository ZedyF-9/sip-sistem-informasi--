<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeluar extends Model
{
    protected $table = 'surat_keluars';

    protected $fillable = [
        'perihal',
        'tujuan',
        'jenis_surat',
        'sifat_surat',
        'tanggal_surat',
        'file_surat',
        'keterangan',
        'data_surat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'approved_at' => 'datetime',
            'dikirim_at' => 'datetime',
            'version' => 'integer',
            'data_surat' => 'array',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

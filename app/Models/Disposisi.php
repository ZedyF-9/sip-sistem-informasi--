<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposisi extends Model
{
    protected $table = 'disposisis';

    protected $fillable = [
        'surat_masuk_id',
        'pemberi_id',
        'penerima_id',
        'instruksi',
        'prioritas',
        'tanggal_disposisi',
        'batas_waktu',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_disposisi' => 'date',
            'batas_waktu' => 'date',
        ];
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function pemberi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemberi_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }
}
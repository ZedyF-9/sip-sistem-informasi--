<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaMitra extends Model
{
    protected $fillable = [
        'mitra_industri_id',
        'judul',
        'tanggal',
        'waktu',
        'lokasi',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(MitraIndustri::class, 'mitra_industri_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

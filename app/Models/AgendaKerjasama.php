<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaKerjasama extends Model
{
    protected $fillable = [
        'kerjasama_id',
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

    public function kerjasama(): BelongsTo
    {
        return $this->belongsTo(Kerjasama::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

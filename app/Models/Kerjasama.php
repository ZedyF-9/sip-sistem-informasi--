<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kerjasama extends Model
{
    protected $table = 'kerjasamas';

    protected $fillable = [
        'mitra_industri_id',
        'judul',
        'nomor_mou',
        'tanggal_mulai',
        'tanggal_selesai',
        'dokumen_mou',
        'status',
        'status_persetujuan',
        'catatan_persetujuan',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'disetujui_pada' => 'datetime',
        ];
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(MitraIndustri::class, 'mitra_industri_id');
    }

    public function scopeBerlaku(Builder $query): Builder
    {
        $hariIni = now('Asia/Jakarta')->toDateString();

        return $query
            ->where('status', 'aktif')
            ->where('status_persetujuan', 'disetujui')
            ->where('tanggal_mulai', '<=', $hariIni)
            ->where(function (Builder $q) use ($hariIni) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $hariIni);
            });
    }

    public function labelPeriode(): string
    {
        $hariIni = now('Asia/Jakarta')->toDateString();

        if ($this->status === 'berakhir') {
            return 'Berakhir';
        }

        if ($this->tanggal_mulai->format('Y-m-d') > $hariIni) {
            return 'Belum dimulai';
        }

        if (
            $this->tanggal_selesai &&
            $this->tanggal_selesai->format('Y-m-d') < $hariIni
        ) {
            return 'Berakhir';
        }

        return 'Berlaku';
    }
}

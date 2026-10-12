<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuks';

    protected $fillable = [
        'nomor_registrasi',
        'nomor_surat',
        'jenis_surat',
        'sifat_surat',
        'asal_surat',
        'nama_pengirim',
        'email_pengirim',
        'perihal',
        'keterangan',
        'metode_pengiriman',
        'nomor_agenda',
        'lokasi_arsip_fisik',
        'tanggal_surat',
        'tanggal_diterima',
        'file_surat',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_diterima' => 'date',
        ];
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disposisis(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'surat_masuk_id');
    }

    public function statusLabel(): string
    {
        if ($this->status === 'diverifikasi' && $this->relationLoaded('disposisis')
            && $this->disposisis->contains(fn ($item) =>
                in_array($item->status_persetujuan, ['menunggu', 'disetujui'], true)
                && $item->penerima?->role === 'kepala_sekolah'
            )) {
            return 'Menunggu Persetujuan Kepala Sekolah';
        }

        return match ($this->status) {
            'diverifikasi' => 'Diarsipkan',
            'disposisi' => 'Disposisi',
            'selesai' => 'Selesai',
            default => 'Tidak diketahui',
        };
    }
}

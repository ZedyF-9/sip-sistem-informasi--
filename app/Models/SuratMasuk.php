<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'diteruskan_at' => 'datetime',
        ];
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'diverifikasi' => 'Diverifikasi',
            'disposisi' => 'Disposisi',
            'ditolak' => 'Ditolak',
            'spam' => 'Spam',
            'selesai' => 'Selesai',
            default => 'Tidak diketahui',
        };
    }
}
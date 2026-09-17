<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('lokasi', 150);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('deskripsi')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });

        Schema::create('sertifikats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kegiatan_id')
                ->constrained('kegiatans')
                ->restrictOnDelete();

            $table->string('kode_peserta', 100);
            $table->string('nama_penerima', 100);
            $table->string('peran', 100);
            $table->string('nomor', 100)->nullable()->unique();
            $table->string('kode_verifikasi', 64)->unique();
            $table->date('tanggal_terbit');
            $table->json('snapshot');
            $table->string('file_pdf')->nullable();

            $table->enum('status', ['aktif', 'dicabut'])
                ->default('aktif');

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('dicabut_oleh')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('dicabut_at')->nullable();
            $table->text('alasan_pencabutan')->nullable();
            $table->timestamps();

            $table->index(['kegiatan_id', 'kode_peserta', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikats');
        Schema::dropIfExists('kegiatans');
    }
};
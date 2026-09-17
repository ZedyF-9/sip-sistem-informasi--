<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100)->nullable()->unique();
            $table->string('perihal');
            $table->string('tujuan');
            $table->string('jenis_surat', 100);
            $table->enum('sifat_surat', ['biasa', 'penting', 'rahasia']);
            $table->date('tanggal_surat');
            $table->string('file_surat');

            $table->enum('status', [
                'draf', 'diajukan', 'revisi', 'disetujui', 'dikirim',
            ])->default('draf')->index();

            $table->foreignId('created_by')
                ->constrained('users')->restrictOnDelete();

            $table->foreignId('approved_by')
                ->nullable()->constrained('users')->restrictOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->timestamp('dikirim_at')->nullable();
            $table->string('metode_pengiriman', 50)->nullable();
            $table->text('catatan_pengiriman')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('riwayat_surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_keluar_id')
                ->constrained('surat_keluars')->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')->restrictOnDelete();
            $table->string('aksi', 50);
            $table->string('status_sebelum', 50)->nullable();
            $table->string('status_sesudah', 50);
            $table->text('catatan')->nullable();
            $table->string('file_surat');
            $table->json('snapshot');
            $table->timestamps();
        });

        Schema::create('nomor_surat_tahunans', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->primary();
            $table->unsignedInteger('nomor_terakhir')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_surat_keluars');
        Schema::dropIfExists('surat_keluars');
        Schema::dropIfExists('nomor_surat_tahunans');
    }
};
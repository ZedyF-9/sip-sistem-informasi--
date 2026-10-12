<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta_pkls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->restrictOnDelete();
            $table->string('nis', 50);
            $table->string('nama', 150);
            $table->string('kelas', 100);
            $table->foreignId('mitra_industri_id')->constrained('mitra_industris')->restrictOnDelete();
            $table->string('nama_industri', 255);
            $table->enum('status', ['berlangsung', 'selesai'])->default('berlangsung');
            $table->timestamps();
            $table->unique(['kegiatan_id', 'nis']);
            $table->index(['kegiatan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_pkls');
    }
};

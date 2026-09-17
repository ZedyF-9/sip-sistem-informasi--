<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra_industris', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mitra');
            $table->string('bidang_usaha', 150);
            $table->text('alamat')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->text('deskripsi');
            $table->enum('status', ['aktif', 'nonaktif'])
                ->default('nonaktif')->index();
            $table->timestamps();
        });

        Schema::create('kerjasamas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mitra_industri_id')
                ->constrained('mitra_industris')
                ->restrictOnDelete();

            $table->string('judul');
            $table->string('nomor_mou', 100)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('dokumen_mou')->nullable();
            $table->enum('status', ['aktif', 'berakhir'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerjasamas');
        Schema::dropIfExists('mitra_industris');
    }
};
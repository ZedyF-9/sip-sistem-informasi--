<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_masuks', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_registrasi', 50)->unique();
            $table->string('nomor_surat', 100)->nullable();

            $table->string('jenis_surat', 100);
            $table->enum('sifat_surat', [
                'biasa',
                'penting',
                'rahasia',
            ])->default('biasa');

            $table->string('asal_surat');
            $table->string('nama_pengirim');
            $table->string('email_pengirim')->nullable();
            $table->string('perihal');

            $table->enum('metode_pengiriman', ['online', 'fisik']);

            $table->string('nomor_agenda', 100)->nullable()->unique();
            $table->string('lokasi_arsip_fisik')->nullable();

            $table->date('tanggal_surat');
            $table->date('tanggal_diterima');

            $table->string('file_surat')->nullable();

            $table->enum('status', [
                'diverifikasi',
                'disposisi',
                'selesai',
            ])->default('diverifikasi')->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuks');
    }
};

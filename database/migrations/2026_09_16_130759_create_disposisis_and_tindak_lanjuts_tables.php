<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surat_masuk_id')
                ->constrained('surat_masuks')
                ->restrictOnDelete();

            $table->foreignId('pemberi_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('penerima_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('instruksi');

            $table->enum('prioritas', [
                'biasa',
                'segera',
                'sangat_segera',
            ]);

            $table->date('tanggal_disposisi');
            $table->date('batas_waktu')->nullable();

            $table->enum('status', [
                'baru',
                'diproses',
                'selesai',
            ])->default('baru');

            $table->timestamps();

            $table->unique(
                ['surat_masuk_id', 'penerima_id'],
                'disposisi_surat_penerima_unique'
            );
        });

        Schema::create('tindak_lanjuts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('disposisi_id')
                ->constrained('disposisis')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('catatan');
            $table->string('file_lampiran')->nullable();
            $table->enum('status', ['diproses', 'selesai']);
            $table->timestamp('tanggal_tindak_lanjut');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjuts');
        Schema::dropIfExists('disposisis');
    }
};
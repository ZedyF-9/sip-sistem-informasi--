<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->timestamp('diteruskan_at')->nullable()->index();
        });

        Schema::create('riwayat_surat_masuks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surat_masuk_id')
                ->constrained('surat_masuks')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('aksi', 50);
            $table->string('status_sebelum', 50);
            $table->string('status_sesudah', 50);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_surat_masuks');

        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropColumn('diteruskan_at');
        });

    }
};

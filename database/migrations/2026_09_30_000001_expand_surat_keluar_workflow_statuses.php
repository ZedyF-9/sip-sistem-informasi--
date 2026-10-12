<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->string('status')->default('draf')->change();
        });
    }

    public function down(): void
    {
        // Preserve data if rolling back: map intermediate states to the
        // closest legacy status before restoring the original enum.
        \Illuminate\Support\Facades\DB::table('surat_keluars')
            ->where('status', 'menunggu_kepala_tu')
            ->update(['status' => 'diajukan']);
        \Illuminate\Support\Facades\DB::table('surat_keluars')
            ->where('status', 'kembali_ke_tu')
            ->update(['status' => 'revisi']);

        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->enum('status', [
                'draf', 'diajukan', 'revisi', 'disetujui', 'dikirim',
            ])->default('draf')->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->string('file_final')->nullable();
            $table->string('hash_sumber_final', 64)->nullable();
            $table->string('nomor_pada_pdf', 100)->nullable();
            $table->timestamp('finalized_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->dropColumn([
                'file_final',
                'hash_sumber_final',
                'nomor_pada_pdf',
                'finalized_at',
            ]);
        });
    }
};
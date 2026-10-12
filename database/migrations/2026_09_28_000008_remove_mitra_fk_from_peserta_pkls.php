<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta_pkls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mitra_industri_id');
        });
    }

    public function down(): void
    {
        Schema::table('peserta_pkls', function (Blueprint $table) {
            $table->foreignId('mitra_industri_id')->nullable()
                ->constrained('mitra_industris')->restrictOnDelete();
        });
    }
};

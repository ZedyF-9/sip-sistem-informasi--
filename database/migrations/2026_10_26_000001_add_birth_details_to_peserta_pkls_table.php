<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta_pkls', function (Blueprint $table) {
            $table->string('tempat_lahir', 100)->nullable()->after('nama');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
        });
    }

    public function down(): void
    {
        Schema::table('peserta_pkls', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir', 'tanggal_lahir']);
        });
    }
};

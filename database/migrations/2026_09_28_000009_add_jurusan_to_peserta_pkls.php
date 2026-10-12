<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta_pkls', function (Blueprint $table) {
            $table->string('jurusan', 100)->default('')->after('kelas');
            $table->index(['kegiatan_id', 'jurusan']);
        });
    }

    public function down(): void
    {
        Schema::table('peserta_pkls', function (Blueprint $table) {
            $table->dropIndex(['kegiatan_id', 'jurusan']);
            $table->dropColumn('jurusan');
        });
    }
};

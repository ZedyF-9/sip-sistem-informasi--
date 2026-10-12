<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikats', function (Blueprint $table) {
            $table->string('kode_cetak', 8)->nullable()->after('kode_verifikasi');
        });

        DB::table('sertifikats')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($sertifikats) {
                foreach ($sertifikats as $sertifikat) {
                    DB::table('sertifikats')
                        ->where('id', $sertifikat->id)
                        ->update(['kode_cetak' => $this->kodeUnik()]);
                }
            });

        Schema::table('sertifikats', function (Blueprint $table) {
            $table->unique('kode_cetak');
        });
    }

    public function down(): void
    {
        Schema::table('sertifikats', function (Blueprint $table) {
            $table->dropUnique(['kode_cetak']);
            $table->dropColumn('kode_cetak');
        });
    }

    private function kodeUnik(): string
    {
        do {
            $kode = (string) random_int(10000000, 99999999);
        } while (DB::table('sertifikats')->where('kode_cetak', $kode)->exists());

        return $kode;
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_agenda_tahunans', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->primary();
            $table->unsignedInteger('nomor_terakhir')->default(0);
        });

        $terakhirPerTahun = [];
        DB::table('surat_masuks')->whereNotNull('nomor_agenda')
            ->pluck('nomor_agenda')->each(function ($nomorAgenda) use (&$terakhirPerTahun) {
                if (preg_match('/^(\d+)\/TU\/(\d{4})$/', (string) $nomorAgenda, $matches)) {
                    $tahun = (int) $matches[2];
                    $terakhirPerTahun[$tahun] = max($terakhirPerTahun[$tahun] ?? 0, (int) $matches[1]);
                }
            });

        foreach ($terakhirPerTahun as $tahun => $nomorTerakhir) {
            DB::table('nomor_agenda_tahunans')->insert([
                'tahun' => $tahun,
                'nomor_terakhir' => $nomorTerakhir,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_agenda_tahunans');
    }
};

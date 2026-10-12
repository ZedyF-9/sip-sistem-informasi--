<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('surat_masuks')
            ->where('status', 'menunggu_verifikasi')
            ->update(['status' => 'diverifikasi']);

        if (Schema::hasColumn('surat_masuks', 'diteruskan_at')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->dropColumn('diteruskan_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('surat_masuks', 'diteruskan_at')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->timestamp('diteruskan_at')->nullable()->index();
            });
        }
    }
};

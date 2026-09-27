<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kerjasamas', function (Blueprint $table) {
            $table->string('status_persetujuan', 20)->default('menunggu')->index();
            $table->text('catatan_persetujuan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
        });

        DB::table('kerjasamas')->update(['status_persetujuan' => 'disetujui']);
        DB::table('users')->where('email', 'waka_humas@smkn4.com')
            ->update(['role' => 'waka_humas']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'waka_humas')
            ->update(['role' => 'penerima_disposisi']);

        Schema::table('kerjasamas', function (Blueprint $table) {
            $table->dropIndex(['status_persetujuan']);
            $table->dropConstrainedForeignId('disetujui_oleh');
            $table->dropColumn([
                'status_persetujuan',
                'catatan_persetujuan',
                'disetujui_pada',
            ]);
        });
    }
};

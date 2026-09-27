<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mitra_industris', function (Blueprint $table) {
            $table->string('status_persetujuan', 20)->default('menunggu')->index();
            $table->text('catatan_persetujuan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
        });

    }

    public function down(): void
    {
        Schema::table('mitra_industris', function (Blueprint $table) {
            $table->dropIndex(['status_persetujuan']);
            $table->dropConstrainedForeignId('disetujui_oleh');
            $table->dropColumn(['status_persetujuan', 'catatan_persetujuan', 'disetujui_pada']);
        });
    }
};

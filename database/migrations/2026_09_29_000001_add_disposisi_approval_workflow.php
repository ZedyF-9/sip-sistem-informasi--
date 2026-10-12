<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->string('status_persetujuan', 20)->default('disetujui')->index();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->text('catatan_persetujuan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropColumn(['status_persetujuan', 'disetujui_oleh', 'disetujui_at', 'catatan_persetujuan']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembatas_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->string('ip_key', 64)->unique();
            $table->timestamp('terakhir_berhasil_at')->nullable();
            $table->unsignedInteger('pelanggaran')->default(0);
            $table->timestamp('diblokir_sampai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembatas_pengiriman');
    }
};
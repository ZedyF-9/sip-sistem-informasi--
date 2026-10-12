<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_kerjasamas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kerjasama_id')
                ->constrained('kerjasamas')
                ->cascadeOnDelete();
            $table->string('judul');
            $table->date('tanggal')->index();
            $table->time('waktu')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_kerjasamas');
    }
};

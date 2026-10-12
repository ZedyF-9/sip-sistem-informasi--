<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_mitras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_industri_id')
                ->constrained('mitra_industris')
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

        $agendasLama = DB::table('agenda_kerjasamas')
            ->join('kerjasamas', 'kerjasamas.id', '=', 'agenda_kerjasamas.kerjasama_id')
            ->select([
                'agenda_kerjasamas.id',
                'kerjasamas.mitra_industri_id',
                'agenda_kerjasamas.judul',
                'agenda_kerjasamas.tanggal',
                'agenda_kerjasamas.waktu',
                'agenda_kerjasamas.lokasi',
                'agenda_kerjasamas.catatan',
                'agenda_kerjasamas.created_by',
                'agenda_kerjasamas.created_at',
                'agenda_kerjasamas.updated_at',
            ])
            ->orderBy('agenda_kerjasamas.id')
            ->get();

        foreach ($agendasLama->chunk(500) as $batch) {
            DB::table('agenda_mitras')->insert(
                $batch->map(fn ($agenda) => (array) $agenda)->all(),
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_mitras');
    }
};

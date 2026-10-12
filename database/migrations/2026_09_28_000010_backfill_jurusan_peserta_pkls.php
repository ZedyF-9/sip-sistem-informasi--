<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('peserta_pkls')
            ->select(['id', 'kelas'])
            ->where('jurusan', '')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    if (preg_match('/^(?:10|11|12|X|XI|XII)\s+(.+?)(?:\s+\d+[A-Z]?)?$/i', trim($row->kelas), $matches)) {
                        DB::table('peserta_pkls')
                            ->where('id', $row->id)
                            ->update(['jurusan' => trim($matches[1])]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Jurusan yang diturunkan dari kelas tidak dapat dibedakan dari nilai asli.
    }
};

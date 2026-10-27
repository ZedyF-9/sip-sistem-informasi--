<?php

namespace App\Console\Commands;

use App\Models\Sertifikat;
use App\Services\PenerbitSertifikat;
use Illuminate\Console\Command;

class RefreshSertifikatPdf extends Command
{
    protected $signature = 'sertifikat:refresh-pdf
        {--id=* : Perbarui hanya sertifikat dengan ID tertentu}
        {--force : Lewati konfirmasi untuk memperbarui semua PDF}';

    protected $description = 'Membuat ulang PDF sertifikat dengan template terbaru';

    public function handle(PenerbitSertifikat $penerbit): int
    {
        $ids = $this->option('id');

        if ($ids === [] && ! $this->option('force') && ! $this->confirm(
            'Perbarui semua PDF sertifikat yang tersimpan?'
        )) {
            $this->info('Tidak ada PDF yang diubah.');

            return self::SUCCESS;
        }

        $jumlah = 0;
        $query = Sertifikat::query()->whereNotNull('file_pdf');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        $query->chunkById(20, function ($sertifikats) use (
            $penerbit,
            &$jumlah
        ) {
            foreach ($sertifikats as $sertifikat) {
                $penerbit->sinkronkanDataLahir($sertifikat);
                $penerbit->perbaruiPdf($sertifikat);
                $jumlah++;
            }
        });

        $this->info("PDF sertifikat diperbarui: {$jumlah}");

        return self::SUCCESS;
    }
}

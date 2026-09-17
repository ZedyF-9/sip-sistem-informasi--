<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CetakLaporanController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'jenis' => ['nullable', Rule::in(['masuk', 'keluar'])],
            'mulai' => ['nullable', 'date_format:Y-m-d'],
            'selesai' => ['nullable', 'date_format:Y-m-d'],
            'format' => ['nullable', Rule::in(['html', 'pdf'])],
        ]);

        $jenis = $data['jenis'] ?? 'masuk';
        $mulai = $data['mulai']
            ?? now('Asia/Jakarta')->startOfMonth()->toDateString();
        $selesai = $data['selesai']
            ?? now('Asia/Jakarta')->toDateString();

        if ($selesai < $mulai) {
            throw ValidationException::withMessages([
                'selesai' => 'Tanggal akhir harus setelah tanggal awal.',
            ]);
        }

        if ($jenis === 'masuk') {
            $query = DB::table('surat_masuks')
                ->select([
                    'nomor_registrasi as nomor',
                    'perihal',
                    'asal_surat as pihak',
                    'tanggal_diterima as tanggal',
                    'status',
                ])
                ->whereBetween('tanggal_diterima', [$mulai, $selesai])
                ->orderBy('tanggal_diterima')
                ->orderBy('id');
        } else {
            $query = DB::table('surat_keluars')
                ->select([
                    'nomor_surat as nomor',
                    'perihal',
                    'tujuan as pihak',
                    'tanggal_surat as tanggal',
                    'status',
                ])
                ->whereBetween('tanggal_surat', [$mulai, $selesai])
                ->when(
                    $request->user()->role === 'kepala_sekolah',
                    fn ($q) => $q->where('status', '!=', 'draf')
                )
                ->orderBy('tanggal_surat')
                ->orderBy('id');
        }

        $rows = $query->limit(501)->get();

        if ($rows->count() > 500) {
            throw ValidationException::withMessages([
                'laporan' => (
                    'Maksimal 500 surat per cetakan. '
                    .'Persempit periode laporan.'
                ),
            ]);
        }

        $viewData = [
            'rows' => $rows,
            'jenis' => $jenis,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'dicetak' => now('Asia/Jakarta')->format('d-m-Y H:i'),
            'pdf' => ($data['format'] ?? 'html') === 'pdf',
        ];

        if ($viewData['pdf']) {
            return Pdf::loadView('cetak.laporan', $viewData)
                ->setPaper('a4', 'landscape')
                ->setOption('isRemoteEnabled', false)
                ->setOption('isPhpEnabled', false)
                ->setOption('isJavascriptEnabled', false)
                ->download('laporan-'.$jenis.'-'.$mulai.'.pdf')
                ->header('Cache-Control', 'private, no-store');
        }

        return response()
            ->view('cetak.laporan', $viewData)
            ->header('Cache-Control', 'private, no-store');
    }
}
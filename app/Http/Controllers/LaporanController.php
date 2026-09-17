<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->query($filters, $request);

        $items = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('laporan.index', compact('filters', 'items'));
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);

        $query = $this->query($filters, $request)
            ->orderBy('tanggal')
            ->orderBy('id');

        $filename = 'laporan-'.$filters['jenis'].'-'
            .$filters['mulai'].'-'.$filters['selesai'].'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Nomor', 'Perihal', 'Asal atau Tujuan', 'Tanggal', 'Status',
            ], ';', '"', '');

            foreach ($query->cursor() as $row) {
                $values = [
                    $row->id,
                    $row->nomor,
                    $row->perihal,
                    $row->pihak,
                    $row->tanggal,
                    $row->status,
                ];

                $values = array_map(function ($value) {
                    $text = (string) ($value ?? '');

                    // Mencegah isi data dibaca sebagai formula spreadsheet.
                    if (preg_match('/^[\s]*[=+\-@]/u', $text)) {
                        $text = "'".$text;
                    }

                    return str_replace(["\r", "\n", "\t"], ' ', $text);
                }, $values);

                fputcsv($out, $values, ';', '"', '');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function filters(Request $request): array
    {
        $data = $request->validate([
            'jenis' => ['nullable', Rule::in(['masuk', 'keluar'])],
            'mulai' => ['nullable', 'date_format:Y-m-d'],
            'selesai' => ['nullable', 'date_format:Y-m-d'],
            'arsip' => ['nullable', 'boolean'],
        ]);

        $filters = [
            'jenis' => $data['jenis'] ?? 'masuk',
            'mulai' => $data['mulai'] ?? now()->startOfMonth()->toDateString(),
            'selesai' => $data['selesai'] ?? now()->toDateString(),
            'arsip' => $request->boolean('arsip'),
        ];

        validator($filters, [
            'selesai' => ['after_or_equal:mulai'],
        ], [
            'selesai.after_or_equal' =>
                'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ])->validate();

        return $filters;
    }

    private function query(array $filters, Request $request)
    {
        if ($filters['jenis'] === 'masuk') {
            return DB::table('surat_masuks')
                ->select([
                    'id',
                    'nomor_registrasi as nomor',
                    'perihal',
                    'asal_surat as pihak',
                    'tanggal_diterima as tanggal',
                    'status',
                ])
                ->whereBetween('tanggal_diterima', [
                    $filters['mulai'], $filters['selesai'],
                ])
                ->when(
                    $filters['arsip'],
                    fn ($q) => $q->where('status', 'selesai')
                );
        }

        return DB::table('surat_keluars')
            ->select([
                'id',
                'nomor_surat as nomor',
                'perihal',
                'tujuan as pihak',
                'tanggal_surat as tanggal',
                'status',
            ])
            ->whereBetween('tanggal_surat', [
                $filters['mulai'], $filters['selesai'],
            ])
            ->when(
                $request->user()->role === 'kepala_sekolah',
                fn ($q) => $q->where('status', '!=', 'draf')
            )
            ->when(
                $filters['arsip'],
                fn ($q) => $q->where('status', 'dikirim')
            );
    }
}
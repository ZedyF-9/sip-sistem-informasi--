<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return match ($user->role) {
            'koordinator_tu' => $this->tuDashboard(),
            'kepala_sekolah' => $this->kepalaDashboard(),
            default => $this->penerimaDashboard(),
        };
    }

    private function tuDashboard(): View
    {
        $suratMasuk = SuratMasuk::query()->count();
        $suratKeluar = SuratKeluar::query()->count();
        $menungguAcc = SuratKeluar::query()->where('status', 'diajukan')->count();
        $totalSurat = $suratMasuk + $suratKeluar;

        return view('dashboard', [
            'ringkasan' => [
                'surat_masuk' => $suratMasuk,
                'surat_keluar' => $suratKeluar,
                'menunggu_acc' => $menungguAcc,
                'total_surat' => $totalSurat,
                'persen_acc' => $totalSurat > 0
                    ? round($menungguAcc / $totalSurat * 100, 1) : 0,
                'persen_masuk' => $totalSurat > 0
                    ? round($suratMasuk / $totalSurat * 100, 1) : 0,
                'persen_keluar' => $totalSurat > 0
                    ? round($suratKeluar / $totalSurat * 100, 1) : 0,
            ],
            'suratTerbaru' => SuratMasuk::query()->latest('id')->limit(5)->get(),
        ]);
    }

    private function kepalaDashboard(): View
    {
        $suratDiverifikasi = SuratMasuk::query()->where('status', 'diverifikasi')->count();
        $suratKeluarDiajukan = SuratKeluar::query()->where('status', 'diajukan')->count();
        $disposisiAktif = Disposisi::query()->where('status', '!=', 'selesai')->count();
        $totalTindakan = $suratDiverifikasi + $suratKeluarDiajukan + $disposisiAktif;

        return view('dashboard', [
            'ringkasan' => [
                'surat_masuk' => $suratDiverifikasi,
                'surat_keluar' => $suratKeluarDiajukan,
                'disposisi_aktif' => $disposisiAktif,
                'total_tindakan' => $totalTindakan,
                'persen_surat' => $totalTindakan > 0
                    ? round($suratDiverifikasi / $totalTindakan * 100, 1) : 0,
                'persen_keluar' => $totalTindakan > 0
                    ? round($suratKeluarDiajukan / $totalTindakan * 100, 1) : 0,
                'persen_disposisi' => $totalTindakan > 0
                    ? round($disposisiAktif / $totalTindakan * 100, 1) : 0,
            ],
            'suratTerbaru' => SuratMasuk::query()
                ->where('status', 'diverifikasi')
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }

    private function penerimaDashboard(): View
    {
        $userId = auth()->id();
        $disposisiBaru = Disposisi::query()
            ->where('penerima_id', $userId)
            ->where('status', 'baru')
            ->count();
        $disposisiProses = Disposisi::query()
            ->where('penerima_id', $userId)
            ->where('status', 'diproses')
            ->count();
        $disposisiSelesai = Disposisi::query()
            ->where('penerima_id', $userId)
            ->where('status', 'selesai')
            ->count();
        $totalDisposisi = $disposisiBaru + $disposisiProses + $disposisiSelesai;

        return view('dashboard', [
            'ringkasan' => [
                'baru' => $disposisiBaru,
                'proses' => $disposisiProses,
                'selesai' => $disposisiSelesai,
                'total' => $totalDisposisi,
                'persen_baru' => $totalDisposisi > 0
                    ? round($disposisiBaru / $totalDisposisi * 100, 1) : 0,
                'persen_proses' => $totalDisposisi > 0
                    ? round($disposisiProses / $totalDisposisi * 100, 1) : 0,
                'persen_selesai' => $totalDisposisi > 0
                    ? round($disposisiSelesai / $totalDisposisi * 100, 1) : 0,
            ],
            'disposisiTerbaru' => Disposisi::query()
                ->with('surat')
                ->where('penerima_id', $userId)
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }
}

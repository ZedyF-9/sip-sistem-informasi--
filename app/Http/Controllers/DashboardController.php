<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Kerjasama;
use App\Models\MitraIndustri;
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
            'kepala_tu' => $this->kepalaTuDashboard(),
            'waka_humas' => $this->wakaHumasDashboard(),
            default => $this->penerimaDashboard(),
        };
    }

    private function kepalaTuDashboard(): View
    {
        $menunggu = SuratKeluar::query()->where('status', 'menunggu_kepala_tu')->count();
        return view('dashboard', [
            'ringkasanKepalaTu' => ['menunggu' => $menunggu],
        ]);
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
                'mitra_industri' => MitraIndustri::query()->count(),
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
        $persetujuanDisposisi = Disposisi::query()
            ->whereIn('status_persetujuan', ['menunggu', 'disetujui'])
            ->whereHas('penerima', fn ($query) => $query->where('role', 'kepala_sekolah'))
            ->count();
        $suratKeluarDiajukan = SuratKeluar::query()->where('status', 'diajukan')->count();
        $suratKeluarMenunggu = SuratKeluar::query()
            ->where('status', 'diajukan')
            ->latest('id')
            ->limit(5)
            ->get();
        $disposisiAktif = Disposisi::query()
            ->where('status_persetujuan', 'disetujui')
            ->whereHas('penerima', fn ($query) => $query->where('role', '!=', 'kepala_sekolah'))
            ->where('status', '!=', 'selesai')->count();
        $totalTindakan = $persetujuanDisposisi + $suratKeluarDiajukan + $disposisiAktif;

        return view('dashboard', [
            'ringkasan' => [
                'surat_masuk' => $persetujuanDisposisi,
                'surat_keluar' => $suratKeluarDiajukan,
                'surat_keluar_menunggu' => $suratKeluarMenunggu,
                'disposisi_aktif' => $disposisiAktif,
                'mitra_industri' => MitraIndustri::query()->count(),
                'total_tindakan' => $totalTindakan,
                'persen_surat' => $totalTindakan > 0
                    ? round($persetujuanDisposisi / $totalTindakan * 100, 1) : 0,
                'persen_keluar' => $totalTindakan > 0
                    ? round($suratKeluarDiajukan / $totalTindakan * 100, 1) : 0,
                'persen_disposisi' => $totalTindakan > 0
                    ? round($disposisiAktif / $totalTindakan * 100, 1) : 0,
            ],
            'suratTerbaru' => Disposisi::query()
                ->with('surat')
                ->whereIn('status_persetujuan', ['menunggu', 'disetujui'])
                ->whereHas('penerima', fn ($query) => $query->where('role', 'kepala_sekolah'))
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

    private function wakaHumasDashboard(): View
    {
        return view('dashboard', [
            'pengajuanMenunggu' => Kerjasama::query()
                ->where('status_persetujuan', 'menunggu')->count()
                + MitraIndustri::query()
                    ->where('status_persetujuan', 'menunggu')->count(),
        ]);
    }
}

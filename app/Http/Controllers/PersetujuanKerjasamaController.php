<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\MitraIndustri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersetujuanKerjasamaController extends Controller
{
    public function index()
    {
        $mitras = MitraIndustri::query()
            ->where('status_persetujuan', 'menunggu')
            ->orderBy('nama_mitra')
            ->paginate(15, ['*'], 'halaman_mitra');

        return view('waka-humas.kerjasama', compact('mitras'));
    }

    public function approveMitra(MitraIndustri $mitra)
    {
        abort_unless($mitra->status_persetujuan === 'menunggu', 409);
        $mitra->update([
            'status_persetujuan' => 'disetujui',
            'catatan_persetujuan' => null,
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
        ]);

        return back()->with('status', 'Mitra industri disetujui.');
    }

    public function rejectMitra(Request $request, MitraIndustri $mitra)
    {
        abort_unless($mitra->status_persetujuan === 'menunggu', 409);
        $data = $request->validate([
            'catatan_persetujuan' => ['required', 'string', 'max:2000'],
        ], ['catatan_persetujuan.required' => 'Alasan penolakan wajib diisi.']);
        $mitra->update([
            'status_persetujuan' => 'ditolak',
            'catatan_persetujuan' => $data['catatan_persetujuan'],
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
        ]);

        return back()->with('status', 'Pengajuan mitra industri ditolak.');
    }

    public function approve(Kerjasama $kerjasama)
    {
        abort_unless($kerjasama->status_persetujuan === 'menunggu', 409);

        $kerjasama->update([
            'status_persetujuan' => 'disetujui',
            'catatan_persetujuan' => null,
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
        ]);

        return back()->with('status', 'Kerja sama disetujui dan dapat ditampilkan ke publik.');
    }

    public function reject(Request $request, Kerjasama $kerjasama)
    {
        abort_unless($kerjasama->status_persetujuan === 'menunggu', 409);

        $data = $request->validate([
            'catatan_persetujuan' => ['required', 'string', 'max:2000'],
        ], [
            'catatan_persetujuan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $kerjasama->update([
            'status_persetujuan' => 'ditolak',
            'catatan_persetujuan' => $data['catatan_persetujuan'],
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
        ]);

        return back()->with('status', 'Pengajuan kerja sama ditolak.');
    }

    public function download(Kerjasama $kerjasama)
    {
        abort_unless($kerjasama->dokumen_mou && Storage::disk('local')->exists($kerjasama->dokumen_mou), 404);

        return Storage::disk('local')->response(
            $kerjasama->dokumen_mou,
            'mou-'.$kerjasama->id.'.pdf',
            ['X-Content-Type-Options' => 'nosniff'],
            'inline'
        );
    }
}

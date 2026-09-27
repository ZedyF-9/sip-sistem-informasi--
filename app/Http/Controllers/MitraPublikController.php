<?php

namespace App\Http\Controllers;

use App\Models\MitraIndustri;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MitraPublikController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($data['q'] ?? '');

        $items = MitraIndustri::query()
            ->where('status', 'aktif')
            ->where('status_persetujuan', 'disetujui')
            ->select([
                'id', 'nama_mitra', 'bidang_usaha', 'deskripsi', 'logo',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search->where('nama_mitra', 'like', "%{$q}%")
                        ->orWhere('bidang_usaha', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_mitra')
            ->paginate(9)
            ->withQueryString();

        return view('publik.mitra.index', compact('items', 'q'));
    }

    public function show(int $mitra)
    {
        $mitra = MitraIndustri::query()
            ->where('status', 'aktif')
            ->where('status_persetujuan', 'disetujui')
            ->select([
                'id', 'nama_mitra', 'bidang_usaha',
                'deskripsi', 'website', 'logo',
            ])
            ->findOrFail($mitra);

        $kerjasamas = $mitra->kerjasamas()
            ->berlaku()
            ->select(['id', 'judul', 'tanggal_mulai', 'tanggal_selesai'])
            ->orderByDesc('tanggal_mulai')
            ->get();

        return view('publik.mitra.show', compact('mitra', 'kerjasamas'));
    }

    public function logo(int $mitra)
    {
        $mitra = MitraIndustri::query()
            ->where('status', 'aktif')
            ->where('status_persetujuan', 'disetujui')
            ->findOrFail($mitra);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($mitra->logo && $disk->exists($mitra->logo), 404);

        return response()->file($disk->path($mitra->logo), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store',
        ]);
    }
}

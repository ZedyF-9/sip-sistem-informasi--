<?php

namespace App\Http\Controllers;

use App\Http\Requests\MitraRequest;
use App\Models\MitraIndustri;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($data['q'] ?? '');

        $items = MitraIndustri::query()
            ->withCount('agendas')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search->where('nama_mitra', 'like', "%{$q}%")
                        ->orWhere('bidang_usaha', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_mitra')
            ->paginate(10)
            ->withQueryString();

        return view('mitra.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('mitra.create', ['mitra' => new MitraIndustri()]);
    }

    public function store(MitraRequest $request)
    {
        $data = $request->validated();
        unset($data['logo']);
        $data['status_persetujuan'] = 'menunggu';
        $data['catatan_persetujuan'] = null;

        $path = $this->storeLogo($request);

        if ($path) {
            $data['logo'] = $path;
        }

        try {
            $mitra = MitraIndustri::create($data);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()->route('tu.mitra.show', $mitra)
            ->with('status', 'Mitra berhasil ditambahkan dan menunggu persetujuan Waka Humas.');
    }

    public function show(MitraIndustri $mitra)
    {
        $agendas = $mitra->agendas()
            ->with('pembuat')
            ->orderBy('tanggal')
            ->orderBy('waktu')
            ->orderBy('id')
            ->paginate(10);

        return view('mitra.show', compact('mitra', 'agendas'));
    }

    public function edit(MitraIndustri $mitra)
    {
        return view('mitra.edit', compact('mitra'));
    }

    public function update(MitraRequest $request, MitraIndustri $mitra)
    {
        $data = $request->validated();
        unset($data['logo']);
        $data['status_persetujuan'] = 'menunggu';
        $data['catatan_persetujuan'] = null;
        $data['disetujui_oleh'] = null;
        $data['disetujui_pada'] = null;

        $logoLama = $mitra->logo;
        $path = $this->storeLogo($request);

        if ($path) {
            $data['logo'] = $path;
        }

        try {
            $mitra->update($data);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        if ($path && $logoLama && $logoLama !== $path) {
            Storage::disk('local')->delete($logoLama);
        }

        return redirect()->route('tu.mitra.show', $mitra)
            ->with('status', 'Data mitra diperbarui.');
    }

    public function logo(MitraIndustri $mitra)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($mitra->logo && $disk->exists($mitra->logo), 404);

        return response()->file($disk->path($mitra->logo), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function storeLogo(MitraRequest $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $path = $request->file('logo')->store('mitra-logo', 'local');

        if (! $path) {
            throw ValidationException::withMessages([
                'logo' => 'Logo gagal disimpan.',
            ]);
        }

        return $path;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($data['q'] ?? '');

        $kegiatans = Kegiatan::query()
            ->withCount('sertifikats')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('kegiatan.index', compact('kegiatans', 'q'));
    }

    public function create()
    {
        return view('kegiatan.form', [
            'kegiatan' => new Kegiatan(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $kegiatan = new Kegiatan($data);
        $kegiatan->created_by = $request->user()->id;
        $kegiatan->save();

        return redirect()
            ->route('tu.kegiatan.show', $kegiatan)
            ->with('status', 'Kegiatan berhasil dibuat.');
    }

    public function show(Kegiatan $kegiatan)
    {
        $sertifikats = $kegiatan->sertifikats()
            ->latest('id')
            ->paginate(10);

        return view(
            'kegiatan.show',
            compact('kegiatan', 'sertifikats')
        );
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('kegiatan.form', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($kegiatan, $data) {
            $target = Kegiatan::query()
                ->lockForUpdate()
                ->findOrFail($kegiatan->id);

            if ($target->sertifikats()->exists()) {
                throw ValidationException::withMessages([
                    'kegiatan' => (
                        'Kegiatan sudah memiliki sertifikat. '
                        .'Data kegiatan tidak dapat diubah.'
                    ),
                ]);
            }

            $target->update($data);
        });

        return redirect()
            ->route('tu.kegiatan.show', $kegiatan)
            ->with('status', 'Kegiatan diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'lokasi' => ['required', 'string', 'max:150'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
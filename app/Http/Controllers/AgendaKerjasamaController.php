<?php

namespace App\Http\Controllers;

use App\Models\AgendaKerjasama;
use App\Models\Kerjasama;
use Illuminate\Http\Request;

class AgendaKerjasamaController extends Controller
{
    public function show(Kerjasama $kerjasama)
    {
        $kerjasama->load([
            'mitra',
            'agendas' => fn ($query) => $query
                ->with('pembuat')
                ->orderBy('tanggal')
                ->orderBy('waktu')
                ->orderBy('id'),
        ]);

        $routePrefix = auth()->user()->role === 'waka_humas'
            ? 'waka-humas.kerjasama'
            : 'tu.kerjasama';
        $backUrl = auth()->user()->role === 'waka_humas'
            ? route('waka-humas.kerjasama.index')
            : route('tu.mitra.show', $kerjasama->mitra_industri_id);

        return view('kerjasama.show', compact('kerjasama', 'routePrefix', 'backUrl'));
    }

    public function store(Request $request, Kerjasama $kerjasama)
    {
        abort_unless($kerjasama->status_persetujuan === 'disetujui', 409);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'waktu' => ['nullable', 'date_format:H:i'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['kerjasama_id'] = $kerjasama->id;
        $data['created_by'] = $request->user()->id;
        AgendaKerjasama::create($data);

        $routePrefix = $request->user()->role === 'waka_humas'
            ? 'waka-humas.kerjasama'
            : 'tu.kerjasama';

        return redirect()
            ->route($routePrefix.'.show', $kerjasama)
            ->with('status', 'Agenda kerja sama berhasil ditambahkan.');
    }
}

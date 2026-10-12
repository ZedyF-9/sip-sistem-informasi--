<?php

namespace App\Http\Controllers;

use App\Models\MitraIndustri;
use Illuminate\Http\Request;

class AgendaMitraController extends Controller
{
    public function store(Request $request, MitraIndustri $mitra)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'waktu' => ['nullable', 'date_format:H:i'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['created_by'] = $request->user()->id;
        $mitra->agendas()->create($data);

        return redirect()->route('tu.mitra.show', $mitra)
            ->with('status', 'Agenda mitra berhasil ditambahkan.');
    }
}

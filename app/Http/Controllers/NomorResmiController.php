<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Services\FinalisasiSuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NomorResmiController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in(['disetujui', 'dikirim']),
            ],
        ]);

        $q = trim($data['q'] ?? '');
        $status = $data['status'] ?? 'disetujui';

        $surats = SuratKeluar::query()
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search
                        ->where('perihal', 'like', "%{$q}%")
                        ->orWhere('tujuan', 'like', "%{$q}%")
                        ->orWhere('nomor_surat', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'nomor-resmi.index',
            compact('surats', 'q', 'status')
        );
    }

    public function store(
        Request $request,
        SuratKeluar $suratKeluar,
        FinalisasiSuratKeluar $service
    ) {
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:1'],
        ]);

        $service->proses(
            $suratKeluar->id,
            $request->user(),
            (int) $data['version']
        );

        return redirect()
            ->route('tu.nomor-resmi.index')
            ->with(
                'status',
                'Nomor dan PDF final siap. Unduh untuk memeriksa hasilnya.'
            );
    }

    public function download(
        SuratKeluar $suratKeluar,
        FinalisasiSuratKeluar $service
    ) {
        return $service->download($suratKeluar);
    }
}
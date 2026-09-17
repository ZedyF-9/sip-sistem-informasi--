<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NomorResmiController extends Controller
{
    public function index()
    {
        $surats = SuratKeluar::query()
            ->where('status', 'disetujui')
            ->whereNull('nomor_surat')
            ->latest('id')
            ->paginate(15);

        return view('nomor-resmi.index', compact('surats'));
    }

    public function store(Request $request, SuratKeluar $suratKeluar)
    {
        $request->merge([
            'nomor_surat' => trim(
                (string) $request->input('nomor_surat')
            ),
        ]);

        $data = $request->validate([
            'nomor_surat' => [
                'required',
                'string',
                'max:100',
                Rule::unique('surat_keluars', 'nomor_surat'),
            ],
            'version' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $suratKeluar, $data) {
            $target = SuratKeluar::query()
                ->lockForUpdate()
                ->findOrFail($suratKeluar->id);

            if (
                $target->status !== 'disetujui'
                || $target->nomor_surat !== null
            ) {
                throw ValidationException::withMessages([
                    'nomor_surat' => (
                        'Surat harus sudah disetujui dan belum bernomor.'
                    ),
                ]);
            }

            if ((int) $target->version !== (int) $data['version']) {
                throw ValidationException::withMessages([
                    'version' => 'Data berubah. Muat ulang halaman.',
                ]);
            }

            $target->nomor_surat = $data['nomor_surat'];
            $target->version++;
            $target->save();

            DB::table('riwayat_surat_keluars')->insert([
                'surat_keluar_id' => $target->id,
                'user_id' => $request->user()->id,
                'aksi' => 'nomor',
                'status_sebelum' => $target->status,
                'status_sesudah' => $target->status,
                'catatan' => 'Nomor resmi dicatat oleh TU.',
                'file_surat' => $target->file_surat,
                'snapshot' => json_encode(
                    $target->getAttributes(),
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('tu.nomor-resmi.index')
            ->with('status', 'Nomor resmi surat berhasil dicatat.');
    }
}
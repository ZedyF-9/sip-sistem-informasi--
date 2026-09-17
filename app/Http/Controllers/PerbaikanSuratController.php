<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PerbaikanSuratController extends Controller
{
    public function index()
    {
        $surats = SuratMasuk::query()
            ->where('status', 'menunggu_verifikasi')
            ->whereNull('diteruskan_at')
            ->latest('id')
            ->paginate(15);

        return view('perbaikan-surat.index', compact('surats'));
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $this->pastikanBoleh($suratMasuk);

        return view('perbaikan-surat.edit', [
            'surat' => $suratMasuk,
            'versi' => $this->versi($suratMasuk),
        ]);
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $fisik = $suratMasuk->metode_pengiriman === 'fisik';

        $data = $request->validate([
            'versi' => ['required', 'string', 'size:64'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'jenis_surat' => ['required', 'string', 'max:100'],
            'sifat_surat' => [
                'required',
                Rule::in(['biasa', 'penting', 'rahasia']),
            ],
            'asal_surat' => ['required', 'string', 'max:255'],
            'nama_pengirim' => ['required', 'string', 'max:255'],
            'email_pengirim' => ['nullable', 'email', 'max:255'],
            'perihal' => ['required', 'string', 'max:255'],
            'nomor_agenda' => [
                $fisik ? 'required' : 'nullable',
                'string',
                'max:100',
                Rule::unique('surat_masuks', 'nomor_agenda')
                    ->ignore($suratMasuk->id),
            ],
            'lokasi_arsip_fisik' => [
                $fisik ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_diterima' => [
                'required',
                'date',
                'after_or_equal:tanggal_surat',
                'before_or_equal:today',
            ],
            'file_surat' => [
                'nullable',
                'file',
                'mimes:pdf',
                'extensions:pdf',
                'max:5120',
            ],
            'alasan' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $path = null;

        try {
            DB::transaction(function () use (
                $request,
                $suratMasuk,
                $data,
                &$path
            ) {
                $target = SuratMasuk::query()
                    ->lockForUpdate()
                    ->findOrFail($suratMasuk->id);

                $this->pastikanBoleh($target);

                if (! hash_equals($this->versi($target), $data['versi'])) {
                    throw ValidationException::withMessages([
                        'surat' => (
                            'Data sudah berubah. Buka kembali formulir '
                            .'sebelum menyimpan.'
                        ),
                    ]);
                }

                $sebelum = $target->getAttributes();

                $perubahan = collect($data)->except([
                    'versi',
                    'file_surat',
                    'alasan',
                ])->all();

                if ($request->hasFile('file_surat')) {
                    $path = $request->file('file_surat')
                        ->store('surat-masuk', 'local');

                    if (! $path) {
                        throw new RuntimeException('PDF gagal disimpan.');
                    }

                    $perubahan['file_surat'] = $path;
                }

                $target->fill($perubahan);
                $target->save();

                DB::table('riwayat_surat_masuks')->insert([
                    'surat_masuk_id' => $target->id,
                    'user_id' => $request->user()->id,
                    'aksi' => 'perbaikan',
                    'status_sebelum' => $target->status,
                    'status_sesudah' => $target->status,
                    'catatan' => $data['alasan']."\n"
                        .json_encode([
                            'sebelum' => $sebelum,
                            'sesudah' => $target->getAttributes(),
                        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('tu.surat-masuk.show', $suratMasuk)
            ->with(
                'status',
                'Data diperbaiki. Surat tetap menunggu verifikasi.'
            );
    }

    private function pastikanBoleh(SuratMasuk $surat): void
    {
        if (
            $surat->status !== 'menunggu_verifikasi'
            || $surat->diteruskan_at !== null
        ) {
            throw ValidationException::withMessages([
                'surat' => (
                    'Perbaikan hanya tersedia untuk surat '
                    .'yang masih menunggu verifikasi dan belum diteruskan.'
                ),
            ]);
        }
    }

    private function versi(SuratMasuk $surat): string
    {
        $attributes = $surat->getRawOriginal();
        ksort($attributes);

        return hash(
            'sha256',
            json_encode($attributes, JSON_THROW_ON_ERROR)
        );
    }
}
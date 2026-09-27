<?php

namespace App\Http\Middleware;

use App\Models\SuratKeluar;
use App\Services\FinalisasiSuratKeluar;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuratKeluarFinal
{
    public function handle(Request $request, Closure $next): Response
    {
        $parameter = $request->route('suratKeluar')
            ?? $request->route('surat_keluar');

        if ($parameter === null) {
            return $next($request);
        }

        $surat = $parameter instanceof SuratKeluar
            ? $parameter
            : SuratKeluar::query()->findOrFail($parameter);

        $service = app(FinalisasiSuratKeluar::class);
        $aksi = $request->input('aksi');

        if (
            in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)
            && $aksi === 'nomor'
        ) {
            $data = $request->validate([
                'version' => ['required', 'integer', 'min:1'],
            ]);

            $service->proses(
                $surat->id,
                $request->user(),
                (int) $data['version']
            );

            return redirect()
                ->route('tu.nomor-resmi.index')
                ->with('status', 'Nomor dan PDF final berhasil dibuat.');
        }

        if (
            in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)
            && $aksi === 'kirim'
            && ! $service->siap($surat)
        ) {
            throw ValidationException::withMessages([
                'pdf' => (
                    'Buat PDF final melalui menu Nomor Resmi '
                    .'sebelum mencatat pengiriman.'
                ),
            ]);
        }

        $method = $request->route()->getActionMethod();

        if (
            $request->isMethod('GET')
            && $method === 'download'
            && in_array($surat->status, ['disetujui', 'dikirim'], true)
        ) {
            return $service->download($surat);
        }

        return $next($request);
    }
}
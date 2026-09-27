<?php

namespace App\Http\Controllers;

use App\Http\Requests\KerjasamaRequest;
use App\Models\Kerjasama;
use App\Models\MitraIndustri;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class KerjasamaController extends Controller
{
    public function create(MitraIndustri $mitra)
    {
        return view('kerjasama.form', [
            'mitra' => $mitra,
            'kerjasama' => new Kerjasama(),
        ]);
    }

    public function store(KerjasamaRequest $request, MitraIndustri $mitra)
    {
        $data = $request->validated();
        unset($data['dokumen_mou']);

        $data['mitra_industri_id'] = $mitra->id;
        $data['status_persetujuan'] = 'menunggu';
        $data['catatan_persetujuan'] = null;

        $path = $this->storeDocument($request);

        if ($path) {
            $data['dokumen_mou'] = $path;
        }

        try {
            Kerjasama::create($data);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()->route('tu.mitra.show', $mitra)
            ->with('status', 'Kerja sama berhasil ditambahkan.');
    }

    public function edit(Kerjasama $kerjasama)
    {
        return view('kerjasama.form', [
            'mitra' => $kerjasama->mitra,
            'kerjasama' => $kerjasama,
        ]);
    }

    public function update(
        KerjasamaRequest $request,
        Kerjasama $kerjasama
    ) {
        $data = $request->validated();
        unset($data['dokumen_mou']);
        $data['status_persetujuan'] = 'menunggu';
        $data['catatan_persetujuan'] = null;
        $data['disetujui_oleh'] = null;
        $data['disetujui_pada'] = null;

        $path = $this->storeDocument($request);

        if ($path) {
            $data['dokumen_mou'] = $path;
        }

        try {
            $kerjasama->update($data);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()->route('tu.mitra.show', $kerjasama->mitra_industri_id)
            ->with('status', 'Kerja sama diperbarui.');
    }

    public function download(Kerjasama $kerjasama)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless(
            $kerjasama->dokumen_mou &&
            $disk->exists($kerjasama->dokumen_mou),
            404,
            'Dokumen MoU tidak ditemukan.'
        );

        return $disk->download(
            $kerjasama->dokumen_mou,
            'mou-'.$kerjasama->id.'.pdf',
            ['X-Content-Type-Options' => 'nosniff']
        );
    }

    private function storeDocument(KerjasamaRequest $request): ?string
    {
        if (! $request->hasFile('dokumen_mou')) {
            return null;
        }

        $path = $request->file('dokumen_mou')
            ->store('dokumen-mou', 'local');

        if (! $path) {
            throw ValidationException::withMessages([
                'dokumen_mou' => 'Dokumen gagal disimpan.',
            ]);
        }

        return $path;
    }
}

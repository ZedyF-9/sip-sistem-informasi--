<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratKeluarRequest;
use App\Models\SuratKeluar;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => [
                'nullable',
                Rule::in(['draf', 'diajukan', 'revisi', 'disetujui', 'dikirim']),
            ],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $data['status'] ?? '';
        $q = trim($data['q'] ?? '');

        $items = SuratKeluar::query()
            ->when(
                $request->user()->role === 'kepala_sekolah',
                fn ($query) => $query->where('status', '!=', 'draf')
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search->where('perihal', 'like', "%{$q}%")
                        ->orWhere('tujuan', 'like', "%{$q}%")
                        ->orWhere('nomor_surat', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('surat-keluar.index', compact('items', 'status', 'q'));
    }

    public function create()
    {
        return view('surat-keluar.create', [
            'surat' => new SuratKeluar(),
        ]);
    }

    public function store(StoreSuratKeluarRequest $request)
    {
        $data = $request->validated();
        unset($data['version']);

        $path = $request->file('file_surat')
            ->store('surat-keluar', 'local');

        if (! $path) {
            throw ValidationException::withMessages([
                'file_surat' => 'PDF gagal disimpan.',
            ]);
        }

        $data['file_surat'] = $path;

        try {
            $surat = DB::transaction(function () use ($data, $request) {
                $surat = new SuratKeluar($data);
                $surat->created_by = $request->user()->id;
                $surat->status = 'draf';
                $surat->version = 1;
                $surat->save();

                $this->history($surat, $request->user()->id, 'buat', null);

                return $surat;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('surat-keluar.show', $surat)
            ->with('status', 'Draf surat keluar dibuat.');
    }

    public function show(Request $request, SuratKeluar $suratKeluar)
    {
        $this->allowRead($request, $suratKeluar);

        $suratKeluar->load(['pembuat', 'penyetuju']);

        $riwayats = DB::table('riwayat_surat_keluars as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.surat_keluar_id', $suratKeluar->id)
            ->select('r.*', 'u.name as petugas')
            ->orderByDesc('r.id')
            ->get();

        return view('surat-keluar.show', [
            'surat' => $suratKeluar,
            'riwayats' => $riwayats,
        ]);
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        abort_unless(
            in_array($suratKeluar->status, ['draf', 'revisi'], true),
            403,
            'Surat tidak dapat diedit pada status ini.'
        );

        return view('surat-keluar.edit', ['surat' => $suratKeluar]);
    }

    public function update(
        StoreSuratKeluarRequest $request,
        SuratKeluar $suratKeluar
    ) {
        $data = $request->validated();
        $version = (int) $data['version'];
        unset($data['version'], $data['file_surat']);

        $path = null;

        if ($request->hasFile('file_surat')) {
            $path = $request->file('file_surat')
                ->store('surat-keluar', 'local');

            if (! $path) {
                throw ValidationException::withMessages([
                    'file_surat' => 'PDF gagal disimpan.',
                ]);
            }

            $data['file_surat'] = $path;
        }

        try {
            DB::transaction(function () use (
                $request, $suratKeluar, $data, $version
            ) {
                $surat = SuratKeluar::query()
                    ->lockForUpdate()->findOrFail($suratKeluar->id);

                $this->checkVersion($surat, $version);

                if (! in_array($surat->status, ['draf', 'revisi'], true)) {
                    $this->fail('Surat tidak dapat diedit pada status ini.');
                }

                $sebelum = $surat->status;
                $surat->fill($data);
                $surat->version++;
                $surat->save();

                $this->history(
                    $surat,
                    $request->user()->id,
                    'edit',
                    $sebelum,
                    'Data atau berkas draf diperbarui.'
                );
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('status', 'Draf diperbarui. Ajukan kembali jika sudah siap.');
    }

    public function action(Request $request, SuratKeluar $suratKeluar)
    {
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:1'],
            'aksi' => [
                'required',
                Rule::in(['ajukan', 'setujui', 'revisi', 'nomor', 'kirim']),
            ],
            'catatan' => [
                'nullable',
                'required_if:aksi,revisi',
                'string',
                'max:2000',
            ],
            'metode_pengiriman' => [
                'nullable',
                'required_if:aksi,kirim',
                Rule::in(['langsung', 'pos', 'email', 'lainnya']),
            ],
            'catatan_pengiriman' => [
                'nullable',
                'required_if:aksi,kirim',
                'string',
                'max:2000',
            ],
        ], [
            'catatan.required_if' => 'Alasan revisi wajib diisi.',
            'catatan_pengiriman.required_if' =>
                'Catatan pengiriman wajib diisi.',
        ]);

        $tuActions = ['ajukan', 'nomor', 'kirim'];

        $requiredRole = in_array($data['aksi'], $tuActions, true)
            ? 'koordinator_tu'
            : 'kepala_sekolah';

        abort_unless($request->user()->role === $requiredRole, 403);

        DB::transaction(function () use ($request, $suratKeluar, $data) {
            $surat = SuratKeluar::query()
                ->lockForUpdate()->findOrFail($suratKeluar->id);

            $this->checkVersion($surat, (int) $data['version']);

            $sebelum = $surat->status;
            $aksi = $data['aksi'];
            $catatan = $data['catatan'] ?? null;

            if (
                in_array($aksi, ['ajukan', 'setujui', 'kirim'], true)
                && ! Storage::disk('local')->exists($surat->file_surat)
            ) {
                $this->fail('PDF tidak tersedia. Periksa berkas surat.');
            }

            switch ($aksi) {
                case 'ajukan':
                    if (! in_array($surat->status, ['draf', 'revisi'], true)) {
                        $this->fail('Hanya draf atau revisi yang dapat diajukan.');
                    }

                    $surat->status = 'diajukan';
                    break;

                case 'setujui':
                    if ($surat->status !== 'diajukan') {
                        $this->fail('Surat belum diajukan atau sudah diputuskan.');
                    }

                    $surat->status = 'disetujui';
                    $surat->approved_by = $request->user()->id;
                    $surat->approved_at = now();
                    $surat->catatan_persetujuan = $catatan;
                    break;

                case 'revisi':
                    if ($surat->status !== 'diajukan') {
                        $this->fail('Hanya surat diajukan yang dapat direvisi.');
                    }

                    $surat->status = 'revisi';
                    $surat->catatan_persetujuan = $catatan;
                    break;

                case 'nomor':
                    if ($surat->status !== 'disetujui' || $surat->nomor_surat) {
                        $this->fail('Surat harus disetujui dan belum bernomor.');
                    }

                    $tahun = (int) $surat->tanggal_surat->format('Y');

                    DB::table('nomor_surat_tahunans')->insertOrIgnore([
                        'tahun' => $tahun,
                        'nomor_terakhir' => 0,
                    ]);

                    $sequence = DB::table('nomor_surat_tahunans')
                        ->where('tahun', $tahun)
                        ->lockForUpdate()
                        ->first();

                    $nomor = $sequence->nomor_terakhir + 1;

                    DB::table('nomor_surat_tahunans')
                        ->where('tahun', $tahun)
                        ->update(['nomor_terakhir' => $nomor]);

                    $surat->nomor_surat = sprintf(
                        'S-%04d/SMKN4/%d',
                        $nomor,
                        $tahun
                    );

                    $catatan = 'Nomor ditetapkan: '.$surat->nomor_surat;
                    break;

                case 'kirim':
                    if (
                        $surat->status !== 'disetujui'
                        || ! $surat->nomor_surat
                    ) {
                        $this->fail('Surat harus disetujui dan diberi nomor.');
                    }

                    $surat->status = 'dikirim';
                    $surat->dikirim_at = now();
                    $surat->metode_pengiriman = $data['metode_pengiriman'];
                    $surat->catatan_pengiriman = $data['catatan_pengiriman'];
                    $catatan = $data['catatan_pengiriman'];
                    break;
            }

            $surat->version++;
            $surat->save();

            $this->history(
                $surat,
                $request->user()->id,
                $aksi,
                $sebelum,
                $catatan
            );
        });

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('status', 'Tindakan berhasil disimpan.');
    }

    public function download(Request $request, SuratKeluar $suratKeluar)
    {
        $this->allowRead($request, $suratKeluar);

        return $this->file(
            $suratKeluar->file_surat,
            'surat-keluar-'.$suratKeluar->id.'.pdf'
        );
    }

    public function downloadHistory(
        Request $request,
        SuratKeluar $suratKeluar,
        int $riwayat
    ) {
        $this->allowRead($request, $suratKeluar);

        $record = DB::table('riwayat_surat_keluars')
            ->where('id', $riwayat)
            ->where('surat_keluar_id', $suratKeluar->id)
            ->first();

        abort_unless($record, 404);

        return $this->file(
            $record->file_surat,
            'surat-keluar-'.$suratKeluar->id.'-riwayat-'.$riwayat.'.pdf'
        );
    }

    private function allowRead(Request $request, SuratKeluar $surat): void
    {
        abort_unless(
            in_array(
                $request->user()->role,
                ['koordinator_tu', 'kepala_sekolah'],
                true
            ),
            403
        );

        abort_if(
            $request->user()->role === 'kepala_sekolah'
            && $surat->status === 'draf',
            403
        );
    }

    private function checkVersion(SuratKeluar $surat, int $version): void
    {
        if ($surat->version !== $version) {
            $this->fail('Data sudah berubah. Muat ulang halaman sebelum melanjutkan.');
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['aksi' => $message]);
    }

    private function file(string $path, string $name)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        return $disk->download(
            $path,
            $name,
            ['X-Content-Type-Options' => 'nosniff']
        );
    }

    private function history(
        SuratKeluar $surat,
        int $userId,
        string $aksi,
        ?string $sebelum,
        ?string $catatan = null
    ): void {
        DB::table('riwayat_surat_keluars')->insert([
            'surat_keluar_id' => $surat->id,
            'user_id' => $userId,
            'aksi' => $aksi,
            'status_sebelum' => $sebelum,
            'status_sesudah' => $surat->status,
            'catatan' => $catatan,
            'file_surat' => $surat->file_surat,
            'snapshot' => json_encode($surat->only([
                'nomor_surat', 'perihal', 'tujuan', 'jenis_surat',
                'sifat_surat', 'tanggal_surat', 'version',
            ]), JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
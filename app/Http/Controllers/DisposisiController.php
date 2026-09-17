<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDisposisiRequest;
use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class DisposisiController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => [
                'nullable',
                Rule::in(['baru', 'diproses', 'selesai']),
            ],
        ]);

        $status = $data['status'] ?? '';

        $items = Disposisi::query()
            ->with(['surat', 'penerima'])
            ->when(
                $request->user()->role === 'penerima_disposisi',
                fn ($query) => $query->where(
                    'penerima_id',
                    $request->user()->id
                )
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('disposisi.index', compact('items', 'status'));
    }

    public function create()
    {
        $surats = SuratMasuk::query()
            ->whereNotNull('diteruskan_at')
            ->whereIn('status', ['diverifikasi', 'disposisi'])
            ->latest('id')
            ->get();

        $penerimas = User::query()
            ->where('role', 'penerima_disposisi')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('disposisi.create', compact('surats', 'penerimas'));
    }

    public function store(StoreDisposisiRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $surat = SuratMasuk::query()
                ->lockForUpdate()
                ->findOrFail($data['surat_masuk_id']);

            if (
                ! $surat->diteruskan_at ||
                ! in_array($surat->status, ['diverifikasi', 'disposisi'], true)
            ) {
                throw ValidationException::withMessages([
                    'surat_masuk_id' =>
                        'Surat belum diteruskan atau sudah selesai.',
                ]);
            }

            $penerimaIds = array_map('intval', $data['penerima_ids']);

            $penerimas = User::query()
                ->whereIn('id', $penerimaIds)
                ->where('role', 'penerima_disposisi')
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            if ($penerimas->count() !== count($penerimaIds)) {
                throw ValidationException::withMessages([
                    'penerima_ids' => 'Ada penerima yang sudah tidak aktif.',
                ]);
            }

            $duplikat = Disposisi::query()
                ->where('surat_masuk_id', $surat->id)
                ->whereIn('penerima_id', $penerimaIds)
                ->exists();

            if ($duplikat) {
                throw ValidationException::withMessages([
                    'penerima_ids' =>
                        'Salah satu penerima sudah mendapat disposisi surat ini. Pilih penerima lain.',
                ]);
            }

            $sebelum = $surat->status;

            foreach ($penerimaIds as $penerimaId) {
                Disposisi::create([
                    'surat_masuk_id' => $surat->id,
                    'pemberi_id' => $request->user()->id,
                    'penerima_id' => $penerimaId,
                    'instruksi' => $data['instruksi'],
                    'prioritas' => $data['prioritas'],
                    'tanggal_disposisi' => now()->toDateString(),
                    'batas_waktu' => $data['batas_waktu'] ?? null,
                    'status' => 'baru',
                ]);
            }

            $surat->status = 'disposisi';
            $surat->save();

            $this->recordHistory(
                $surat,
                $request->user()->id,
                'disposisi',
                $sebelum,
                'Disposisi kepada: '.$penerimas->pluck('name')->implode(', ')
            );
        });

        return redirect()
            ->route('disposisi.index')
            ->with('status', 'Disposisi berhasil dibuat.');
    }

    public function show(Request $request, Disposisi $disposisi)
    {
        $this->authorizeRead($request, $disposisi);

        $disposisi->load(['surat', 'pemberi', 'penerima']);

        $riwayats = DB::table('tindak_lanjuts as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('t.disposisi_id', $disposisi->id)
            ->select('t.*', 'u.name as nama_petugas')
            ->orderByDesc('t.id')
            ->get();

        return view('disposisi.show', compact('disposisi', 'riwayats'));
    }

    public function followUp(Request $request, Disposisi $disposisi)
    {
        abort_unless(
            $request->user()->role === 'penerima_disposisi'
            && (int) $disposisi->penerima_id === (int) $request->user()->id,
            403
        );

        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['diproses', 'selesai']),
            ],
            'catatan' => ['required', 'string', 'max:5000'],
            'file_lampiran' => [
                'nullable',
                'file',
                'mimes:pdf',
                'extensions:pdf',
                'max:5120',
            ],
        ], [
            'catatan.required' => 'Catatan tindak lanjut wajib diisi.',
            'file_lampiran.max' => 'Lampiran maksimal 5 MB.',
            'file_lampiran.mimes' => 'Lampiran harus berupa PDF.',
        ]);

        $path = null;

        if ($request->hasFile('file_lampiran')) {
            $path = $request->file('file_lampiran')
                ->store('tindak-lanjut', 'local');

            if (! $path) {
                throw ValidationException::withMessages([
                    'file_lampiran' => 'Lampiran gagal disimpan.',
                ]);
            }
        }

        try {
            DB::transaction(function () use ($request, $disposisi, $data, $path) {
                $surat = SuratMasuk::query()
                    ->lockForUpdate()
                    ->findOrFail($disposisi->surat_masuk_id);

                $item = Disposisi::query()
                    ->lockForUpdate()
                    ->findOrFail($disposisi->id);

                abort_unless(
                    (int) $item->penerima_id === (int) $request->user()->id,
                    403
                );

                if ($item->status === 'selesai') {
                    throw ValidationException::withMessages([
                        'status' => 'Disposisi ini sudah selesai.',
                    ]);
                }

                if ($item->status === 'baru' && $data['status'] !== 'diproses') {
                    throw ValidationException::withMessages([
                        'status' => 'Mulai dengan status Diproses terlebih dahulu.',
                    ]);
                }

                if ($surat->status !== 'disposisi') {
                    throw ValidationException::withMessages([
                        'status' => 'Status surat tidak mengizinkan tindak lanjut.',
                    ]);
                }

                DB::table('tindak_lanjuts')->insert([
                    'disposisi_id' => $item->id,
                    'user_id' => $request->user()->id,
                    'catatan' => $data['catatan'],
                    'file_lampiran' => $path,
                    'status' => $data['status'],
                    'tanggal_tindak_lanjut' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $item->status = $data['status'];
                $item->save();

                $masihBerjalan = Disposisi::query()
                    ->where('surat_masuk_id', $surat->id)
                    ->where('status', '!=', 'selesai')
                    ->exists();

                if (! $masihBerjalan) {
                    $sebelum = $surat->status;
                    $surat->status = 'selesai';
                    $surat->save();

                    $this->recordHistory(
                        $surat,
                        $request->user()->id,
                        'selesai',
                        $sebelum,
                        'Seluruh disposisi untuk surat ini telah selesai.'
                    );
                }
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('disposisi.show', $disposisi)
            ->with('status', 'Tindak lanjut berhasil disimpan.');
    }

    public function downloadSurat(Request $request, Disposisi $disposisi)
    {
        $this->authorizeRead($request, $disposisi);

        $surat = $disposisi->surat;

        return $this->downloadFile(
            $surat->file_surat,
            $surat->nomor_registrasi.'.pdf'
        );
    }

    public function downloadLampiran(
        Request $request,
        Disposisi $disposisi,
        int $lampiran
    ) {
        $this->authorizeRead($request, $disposisi);

        $record = DB::table('tindak_lanjuts')
            ->where('id', $lampiran)
            ->where('disposisi_id', $disposisi->id)
            ->first();

        abort_unless($record && $record->file_lampiran, 404);

        return $this->downloadFile(
            $record->file_lampiran,
            'hasil-disposisi-'.$disposisi->id.'-'.$record->id.'.pdf'
        );
    }

    private function authorizeRead(Request $request, Disposisi $disposisi): void
    {
        $user = $request->user();

        $allowed = in_array(
            $user->role,
            ['koordinator_tu', 'kepala_sekolah'],
            true
        ) || (
            $user->role === 'penerima_disposisi'
            && (int) $disposisi->penerima_id === (int) $user->id
        );

        abort_unless($allowed, 403);
    }

    private function downloadFile(?string $path, string $name)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($path && $disk->exists($path), 404);

        return $disk->download(
            $path,
            $name,
            ['X-Content-Type-Options' => 'nosniff']
        );
    }

    private function recordHistory(
        SuratMasuk $surat,
        int $userId,
        string $aksi,
        string $sebelum,
        string $catatan
    ): void {
        DB::table('riwayat_surat_masuks')->insert([
            'surat_masuk_id' => $surat->id,
            'user_id' => $userId,
            'aksi' => $aksi,
            'status_sebelum' => $sebelum,
            'status_sesudah' => $surat->status,
            'catatan' => $catatan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
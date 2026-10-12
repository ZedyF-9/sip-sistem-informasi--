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
use Barryvdh\DomPDF\Facade\Pdf;

class DisposisiController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => [
                'nullable',
                Rule::in(['baru', 'diproses']),
            ],
        ]);

        $status = $data['status'] ?? '';
        $suratMenunggu = $request->user()->role === 'koordinator_tu'
            ? SuratMasuk::query()
                ->where('status', 'diverifikasi')
                ->whereDoesntHave('disposisis', fn ($query) => $query->where('status_persetujuan', '!=', 'ditolak'))
                ->latest('id')
                ->get()
            : collect();

        $items = Disposisi::query()
            ->with(['surat', 'penerima'])
            ->where('status', '!=', 'selesai')
            ->when(
                in_array($request->user()->role, ['penerima_disposisi', 'waka_humas'], true),
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

        return view('disposisi.index', compact('items', 'status', 'suratMenunggu'));
    }

    public function archive(Request $request)
    {
        $items = Disposisi::query()
            ->with(['surat', 'penerima'])
            ->where(function ($query) {
                $query->where('status', 'selesai')
                    ->orWhere('status_persetujuan', 'ditolak');
            })
            ->when(
                in_array($request->user()->role, ['penerima_disposisi', 'waka_humas'], true),
                fn ($query) => $query->where('penerima_id', $request->user()->id)
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('disposisi.archive', compact('items'));
    }

    public function create()
    {
        $surats = SuratMasuk::query()
            ->where('status', 'diverifikasi')
            ->whereDoesntHave('disposisis', fn ($query) => $query->where('status_persetujuan', '!=', 'ditolak'))
            ->latest('id')
            ->get();

        $kepalaSekolah = User::query()
            ->where('role', 'kepala_sekolah')->where('is_active', true)->first();

        return view('disposisi.create', compact('surats', 'kepalaSekolah'));
    }

    public function store(StoreDisposisiRequest $request)
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $data) {
            $surat = SuratMasuk::query()
                ->lockForUpdate()
                ->findOrFail($data['surat_masuk_id']);

            if ($surat->status !== 'diverifikasi') {
                throw ValidationException::withMessages([
                    'surat_masuk_id' =>
                        'Surat tidak tersedia untuk disposisi atau sudah didisposisikan.',
                ]);
            }

            $kepalaSekolah = User::query()->where('role', 'kepala_sekolah')
                ->where('is_active', true)->lockForUpdate()->first();

            if (! $kepalaSekolah) {
                throw ValidationException::withMessages([
                    'surat_masuk_id' => 'Akun kepala sekolah aktif belum tersedia untuk menerima persetujuan.',
                ]);
            }

            $duplikat = Disposisi::query()->where('surat_masuk_id', $surat->id)
                ->where('status_persetujuan', '!=', 'ditolak')
                ->exists();

            if ($duplikat) {
                throw ValidationException::withMessages([
                    'surat_masuk_id' => 'Surat ini sudah diajukan kepada kepala sekolah.',
                ]);
            }

            $sebelum = $surat->status;
            $pengajuanDitolak = Disposisi::query()
                ->where('surat_masuk_id', $surat->id)
                ->where('status_persetujuan', 'ditolak')
                ->lockForUpdate()
                ->first();
            $atribut = [
                'surat_masuk_id' => $surat->id,
                'pemberi_id' => $request->user()->id,
                'penerima_id' => $kepalaSekolah->id,
                'instruksi' => $data['catatan_pengajuan'] ?: 'Permohonan TU agar surat ini ditinjau dan didisposisikan oleh Kepala Sekolah.',
                'arahan' => [],
                'prioritas' => 'biasa',
                'tanggal_disposisi' => now()->toDateString(),
                'batas_waktu' => null,
                'status' => 'baru',
                'status_persetujuan' => 'menunggu',
                'disetujui_oleh' => null,
                'disetujui_at' => null,
                'catatan_persetujuan' => null,
            ];
            if ($pengajuanDitolak) {
                $pengajuanDitolak->update($atribut);
            } else {
                Disposisi::create($atribut);
            }

            $this->recordHistory(
                $surat,
                $request->user()->id,
                'disposisi',
                $sebelum,
                'TU mengajukan surat untuk disetujui dan didisposisikan oleh Kepala Sekolah.'
            );
        });

        return redirect()
            ->route('disposisi.index')
            ->with('status', 'Disposisi dikirim ke Kepala Sekolah dan menunggu persetujuan.');
    }

    public function approve(Request $request, Disposisi $disposisi)
    {
        abort_unless($request->user()->role === 'kepala_sekolah', 403);

        DB::transaction(function () use ($request, $disposisi) {
            $surat = SuratMasuk::query()->lockForUpdate()->findOrFail($disposisi->surat_masuk_id);
            $item = Disposisi::query()->lockForUpdate()->findOrFail($disposisi->id);
            abort_unless($item->status_persetujuan === 'menunggu', 409, 'Disposisi sudah ditinjau.');

            $item->status_persetujuan = 'disetujui';
            $item->disetujui_oleh = $request->user()->id;
            $item->disetujui_at = now();
            $item->save();
            $this->recordHistory($surat, $request->user()->id, 'disposisi_disetujui', $surat->status,
                'Kepala Sekolah menyetujui pengajuan TU dan akan menerbitkan lembar disposisi.');
        });

        return redirect()->route('disposisi.show', $disposisi)->with('status', 'Pengajuan disetujui. Silakan isi dan kirim lembar disposisi resmi kepada penerima.');
    }

    public function issue(Request $request, Disposisi $disposisi)
    {
        abort_unless($request->user()->role === 'kepala_sekolah', 403);
        $data = $request->validate([
            'penerima_ids' => ['required', 'array', 'min:1'],
            'penerima_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where(
                fn ($query) => $query->whereIn('role', ['penerima_disposisi', 'waka_humas'])->where('is_active', true)
            )],
            'instruksi' => ['required', 'string', 'max:5000'],
            'sifat_surat' => ['required', Rule::in(['biasa', 'rahasia'])],
            'arahan' => ['nullable', 'array'],
            'arahan.*' => ['string', Rule::in(['tanggapan_saran', 'proses_lanjut', 'koordinasi_konfirmasi', 'lainnya'])],
            'prioritas' => ['required', Rule::in(['biasa', 'segera', 'sangat_segera'])],
            'batas_waktu' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        DB::transaction(function () use ($request, $disposisi, $data) {
            $surat = SuratMasuk::query()->lockForUpdate()->findOrFail($disposisi->surat_masuk_id);
            $pengajuan = Disposisi::query()->lockForUpdate()->findOrFail($disposisi->id);
            abort_unless(
                $pengajuan->status_persetujuan === 'disetujui'
                && (int) $pengajuan->penerima_id === (int) $request->user()->id,
                409,
                'Pengajuan belum disetujui atau sudah diteruskan.'
            );

            $penerimaIds = array_map('intval', $data['penerima_ids']);
            $penerimas = User::query()->whereIn('id', $penerimaIds)->where('is_active', true)->lockForUpdate()->get();
            abort_unless($penerimas->count() === count(array_unique($penerimaIds)), 422, 'Penerima tidak aktif atau tidak sesuai.');
            $alreadyAssigned = Disposisi::query()->where('surat_masuk_id', $surat->id)
                ->whereIn('penerima_id', $penerimaIds)->exists();
            abort_if($alreadyAssigned, 409, 'Salah satu penerima sudah memiliki disposisi untuk surat ini.');

            foreach ($penerimas as $penerima) {
                Disposisi::create([
                    'surat_masuk_id' => $surat->id,
                    'pemberi_id' => $request->user()->id,
                    'penerima_id' => $penerima->id,
                    'instruksi' => $data['instruksi'],
                    'arahan' => $data['arahan'] ?? [],
                    'prioritas' => $data['prioritas'],
                    'tanggal_disposisi' => today(),
                    'batas_waktu' => $data['batas_waktu'] ?? null,
                    'status' => 'baru',
                    'status_persetujuan' => 'disetujui',
                    'disetujui_oleh' => $request->user()->id,
                    'disetujui_at' => now(),
                ]);
            }

            $pengajuan->status = 'selesai';
            $pengajuan->status_persetujuan = 'diteruskan';
            $pengajuan->save();
            $sebelum = $surat->status;
            $surat->sifat_surat = $data['sifat_surat'];
            $surat->status = 'disposisi';
            $surat->save();
            $this->recordHistory($surat, $request->user()->id, 'disposisi_diteruskan', $sebelum,
                'Kepala Sekolah mengirim disposisi kepada: '.$penerimas->pluck('name')->implode(', '));
        });

        return redirect()->route('disposisi.index')->with('status', 'Lembar disposisi resmi berhasil dikirim kepada penerima.');
    }

    public function reject(Request $request, Disposisi $disposisi)
    {
        abort_unless($request->user()->role === 'kepala_sekolah', 403);
        $data = $request->validate(['catatan_persetujuan' => ['required', 'string', 'min:5', 'max:2000']]);

        DB::transaction(function () use ($request, $disposisi, $data) {
            $surat = SuratMasuk::query()->lockForUpdate()->findOrFail($disposisi->surat_masuk_id);
            $item = Disposisi::query()->lockForUpdate()->findOrFail($disposisi->id);
            abort_unless($item->status_persetujuan === 'menunggu', 409, 'Disposisi sudah ditinjau.');
            $item->status_persetujuan = 'ditolak';
            $item->disetujui_oleh = $request->user()->id;
            $item->disetujui_at = now();
            $item->catatan_persetujuan = $data['catatan_persetujuan'];
            $item->status = 'selesai';
            $item->save();

            $sebelum = $surat->status;
            $surat->status = 'diverifikasi';
            $surat->save();
            $this->recordHistory($surat, $request->user()->id, 'disposisi_ditolak', $sebelum,
                'Kepala Sekolah menolak disposisi TU: '.$data['catatan_persetujuan']);
        });

        return redirect()->route('disposisi.index')->with('status', 'Pengajuan ditolak. Surat kembali tersedia untuk diperbaiki oleh TU.');
    }

    public function show(Request $request, Disposisi $disposisi)
    {
        $this->authorizeRead($request, $disposisi);

        $disposisi->load(['surat', 'pemberi', 'penerima', 'penyetuju']);
        $penerimas = collect();
        if ($request->user()->role === 'kepala_sekolah'
            && $disposisi->status_persetujuan === 'disetujui'
            && $disposisi->penerima->role === 'kepala_sekolah') {
            $penerimas = User::query()->whereIn('role', ['penerima_disposisi', 'waka_humas'])
                ->where('is_active', true)->orderBy('name')->get();
        }
        $riwayats = DB::table('tindak_lanjuts as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('t.disposisi_id', $disposisi->id)
            ->select('t.*', 'u.name as nama_petugas')
            ->orderByDesc('t.id')
            ->get();

        $logoKepri = base64_encode(file_get_contents(public_path('reference/kepri.jpg')));
        $logoSekolah = base64_encode(file_get_contents(public_path('reference/image.jpg')));

        return view('disposisi.show', compact(
            'disposisi', 'riwayats', 'logoKepri', 'logoSekolah', 'penerimas'
        ));
    }

    public function followUp(Request $request, Disposisi $disposisi)
    {
        $disposisi->loadMissing('penerima');
        abort_unless(
            in_array($request->user()->role, ['kepala_sekolah', 'penerima_disposisi', 'waka_humas'], true)
            && (int) $disposisi->penerima_id === (int) $request->user()->id
            && $disposisi->penerima->role !== 'kepala_sekolah'
            && $disposisi->status_persetujuan === 'disetujui',
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

    public function downloadSuratMasuk(Request $request, Disposisi $disposisi)
    {
        $this->authorizeRead($request, $disposisi);

        $surat = SuratMasuk::query()
            ->findOrFail($disposisi->surat_masuk_id);

        abort_unless(
            $surat->file_surat
            && str_starts_with($surat->file_surat, 'surat-masuk/'),
            404,
            'PDF surat masuk asli tidak ditemukan di arsip.'
        );

        return $this->downloadFile(
            $surat->file_surat,
            'surat-masuk-'.$surat->nomor_registrasi.'.pdf'
        );
    }

    public function downloadLembar(Request $request, Disposisi $disposisi)
    {
        $this->authorizeRead($request, $disposisi);
        $disposisi->load(['surat', 'pemberi', 'penerima', 'penyetuju']);
        abort_unless($disposisi->penerima->role !== 'kepala_sekolah', 404);
        $logoKepri = base64_encode(file_get_contents(public_path('reference/kepri.jpg')));
        $logoSekolah = base64_encode(file_get_contents(public_path('reference/image.jpg')));

        return Pdf::loadView('disposisi.lembar', compact(
            'disposisi', 'logoKepri', 'logoSekolah'
        ))
            ->setPaper('a4', 'landscape')
            ->download('lembar-disposisi-'.$disposisi->id.'.pdf');
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
            in_array($user->role, ['penerima_disposisi', 'waka_humas'], true)
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

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
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in(['dikirim', 'menunggu_kepala_tu', 'kembali_ke_tu', 'diajukan', 'disetujui', 'draf', 'revisi'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $data['status'] ?? '';
        $q = trim($data['q'] ?? '');

        $role = $request->user()->role;
        $items = SuratKeluar::query()
            ->when($role === 'kepala_tu', fn ($query) => $query->where('status', 'menunggu_kepala_tu'))
            ->when($role === 'kepala_sekolah', fn ($query) => $query->whereIn('status', ['diajukan', 'disetujui', 'dikirim']))
            ->when($role === 'koordinator_tu', fn ($query) => $query->whereIn('status', ['draf', 'revisi', 'kembali_ke_tu', 'diajukan', 'disetujui', 'dikirim']))
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
        $input = $request->validated();
        $jenis = $input['jenis_surat'];
        $dataSurat = collect($input)->except(['jenis_surat', 'tanggal_surat'])->all();
        [$tujuan, $perihal, $keterangan] = match ($jenis) {
            'Surat Undangan' => [
                $dataSurat['alamat_penerima'],
                'Undangan '.$dataSurat['nama_kegiatan'],
                $dataSurat['keperluan'],
            ],
            'Surat Perintah Tugas' => [
                $dataSurat['nama_penerima_tugas'].' — '.$dataSurat['jabatan_penerima'],
                'Surat Perintah Tugas',
                $dataSurat['uraian_tugas'],
            ],
            'Surat Keputusan' => [
                $dataSurat['nama_penerima_sk'].' — '.$dataSurat['jabatan_penerima_sk'],
                'Surat Keputusan '.$dataSurat['nama_tugas'],
                'Tahun Pelajaran '.$dataSurat['tahun_pelajaran'],
            ],
            default => [
                $dataSurat['sekolah_tujuan'],
                'Rekomendasi Mutasi/Pindah Siswa',
                'Rekomendasi mutasi '.$dataSurat['nama_siswa'].' ke '.$dataSurat['sekolah_tujuan'],
            ],
        };

        $data = [
            'tanggal_surat' => $input['tanggal_surat'],
            'jenis_surat' => $jenis,
            'tujuan' => $tujuan,
            'perihal' => $perihal,
            'keterangan' => $keterangan,
            'data_surat' => $dataSurat,
            'sifat_surat' => 'biasa',
        ];
        $path = null;
        try {
            $surat = DB::transaction(function () use ($data, $request, &$path) {
                $data['nomor_surat'] = null;
                $data['status'] = 'draf';

                $pdf = Pdf::loadView('surat-keluar.pdf', [
                    'surat' => (object) $data,
                    'logoKepri' => base64_encode(file_get_contents(public_path('reference/kepri.jpg'))),
                    'logoSekolah' => base64_encode(file_get_contents(public_path('reference/image.jpg'))),
                ])->output();
                $path = 'surat-keluar/'.uniqid('surat-', true).'.pdf';
                if (! Storage::disk('local')->put($path, $pdf)) {
                    throw ValidationException::withMessages([
                        'file_surat' => 'PDF gagal disimpan.',
                    ]);
                }
                $data['file_surat'] = $path;

                $surat = new SuratKeluar($data);
                $surat->created_by = $request->user()->id;
                $surat->status = 'draf';
                $surat->version = 1;
                $surat->save();

                $this->history($surat, $request->user()->id, 'buat_draf', null, 'Draf surat dibuat untuk diajukan ke Kepala TU.');

                return $surat;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('surat-keluar.show', $surat)
            ->with('status', 'Draf surat dibuat. Ajukan untuk pemeriksaan Kepala TU.');
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
        $input = $request->validated();
        $version = (int) ($input['version'] ?? 0);
        unset($input['version']);
        $jenis = $input['jenis_surat'];
        $dataSurat = collect($input)->except(['jenis_surat', 'tanggal_surat'])->all();
        [$tujuan, $perihal, $keterangan] = match ($jenis) {
            'Surat Undangan' => [$dataSurat['alamat_penerima'], 'Undangan '.$dataSurat['nama_kegiatan'], $dataSurat['keperluan']],
            'Surat Perintah Tugas' => [$dataSurat['nama_penerima_tugas'].' — '.$dataSurat['jabatan_penerima'], 'Surat Perintah Tugas', $dataSurat['uraian_tugas']],
            'Surat Keputusan' => [$dataSurat['nama_penerima_sk'].' — '.$dataSurat['jabatan_penerima_sk'], 'Surat Keputusan '.$dataSurat['nama_tugas'], 'Tahun Pelajaran '.$dataSurat['tahun_pelajaran']],
            default => [$dataSurat['sekolah_tujuan'], 'Rekomendasi Mutasi/Pindah Siswa', 'Rekomendasi mutasi '.$dataSurat['nama_siswa'].' ke '.$dataSurat['sekolah_tujuan']],
        };
        $data = [
            'tanggal_surat' => $input['tanggal_surat'], 'jenis_surat' => $jenis,
            'tujuan' => $tujuan, 'perihal' => $perihal, 'keterangan' => $keterangan,
            'data_surat' => $dataSurat, 'sifat_surat' => 'biasa',
            'nomor_surat' => null, 'status' => 'revisi',
        ];
        $path = 'surat-keluar/'.uniqid('surat-', true).'.pdf';
        $pdf = Pdf::loadView('surat-keluar.pdf', [
            'surat' => (object) $data,
            'logoKepri' => base64_encode(file_get_contents(public_path('reference/kepri.jpg'))),
            'logoSekolah' => base64_encode(file_get_contents(public_path('reference/image.jpg'))),
        ])->output();
        if (! Storage::disk('local')->put($path, $pdf)) {
            throw ValidationException::withMessages(['file_surat' => 'PDF gagal disimpan.']);
        }
        $oldPath = null;

        try {
            DB::transaction(function () use (
                $request, $suratKeluar, $data, $version, $path, &$oldPath
            ) {
                $surat = SuratKeluar::query()
                    ->lockForUpdate()->findOrFail($suratKeluar->id);

                $this->checkVersion($surat, $version);

                if (! in_array($surat->status, ['draf', 'revisi'], true)) {
                    $this->fail('Surat tidak dapat diedit pada status ini.');
                }

                $sebelum = $surat->status;
                $oldPath = $surat->file_surat;
                $surat->fill($data);
                $surat->file_surat = $path;
                $surat->version++;
                $surat->save();

                $this->history(
                    $surat,
                    $request->user()->id,
                    'revisi_tu',
                    $sebelum,
                    'TU memperbaiki surat sesuai catatan pemeriksaan Kepala TU.'
                );
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('status', 'Revisi disimpan. Periksa surat, lalu ajukan kembali ke Kepala TU.');
    }

    public function action(Request $request, SuratKeluar $suratKeluar)
    {
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:1'],
            'aksi' => [
                'required',
                Rule::in(['ajukan', 'teruskan_kepala', 'setujui', 'revisi', 'nomor', 'kirim']),
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

        $requiredRole = match ($data['aksi']) {
            'ajukan', 'nomor', 'kirim' => 'koordinator_tu',
            'teruskan_kepala', 'revisi' => 'kepala_tu',
            default => 'kepala_sekolah',
        };

        abort_unless($request->user()->role === $requiredRole, 403);

        DB::transaction(function () use ($request, $suratKeluar, $data) {
            $surat = SuratKeluar::query()
                ->lockForUpdate()->findOrFail($suratKeluar->id);

            $this->checkVersion($surat, (int) $data['version']);

            $sebelum = $surat->status;
            $aksi = $data['aksi'];
            $catatan = $data['catatan'] ?? null;

            if (
                in_array($aksi, ['ajukan', 'teruskan_kepala', 'setujui', 'kirim'], true)
                && ! Storage::disk('local')->exists($surat->file_surat)
            ) {
                $this->fail('PDF tidak tersedia. Periksa berkas surat.');
            }

            switch ($aksi) {
                case 'ajukan':
                    if ($surat->status === 'kembali_ke_tu') {
                        $surat->status = 'diajukan';
                    } elseif ($surat->status === 'revisi') {
                        $surat->status = 'menunggu_kepala_tu';
                    } elseif ($surat->status === 'draf') {
                        $surat->status = 'menunggu_kepala_tu';
                    } else {
                        $this->fail('Surat belum siap diajukan.');
                    }
                    break;

                case 'teruskan_kepala':
                    if ($surat->status !== 'menunggu_kepala_tu') {
                        $this->fail('Surat tidak sedang menunggu pemeriksaan Kepala TU.');
                    }
                    $surat->status = 'kembali_ke_tu';
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
                    if ($surat->status !== 'menunggu_kepala_tu') {
                        $this->fail('Hanya surat yang diperiksa Kepala TU yang dapat diminta revisi.');
                    }

                    $surat->status = 'revisi';
                    $surat->catatan_persetujuan = $catatan;
                    break;

                case 'nomor':
                    if ($surat->status !== 'disetujui' || $surat->nomor_surat) {
                        $this->fail('Surat harus disetujui dan belum bernomor.');
                    }

                    $tahun = (int) $surat->tanggal_surat->format('Y');
                    $surat->nomor_surat = $this->nextOutgoingNumber($tahun);

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

        $pesan = match (true) {
            $data['aksi'] === 'ajukan' && $suratKeluar->status === 'revisi'
                => 'Surat revisi berhasil dikirim kembali ke antrean pemeriksaan Kepala TU.',
            $data['aksi'] === 'ajukan' && $suratKeluar->status === 'kembali_ke_tu'
                => 'Surat berhasil diteruskan ke Kepala Sekolah.',
            default => 'Tindakan berhasil disimpan.',
        };

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('status', $pesan);
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
                ['koordinator_tu', 'kepala_tu', 'kepala_sekolah'],
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

    private function nextOutgoingNumber(int $tahun): string
    {
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

        return sprintf('S-%04d/SMKN4/%d', $nomor, $tahun);
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
                'sifat_surat', 'tanggal_surat', 'version', 'keterangan', 'data_surat',
            ]), JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PemeriksaanSuratController extends Controller
{
    public function indexTu()
    {
        $surats = SuratMasuk::query()
            ->whereNull('diteruskan_at')
            ->whereIn('status', [
                'menunggu_verifikasi',
                'diverifikasi',
            ])
            ->oldest('id')
            ->paginate(10);

        return view('pemeriksaan.index', compact('surats'));
    }

    public function showTu(SuratMasuk $suratMasuk)
    {
        return view(
            'pemeriksaan.show',
            $this->detailData($suratMasuk)
        );
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $data = $request->validate([
            'aksi' => [
                'required',
                Rule::in(['verifikasi', 'tolak', 'spam', 'teruskan']),
            ],
            'catatan' => [
                'nullable',
                'required_if:aksi,tolak,spam',
                'string',
                'max:2000',
            ],
        ], [
            'catatan.required_if' =>
                'Alasan wajib diisi untuk penolakan atau penandaan spam.',
            'catatan.max' => 'Catatan maksimal 2.000 karakter.',
            'aksi.in' => 'Tindakan tidak valid.',
        ]);

        DB::transaction(function () use ($request, $suratMasuk, $data) {
            $surat = SuratMasuk::query()
                ->lockForUpdate()
                ->findOrFail($suratMasuk->id);

            $aksi = $data['aksi'];
            $sebelum = $surat->status;

            if ($aksi === 'teruskan') {
                if (
                    $surat->status !== 'diverifikasi' ||
                    $surat->diteruskan_at !== null
                ) {
                    throw ValidationException::withMessages([
                        'aksi' =>
                            'Surat harus sudah diverifikasi dan belum diteruskan.',
                    ]);
                }
            } else {
                if (
                    $surat->status !== 'menunggu_verifikasi' ||
                    $surat->diteruskan_at !== null
                ) {
                    throw ValidationException::withMessages([
                        'aksi' =>
                            'Surat sudah diperiksa. Muat ulang halaman untuk melihat status terbaru.',
                    ]);
                }
            }

            if (in_array($aksi, ['verifikasi', 'teruskan'], true)) {
                if (
                    ! $surat->file_surat ||
                    ! Storage::disk('local')->exists($surat->file_surat)
                ) {
                    throw ValidationException::withMessages([
                        'aksi' =>
                            'PDF surat belum tersedia. Surat tidak dapat diproses.',
                    ]);
                }
            }

            if ($aksi === 'teruskan') {
                $surat->diteruskan_at = now();
            } else {
                $surat->status = match ($aksi) {
                    'verifikasi' => 'diverifikasi',
                    'tolak' => 'ditolak',
                    'spam' => 'spam',
                };
            }

            $surat->save();

            DB::table('riwayat_surat_masuks')->insert([
                'surat_masuk_id' => $surat->id,
                'user_id' => $request->user()->id,
                'aksi' => $aksi,
                'status_sebelum' => $sebelum,
                'status_sesudah' => $surat->status,
                'catatan' => $data['catatan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $pesan = match ($data['aksi']) {
            'verifikasi' => 'Surat berhasil diverifikasi.',
            'tolak' => 'Surat ditolak dan alasannya disimpan.',
            'spam' => 'Surat ditandai sebagai spam.',
            'teruskan' => 'Surat berhasil diteruskan ke Kepala Sekolah.',
        };

        return redirect()
            ->route('tu.pemeriksaan.show', $suratMasuk)
            ->with('status', $pesan);
    }

    public function indexKepala()
    {
        $surats = SuratMasuk::query()
            ->whereNotNull('diteruskan_at')
            ->whereIn('status', ['diverifikasi', 'disposisi', 'selesai'])
            ->latest('diteruskan_at')
            ->paginate(10);

        return view('kepala.surat.index', compact('surats'));
    }

    public function showKepala(SuratMasuk $suratMasuk)
    {
        $this->ensureForwarded($suratMasuk);

        return view(
            'kepala.surat.show',
            $this->detailData($suratMasuk)
        );
    }

    public function downloadKepala(SuratMasuk $suratMasuk)
    {
        $this->ensureForwarded($suratMasuk);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless(
            $suratMasuk->file_surat &&
            $disk->exists($suratMasuk->file_surat),
            404,
            'Berkas surat tidak ditemukan.'
        );

        return $disk->download(
            $suratMasuk->file_surat,
            $suratMasuk->nomor_registrasi.'.pdf',
            ['X-Content-Type-Options' => 'nosniff']
        );
    }

    private function ensureForwarded(SuratMasuk $surat): void
    {
        abort_unless(
            $surat->diteruskan_at !== null &&
            in_array(
                $surat->status,
                ['diverifikasi', 'disposisi', 'selesai'],
                true
            ),
            404
        );
    }

    private function detailData(SuratMasuk $surat): array
    {
        $surat->load('pencatat');

        $riwayats = DB::table('riwayat_surat_masuks as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.surat_masuk_id', $surat->id)
            ->select('r.*', 'u.name as nama_petugas')
            ->orderByDesc('r.id')
            ->get();

        return [
            'surat' => $surat,
            'riwayats' => $riwayats,
        ];
    }
}
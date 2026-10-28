<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Kegiatan;
use App\Models\MitraIndustri;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HapusDataController extends Controller
{
    public function suratMasuk(SuratMasuk $suratMasuk)
    {
        $paths = DB::transaction(function () use ($suratMasuk) {
            $surat = SuratMasuk::query()->lockForUpdate()->findOrFail($suratMasuk->id);
            $ids = Disposisi::where('surat_masuk_id', $surat->id)->lockForUpdate()->pluck('id');
            $paths = DB::table('tindak_lanjuts')->whereIn('disposisi_id', $ids)->pluck('file_lampiran')->all();
            $paths[] = $surat->file_surat;
            DB::table('tindak_lanjuts')->whereIn('disposisi_id', $ids)->delete();
            Disposisi::whereIn('id', $ids)->delete();
            DB::table('riwayat_surat_masuks')->where('surat_masuk_id', $surat->id)->delete();
            $surat->delete();

            return $paths;
        });
        $this->hapusBerkas($paths);

        return redirect()->route('tu.surat-masuk.index')->with('status', 'Surat masuk beserta disposisi dan riwayatnya berhasil dihapus.');
    }

    public function suratKeluar(SuratKeluar $suratKeluar)
    {
        $paths = DB::transaction(function () use ($suratKeluar) {
            $surat = SuratKeluar::query()->lockForUpdate()->findOrFail($suratKeluar->id);
            $riwayat = DB::table('riwayat_surat_keluars')->where('surat_keluar_id', $surat->id);
            $paths = $riwayat->pluck('file_surat')->all();
            $paths[] = $surat->file_surat;
            $paths[] = $surat->file_final;
            $riwayat->delete();
            $surat->delete();

            return $paths;
        });
        $this->hapusBerkas($paths);

        return redirect()->route('surat-keluar.index')->with('status', 'Surat keluar beserta riwayatnya berhasil dihapus.');
    }

    public function disposisi(Request $request, Disposisi $disposisi)
    {
        $paths = DB::transaction(function () use ($request, $disposisi) {
            $surat = SuratMasuk::query()->lockForUpdate()->findOrFail($disposisi->surat_masuk_id);
            $item = Disposisi::query()->lockForUpdate()->findOrFail($disposisi->id);
            $lanjuts = DB::table('tindak_lanjuts')->where('disposisi_id', $item->id);
            $paths = $lanjuts->pluck('file_lampiran')->all();
            $lanjuts->delete();
            $item->delete();
            $sisa = Disposisi::where('surat_masuk_id', $surat->id)->get();
            $sebelum = $surat->status;
            $surat->status = $sisa->isEmpty() ? 'diverifikasi'
                : ($sisa->every(fn ($row) => $row->status === 'selesai') ? 'selesai' : 'disposisi');
            $surat->save();
            DB::table('riwayat_surat_masuks')->insert([
                'surat_masuk_id' => $surat->id,
                'user_id' => $request->user()->id,
                'aksi' => 'hapus_disposisi',
                'status_sebelum' => $sebelum,
                'status_sesudah' => $surat->status,
                'catatan' => 'Disposisi #'.$item->id.' dihapus.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $paths;
        });
        $this->hapusBerkas($paths);

        return redirect()->route('disposisi.index')->with('status', 'Disposisi beserta tindak lanjutnya berhasil dihapus.');
    }

    public function kegiatan(Kegiatan $kegiatan)
    {
        $dataTerhapus = DB::transaction(function () use ($kegiatan) {
            $item = Kegiatan::query()->lockForUpdate()->findOrFail($kegiatan->id);

            $sertifikats = $item->sertifikats()
                ->lockForUpdate()
                ->get(['id', 'file_pdf']);

            $peserta = $item->pesertaPkls()
                ->lockForUpdate()
                ->get(['id']);

            $paths = $sertifikats->pluck('file_pdf')->all();

            $item->sertifikats()->delete();
            $item->pesertaPkls()->delete();
            $item->delete();

            return [
                'paths' => $paths,
                'peserta' => $peserta->count(),
                'sertifikat' => $sertifikats->count(),
            ];
        });
        $this->hapusBerkas($dataTerhapus['paths']);

        return redirect()->route('tu.kegiatan.index')->with(
            'status',
            'Sesi PKL berhasil dihapus bersama '
                .$dataTerhapus['peserta'].' data siswa dan '
                .$dataTerhapus['sertifikat'].' sertifikat.'
        );
    }

    public function mitra(MitraIndustri $mitra)
    {
        $paths = DB::transaction(function () use ($mitra) {
            $item = MitraIndustri::query()->lockForUpdate()->findOrFail($mitra->id);
            $paths = $item->kerjasamas()->lockForUpdate()->pluck('dokumen_mou')->all();
            $paths[] = $item->logo;
            $item->kerjasamas()->delete();
            $item->delete();

            return $paths;
        });
        $this->hapusBerkas($paths);

        return redirect()->route('tu.mitra.index')
            ->with('status', 'Mitra beserta agenda, kerja sama lama, logo, dan dokumen MoU berhasil dihapus.');
    }

    private function hapusBerkas(array $paths): void
    {
        $paths = array_values(array_unique(array_filter($paths)));
        if ($paths !== []) {
            Storage::disk('local')->delete($paths);
        }
    }
}

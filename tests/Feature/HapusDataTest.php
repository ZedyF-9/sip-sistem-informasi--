<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Kegiatan;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HapusDataTest extends TestCase
{
    use RefreshDatabase;

    private function akun(string $role = 'koordinator_tu'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    private function surat(): SuratMasuk
    {
        return SuratMasuk::create([
            'nomor_registrasi' => 'SM-'.uniqid(), 'jenis_surat' => 'Undangan',
            'sifat_surat' => 'biasa', 'asal_surat' => 'Sekolah', 'nama_pengirim' => 'Pengirim',
            'perihal' => 'Rapat', 'metode_pengiriman' => 'online',
            'tanggal_surat' => today(), 'tanggal_diterima' => today(),
            'file_surat' => 'surat/uji.pdf', 'status' => 'disposisi',
        ]);
    }

    private function disposisi(SuratMasuk $surat, string $status = 'baru'): Disposisi
    {
        return Disposisi::create([
            'surat_masuk_id' => $surat->id, 'pemberi_id' => $this->akun('kepala_sekolah')->id,
            'penerima_id' => $this->akun('penerima_disposisi')->id,
            'instruksi' => 'Kerjakan', 'prioritas' => 'biasa',
            'tanggal_disposisi' => today(), 'status' => $status,
        ]);
    }

    public function test_tu_can_delete_incoming_letter_and_related_records_and_files(): void
    {
        Storage::fake('local');
        $surat = $this->surat();
        $disposisi = $this->disposisi($surat);
        Storage::disk('local')->put($surat->file_surat, 'pdf');
        Storage::disk('local')->put('lampiran/uji.pdf', 'pdf');
        DB::table('tindak_lanjuts')->insert([
            'disposisi_id' => $disposisi->id, 'user_id' => $disposisi->penerima_id,
            'catatan' => 'Diproses', 'status' => 'diproses', 'tanggal_tindak_lanjut' => now(),
            'file_lampiran' => 'lampiran/uji.pdf',
        ]);
        $this->actingAs($this->akun())->delete(route('tu.surat-masuk.destroy', $surat))
            ->assertRedirect(route('tu.surat-masuk.index'));
        $this->assertDatabaseMissing('surat_masuks', ['id' => $surat->id]);
        $this->assertDatabaseMissing('disposisis', ['id' => $disposisi->id]);
        $this->assertDatabaseCount('tindak_lanjuts', 0);
        Storage::disk('local')->assertMissing(['surat/uji.pdf', 'lampiran/uji.pdf']);
    }

    public function test_deleting_dispositions_recalculates_letter_status_and_keeps_history(): void
    {
        $surat = $this->surat();
        $selesai = $this->disposisi($surat, 'selesai');
        $baru = $this->disposisi($surat);
        $this->actingAs($this->akun('kepala_sekolah'));
        $this->delete(route('disposisi.destroy', $baru))->assertRedirect(route('disposisi.index'));
        $this->assertSame('selesai', $surat->fresh()->status);
        $this->delete(route('disposisi.destroy', $selesai))->assertRedirect();
        $this->assertSame('diverifikasi', $surat->fresh()->status);
        $this->assertDatabaseCount('riwayat_surat_masuks', 2);
    }

    public function test_outgoing_letter_deletes_history_and_all_pdf_versions(): void
    {
        Storage::fake('local');
        $tu = $this->akun();
        $surat = new SuratKeluar;
        $surat->forceFill([
            'perihal' => 'Undangan', 'tujuan' => 'Sekolah', 'jenis_surat' => 'Undangan',
            'sifat_surat' => 'biasa', 'tanggal_surat' => today(), 'created_by' => $tu->id,
            'file_surat' => 'baru.pdf', 'file_final' => 'final.pdf',
        ])->save();
        DB::table('riwayat_surat_keluars')->insert([
            'surat_keluar_id' => $surat->id, 'user_id' => $tu->id,
            'aksi' => 'buat', 'status_sesudah' => 'draf', 'file_surat' => 'lama.pdf', 'snapshot' => '{}',
        ]);
        foreach (['baru.pdf', 'final.pdf', 'lama.pdf'] as $path) {
            Storage::disk('local')->put($path, 'pdf');
        }
        $this->actingAs($tu)->get(route('surat-keluar.index'))->assertOk()->assertSee('Hapus');
        $this->delete(route('surat-keluar.destroy', $surat))->assertRedirect(route('surat-keluar.index'));
        $this->assertDatabaseCount('surat_keluars', 0);
        $this->assertDatabaseCount('riwayat_surat_keluars', 0);
        Storage::disk('local')->assertMissing(['baru.pdf', 'final.pdf', 'lama.pdf']);
    }

    public function test_activities_with_certificates_are_protected(): void
    {
        $tu = $this->akun();
        $kegiatan = new Kegiatan([
            'nama' => 'Pelatihan', 'lokasi' => 'Sekolah',
            'tanggal_mulai' => today(), 'tanggal_selesai' => today(),
        ]);
        $kegiatan->created_by = $tu->id;
        $kegiatan->save();
        $this->actingAs($tu)->get(route('tu.kegiatan.index'))->assertOk()->assertSee('Hapus');
        DB::table('sertifikats')->insert([
            'kegiatan_id' => $kegiatan->id, 'kode_peserta' => 'P1', 'nama_penerima' => 'Peserta',
            'peran' => 'Peserta', 'kode_verifikasi' => str_repeat('a', 64),
            'tanggal_terbit' => today(), 'snapshot' => '{}', 'created_by' => $tu->id,
        ]);
        $this->delete(route('tu.kegiatan.destroy', $kegiatan))->assertSessionHasErrors('kegiatan');
        $this->assertDatabaseHas('kegiatans', ['id' => $kegiatan->id]);
        DB::table('sertifikats')->delete();
        $this->delete(route('tu.kegiatan.destroy', $kegiatan))->assertRedirect(route('tu.kegiatan.index'));
        $this->assertDatabaseCount('kegiatans', 0);
    }

    public function test_receivers_and_guests_cannot_delete_records(): void
    {
        $surat = $this->surat();
        $item = $this->disposisi($surat);
        $this->delete(route('tu.surat-masuk.destroy', $surat))->assertRedirect(route('login'));
        $this->actingAs(User::findOrFail($item->penerima_id));
        $this->delete(route('tu.surat-masuk.destroy', $surat))->assertForbidden();
        $this->delete(route('disposisi.destroy', $item))->assertForbidden();
        $keluar = new SuratKeluar;
        $keluar->forceFill([
            'perihal' => 'Undangan', 'tujuan' => 'Sekolah', 'jenis_surat' => 'Undangan',
            'sifat_surat' => 'biasa', 'tanggal_surat' => today(),
            'created_by' => $this->akun()->id, 'file_surat' => 'uji.pdf',
        ])->save();
        $kegiatan = new Kegiatan([
            'nama' => 'Pelatihan', 'lokasi' => 'Sekolah',
            'tanggal_mulai' => today(), 'tanggal_selesai' => today(),
        ]);
        $kegiatan->created_by = $keluar->created_by;
        $kegiatan->save();
        $this->delete(route('surat-keluar.destroy', $keluar))->assertForbidden();
        $this->delete(route('tu.kegiatan.destroy', $kegiatan))->assertForbidden();
        $this->get(route('disposisi.index'))->assertOk()->assertDontSee('>Hapus</button>', false);
        $this->assertDatabaseHas('surat_masuks', ['id' => $surat->id]);
    }
}

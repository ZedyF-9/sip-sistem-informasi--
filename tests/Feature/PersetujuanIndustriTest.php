<?php

namespace Tests\Feature;

use App\Models\Kerjasama;
use App\Models\MitraIndustri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersetujuanIndustriTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_baru_menunggu_persetujuan_dan_tidak_tampil_publik(): void
    {
        $tu = User::factory()->create([
            'role' => 'koordinator_tu',
            'is_active' => true,
        ]);

        $this->actingAs($tu)->post(route('tu.mitra.store'), [
            'nama_mitra' => 'Industri Uji',
            'bidang_usaha' => 'Teknologi',
            'deskripsi' => 'Profil mitra pengujian persetujuan.',
            'status' => 'aktif',
        ])->assertRedirect();

        $mitra = MitraIndustri::query()->where('nama_mitra', 'Industri Uji')->firstOrFail();
        $this->assertSame('menunggu', $mitra->status_persetujuan);
        $this->get(route('publik.mitra.index'))->assertDontSee('Industri Uji');
        $this->get(route('publik.mitra.show', $mitra))->assertNotFound();

        $waka = User::factory()->create([
            'role' => 'waka_humas',
            'is_active' => true,
        ]);

        $this->actingAs($waka)
            ->get(route('waka-humas.kerjasama.index'))
            ->assertOk()
            ->assertSee('Industri Uji')
            ->assertSee('Setujui Mitra');

        $this->patch(route('waka-humas.kerjasama.mitra.approve', $mitra))
            ->assertRedirect();

        $this->assertDatabaseHas('mitra_industris', [
            'id' => $mitra->id,
            'status_persetujuan' => 'disetujui',
            'disetujui_oleh' => $waka->id,
        ]);
        $this->get(route('publik.mitra.show', $mitra))->assertOk()
            ->assertSee('Industri Uji');
    }

    public function test_waka_humas_menolak_mitra_dengan_alasan(): void
    {
        $mitra = $this->buatMitraMenunggu();
        $waka = User::factory()->create([
            'role' => 'waka_humas',
            'is_active' => true,
        ]);

        $this->actingAs($waka)
            ->patch(route('waka-humas.kerjasama.mitra.reject', $mitra), [])
            ->assertSessionHasErrors('catatan_persetujuan');

        $this->patch(route('waka-humas.kerjasama.mitra.reject', $mitra), [
            'catatan_persetujuan' => 'Data mitra perlu dilengkapi.',
        ])->assertRedirect();

        $this->assertDatabaseHas('mitra_industris', [
            'id' => $mitra->id,
            'status_persetujuan' => 'ditolak',
            'catatan_persetujuan' => 'Data mitra perlu dilengkapi.',
            'disetujui_oleh' => $waka->id,
        ]);
        $this->get(route('publik.mitra.index'))->assertDontSee($mitra->nama_mitra);
    }

    public function test_mou_baru_menunggu_persetujuan_sebelum_tampil_di_publik(): void
    {
        $mitra = MitraIndustri::query()->create([
            'nama_mitra' => 'Mitra MoU',
            'bidang_usaha' => 'Pendidikan',
            'deskripsi' => 'Mitra yang sudah disetujui.',
            'status' => 'aktif',
            'status_persetujuan' => 'disetujui',
        ]);
        $tu = User::factory()->create([
            'role' => 'koordinator_tu',
            'is_active' => true,
        ]);

        $this->actingAs($tu)->post(route('tu.kerjasama.store', $mitra), [
            'judul' => 'Program Magang',
            'nomor_mou' => 'MOU-001',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addYear()->toDateString(),
            'status' => 'aktif',
        ])->assertRedirect();

        $kerjasama = Kerjasama::query()->where('judul', 'Program Magang')->firstOrFail();
        $this->assertSame('menunggu', $kerjasama->status_persetujuan);
        $this->get(route('publik.mitra.show', $mitra))->assertDontSee('Program Magang');

        $waka = User::factory()->create([
            'role' => 'waka_humas',
            'is_active' => true,
        ]);
        $this->actingAs($waka)
            ->patch(route('waka-humas.kerjasama.approve', $kerjasama))
            ->assertRedirect();

        $this->assertDatabaseHas('kerjasamas', [
            'id' => $kerjasama->id,
            'status_persetujuan' => 'disetujui',
            'disetujui_oleh' => $waka->id,
        ]);
        $this->get(route('publik.mitra.show', $mitra))->assertSee('Program Magang');
    }

    public function test_only_waka_humas_can_decide_industrial_approvals(): void
    {
        $mitra = $this->buatMitraMenunggu();
        $tu = User::factory()->create([
            'role' => 'koordinator_tu',
            'is_active' => true,
        ]);

        $this->actingAs($tu)
            ->patch(route('waka-humas.kerjasama.mitra.approve', $mitra))
            ->assertForbidden();
    }

    private function buatMitraMenunggu(): MitraIndustri
    {
        return MitraIndustri::query()->create([
            'nama_mitra' => 'Mitra Menunggu',
            'bidang_usaha' => 'Industri',
            'deskripsi' => 'Menunggu keputusan Waka Humas.',
            'status' => 'aktif',
            'status_persetujuan' => 'menunggu',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\MitraIndustri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HapusMitraTest extends TestCase
{
    use RefreshDatabase;

    private function mitra(array $attributes = []): MitraIndustri
    {
        return MitraIndustri::create(array_merge([
            'nama_mitra' => 'Mitra Uji', 'bidang_usaha' => 'Teknologi',
            'deskripsi' => 'Profil mitra', 'status' => 'aktif',
            'status_persetujuan' => 'disetujui',
        ], $attributes));
    }

    public function test_tu_can_delete_partner_with_agreements_and_files(): void
    {
        Storage::fake('local');
        $mitra = $this->mitra(['logo' => 'mitra-logo/uji.png']);
        $lain = $this->mitra(['logo' => 'mitra-logo/lain.png']);
        $kerjasama = $mitra->kerjasamas()->create([
            'judul' => 'Magang', 'tanggal_mulai' => today(), 'dokumen_mou' => 'dokumen-mou/uji.pdf',
        ]);
        foreach ([$mitra->logo, $lain->logo, $kerjasama->dokumen_mou] as $path) {
            Storage::disk('local')->put($path, 'file');
        }
        $this->actingAs(User::factory()->create(['role' => 'koordinator_tu', 'is_active' => true]));
        foreach (['tu.mitra.index', 'tu.mitra.show'] as $route) {
            $this->get(route($route, $route === 'tu.mitra.show' ? $mitra : []))
                ->assertOk()->assertSee(route('tu.mitra.destroy', $mitra), false)
                ->assertSee('dokumen MoU terkait ikut dihapus permanen.');
        }
        $this->delete(route('tu.mitra.destroy', $mitra))->assertRedirect(route('tu.mitra.index'));
        $this->assertDatabaseMissing('mitra_industris', ['id' => $mitra->id]);
        $this->assertDatabaseMissing('kerjasamas', ['id' => $kerjasama->id]);
        $this->assertDatabaseHas('mitra_industris', ['id' => $lain->id]);
        Storage::disk('local')->assertMissing([$mitra->logo, $kerjasama->dokumen_mou]);
        Storage::disk('local')->assertExists($lain->logo);
        $this->get(route('publik.mitra.show', $mitra))->assertNotFound();
    }

    public function test_partner_without_files_or_agreements_can_be_deleted(): void
    {
        $mitra = $this->mitra();
        $this->actingAs(User::factory()->create(['role' => 'koordinator_tu', 'is_active' => true]))
            ->delete(route('tu.mitra.destroy', $mitra))->assertRedirect(route('tu.mitra.index'));
        $this->assertDatabaseMissing('mitra_industris', ['id' => $mitra->id]);
    }

    public function test_guests_and_other_roles_cannot_delete_partners(): void
    {
        $mitra = $this->mitra();
        $this->delete(route('tu.mitra.destroy', $mitra))->assertRedirect(route('login'));
        foreach (['kepala_sekolah', 'waka_humas', 'penerima_disposisi'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]))
                ->delete(route('tu.mitra.destroy', $mitra))->assertForbidden();
        }
        $this->assertDatabaseHas('mitra_industris', ['id' => $mitra->id]);
    }
}

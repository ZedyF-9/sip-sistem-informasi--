<?php

namespace Tests\Feature;

use App\Models\MitraIndustri;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_dashboard_has_zero_totals_and_an_empty_table(): void
    {
        $this->actingAs($this->userWithRole('koordinator_tu'))
            ->get(route('tu.dashboard'))
            ->assertOk()
            ->assertViewHas('ringkasan', [
                'surat_masuk' => 0,
                'surat_keluar' => 0,
                'menunggu_acc' => 0,
                'mitra_industri' => 0,
                'total_surat' => 0,
                'persen_acc' => 0,
                'persen_masuk' => 0,
                'persen_keluar' => 0,
            ])
            ->assertViewHas('suratTerbaru', fn ($surats) => $surats->isEmpty())
            ->assertSeeText('Belum ada surat masuk')
            ->assertSeeText('Mitra tercatat');
    }

    public function test_dashboard_counts_existing_letters_and_shows_only_the_five_latest(): void
    {
        $user = $this->userWithRole('koordinator_tu');
        $surats = collect();

        foreach (['menunggu_verifikasi', 'diverifikasi', 'disposisi', 'ditolak', 'spam', 'selesai'] as $index => $status) {
            $surats->push(SuratMasuk::query()->create([
                'nomor_registrasi' => 'REG-'.$index,
                'nomor_surat' => $index === 5 ? null : 'SURAT-'.$index,
                'jenis_surat' => 'Undangan',
                'sifat_surat' => 'biasa',
                'asal_surat' => 'Instansi '.$index,
                'nama_pengirim' => 'Pengirim',
                'perihal' => 'Undangan kegiatan',
                'metode_pengiriman' => 'online',
                'tanggal_surat' => '2026-09-16',
                'tanggal_diterima' => '2026-09-17',
                'status' => $status,
            ]));
        }

        $pending = null;
        foreach (['draf', 'diajukan', 'revisi', 'disetujui', 'dikirim', 'diajukan'] as $status) {
            $surat = new SuratKeluar([
                'perihal' => 'Surat keluar',
                'tujuan' => 'Instansi tujuan',
                'jenis_surat' => 'Undangan',
                'sifat_surat' => 'biasa',
                'tanggal_surat' => '2026-09-17',
                'file_surat' => 'surat-keluar/test.pdf',
            ]);
            $surat->created_by = $user->id;
            $surat->status = $status;
            $surat->save();
            $pending = $surat;
        }

        $response = $this->actingAs($user)->get(route('tu.dashboard'));
        $response->assertOk()
            ->assertViewHas('ringkasan', [
                'surat_masuk' => 6,
                'surat_keluar' => 6,
                'menunggu_acc' => 2,
                'mitra_industri' => 0,
                'total_surat' => 12,
                'persen_acc' => 16.7,
                'persen_masuk' => 50.0,
                'persen_keluar' => 50.0,
            ])
            ->assertViewHas('suratTerbaru', fn ($items) => $items->modelKeys() === $surats->reverse()->take(5)->pluck('id')->all())
            ->assertSeeTextInOrder(['REG-5', 'SURAT-4', 'SURAT-3', 'SURAT-2', 'SURAT-1'])
            ->assertSeeText('Instansi 5')
            ->assertSeeText('16/09/2026')
            ->assertSeeText('Selesai')
            ->assertSee('aria-label="2 antrean ACC"', false)
            ->assertSee('style="width: 50%"', false)
            ->assertSee(route('tu.surat-masuk.show', $surats->last()))
            ->assertDontSeeText('SURAT-0')
            ->assertDontSeeText('Belum ada surat masuk');

        // A subsequent visit must reflect updated operational data.
        $pending->status = 'disetujui';
        $pending->save();

        $this->get(route('tu.dashboard'))
            ->assertOk()
            ->assertViewHas('ringkasan', fn ($data) => $data['menunggu_acc'] === 1 && $data['persen_acc'] === 8.3);
    }

    public function test_industry_partner_statistic_uses_current_database_total(): void
    {
        foreach ([
            ['aktif', 'disetujui'],
            ['nonaktif', 'menunggu'],
            ['nonaktif', 'ditolak'],
        ] as $index => [$status, $approval]) {
            MitraIndustri::query()->create([
                'nama_mitra' => 'Mitra '.($index + 1),
                'bidang_usaha' => 'Bidang '.($index + 1),
                'deskripsi' => 'Data mitra untuk statistik dashboard.',
                'status' => $status,
                'status_persetujuan' => $approval,
            ]);
        }

        foreach ([
            'koordinator_tu' => 'tu.dashboard',
            'kepala_sekolah' => 'kepala.dashboard',
        ] as $role => $route) {
            $this->actingAs($this->userWithRole($role))
                ->get(route($route))
                ->assertOk()
                ->assertViewHas('ringkasan', fn ($data) => $data['mitra_industri'] === 3)
                ->assertSeeTextInOrder(['Mitra Industri', '3', 'Mitra tercatat']);
        }

        MitraIndustri::query()->firstOrFail()->delete();

        $this->actingAs($this->userWithRole('koordinator_tu'))
            ->get(route('tu.dashboard'))
            ->assertViewHas('ringkasan', fn ($data) => $data['mitra_industri'] === 2);
    }

    public function test_dashboard_access_and_other_role_dashboards_are_preserved(): void
    {
        $this->get(route('tu.dashboard'))->assertRedirect(route('login'));

        foreach (['kepala_sekolah' => 'kepala.dashboard', 'penerima_disposisi' => 'penerima.dashboard'] as $role => $route) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('tu.dashboard'))->assertForbidden();
            $this->get(route($route))
                ->assertOk()
                ->assertSeeText('Selamat datang,')
                ->assertDontSeeText('Ringkasan Data Sekolah');
            $this->get(route('dashboard'))->assertRedirect(route($route));
        }

        $this->actingAs($this->userWithRole('koordinator_tu'))
            ->get(route('dashboard'))->assertRedirect(route('tu.dashboard'));
    }

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }
}

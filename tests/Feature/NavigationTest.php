<?php

namespace Tests\Feature;

use App\Models\MitraIndustri;
use App\Models\Kegiatan;
use App\Models\SuratMasuk;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_public_pages_and_follow_the_public_navigation(): void
    {
        $mitra = MitraIndustri::query()->create([
            'nama_mitra' => 'Mitra Uji',
            'bidang_usaha' => 'Teknologi',
            'deskripsi' => 'Mitra untuk pengujian navigasi publik.',
            'status' => 'aktif',
            'status_persetujuan' => 'disetujui',
        ]);

        $pages = [
            route('beranda'),
            route('publik.kirim'),
            route('publik.lacak'),
            route('publik.mitra.index'),
            route('publik.mitra.show', $mitra),
            route('publik.sertifikat.form'),
        ];

        $expectedLinks = [
            'Beranda' => route('beranda'),
            'Kirim Surat' => route('publik.kirim'),
            'Lacak Surat' => route('publik.lacak'),
            'Mitra' => route('publik.mitra.index'),
            'Verifikasi Sertifikat' => route('publik.sertifikat.form'),
            'Login Internal' => route('login'),
        ];

        foreach ($pages as $url) {
            $response = $this->get($url)->assertOk()
                ->assertDontSee('action="'.route('logout').'"', false);

            $xpath = $this->html($response->getContent());
            $links = [];
            foreach ($xpath->query('//nav[@aria-label="Navigasi publik"]//a') as $link) {
                $links[trim($link->textContent)] = $link->getAttribute('href');
            }
            $this->assertSame($expectedLinks, $links);
            $this->assertSame(1, $xpath->query('//nav//a[@aria-current="page"]')->length);
            $this->assertSame(route('beranda'), $xpath->query('//header/div/a')->item(0)->getAttribute('href'));
        }

        foreach ($expectedLinks as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_public_result_pages_keep_their_existing_behavior_for_guests(): void
    {
        $this->get(route('publik.berhasil'))->assertRedirect(route('publik.kirim'));

        $this->withSession(['nomor_surat_terakhir' => 'SM-01ARZ3NDEKTSV4RRFFQ69G5FAV'])
            ->get(route('publik.berhasil'))->assertOk()
            ->assertSee('href="'.route('publik.lacak').'"', false);

        $this->post(route('publik.lacak.submit'), [
            'nomor_registrasi' => 'SM-01ARZ3NDEKTSV4RRFFQ69G5FAV',
        ])->assertOk()->assertViewHas('dicari', true);
    }

    public function test_authenticated_public_navigation_returns_each_role_to_its_dashboard(): void
    {
        foreach ([
            'koordinator_tu' => 'tu.dashboard',
            'kepala_sekolah' => 'kepala.dashboard',
            'waka_humas' => 'waka-humas.dashboard',
            'penerima_disposisi' => 'penerima.dashboard',
        ] as $role => $dashboard) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]));

            $response = $this->get(route('beranda'))->assertOk()
                ->assertSee('href="'.route('dashboard').'"', false)
                ->assertDontSee('href="'.route('login').'"', false);

            $logout = $this->html($response->getContent())->query('//form')->item(0);
            $this->assertSame(route('logout'), $logout->getAttribute('action'));
            $this->assertSame('POST', $logout->getAttribute('method'));

            $this->get(route('dashboard'))->assertRedirect(route($dashboard));
            $this->get(route($dashboard))->assertOk();
            $this->post(route('logout'))->assertRedirect(route('login'));
            $this->assertGuest();
            $this->get(route('beranda'))->assertOk();
        }
    }

    public function test_internal_menu_destinations_are_accessible_to_their_respective_roles(): void
    {
        foreach ([
            'koordinator_tu' => 'tu.dashboard',
            'kepala_sekolah' => 'kepala.dashboard',
            'waka_humas' => 'waka-humas.dashboard',
            'penerima_disposisi' => 'penerima.dashboard',
        ] as $role => $dashboard) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]));
            $response = $this->get(route($dashboard))->assertOk();
            $xpath = $this->html($response->getContent());
            $this->assertSame(1, $xpath->query('//body[contains(@class, "dashboard-shell")]')->length);
            $this->assertSame(1, $xpath->query('//aside[@id="dashboard-sidebar"]')->length);
            $this->assertSame(1, $xpath->query('//button[@aria-controls="dashboard-sidebar"]')->length);
            $this->assertSame(1, $xpath->query('//main[@id="dashboard-content"]')->length);
            $links = $xpath->query('//nav//a[@href]');
            $this->assertGreaterThan(0, $links->length);

            foreach ($links as $link) {
                $this->get($link->getAttribute('href'))->assertOk();
            }
        }
    }

    public function test_active_menu_tracks_forms_details_and_filtered_lists(): void
    {
        $mitra = MitraIndustri::query()->create([
            'nama_mitra' => 'Mitra Navigasi', 'bidang_usaha' => 'Teknologi', 'status' => 'aktif',
            'deskripsi' => 'Mitra untuk pemeriksaan navigasi.',
            'status_persetujuan' => 'disetujui',
        ]);

        $cases = [
            ['koordinator_tu', 'tu.surat-masuk.create', [], 'Surat Masuk'],
            ['koordinator_tu', 'surat-keluar.create', [], 'Surat Keluar'],
            ['koordinator_tu', 'surat-keluar.index', ['status' => 'dikirim'], 'Arsip Surat Keluar'],
            ['koordinator_tu', 'surat-keluar.index', ['status' => 'draf'], 'Surat Keluar'],
            ['koordinator_tu', 'tu.mitra.show', [$mitra], 'Mitra dan Kerja Sama'],
            ['koordinator_tu', 'tu.kerjasama.create', [$mitra], 'Mitra dan Kerja Sama'],
            ['koordinator_tu', 'tu.kegiatan.create', [], 'Kegiatan dan Sertifikat'],
            ['koordinator_tu', 'tu.perbaikan.index', [], 'Surat Masuk'],
            ['koordinator_tu', 'tu.nomor-resmi.index', [], 'Nomor Resmi'],
            ['koordinator_tu', 'laporan.cetak', [], 'Cetak Laporan / PDF'],
            ['kepala_sekolah', 'surat-keluar.index', ['status' => 'diajukan'], 'Persetujuan Surat Keluar'],
            ['kepala_sekolah', 'disposisi.create', [], 'Pemantauan Disposisi'],
            ['waka_humas', 'waka-humas.kerjasama.index', [], 'Persetujuan Mitra dan Kerja Sama'],
            ['waka_humas', 'disposisi.index', ['status' => 'diproses'], 'Sedang Diproses'],
            ['penerima_disposisi', 'disposisi.index', ['status' => 'diproses'], 'Sedang Diproses'],
            ['penerima_disposisi', 'disposisi.index', ['status' => 'selesai'], 'Disposisi Selesai'],
            ['penerima_disposisi', 'disposisi.index', ['status' => 'baru'], 'Disposisi Saya'],
        ];

        foreach ($cases as [$role, $route, $parameters, $label]) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]));
            $response = $this->get(route($route, $parameters))->assertOk();
            $active = $this->html($response->getContent())
                ->query('//nav[@aria-label="Menu internal"]//a[@aria-current="page"]');
            $this->assertSame(1, $active->length, $route);
            $this->assertSame($label, trim($active->item(0)->textContent));
        }
    }

    public function test_create_disposition_link_preserves_the_selected_letter(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah', 'is_active' => true]));
        $surat = SuratMasuk::query()->create([
            'nomor_registrasi' => 'REG-NAV', 'jenis_surat' => 'Undangan',
            'sifat_surat' => 'biasa', 'asal_surat' => 'Instansi', 'nama_pengirim' => 'Pengirim',
            'perihal' => 'Pertemuan', 'metode_pengiriman' => 'online',
            'tanggal_surat' => '2026-09-16', 'tanggal_diterima' => '2026-09-17',
            'status' => 'diverifikasi',
        ]);
        $surat->diteruskan_at = now();
        $surat->save();
        $url = route('disposisi.create', ['surat_masuk_id' => $surat->id]);
        $this->get(route('kepala.surat.show', $surat))->assertOk()->assertSee('href="'.$url.'"', false);
        $response = $this->get($url)->assertOk();
        $selected = $this->html($response->getContent())
            ->query('//select[@name="surat_masuk_id"]/option[@selected]');
        $this->assertSame(1, $selected->length);
        $this->assertSame((string) $surat->id, $selected->item(0)->getAttribute('value'));

        // A validation redirect must preserve the user's choice, including an empty choice.
        $response = $this->withSession(['_old_input' => ['surat_masuk_id' => '']])->get($url)->assertOk();
        $this->assertSame(0, $this->html($response->getContent())
            ->query('//select[@name="surat_masuk_id"]/option[@selected]')->length);
    }

    public function test_activity_detail_and_edit_have_contextual_return_links(): void
    {
        $user = User::factory()->create(['role' => 'koordinator_tu', 'is_active' => true]);
        $this->actingAs($user);
        $kegiatan = new Kegiatan([
            'nama' => 'Pelatihan Guru', 'lokasi' => 'Aula Sekolah',
            'tanggal_mulai' => '2026-09-17', 'tanggal_selesai' => '2026-09-17',
        ]);
        $kegiatan->created_by = $user->id;
        $kegiatan->save();
        foreach (['tu.kegiatan.show', 'tu.kegiatan.edit'] as $route) {
            $response = $this->get(route($route, $kegiatan))->assertOk();
            $xpath = $this->html($response->getContent());
            $active = $xpath->query('//nav[@aria-label="Menu internal"]//a[@aria-current="page"]');
            $this->assertSame(1, $active->length);
            $this->assertSame('Kegiatan dan Sertifikat', trim($active->item(0)->textContent));
            $back = $xpath->query('//main//a')->item(0);
            $this->assertSame(route($route === 'tu.kegiatan.show' ? 'tu.kegiatan.index' : 'tu.kegiatan.show',
                $route === 'tu.kegiatan.show' ? [] : [$kegiatan]), $back->getAttribute('href'));
        }
    }

    public function test_report_preview_uses_dashboard_layout_and_pdf_still_downloads(): void
    {
        foreach (['koordinator_tu', 'kepala_sekolah'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => true]));
            $parameters = ['jenis' => 'keluar', 'mulai' => '2026-09-01', 'selesai' => '2026-09-17'];
            $response = $this->get(route('laporan.cetak', $parameters))->assertOk()
                ->assertSee('dashboard-shell')->assertSee('dashboard-print-tools')
                ->assertSee('href="'.e(route('laporan.index', $parameters)).'"', false);
            $this->assertSame(1, $this->html($response->getContent())->query('//h1')->length);

            $pdf = $this->get(route('laporan.cetak', $parameters + ['format' => 'pdf']))
                ->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $pdf->getContent());
            $this->assertStringContainsString('attachment', $pdf->headers->get('content-disposition'));
        }
        $this->actingAs(User::factory()->create(['role' => 'penerima_disposisi', 'is_active' => true]));
        $this->get(route('laporan.cetak'))->assertForbidden();
    }

    private function html(string $html): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument;
            $document->loadHTML($html);

            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}

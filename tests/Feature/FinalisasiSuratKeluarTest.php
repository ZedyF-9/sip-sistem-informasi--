<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\FinalisasiSuratKeluar;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class FinalisasiSuratKeluarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Semua query model dalam pengujian ini memakai database
         * SQLite di memori. Tidak memakai db_sip_sekolah.
         */
        config([
            'database.default' => 'sip_test',
            'database.connections.sip_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
            'penomoran.format' => 'S-{urut}/SMKN4/{tahun}',
            'penomoran.digit' => 4,
            'penomoran.awal' => 1,
            'penomoran.maksimal_halaman' => 50,
            'penomoran.tinggi_bidang_nomor' => 16,
        ]);

        DB::purge('sip_test');
        DB::setDefaultConnection('sip_test');

        Artisan::call('migrate', [
            '--database' => 'sip_test',
            '--force' => true,
        ]);

        Storage::fake('local');
    }

    public function test_dua_surat_mendapat_nomor_berbeda(): void
    {
        $tu = $this->akun('koordinator_tu');
        $satu = $this->surat($tu);
        $dua = $this->surat($tu);

        $service = app(FinalisasiSuratKeluar::class);

        $hasilSatu = $service->proses($satu->id, $tu, 1);
        $hasilDua = $service->proses($dua->id, $tu, 1);

        $this->assertNotSame(
            $hasilSatu->nomor_surat,
            $hasilDua->nomor_surat
        );

        $this->assertTrue($service->siap($hasilSatu));
        $this->assertTrue($service->siap($hasilDua));
    }

    public function test_klik_ulang_tidak_membuat_nomor_baru(): void
    {
        $tu = $this->akun('koordinator_tu');
        $surat = $this->surat($tu);
        $service = app(FinalisasiSuratKeluar::class);

        $pertama = $service->proses($surat->id, $tu, 1);
        $kedua = $service->proses($surat->id, $tu, 1);

        $this->assertSame(
            $pertama->nomor_surat,
            $kedua->nomor_surat
        );

        $this->assertSame(
            $pertama->file_final,
            $kedua->file_final
        );

        $this->assertSame(
            1,
            DB::table('riwayat_surat_keluars')
                ->where('surat_keluar_id', $surat->id)
                ->where('aksi', 'finalisasi')
                ->count()
        );
    }

    public function test_nomor_lama_dipertahankan(): void
    {
        $tu = $this->akun('koordinator_tu');
        $surat = $this->surat($tu);

        $surat->nomor_surat = 'LAMA/025/2026';
        $surat->save();

        $hasil = app(FinalisasiSuratKeluar::class)
            ->proses($surat->id, $tu, 1);

        $this->assertSame('LAMA/025/2026', $hasil->nomor_surat);
        $this->assertSame('LAMA/025/2026', $hasil->nomor_pada_pdf);
    }

    public function test_surat_draf_tidak_boleh_difinalisasi(): void
    {
        $tu = $this->akun('koordinator_tu');
        $surat = $this->surat($tu);

        $surat->status = 'draf';
        $surat->save();

        $this->expectException(ValidationException::class);

        app(FinalisasiSuratKeluar::class)
            ->proses($surat->id, $tu, 1);
    }

    public function test_pdf_rusak_tidak_menyimpan_nomor_baru(): void
    {
        $tu = $this->akun('koordinator_tu');
        $surat = $this->surat($tu);

        Storage::disk('local')->put(
            $surat->file_surat,
            'Ini bukan PDF.'
        );

        try {
            app(FinalisasiSuratKeluar::class)
                ->proses($surat->id, $tu, 1);

            $this->fail('PDF rusak seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $surat->refresh();

            $this->assertNull($surat->nomor_surat);
            $this->assertNull($surat->file_final);

            $this->assertSame(
                0,
                DB::table('riwayat_surat_keluars')
                    ->where('surat_keluar_id', $surat->id)
                    ->count()
            );

            $this->assertSame(
                0,
                DB::table('nomor_surat_tahunans')->count()
            );
        }
    }

    public function test_pdf_final_mempertahankan_jumlah_halaman(): void
    {
        $tu = $this->akun('koordinator_tu');
        $surat = $this->surat($tu);

        $hasil = app(FinalisasiSuratKeluar::class)
            ->proses($surat->id, $tu, 1);

        $pdf = new Fpdi();

        $jumlah = $pdf->setSourceFile(
            Storage::disk('local')->path($hasil->file_final)
        );

        $this->assertSame(2, $jumlah);

        $template = $pdf->importPage(1);
        $ukuran = $pdf->getTemplateSize($template);

        $this->assertEqualsWithDelta(210, $ukuran['width'], 0.2);
        $this->assertEqualsWithDelta(313, $ukuran['height'], 0.2);
    }

    public function test_perubahan_pdf_asli_membatalkan_kesiapan_final(): void
    {
        $tu = $this->akun('koordinator_tu');
        $surat = $this->surat($tu);
        $service = app(FinalisasiSuratKeluar::class);

        $hasil = $service->proses($surat->id, $tu, 1);

        Storage::disk('local')->put(
            $hasil->file_surat,
            'Berkas berubah setelah finalisasi.'
        );

        $this->assertFalse($service->siap($hasil->fresh()));
    }

    public function test_penerima_disposisi_tidak_boleh_memberi_nomor(): void
    {
        $tu = $this->akun('koordinator_tu');
        $penerima = $this->akun('penerima_disposisi');
        $surat = $this->surat($tu);

        $this->actingAs($penerima)
            ->post(route('tu.nomor-resmi.store', $surat), [
                'version' => 1,
            ])
            ->assertForbidden();
    }

    private function akun(string $role): User
    {
        $user = new User();

        $user->forceFill([
            'name' => 'Pengguna Uji',
            'email' => Str::uuid().'@example.test',
            'password' => Hash::make('PasswordPengujian123'),
            'role' => $role,
            'is_active' => true,
        ]);

        $user->save();

        return $user;
    }

    private function surat(User $tu): SuratKeluar
    {
        $kepala = $this->akun('kepala_sekolah');

        $pdf = new \FPDF();

        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(100, 10, 'Surat percobaan halaman satu.');

        $pdf->AddPage();
        $pdf->Cell(100, 10, 'Surat percobaan halaman dua.');

        $path = 'surat-keluar/uji-'.Str::uuid().'.pdf';

        Storage::disk('local')->put(
            $path,
            $pdf->Output('S')
        );

        $surat = new SuratKeluar();

        $surat->forceFill([
            'perihal' => 'Undangan Pengujian',
            'tujuan' => 'Penerima Pengujian',
            'jenis_surat' => 'Undangan',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => '2026-09-26',
            'file_surat' => $path,
            'status' => 'disetujui',
            'created_by' => $tu->id,
            'approved_by' => $kepala->id,
            'approved_at' => now(),
            'version' => 1,
        ]);

        $surat->save();

        return $surat;
    }
}
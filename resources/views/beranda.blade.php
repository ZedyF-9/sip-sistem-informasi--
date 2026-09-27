@extends('layouts.publik')

@section('title', 'Beranda')

@section('content')
    <section class="dashboard-hero public-hero">
        <div>
            <p class="dashboard-eyebrow">
                Portal Layanan Sekolah
            </p>

            <h1>
                Sistem Informasi Persuratan SMKN 4 Tanjungpinang
            </h1>

            <p class="dashboard-hero-description">
                Kirim surat, pantau prosesnya, lihat mitra sekolah,
                dan periksa sertifikat melalui satu portal.
            </p>

            <div class="mt-8 flex flex-wrap gap-4">
                <a
                    href="{{ url('/kirim-surat') }}"
                    class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white"
                >
                    Kirim Surat
                </a>

                <a
                    href="{{ url('/lacak-surat') }}"
                    class="rounded-xl border border-slate-300 px-5 py-3 font-semibold"
                >
                    Lacak Surat
                </a>
            </div>
        </div>
        <aside class="public-guide" aria-label="Alur pengiriman surat">
            <h2>Cara mengirim surat</h2>
            <ol>
                <li><span>1</span><div><strong>Kirim surat</strong><p>Isi data dan lampirkan PDF.</p></div></li>
                <li><span>2</span><div><strong>Simpan nomor registrasi</strong><p>Diterima setelah pengiriman berhasil.</p></div></li>
                <li><span>3</span><div><strong>Pantau prosesnya</strong><p>Periksa melalui layanan Lacak Surat.</p></div></li>
            </ol>
        </aside>
    </section>

    <div class="dashboard-section-heading"><h2>Layanan Publik</h2></div>
    <section class="dashboard-quick-grid" aria-label="Layanan publik">
        @foreach ([
            ['Kirim Surat', 'Ajukan surat beserta lampiran PDF maksimal 5 MB.', url('/kirim-surat')],
            ['Lacak Surat', 'Periksa perkembangan surat menggunakan nomor registrasi.', url('/lacak-surat')],
            ['Mitra Sekolah', 'Lihat mitra industri dan kerja sama yang berlaku.', route('publik.mitra.index')],
            ['Verifikasi Sertifikat', 'Periksa kecocokan data dan status sertifikat melalui kode atau QR.', route('publik.sertifikat.form')],
        ] as [$title, $description, $href])
            <a
                href="{{ $href }}"
                class="dashboard-quick-card"
            >
                <x-dashboard-icon :name="match ($title) { 'Kirim Surat' => 'send', 'Lacak Surat' => 'inbox', 'Mitra Sekolah' => 'globe', default => 'check' }" />
                <h2 class="text-xl font-bold text-blue-950">{{ $title }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $description }}</p>
                <span class="mt-5 inline-block text-sm font-semibold text-blue-700">
                    Buka Layanan →
                </span>
            </a>
        @endforeach
    </section>

    <section class="public-notice mt-8 rounded-2xl bg-blue-50 p-6">
        <h2 class="font-bold text-blue-950">Ketentuan Pengiriman Surat</h2>
        <p class="mt-3 text-sm leading-7 text-blue-900">
            Lampiran harus berformat PDF dengan ukuran maksimal 5 MB.
            Beri jeda minimal 10 menit setelah pengiriman berhasil.
            Simpan nomor registrasi untuk memantau proses surat.
        </p>
    </section>
@endsection

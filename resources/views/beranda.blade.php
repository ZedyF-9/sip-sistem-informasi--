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
                Informasi mitra sekolah dan layanan verifikasi sertifikat.
            </p>

        </div>
        <aside class="public-guide" aria-label="Alur pengiriman surat">
            <h2>Layanan sekolah</h2>
            <ol>
                <li><span>1</span><div><strong>Mitra sekolah</strong><p>Lihat mitra industri dan kerja sama yang berlaku.</p></div></li>
                <li><span>2</span><div><strong>Verifikasi sertifikat</strong><p>Periksa keaslian sertifikat melalui kode atau QR.</p></div></li>
                <li><span>3</span><div><strong>Layanan persuratan</strong><p>Pengelolaan surat dilakukan oleh petugas internal.</p></div></li>
            </ol>
        </aside>
    </section>

    <div class="dashboard-section-heading"><h2>Layanan Publik</h2></div>
    <section class="dashboard-quick-grid" aria-label="Layanan publik">
        @foreach ([
            ['Mitra Sekolah', 'Lihat mitra industri dan kerja sama yang berlaku.', route('publik.mitra.index')],
            ['Verifikasi Sertifikat', 'Periksa kecocokan data dan status sertifikat melalui kode atau QR.', route('publik.sertifikat.form')],
        ] as [$title, $description, $href])
            <a
                href="{{ $href }}"
                class="dashboard-quick-card"
            >
                <x-dashboard-icon :name="match ($title) { 'Mitra Sekolah' => 'globe', default => 'check' }" />
                <h2 class="text-xl font-bold text-blue-950">{{ $title }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $description }}</p>
                <span class="mt-5 inline-block text-sm font-semibold text-blue-700">
                    Buka Layanan →
                </span>
            </a>
        @endforeach
    </section>

@endsection

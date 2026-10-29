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
        <aside class="public-guide" aria-label="Panduan verifikasi sertifikat">
            <h2>Panduan verifikasi</h2>
            <ol>
                <li><span>1</span><div><strong>Temukan kode pada sertifikat</strong><p>Kode 8 digit ada di sudut kanan bawah, di bawah label “Kode Verifikasi”.</p></div></li>
                <li><span>2</span><div><strong>Masukkan kode</strong><p>Buka layanan verifikasi sertifikat, lalu masukkan 8 digit tersebut.</p></div></li>
                <li><span>3</span><div><strong>Cocokkan hasilnya</strong><p>Lihat status sertifikat beserta data penerbitan yang tercatat.</p></div></li>
            </ol>
        </aside>
    </section>

    <div class="dashboard-section-heading"><h2>Layanan Publik</h2></div>
    <section class="dashboard-quick-grid" aria-label="Layanan publik">
        @foreach ([
            ['Mitra Sekolah', 'Lihat mitra industri dan kerja sama yang berlaku.', route('publik.mitra.index')],
            ['Verifikasi Sertifikat', 'Periksa status dan kecocokan data sertifikat menggunakan kode 8 digit.', route('publik.sertifikat.form')],
        ] as [$title, $description, $href])
            <a
                href="{{ $href }}"
                class="dashboard-quick-card"
            >
                <x-dashboard-icon :name="match ($title) { 'Mitra Sekolah' => 'globe', default => 'check' }" />
                <h2 class="text-xl font-bold text-blue-950">{{ $title }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $description }}</p>
                <span class="public-action-button">
                    Buka Layanan →
                </span>
            </a>
        @endforeach
    </section>

@endsection

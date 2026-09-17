@extends('layouts.publik')

@section('title', 'Beranda')

@section('content')
    <section class="rounded-3xl bg-blue-950 px-7 py-12 text-white md:px-12">
        <p class="text-sm font-semibold uppercase tracking-widest text-blue-200">
            Portal Layanan Sekolah
        </p>

        <h1 class="mt-5 max-w-3xl text-3xl font-bold leading-tight md:text-5xl">
            Sistem Informasi Persuratan SMKN 4 Tanjungpinang
        </h1>

        <p class="mt-6 max-w-2xl leading-8 text-blue-100">
            Kirim surat, pantau prosesnya, lihat mitra sekolah,
            dan periksa sertifikat melalui satu portal.
        </p>

        <div class="mt-8 flex flex-wrap gap-4">
            <a
                href="{{ url('/kirim-surat') }}"
                class="rounded-xl bg-white px-5 py-3 font-semibold text-blue-950"
            >
                Kirim Surat
            </a>

            <a
                href="{{ url('/lacak-surat') }}"
                class="rounded-xl border border-blue-300 px-5 py-3 font-semibold"
            >
                Lacak Surat
            </a>
        </div>
    </section>

    <section class="mt-8 grid gap-5 md:grid-cols-2">
        @foreach ([
            ['Kirim Surat', 'Ajukan surat beserta lampiran PDF maksimal 5 MB.', url('/kirim-surat')],
            ['Lacak Surat', 'Periksa perkembangan surat menggunakan nomor registrasi.', url('/lacak-surat')],
            ['Mitra Sekolah', 'Lihat mitra industri dan kerja sama yang berlaku.', route('publik.mitra.index')],
            ['Verifikasi Sertifikat', 'Periksa kecocokan data dan status sertifikat melalui kode atau QR.', route('publik.sertifikat.form')],
        ] as [$title, $description, $href])
            <a
                href="{{ $href }}"
                class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:border-blue-400"
            >
                <h2 class="text-xl font-bold text-blue-950">{{ $title }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $description }}</p>
                <span class="mt-5 inline-block text-sm font-semibold text-blue-700">
                    Buka Layanan →
                </span>
            </a>
        @endforeach
    </section>

    <section class="mt-8 rounded-2xl bg-blue-50 p-6">
        <h2 class="font-bold text-blue-950">Ketentuan Pengiriman Surat</h2>
        <p class="mt-3 text-sm leading-7 text-blue-900">
            Lampiran harus berformat PDF dengan ukuran maksimal 5 MB.
            Beri jeda minimal 10 menit setelah pengiriman berhasil.
            Simpan nomor registrasi untuk memantau proses surat.
        </p>
    </section>
@endsection
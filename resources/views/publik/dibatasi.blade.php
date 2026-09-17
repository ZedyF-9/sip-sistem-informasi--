@extends('layouts.publik')

@section('title', 'Pengiriman Dibatasi')

@section('content')
    <div class="mx-auto max-w-2xl px-6 py-16">
        <section class="rounded-2xl border border-amber-200 bg-white p-8">
            <p class="font-semibold text-amber-700">Pengiriman belum dapat dilakukan</p>

            <h1 class="mt-3 text-3xl font-bold">Silakan tunggu</h1>

            <p class="mt-5 text-sm leading-7 text-slate-600">
                {{ $pesan }}
            </p>

            <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                Sisa waktu saat halaman ini dimuat:
                sekitar {{ max(1, (int) ceil($detik / 60)) }} menit.
            </p>

            <p class="mt-4 text-sm leading-7 text-slate-500">
                Surat yang sebelumnya berhasil dikirim tetap tersimpan.
                Gunakan nomor registrasi untuk memeriksa statusnya.
            </p>

            <a href="{{ route('publik.lacak') }}"
               class="mt-6 inline-block rounded-xl bg-blue-700 px-6 py-3 text-white">
                Lacak Surat Sebelumnya
            </a>

            <a href="{{ route('beranda') }}"
               class="mt-3 inline-block px-4 py-3 text-sm font-semibold text-blue-700">
                Kembali ke Beranda
            </a>
        </section>
    </div>
@endsection
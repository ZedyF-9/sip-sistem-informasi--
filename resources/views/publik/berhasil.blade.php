@extends('layouts.publik')

@section('title', 'Surat Berhasil Dikirim')

@section('content')
    <div class="mx-auto max-w-2xl px-6 py-16">
        <section class="rounded-2xl border border-slate-200 bg-white p-8">
            <p class="font-semibold text-emerald-700">Pengiriman berhasil</p>

            <h1 class="mt-3 text-3xl font-bold">Surat telah diterima sistem</h1>

            <p class="mt-4 text-sm leading-7 text-slate-600">
                Surat menunggu verifikasi Tata Usaha.
                Simpan nomor registrasi berikut untuk melacak perkembangannya.
            </p>

            <div class="mt-6 rounded-xl bg-blue-50 p-5">
                <p class="text-sm text-blue-800">Nomor registrasi</p>
                <p class="mt-2 select-all break-all font-mono text-xl font-bold text-blue-950">
                    {{ $nomor }}
                </p>
            </div>

            <p class="mt-4 text-sm text-slate-500">
                Salin nomor ini sebelum menutup halaman.
            </p>

            <a href="{{ route('publik.lacak') }}"
               class="mt-6 inline-block rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
                Lacak Status Surat
            </a>
        </section>
    </div>
@endsection
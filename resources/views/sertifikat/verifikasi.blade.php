@extends('layouts.publik')

@section('title', 'Verifikasi Sertifikat')

@section('content')
    <div class="mx-auto max-w-2xl py-10">
        <h1 class="text-3xl font-bold text-blue-950">Verifikasi Sertifikat</h1>

        <p class="mt-3 text-slate-600">
            Masukkan kode yang tercetak pada sertifikat atau pindai QR Code.
        </p>

        @if ($errors->any())
            <div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            action="{{ route('publik.sertifikat.cari') }}"
            method="POST"
            class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-6"
        >
            @csrf

            <label for="kode" class="block text-sm font-semibold">
                Kode Verifikasi
            </label>

            <input
                id="kode"
                name="kode"
                value="{{ old('kode', $kode) }}"
                minlength="64"
                maxlength="64"
                pattern="[a-f0-9]{64}"
                required
                autocomplete="off"
                spellcheck="false"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-sm"
            >

            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
                Periksa Sertifikat
            </button>
        </form>

        @if ($hasil !== null)
            @if ($hasil['status'] === 'aktif')
                <section class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                    <h2 class="text-xl font-bold text-emerald-800">
                        Sertifikat Terdaftar dan Aktif
                    </h2>

                    <dl class="mt-5 space-y-4 text-sm">
                        @foreach ([
                            'Nomor' => $hasil['nomor'],
                            'Nama' => $hasil['nama'],
                            'Kegiatan' => $hasil['kegiatan'],
                            'Peran' => $hasil['peran'],
                            'Tanggal Terbit' => $hasil['tanggal'],
                        ] as $label => $value)
                            <div>
                                <dt class="text-emerald-700">{{ $label }}</dt>
                                <dd class="mt-1 font-semibold text-emerald-950">
                                    {{ $value }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>

                    <p class="mt-5 text-sm text-emerald-800">
                        Cocokkan seluruh data di atas dengan dokumen yang diperiksa.
                    </p>
                </section>
            @elseif ($hasil['status'] === 'dicabut')
                <section class="mt-6 rounded-2xl bg-red-50 p-6 text-red-800">
                    <h2 class="font-bold">Sertifikat Tidak Berlaku</h2>
                    <p class="mt-2 text-sm">
                        Sertifikat dengan kode ini telah dicabut oleh pengelola.
                    </p>
                </section>
            @else
                <section class="mt-6 rounded-2xl bg-amber-50 p-6 text-amber-900">
                    <h2 class="font-bold">Kode Tidak Ditemukan</h2>
                    <p class="mt-2 text-sm">
                        Periksa kembali kode yang dimasukkan.
                    </p>
                </section>
            @endif
        @endif
    </div>
@endsection
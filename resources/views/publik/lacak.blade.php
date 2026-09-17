@extends('layouts.publik')

@section('title', 'Lacak Surat')

@section('content')
    <div class="mx-auto max-w-2xl px-6 py-12">
        <h1 class="text-3xl font-bold">Lacak Surat</h1>

        <p class="mt-3 text-sm leading-7 text-slate-600">
            Masukkan nomor registrasi yang diterima setelah pengiriman
            atau yang diberikan petugas TU.
        </p>

        <form method="POST" action="{{ route('publik.lacak.submit') }}"
              class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf

            <label for="nomor_registrasi" class="mb-2 block font-semibold">
                Nomor registrasi
            </label>

            <input id="nomor_registrasi" name="nomor_registrasi"
                   value="{{ old('nomor_registrasi', $nomor) }}"
                   maxlength="29" required
                   autocomplete="off" spellcheck="false"
                   placeholder="SM-..."
                   class="w-full rounded-xl border border-slate-300 p-3 font-mono">

            @error('nomor_registrasi')
                <p role="alert" class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <button class="mt-5 rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
                Periksa Status
            </button>
        </form>

        @if ($dicari)
            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6" role="status">
                @if ($hasil)
                    <p class="text-sm text-slate-500">Nomor registrasi</p>
                    <p class="mt-2 break-all font-mono font-semibold">
                        {{ $hasil['nomor'] }}
                    </p>

                    <p class="mt-5 text-sm text-slate-500">Status saat ini</p>
                    <p class="mt-2 text-xl font-bold text-blue-800">
                        {{ $hasil['status'] }}
                    </p>
                @else
                    <p class="font-semibold">Nomor registrasi tidak ditemukan.</p>
                    <p class="mt-2 text-sm text-slate-500">
                        Periksa kembali nomor yang dimasukkan.
                    </p>
                @endif
            </section>
        @endif
    </div>
@endsection
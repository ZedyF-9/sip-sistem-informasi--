@extends('layouts.tu')

@section('title', 'Catat Surat Fisik')

@section('content')
    <p class="mb-6 text-sm leading-7 text-slate-600">
        Catat surat yang diterima langsung dan unggah hasil pindainya.
        Surat disimpan dengan status Menunggu Verifikasi.
    </p>

    <form method="POST"
          action="{{ route('tu.surat-masuk.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            @foreach ([
                'nomor_surat' => ['Nomor surat pengirim', 100, false],
                'jenis_surat' => ['Jenis surat', 100, true],
                'asal_surat' => ['Asal / instansi pengirim', 255, true],
                'nama_pengirim' => ['Nama pengirim', 255, true],
                'nomor_agenda' => ['Nomor agenda, contoh: 001/TU/2026', 100, true],
                'lokasi_arsip_fisik' => ['Lokasi arsip, contoh: Lemari A / Rak 2', 255, true],
            ] as $field => $settings)
                <div>
                    <label for="{{ $field }}" class="mb-2 block text-sm font-semibold">
                        {{ $settings[0] }} {{ $settings[2] ? '*' : '(opsional)' }}
                    </label>

                    <input id="{{ $field }}"
                           name="{{ $field }}"
                           type="text"
                           value="{{ old($field) }}"
                           maxlength="{{ $settings[1] }}"
                           @required($settings[2])
                           class="w-full rounded-xl border border-slate-300 px-4 py-3">

                    @error($field)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div>
                <label for="email_pengirim" class="mb-2 block text-sm font-semibold">
                    Email pengirim (opsional)
                </label>

                <input id="email_pengirim"
                       name="email_pengirim"
                       type="email"
                       value="{{ old('email_pengirim') }}"
                       maxlength="255"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3">

                @error('email_pengirim')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sifat_surat" class="mb-2 block text-sm font-semibold">
                    Sifat surat *
                </label>

                <select id="sifat_surat"
                        name="sifat_surat"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                    @foreach (['biasa' => 'Biasa', 'penting' => 'Penting', 'rahasia' => 'Rahasia'] as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('sifat_surat', 'biasa') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @error('sifat_surat')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @foreach ([
                'tanggal_surat' => 'Tanggal surat',
                'tanggal_diterima' => 'Tanggal diterima',
            ] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-2 block text-sm font-semibold">
                        {{ $label }} *
                    </label>

                    <input id="{{ $field }}"
                           name="{{ $field }}"
                           type="date"
                           value="{{ old($field, now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}"
                           required
                           class="w-full rounded-xl border border-slate-300 px-4 py-3">

                    @error($field)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="md:col-span-2">
                <label for="perihal" class="mb-2 block text-sm font-semibold">
                    Perihal *
                </label>

                <input id="perihal"
                       name="perihal"
                       value="{{ old('perihal') }}"
                       maxlength="255"
                       required
                       class="w-full rounded-xl border border-slate-300 px-4 py-3">

                @error('perihal')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="file_surat" class="mb-2 block text-sm font-semibold">
                    Hasil pindai PDF — maksimal 5 MB *
                </label>

                <input id="file_surat"
                       name="file_surat"
                       type="file"
                       accept=".pdf,application/pdf"
                       required
                       class="w-full rounded-xl border border-slate-300 p-3">

                <p class="mt-2 text-xs text-slate-500">
                    Jika formulir gagal divalidasi, pilih ulang berkas PDF.
                </p>

                @error('file_surat')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <button class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white hover:bg-blue-800">
                Simpan Surat
            </button>

            <a href="{{ route('tu.surat-masuk.index') }}"
               class="rounded-xl border border-slate-300 px-6 py-3">
                Batal
            </a>
        </div>
    </form>
@endsection
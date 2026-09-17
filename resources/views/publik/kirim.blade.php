@extends('layouts.publik')

@section('title', 'Kirim Surat')

@section('content')
    <div class="mx-auto max-w-3xl px-6 py-12">
        <h1 class="text-3xl font-bold">Kirim Surat</h1>

        <p class="mt-3 text-sm leading-7 text-slate-600">
            Isi formulir berikut dan unggah PDF maksimal 5 MB.
            Surat akan diperiksa oleh Tata Usaha.
        </p>

        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-xl bg-red-50 p-4 text-red-800">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('publik.store') }}"
              enctype="multipart/form-data"
              class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                @foreach ([
                    'nomor_surat' => ['Nomor surat (opsional)', 100, false],
                    'jenis_surat' => ['Jenis surat', 100, true],
                    'asal_surat' => ['Asal / instansi pengirim', 255, true],
                    'nama_pengirim' => ['Nama pengirim', 255, true],
                ] as $field => $settings)
                    <div>
                        <label for="{{ $field }}" class="mb-2 block text-sm font-semibold">
                            {{ $settings[0] }}
                        </label>

                        <input id="{{ $field }}" name="{{ $field }}"
                               value="{{ old($field) }}"
                               maxlength="{{ $settings[1] }}"
                               @required($settings[2])
                               class="w-full rounded-xl border border-slate-300 p-3">

                        @error($field)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <div>
                    <label for="email_pengirim" class="mb-2 block text-sm font-semibold">
                        Email (opsional)
                    </label>

                    <input id="email_pengirim" name="email_pengirim"
                           type="email" value="{{ old('email_pengirim') }}"
                           maxlength="255"
                           class="w-full rounded-xl border border-slate-300 p-3">

                    @error('email_pengirim')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sifat_surat" class="mb-2 block text-sm font-semibold">
                        Sifat surat
                    </label>

                    <select id="sifat_surat" name="sifat_surat" required
                            class="w-full rounded-xl border border-slate-300 bg-white p-3">
                        @foreach (['biasa', 'penting', 'rahasia'] as $value)
                            <option value="{{ $value }}"
                                @selected(old('sifat_surat', 'biasa') === $value)>
                                {{ ucfirst($value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal_surat" class="mb-2 block text-sm font-semibold">
                        Tanggal surat
                    </label>

                    <input id="tanggal_surat" name="tanggal_surat"
                           type="date" required
                           value="{{ old('tanggal_surat', today()->toDateString()) }}"
                           max="{{ today()->toDateString() }}"
                           class="w-full rounded-xl border border-slate-300 p-3">

                    @error('tanggal_surat')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="perihal" class="mb-2 block text-sm font-semibold">
                        Perihal
                    </label>

                    <input id="perihal" name="perihal" required
                           value="{{ old('perihal') }}" maxlength="255"
                           class="w-full rounded-xl border border-slate-300 p-3">

                    @error('perihal')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="file_surat" class="mb-2 block text-sm font-semibold">
                        Dokumen PDF — maksimal 5 MB
                    </label>

                    <input id="file_surat" name="file_surat" type="file"
                           accept=".pdf,application/pdf" required
                           class="w-full rounded-xl border border-slate-300 p-3">

                    @error('file_surat')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs text-slate-500">
                        Pilih ulang berkas jika ada kesalahan pengisian.
                    </p>
                </div>
            </div>

            <p class="mt-6 rounded-xl bg-blue-50 p-4 text-sm leading-7 text-blue-900">
                Setelah berhasil, tunggu 10 menit sebelum mengirim surat berikutnya.
                Percobaan berulang selama masa tunggu dapat menyebabkan blokir 24 jam.
            </p>

            <button type="submit"
                    class="mt-6 rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
                Kirim Surat
            </button>
        </form>
    </div>
@endsection
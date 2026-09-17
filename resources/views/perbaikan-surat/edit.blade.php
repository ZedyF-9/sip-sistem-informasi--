@extends('layouts.internal')

@section('title', 'Perbaiki Surat Masuk')

@section('content')
    <form
        action="{{ route('tu.perbaikan.update', $surat) }}"
        method="POST"
        enctype="multipart/form-data"
        class="max-w-4xl space-y-5 rounded-2xl bg-white p-6"
    >
        @csrf
        @method('PUT')

        <input type="hidden" name="versi" value="{{ $versi }}">

        <p class="rounded-xl bg-blue-50 p-4 text-sm text-blue-900">
            {{ $surat->nomor_registrasi }} ·
            Metode: {{ $surat->metode_pengiriman }}
        </p>

        <div class="grid gap-5 md:grid-cols-2">
            @foreach ([
                'nomor_surat' => ['Nomor Surat', 100, false],
                'jenis_surat' => ['Jenis Surat', 100, true],
                'asal_surat' => ['Asal Surat', 255, true],
                'nama_pengirim' => ['Nama Pengirim', 255, true],
                'perihal' => ['Perihal', 255, true],
                'nomor_agenda' => ['Nomor Agenda', 100, $surat->metode_pengiriman === 'fisik'],
                'lokasi_arsip_fisik' => ['Lokasi Arsip Fisik', 255, $surat->metode_pengiriman === 'fisik'],
            ] as $field => [$label, $max, $required])
                <div>
                    <label for="{{ $field }}" class="mb-2 block text-sm font-semibold">
                        {{ $label }}
                    </label>
                    <input
                        id="{{ $field }}"
                        name="{{ $field }}"
                        value="{{ old($field, $surat->{$field}) }}"
                        maxlength="{{ $max }}"
                        @required($required)
                        class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                </div>
            @endforeach

            <div>
                <label for="email_pengirim" class="mb-2 block text-sm font-semibold">
                    Email Pengirim
                </label>
                <input
                    id="email_pengirim"
                    name="email_pengirim"
                    type="email"
                    value="{{ old('email_pengirim', $surat->email_pengirim) }}"
                    maxlength="255"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
            </div>

            <div>
                <label for="sifat_surat" class="mb-2 block text-sm font-semibold">
                    Sifat Surat
                </label>
                <select
                    id="sifat_surat"
                    name="sifat_surat"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
                    @foreach (['biasa', 'penting', 'rahasia'] as $sifat)
                        <option
                            value="{{ $sifat }}"
                            @selected(old('sifat_surat', $surat->sifat_surat) === $sifat)
                        >
                            {{ ucfirst($sifat) }}
                        </option>
                    @endforeach
                </select>
            </div>

            @foreach ([
                'tanggal_surat' => 'Tanggal Surat',
                'tanggal_diterima' => 'Tanggal Diterima',
            ] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-2 block text-sm font-semibold">
                        {{ $label }}
                    </label>
                    <input
                        id="{{ $field }}"
                        name="{{ $field }}"
                        type="date"
                        value="{{ old($field, $surat->{$field}?->format('Y-m-d')) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                </div>
            @endforeach
        </div>

        <div>
            <label for="file_surat" class="mb-2 block text-sm font-semibold">
                Ganti PDF — opsional, maksimal 5 MB
            </label>
            <input
                id="file_surat"
                name="file_surat"
                type="file"
                accept=".pdf,application/pdf"
                class="block w-full text-sm"
            >
            <p class="mt-2 text-sm text-slate-500">
                Kosongkan jika berkas lama tetap digunakan.
            </p>
        </div>

        <div>
            <label for="alasan" class="mb-2 block text-sm font-semibold">
                Alasan Perbaikan
            </label>
            <textarea
                id="alasan"
                name="alasan"
                rows="3"
                minlength="5"
                maxlength="1000"
                required
                class="w-full rounded-xl border border-slate-300 p-3"
            >{{ old('alasan') }}</textarea>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
                Simpan Perbaikan
            </button>
            <a href="{{ route('tu.perbaikan.index') }}" class="rounded-xl border border-slate-300 px-5 py-3">Batal</a>
        </div>
    </form>
@endsection

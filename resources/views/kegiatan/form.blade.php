@extends('layouts.internal')

@section('title', $kegiatan->exists ? 'Edit Kegiatan' : 'Tambah Kegiatan')

@section('content')
    <form
        action="{{ $kegiatan->exists
            ? route('tu.kegiatan.update', $kegiatan)
            : route('tu.kegiatan.store') }}"
        method="POST"
        class="max-w-3xl space-y-5 rounded-2xl bg-white p-6"
    >
        @csrf

        @if ($kegiatan->exists)
            @method('PUT')
        @endif

        <div>
            <label for="nama" class="mb-2 block font-semibold">Nama Kegiatan</label>
            <input
                id="nama"
                name="nama"
                value="{{ old('nama', $kegiatan->nama) }}"
                maxlength="150"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
        </div>

        <div>
            <label for="lokasi" class="mb-2 block font-semibold">Lokasi</label>
            <input
                id="lokasi"
                name="lokasi"
                value="{{ old('lokasi', $kegiatan->lokasi) }}"
                maxlength="150"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="tanggal_mulai" class="mb-2 block font-semibold">
                    Tanggal Mulai
                </label>
                <input
                    id="tanggal_mulai"
                    name="tanggal_mulai"
                    type="date"
                    value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai?->format('Y-m-d')) }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
            </div>

            <div>
                <label for="tanggal_selesai" class="mb-2 block font-semibold">
                    Tanggal Selesai
                </label>
                <input
                    id="tanggal_selesai"
                    name="tanggal_selesai"
                    type="date"
                    value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai?->format('Y-m-d')) }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
            </div>
        </div>

        <div>
            <label for="deskripsi" class="mb-2 block font-semibold">Deskripsi</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="4"
                maxlength="2000"
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
        </div>

        <div class="flex gap-4">
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
                Simpan
            </button>

            <a href="{{ $kegiatan->exists ? route('tu.kegiatan.show', $kegiatan) : route('tu.kegiatan.index') }}" class="rounded-xl border border-slate-300 px-5 py-3">
                Batal
            </a>
        </div>
    </form>
@endsection

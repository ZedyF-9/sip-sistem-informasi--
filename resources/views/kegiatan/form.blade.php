@extends('layouts.internal')

@section('title', $kegiatan->exists ? 'Edit Sesi PKL' : 'Buat Sesi PKL')

@section('content')
    <form action="{{ $kegiatan->exists ? route('tu.kegiatan.update', $kegiatan) : route('tu.kegiatan.store') }}"
          method="POST" class="max-w-3xl space-y-5 rounded-2xl bg-white p-6">
        @csrf
        @if ($kegiatan->exists)
            @method('PUT')
        @endif

        <p class="text-sm leading-6 text-slate-600">
            Buat satu sesi/gelombang PKL. Untuk dua sesi, buat masing-masing sebagai data terpisah lalu impor daftar siswa sesuai sesinya.
        </p>

        <div>
            <label for="nama" class="mb-2 block font-semibold">Nama sesi / angkatan PKL</label>
            <input id="nama" name="nama" value="{{ old('nama', $kegiatan->nama) }}"
                   maxlength="150" required placeholder="Contoh: PKL 2026/2027 - Sesi 1"
                   class="w-full rounded-xl border border-slate-300 px-4 py-3">
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="tanggal_mulai" class="mb-2 block font-semibold">Tanggal mulai PKL</label>
                <input id="tanggal_mulai" name="tanggal_mulai" type="date"
                       value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai?->format('Y-m-d')) }}"
                       required class="w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
            <div>
                <label for="tanggal_selesai" class="mb-2 block font-semibold">Tanggal selesai PKL</label>
                <input id="tanggal_selesai" name="tanggal_selesai" type="date"
                       value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai?->format('Y-m-d')) }}"
                       required class="w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
        </div>

        <div>
            <label for="deskripsi" class="mb-2 block font-semibold">Keterangan (opsional)</label>
            <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="2000"
                      class="w-full rounded-xl border border-slate-300 px-4 py-3">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
        </div>

        <div class="flex gap-4">
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">Simpan Sesi PKL</button>
            <a href="{{ $kegiatan->exists ? route('tu.kegiatan.show', $kegiatan) : route('tu.kegiatan.index') }}"
               class="rounded-xl border border-slate-300 px-5 py-3">Batal</a>
        </div>
    </form>
@endsection

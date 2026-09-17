@extends('layouts.internal')

@section('title', $kerjasama->exists ? 'Edit Kerja Sama' : 'Tambah Kerja Sama')

@section('content')
    <p class="mb-6 text-slate-600">Mitra: {{ $mitra->nama_mitra }}</p>

    <form method="POST"
          action="{{ $kerjasama->exists
              ? route('tu.kerjasama.update', $kerjasama)
              : route('tu.kerjasama.store', $mitra) }}"
          enctype="multipart/form-data"
          class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf

        @if ($kerjasama->exists)
            @method('PUT')
        @endif

        <div>
            <label for="judul" class="mb-2 block font-semibold">Judul / ruang lingkup kerja sama</label>
            <input id="judul" name="judul" required maxlength="255"
                   value="{{ old('judul', $kerjasama->judul) }}"
                   class="w-full rounded-xl border border-slate-300 p-3">
        </div>

        <div>
            <label for="nomor_mou" class="mb-2 block font-semibold">Nomor MoU (opsional)</label>
            <input id="nomor_mou" name="nomor_mou" maxlength="100"
                   value="{{ old('nomor_mou', $kerjasama->nomor_mou) }}"
                   class="w-full rounded-xl border border-slate-300 p-3">
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="tanggal_mulai" class="mb-2 block font-semibold">Tanggal mulai</label>
                <input id="tanggal_mulai" name="tanggal_mulai" type="date" required
                       value="{{ old('tanggal_mulai', $kerjasama->tanggal_mulai?->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-300 p-3">
            </div>
            <div>
                <label for="tanggal_selesai" class="mb-2 block font-semibold">Tanggal selesai (opsional)</label>
                <input id="tanggal_selesai" name="tanggal_selesai" type="date"
                       value="{{ old('tanggal_selesai', $kerjasama->tanggal_selesai?->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-300 p-3">
            </div>
        </div>

        <div>
            <label for="status" class="mb-2 block font-semibold">Status</label>
            <select id="status" name="status"
                    class="w-full rounded-xl border border-slate-300 bg-white p-3">
                <option value="aktif"
                    @selected(old('status', $kerjasama->status ?? 'aktif') === 'aktif')>
                    Aktif
                </option>
                <option value="berakhir"
                    @selected(old('status', $kerjasama->status) === 'berakhir')>
                    Berakhir
                </option>
            </select>
            <p class="mt-2 text-sm text-slate-500">
                Portal publik tetap memeriksa tanggal mulai dan selesai.
            </p>
        </div>

        <div>
            <label for="dokumen_mou" class="mb-2 block font-semibold">
                Dokumen MoU PDF (opsional, maksimal 5 MB)
            </label>
            <input id="dokumen_mou" name="dokumen_mou" type="file"
                   accept=".pdf,application/pdf"
                   class="w-full rounded-xl border border-slate-300 p-3">

            @if ($kerjasama->dokumen_mou)
                <a href="{{ route('tu.kerjasama.download', $kerjasama) }}"
                   class="mt-3 inline-block text-sm font-semibold text-blue-700">
                    Unduh dokumen saat ini
                </a>
                <p class="mt-2 text-sm text-slate-500">
                    Kosongkan unggahan untuk mempertahankan dokumen.
                </p>
            @endif
        </div>

        <div class="flex gap-3">
            <button class="rounded-xl bg-blue-700 px-6 py-3 text-white">
                Simpan Kerja Sama
            </button>
            <a href="{{ route('tu.mitra.show', $mitra) }}"
               class="rounded-xl border px-6 py-3">Batal</a>
        </div>
    </form>
@endsection
@extends('layouts.tu')

@section('title', 'Catat Surat Masuk')

@section('content')
    <div class="mb-6 max-w-3xl">
        <p class="text-sm leading-6 text-slate-600">Catat identitas surat sesuai dokumen asli. Surat yang disimpan langsung masuk arsip dan siap diproses untuk disposisi.</p>
        <p class="mt-2 text-xs text-slate-500"><span class="font-semibold text-red-700">*</span> Wajib diisi · Lampirkan PDF maksimal 5 MB.</p>
    </div>

    <form method="POST" action="{{ route('tu.surat-masuk.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        @csrf

        <fieldset class="grid gap-x-5 gap-y-4 md:grid-cols-2">
            <legend class="mb-4 text-base font-bold text-slate-900">Identitas surat</legend>
            <div class="md:col-span-2 rounded-lg border border-blue-100 bg-blue-50/60 p-4">
                <p class="text-sm font-semibold text-slate-800">Nomor agenda dibuat otomatis</p>
                <p class="mt-1 text-xs leading-5 text-slate-600">Sistem memberi nomor berurutan berdasarkan tahun tanggal diterima saat surat disimpan.</p>
            </div>

            <div>
                <label for="nomor_surat" class="mb-2 block text-sm font-semibold">Nomor surat</label>
                <input id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}"
                       maxlength="100" placeholder="Nomor pada surat (jika ada)"
                       class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
                @error('nomor_surat')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="tanggal_surat" class="mb-2 block text-sm font-semibold">Tanggal surat *</label>
                <input id="tanggal_surat" name="tanggal_surat" type="date"
                       value="{{ old('tanggal_surat', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required
                       class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
                @error('tanggal_surat')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="tanggal_diterima" class="mb-2 block text-sm font-semibold">Tanggal diterima *</label>
                <input id="tanggal_diterima" name="tanggal_diterima" type="date"
                       value="{{ old('tanggal_diterima', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required
                       class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
                @error('tanggal_diterima')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2 border-t border-slate-100 pt-4">
                <p class="mb-4 text-base font-bold text-slate-900">Asal dan isi surat</p>
                <label for="asal_surat" class="mb-2 block text-sm font-semibold">Alamat asal (pengirim) *</label>
                <input id="asal_surat" name="asal_surat" value="{{ old('asal_surat') }}"
                       maxlength="255" required placeholder="Nama instansi atau alamat pengirim"
                       class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
                @error('asal_surat')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="perihal" class="mb-2 block text-sm font-semibold">Perihal *</label>
                <input id="perihal" name="perihal" value="{{ old('perihal') }}"
                       maxlength="255" required placeholder="Ringkasan pokok surat"
                       class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
                @error('perihal')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="keterangan" class="mb-2 block text-sm font-semibold">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3" maxlength="2000"
                          placeholder="Catatan tambahan (opsional)" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">{{ old('keterangan') }}</textarea>
                @error('keterangan')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2 border-t border-slate-100 pt-4">
                <p class="mb-3 text-base font-bold text-slate-900">Berkas surat</p>
                <label for="file_surat" class="mb-2 block text-sm font-semibold">PDF surat (maksimal 5 MB) *</label>
                <input id="file_surat" name="file_surat" type="file" accept=".pdf,application/pdf" required
                       class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold file:text-slate-700 focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15">
                <p class="mt-2 text-xs text-slate-500">Jika formulir gagal divalidasi, pilih ulang berkas PDF.</p>
                @error('file_surat')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </fieldset>

        <div class="mt-8 flex flex-wrap gap-3">
            <button class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white hover:bg-blue-800">Simpan ke Arsip Surat Masuk</button>
            <a href="{{ route('tu.surat-masuk.index') }}" class="rounded-xl border border-slate-300 px-6 py-3">Batal</a>
        </div>
    </form>
@endsection

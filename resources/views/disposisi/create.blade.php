@extends('layouts.internal')

@section('title', 'Ajukan Surat untuk Disposisi')

@section('content')
    <form method="POST" action="{{ route('disposisi.store') }}"
          class="max-w-3xl space-y-6 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <p class="font-semibold text-blue-950">Alur pengajuan</p>
            <p class="mt-1 text-sm leading-6 text-blue-800">TU memilih surat dan mengirim permohonan. Kepala Sekolah akan meninjau, menyetujui, lalu mengisi sendiri lembar disposisi resmi dan menentukan penerimanya.</p>
        </div>

        <div>
            <label for="surat_masuk_id" class="mb-2 block font-semibold">Surat masuk</label>
            <select id="surat_masuk_id" name="surat_masuk_id" required class="w-full rounded-xl border border-slate-300 bg-white p-3">
                <option value="">Pilih surat masuk</option>
                @foreach ($surats as $surat)
                    <option value="{{ $surat->id }}" @selected((string) old('surat_masuk_id', request()->query('surat_masuk_id')) === (string) $surat->id)>
                        {{ $surat->nomor_registrasi }} — {{ $surat->perihal }}
                    </option>
                @endforeach
            </select>
            @if ($surats->isEmpty())
                <p class="mt-2 text-sm text-amber-700">Belum ada surat masuk yang tersedia untuk diajukan.</p>
            @endif
        </div>

        @if ($kepalaSekolah)
            <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-700">Dikirim untuk ditinjau oleh <strong>{{ $kepalaSekolah->name }}</strong> sebagai Kepala Sekolah.</p>
        @else
            <p class="rounded-xl bg-red-50 p-4 text-sm font-semibold text-red-700">Akun Kepala Sekolah aktif belum tersedia. Pengajuan belum dapat dikirim.</p>
        @endif

        <div>
            <label for="catatan_pengajuan" class="mb-2 block font-semibold">Catatan pengajuan (opsional)</label>
            <textarea id="catatan_pengajuan" name="catatan_pengajuan" rows="3" maxlength="2000" class="w-full rounded-xl border border-slate-300 p-3">{{ old('catatan_pengajuan') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Catatan ini hanya untuk Kepala Sekolah saat meninjau, bukan instruksi pada lembar disposisi resmi.</p>
        </div>

        <div class="flex gap-3">
            <button @disabled(! $kepalaSekolah || $surats->isEmpty()) class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">Kirim Permohonan</button>
            <a href="{{ route('disposisi.index') }}" class="rounded-xl border border-slate-300 px-6 py-3">Batal</a>
        </div>
    </form>
@endsection

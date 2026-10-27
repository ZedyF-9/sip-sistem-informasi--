@extends('layouts.internal')

@section('title', 'Detail Kerja Sama')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ $backUrl }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">Kembali</a>
        @if ($kerjasama->dokumen_mou)
            <a href="{{ route($routePrefix.'.download', $kerjasama) }}"
               class="rounded-xl bg-blue-700 px-5 py-3 text-white">Lihat MoU</a>
        @endif
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <p class="text-sm text-slate-500">{{ $kerjasama->mitra->nama_mitra }}</p>
        <h2 class="mt-2 text-2xl font-bold">{{ $kerjasama->judul }}</h2>
        <dl class="mt-6 grid gap-5 md:grid-cols-2">
            <div>
                <dt class="text-sm text-slate-500">Nomor MoU</dt>
                <dd class="mt-1">{{ $kerjasama->nomor_mou ?: 'Belum dicantumkan' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Periode</dt>
                <dd class="mt-1">
                    {{ $kerjasama->tanggal_mulai->format('d/m/Y') }}
                    – {{ $kerjasama->tanggal_selesai?->format('d/m/Y') ?? 'Tanpa batas akhir' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Persetujuan Waka Humas</dt>
                <dd class="mt-1">{{ ucfirst($kerjasama->status_persetujuan) }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Kondisi kerja sama</dt>
                <dd class="mt-1">{{ $kerjasama->labelPeriode() }}</dd>
            </div>
        </dl>
        @if ($kerjasama->catatan_persetujuan)
            <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                Catatan: {{ $kerjasama->catatan_persetujuan }}
            </p>
        @endif
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
        <div>
            <h2 class="text-xl font-bold">Agenda Kerja Sama</h2>
            <p class="mt-1 text-sm text-slate-600">
                Catat kegiatan dan jadwal yang berkaitan dengan kerja sama ini.
            </p>
        </div>

        @if ($kerjasama->status_persetujuan === 'disetujui')
            <form method="POST" action="{{ route($routePrefix.'.agenda.store', $kerjasama) }}"
                  class="mt-5 grid gap-4 rounded-xl bg-slate-50 p-5 md:grid-cols-2">
                @csrf
                <div>
                    <label for="judul" class="mb-2 block font-semibold">Nama agenda</label>
                    <input id="judul" name="judul" value="{{ old('judul') }}" maxlength="255"
                           required class="w-full rounded-lg border border-slate-300 bg-white p-3">
                </div>
                <div>
                    <label for="tanggal" class="mb-2 block font-semibold">Tanggal</label>
                    <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal') }}"
                           required class="w-full rounded-lg border border-slate-300 bg-white p-3">
                </div>
                <div>
                    <label for="waktu" class="mb-2 block font-semibold">Waktu (opsional)</label>
                    <input id="waktu" name="waktu" type="time" value="{{ old('waktu') }}"
                           class="w-full rounded-lg border border-slate-300 bg-white p-3">
                </div>
                <div>
                    <label for="lokasi" class="mb-2 block font-semibold">Lokasi (opsional)</label>
                    <input id="lokasi" name="lokasi" value="{{ old('lokasi') }}" maxlength="255"
                           class="w-full rounded-lg border border-slate-300 bg-white p-3">
                </div>
                <div class="md:col-span-2">
                    <label for="catatan" class="mb-2 block font-semibold">Catatan (opsional)</label>
                    <textarea id="catatan" name="catatan" rows="3" maxlength="2000"
                              class="w-full rounded-lg border border-slate-300 bg-white p-3">{{ old('catatan') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
                        Tambahkan Agenda
                    </button>
                </div>
            </form>
        @else
            <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                Agenda dapat ditambahkan setelah kerja sama disetujui Waka Humas.
            </p>
        @endif

        <div class="mt-6 space-y-3">
            @forelse ($kerjasama->agendas as $agenda)
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h3 class="font-bold">{{ $agenda->judul }}</h3>
                        <span class="text-sm font-medium text-blue-800">
                            {{ $agenda->tanggal->format('d/m/Y') }}
                            @if ($agenda->waktu)
                                · {{ \Illuminate\Support\Carbon::parse($agenda->waktu)->format('H:i') }} WIB
                            @endif
                        </span>
                    </div>
                    @if ($agenda->lokasi)
                        <p class="mt-2 text-sm">Lokasi: {{ $agenda->lokasi }}</p>
                    @endif
                    @if ($agenda->catatan)
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $agenda->catatan }}</p>
                    @endif
                    <p class="mt-3 text-xs text-slate-500">
                        Ditambahkan oleh {{ $agenda->pembuat?->name ?? 'Pengguna yang sudah dihapus' }}
                    </p>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                    Belum ada agenda untuk kerja sama ini.
                </p>
            @endforelse
        </div>
    </section>
@endsection

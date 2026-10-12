@extends('layouts.internal')

@section('title', 'Detail Mitra Industri')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('tu.mitra.index') }}"
           class="rounded-xl border bg-white px-5 py-3">Kembali</a>
        <a href="{{ route('tu.mitra.edit', $mitra) }}"
           class="rounded-xl bg-blue-700 px-5 py-3 text-white">Edit Mitra</a>
        <x-hapus-data :action="route('tu.mitra.destroy', $mitra)" :label="$mitra->nama_mitra"
            :message="'Hapus mitra '.$mitra->nama_mitra.'? Semua agenda, kerja sama lama, logo, dan dokumen MoU terkait ikut dihapus permanen.'" />

        @if ($mitra->status === 'aktif' && $mitra->status_persetujuan === 'disetujui')
            <a href="{{ route('publik.mitra.show', $mitra) }}"
               class="rounded-xl border border-blue-700 px-5 py-3 text-blue-700">
                Lihat Portal Publik
            </a>
        @endif
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        @if ($mitra->logo)
            <img src="{{ route('tu.mitra.logo', $mitra) }}"
                 alt="Logo {{ $mitra->nama_mitra }}"
                 class="mb-5 h-24 w-24 rounded-xl border object-contain p-3">
        @endif

        <h2 class="text-2xl font-bold">{{ $mitra->nama_mitra }}</h2>
        <p class="mt-2 text-blue-700">{{ $mitra->bidang_usaha }}</p>

        <dl class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach ([
                'Status publikasi' => ucfirst($mitra->status),
                'Persetujuan Waka Humas' => ucfirst($mitra->status_persetujuan),
                'Alamat' => $mitra->alamat,
                'Telepon' => $mitra->telepon,
                'Email' => $mitra->email,
                'Website' => $mitra->website,
            ] as $label => $value)
                <div>
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="mt-1 break-words">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>

        <h3 class="mt-6 font-semibold">Profil publik</h3>
        <p class="mt-3 whitespace-pre-line text-sm leading-7">{{ $mitra->deskripsi }}</p>
        @if ($mitra->catatan_persetujuan)
            <p class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-800">Catatan penolakan: {{ $mitra->catatan_persetujuan }}</p>
        @endif
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
        <div>
            <h2 class="text-xl font-bold">Agenda Mitra</h2>
            <p class="mt-1 text-sm text-slate-600">Catat kunjungan, rapat, dan kegiatan lain bersama mitra ini.</p>
        </div>

        <form method="POST" action="{{ route('tu.mitra.agenda.store', $mitra) }}"
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
                <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">Tambahkan Agenda</button>
            </div>
        </form>

        <div class="mt-6 space-y-3">
            @forelse ($agendas as $agenda)
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h3 class="font-bold">{{ $agenda->judul }}</h3>
                        <span class="text-sm font-medium text-blue-800">
                            {{ $agenda->tanggal->format('d/m/Y') }}
                            @if ($agenda->waktu)
                                · {{ substr($agenda->waktu, 0, 5) }} WIB
                            @endif
                        </span>
                    </div>
                    @if ($agenda->lokasi)
                        <p class="mt-2 text-sm">Lokasi: {{ $agenda->lokasi }}</p>
                    @endif
                    @if ($agenda->catatan)
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $agenda->catatan }}</p>
                    @endif
                    <p class="mt-3 text-xs text-slate-500">Ditambahkan oleh {{ $agenda->pembuat?->name ?? 'Pengguna yang sudah dihapus' }}</p>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Belum ada agenda untuk mitra ini.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $agendas->links() }}</div>
    </section>
@endsection

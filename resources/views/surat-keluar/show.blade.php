@extends('layouts.internal')

@section('title', 'Detail Surat Keluar')

@section('content')
    @php
        $isTu = auth()->user()->role === 'koordinator_tu';
    @endphp

    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('surat-keluar.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">Kembali</a>

        <a href="{{ route('surat-keluar.download', $surat) }}"
           class="rounded-xl bg-blue-700 px-5 py-3 text-white">Unduh PDF</a>

        @if ($isTu && in_array($surat->status, ['draf', 'revisi'], true))
            <a href="{{ route('surat-keluar.edit', $surat) }}"
               class="rounded-xl border border-blue-700 px-5 py-3 text-blue-700">
                Edit Draf
            </a>
        @endif
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <p class="font-semibold text-blue-700">
            {{ $surat->nomor_surat ?: 'Belum bernomor' }}
        </p>
        <h2 class="mt-3 text-2xl font-bold">{{ $surat->perihal }}</h2>

        <dl class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach ([
                'Tujuan' => $surat->tujuan,
                'Jenis' => $surat->jenis_surat,
                'Sifat' => ucfirst($surat->sifat_surat),
                'Tanggal surat' => $surat->tanggal_surat->format('d/m/Y'),
                'Status' => ucfirst($surat->status),
                'Pembuat' => $surat->pembuat->name,
                'Disetujui oleh' => $surat->penyetuju?->name,
                'Waktu persetujuan' => $surat->approved_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                'Waktu pengiriman' => $surat->dikirim_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                'Metode pengiriman' => $surat->metode_pengiriman,
            ] as $label => $value)
                <div>
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="mt-1 break-words font-medium">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($surat->catatan_persetujuan)
            <div class="mt-5 rounded-xl bg-blue-50 p-4">
                <p class="font-semibold">Catatan keputusan</p>
                <p class="mt-2 whitespace-pre-line">{{ $surat->catatan_persetujuan }}</p>
            </div>
        @endif

        @if ($surat->catatan_pengiriman)
            <div class="mt-5 rounded-xl bg-emerald-50 p-4">
                <p class="font-semibold">Catatan pengiriman</p>
                <p class="mt-2 whitespace-pre-line">{{ $surat->catatan_pengiriman }}</p>
            </div>
        @endif

        <p class="mt-5 text-xs text-slate-500">Waktu ditampilkan dalam WIB.</p>
    </section>

    @if ($isTu && in_array($surat->status, ['draf', 'revisi'], true))
        <form method="POST" action="{{ route('surat-keluar.aksi', $surat) }}"
              class="mt-6 rounded-2xl bg-white p-6">
            @csrf
            <input type="hidden" name="version" value="{{ $surat->version }}">
            <input type="hidden" name="aksi" value="ajukan">
            <p class="mb-4 text-sm text-slate-600">
                Pastikan data dan PDF sudah benar sebelum diajukan.
            </p>
            <button class="rounded-xl bg-blue-700 px-6 py-3 text-white">
                Ajukan ke Kepala Sekolah
            </button>
        </form>
    @endif

    @if (! $isTu && $surat->status === 'diajukan')
        <form method="POST" action="{{ route('surat-keluar.aksi', $surat) }}"
              class="mt-6 space-y-4 rounded-2xl bg-white p-6">
            @csrf
            <input type="hidden" name="version" value="{{ $surat->version }}">

            <h2 class="text-lg font-bold">Keputusan Kepala Sekolah</h2>

            <label for="aksi" class="block font-semibold">Keputusan</label>
            <select id="aksi" name="aksi" required
                    class="w-full rounded-xl border border-slate-300 p-3">
                <option value="">Pilih keputusan</option>
                <option value="setujui" @selected(old('aksi') === 'setujui')>Setujui</option>
                <option value="revisi" @selected(old('aksi') === 'revisi')>Minta Revisi</option>
            </select>

            <label for="catatan" class="block font-semibold">Catatan</label>
            <textarea id="catatan" name="catatan" rows="4" maxlength="2000"
                      class="w-full rounded-xl border border-slate-300 p-3">{{ old('catatan') }}</textarea>

            <p class="text-sm text-slate-500">Alasan wajib diisi untuk revisi.</p>

            <button class="rounded-xl bg-blue-700 px-6 py-3 text-white">
                Simpan Keputusan
            </button>
        </form>
    @endif

    @if ($isTu && $surat->status === 'disetujui' && ! $surat->nomor_surat)
        <form method="POST" action="{{ route('surat-keluar.aksi', $surat) }}"
              class="mt-6 rounded-2xl bg-white p-6">
            @csrf
            <input type="hidden" name="version" value="{{ $surat->version }}">
            <input type="hidden" name="aksi" value="nomor">
            <h2 class="text-lg font-bold">Penomoran Surat</h2>
            <p class="mt-2 text-sm text-slate-600">
                Nomor otomatis disimpan pada data surat.
            </p>
            <button class="mt-4 rounded-xl bg-blue-700 px-6 py-3 text-white">
                Berikan Nomor Surat
            </button>
        </form>
    @endif

    @if ($isTu && $surat->status === 'disetujui' && $surat->nomor_surat)
        <form method="POST" action="{{ route('surat-keluar.aksi', $surat) }}"
              class="mt-6 space-y-4 rounded-2xl bg-white p-6">
            @csrf
            <input type="hidden" name="version" value="{{ $surat->version }}">
            <input type="hidden" name="aksi" value="kirim">

            <h2 class="text-lg font-bold">Catat Pengiriman</h2>
            <p class="text-sm text-slate-600">
                Isi setelah surat benar-benar dikirim melalui saluran yang dipilih.
            </p>

            <label for="metode_pengiriman" class="block font-semibold">Metode</label>
            <select id="metode_pengiriman" name="metode_pengiriman" required
                    class="w-full rounded-xl border border-slate-300 p-3">
                <option value="">Pilih metode</option>
                @foreach (['langsung', 'pos', 'email', 'lainnya'] as $value)
                    <option value="{{ $value }}" @selected(old('metode_pengiriman') === $value)>
                        {{ ucfirst($value) }}
                    </option>
                @endforeach
            </select>

            <label for="catatan_pengiriman" class="block font-semibold">
                Catatan penerima / nomor resi / keterangan pengiriman
            </label>
            <textarea id="catatan_pengiriman" name="catatan_pengiriman"
                      rows="4" maxlength="2000" required
                      class="w-full rounded-xl border border-slate-300 p-3">{{ old('catatan_pengiriman') }}</textarea>

            <button class="rounded-xl bg-blue-700 px-6 py-3 text-white">
                Simpan Catatan Pengiriman
            </button>
        </form>
    @endif

    @include('surat-keluar.riwayat')
@endsection
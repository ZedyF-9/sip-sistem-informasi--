@extends('layouts.internal')

@section('title', 'Detail Surat Keluar')

@section('content')
    @php
        $isTu = auth()->user()->role === 'koordinator_tu';
        $isKepalaTu = auth()->user()->role === 'kepala_tu';
    @endphp

    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('surat-keluar.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">Kembali</a>

        <a href="{{ route('surat-keluar.download', $surat) }}"
           class="rounded-xl bg-blue-700 px-5 py-3 text-white">Lihat PDF</a>

    </div>

    @if ($isTu && $surat->status === 'revisi')
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
            <p class="font-semibold">Kepala TU mengembalikan surat ini untuk direvisi.</p>
            @if ($surat->catatan_persetujuan)
                <p class="mt-2 whitespace-pre-line">{{ $surat->catatan_persetujuan }}</p>
            @endif
            <a href="{{ route('surat-keluar.edit', $surat) }}" class="mt-3 inline-flex rounded-lg bg-amber-700 px-4 py-2 font-semibold text-white">Edit surat revisi</a>
        </div>
    @endif

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
                'Status' => match ($surat->status) {
                    'menunggu_kepala_tu' => 'Menunggu pemeriksaan Kepala TU',
                    'kembali_ke_tu' => 'Menunggu tindak lanjut TU',
                    'diajukan' => 'Menunggu persetujuan Kepala Sekolah',
                    default => ucfirst($surat->status),
                },
                'Pembuat' => $surat->pembuat->name,
                'Disetujui oleh' => $surat->penyetuju?->name,
                'Waktu persetujuan' => $surat->approved_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                'Waktu pengiriman' => $surat->dikirim_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                'Metode pengiriman' => $surat->metode_pengiriman,
                'Keterangan' => $surat->keterangan,
            ] as $label => $value)
                @if (filled($value))
                    <div>
                        <dt class="text-sm text-slate-500">{{ $label }}</dt>
                        <dd class="mt-1 break-words font-medium">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>

        @if ($surat->data_surat)
            <section class="mt-6 border-t border-slate-100 pt-5">
                <h3 class="font-bold">Rincian pada surat</h3>
                <dl class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($surat->data_surat as $field => $value)
                        @if (filled($value))
                            <div>
                                <dt class="text-sm text-slate-500">{{ \Illuminate\Support\Str::headline($field) }}</dt>
                                <dd class="mt-1 whitespace-pre-line break-words font-medium">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </section>
        @endif

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

    @if ($isTu && in_array($surat->status, ['draf', 'revisi', 'kembali_ke_tu'], true))
        <form method="POST" action="{{ route('surat-keluar.aksi', $surat) }}"
              class="mt-6 rounded-2xl bg-white p-6">
            @csrf
            <input type="hidden" name="version" value="{{ $surat->version }}">
            <input type="hidden" name="aksi" value="ajukan">
            <p class="mb-4 text-sm text-slate-600">
                Pastikan data dan PDF sudah benar sebelum diajukan.
            </p>
            <button class="rounded-xl bg-blue-700 px-6 py-3 text-white">
                {{ match ($surat->status) {
                    'kembali_ke_tu' => 'Teruskan ke Kepala Sekolah',
                    'revisi' => 'Kirim ulang ke Kepala TU',
                    default => 'Ajukan ke Kepala TU',
                } }}
            </button>
        </form>
    @endif

    @if ($isKepalaTu && $surat->status === 'menunggu_kepala_tu')
        <form method="POST" action="{{ route('surat-keluar.aksi', $surat) }}" class="mt-6 space-y-4 rounded-2xl bg-white p-6">
            @csrf
            <input type="hidden" name="version" value="{{ $surat->version }}">
            <h2 class="text-lg font-bold">Pemeriksaan Kepala TU</h2>
            <label for="catatan" class="block font-semibold">Catatan pemeriksaan (wajib jika minta revisi)</label>
            <textarea id="catatan" name="catatan" rows="3" maxlength="2000" class="w-full rounded-xl border border-slate-300 p-3">{{ old('catatan') }}</textarea>
            <div class="flex gap-3">
                <button name="aksi" value="teruskan_kepala" class="rounded-xl bg-blue-700 px-6 py-3 text-white">Luluskan ke TU</button>
                <button name="aksi" value="revisi" class="rounded-xl border border-slate-300 px-6 py-3">Minta Revisi</button>
            </div>
        </form>
    @endif

    @if (! $isTu && ! $isKepalaTu && $surat->status === 'diajukan')
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

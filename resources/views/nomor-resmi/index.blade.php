@extends('layouts.internal')

@section('title', 'Penomoran dan PDF Final')

@section('content')
    <div class="mb-6 rounded-2xl bg-blue-50 p-5 text-sm leading-7 text-blue-950">
        <p class="font-semibold">
            Format saat ini: {{ config('penomoran.format') }}
        </p>

        <p>
            Nomor baru dibuat untuk surat yang telah disetujui.
            Surat yang sudah bernomor menggunakan nomor yang sama.
        </p>

        <p>
            Nomor dicetak pada bidang tambahan di atas halaman.
            Periksa PDF final sebelum mencatat pengiriman.
        </p>
    </div>

    <form
        action="{{ route('tu.nomor-resmi.index') }}"
        method="GET"
        class="mb-6 grid gap-3 md:grid-cols-4"
    >
        <input
            type="search"
            name="q"
            value="{{ $q }}"
            maxlength="100"
            aria-label="Cari surat"
            placeholder="Cari perihal, tujuan, atau nomor..."
            class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"
        >

        <select
            name="status"
            aria-label="Status surat"
            class="rounded-xl border border-slate-300 px-4 py-3"
        >
            <option value="disetujui" @selected($status === 'disetujui')>
                Disetujui
            </option>
            <option value="dikirim" @selected($status === 'dikirim')>
                Dikirim
            </option>
        </select>

        <button class="rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white">
            Tampilkan
        </button>
    </form>

    <div class="space-y-4">
        @forelse ($surats as $surat)
            <article class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="font-bold">{{ $surat->perihal }}</h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Tujuan: {{ $surat->tujuan }}
                        </p>

                        <p class="mt-2 text-sm">
                            Nomor:
                            <strong>{{ $surat->nomor_surat ?: 'Belum diberikan' }}</strong>
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            Status: {{ ucfirst($surat->status) }}
                        </p>
                    </div>

                    <a
                        href="{{ route('surat-keluar.show', $surat) }}"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm"
                    >
                        Detail Surat
                    </a>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    @if ($surat->status === 'disetujui')
                        <form
                            action="{{ route('tu.nomor-resmi.store', $surat) }}"
                            method="POST"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="version"
                                value="{{ $surat->version }}"
                            >

                            <button
                                type="submit"
                                class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
                            >
                                {{ $surat->file_final
                                    ? 'Periksa / Pulihkan PDF Final'
                                    : 'Buat Nomor dan PDF Final' }}
                            </button>
                        </form>
                    @endif

                    @if ($surat->file_final)
                        <a
                            href="{{ route('surat-keluar.final.download', $surat) }}"
                            class="rounded-xl border border-blue-300 px-5 py-3 text-sm font-semibold text-blue-700"
                        >
                            Unduh PDF Final
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl bg-white p-8 text-center text-slate-500">
                Tidak ada surat sesuai pencarian.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $surats->links() }}</div>
@endsection
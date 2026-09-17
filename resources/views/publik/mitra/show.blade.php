@extends('layouts.publik')

@section('title', $mitra->nama_mitra)

@section('content')
    <div class="mx-auto max-w-4xl px-6 py-12">
        <a href="{{ route('publik.mitra.index') }}"
           class="font-semibold text-blue-700">← Semua Mitra</a>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-8">
            @if ($mitra->logo)
                <img src="{{ route('publik.mitra.logo', $mitra->id) }}"
                     alt="Logo {{ $mitra->nama_mitra }}"
                     class="mb-6 h-24 w-24 rounded-xl border object-contain p-3">
            @endif

            <h1 class="text-3xl font-bold">{{ $mitra->nama_mitra }}</h1>
            <p class="mt-3 font-semibold text-blue-700">{{ $mitra->bidang_usaha }}</p>
            <p class="mt-6 whitespace-pre-line leading-8 text-slate-600">{{ $mitra->deskripsi }}</p>

            @if ($mitra->website && preg_match('/^https?:\/\//i', $mitra->website))
                <a href="{{ $mitra->website }}" target="_blank" rel="noopener noreferrer"
                   class="mt-6 inline-block rounded-xl bg-blue-700 px-5 py-3 text-white">
                    Kunjungi Website Mitra
                </a>
            @endif
        </section>

        <section class="mt-8">
            <h2 class="text-2xl font-bold">Kerja Sama yang Berlaku</h2>

            <div class="mt-5 space-y-4">
                @forelse ($kerjasamas as $item)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h3 class="text-lg font-bold">{{ $item->judul }}</h3>
                        <p class="mt-3 text-sm text-slate-600">
                            {{ $item->tanggal_mulai->format('d/m/Y') }}
                            –
                            {{ $item->tanggal_selesai?->format('d/m/Y') ?? 'Tanpa batas akhir' }}
                        </p>
                    </article>
                @empty
                    <p class="rounded-xl bg-white p-6 text-slate-500">
                        Belum ada informasi kerja sama yang sedang berlaku.
                    </p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
@extends('layouts.publik')

@section('title', 'Mitra Industri')

@section('content')
    <section class="bg-blue-950 text-white">
        <div class="mx-auto max-w-6xl px-6 py-14">
            <p class="text-sm uppercase tracking-widest text-blue-200">SMKN 4 Tanjungpinang</p>
            <h1 class="mt-4 text-4xl font-bold">Mitra Industri</h1>
            <p class="mt-4 max-w-2xl leading-7 text-blue-100">
                Informasi mitra dan kerja sama yang mendukung kegiatan sekolah.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-6 py-10">
        <form method="GET" action="{{ route('publik.mitra.index') }}"
              class="mb-8 flex gap-3">
            <input name="q" value="{{ $q }}" maxlength="100"
                   aria-label="Cari mitra"
                   placeholder="Cari nama atau bidang usaha"
                   class="min-w-0 flex-1 rounded-xl border bg-white p-3">
            <button class="rounded-xl bg-blue-700 px-6 py-3 text-white">Cari</button>
        </form>

        @error('q')
            <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($items as $item)
                <article class="rounded-2xl border border-slate-200 bg-white p-6">
                    @if ($item->logo)
                        <img src="{{ route('publik.mitra.logo', $item->id) }}"
                             alt="Logo {{ $item->nama_mitra }}"
                             loading="lazy"
                             class="h-20 w-20 rounded-xl border object-contain p-2">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-blue-50 font-bold text-blue-800">
                            Mitra
                        </div>
                    @endif

                    <h2 class="mt-5 text-xl font-bold">{{ $item->nama_mitra }}</h2>
                    <p class="mt-2 text-sm font-semibold text-blue-700">{{ $item->bidang_usaha }}</p>
                    <p class="mt-4 text-sm leading-7 text-slate-600">
                        {{ \Illuminate\Support\Str::limit($item->deskripsi, 170) }}
                    </p>
                    <a href="{{ route('publik.mitra.show', $item->id) }}"
                       class="mt-5 inline-block font-semibold text-blue-700">
                        Lihat profil →
                    </a>
                </article>
            @empty
                <p class="rounded-xl bg-white p-8 text-slate-500 md:col-span-2 lg:col-span-3">
                    Belum ada mitra yang sesuai.
                </p>
            @endforelse
        </div>

        <div class="mt-8">{{ $items->links() }}</div>
    </div>
@endsection
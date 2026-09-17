@extends('layouts.internal')

@section('title', 'Perbaikan Surat Masuk')

@section('content')
    <p class="mb-6 text-sm text-slate-600">
        Hanya surat yang menunggu verifikasi dan belum diteruskan yang dapat diperbaiki.
    </p>

    <div class="space-y-3">
        @forelse ($surats as $surat)
            <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-5">
                <div>
                    <p class="font-semibold">{{ $surat->perihal }}</p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $surat->nomor_registrasi }} · {{ $surat->asal_surat }}
                    </p>
                </div>

                <a
                    href="{{ route('tu.perbaikan.edit', $surat) }}"
                    class="rounded-xl bg-blue-700 px-4 py-2 text-sm text-white"
                >
                    Perbaiki
                </a>
            </article>
        @empty
            <p class="rounded-2xl bg-white p-6 text-slate-500">
                Tidak ada surat yang dapat diperbaiki.
            </p>
        @endforelse
    </div>

    <div class="mt-6">{{ $surats->links() }}</div>
@endsection
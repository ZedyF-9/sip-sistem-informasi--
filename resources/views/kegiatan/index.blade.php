@extends('layouts.internal')

@section('title', 'Kegiatan dan Sertifikat')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            Catat kegiatan dan terbitkan sertifikat peserta.
        </p>

        <a
            href="{{ route('tu.kegiatan.create') }}"
            class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white"
        >
            Tambah Kegiatan
        </a>
    </div>

    <form method="GET" class="mb-6 flex gap-3">
        <input
            name="q"
            value="{{ $q }}"
            maxlength="100"
            placeholder="Cari kegiatan..."
            aria-label="Cari kegiatan"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >

        <button class="rounded-xl bg-slate-900 px-5 py-3 text-white">
            Cari
        </button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Kegiatan</th>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Sertifikat</th>
                    <th class="p-4">Tindakan</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($kegiatans as $kegiatan)
                    <tr>
                        <td class="p-4">
                            <p class="font-semibold">{{ $kegiatan->nama }}</p>
                            <p class="mt-1 text-slate-500">{{ $kegiatan->lokasi }}</p>
                        </td>

                        <td class="p-4">
                            {{ $kegiatan->tanggal_mulai->format('d-m-Y') }}
                            —
                            {{ $kegiatan->tanggal_selesai->format('d-m-Y') }}
                        </td>

                        <td class="p-4">{{ $kegiatan->sertifikats_count }}</td>

                        <td class="p-4">
                            <a
                                href="{{ route('tu.kegiatan.show', $kegiatan) }}"
                                class="font-semibold text-blue-700"
                            >
                                Kelola
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">
                            Belum ada kegiatan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $kegiatans->links() }}</div>
@endsection
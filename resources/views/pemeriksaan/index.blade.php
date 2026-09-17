@extends('layouts.pemeriksaan')

@section('title', 'Antrean Pemeriksaan TU')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar data">
    <p class="mb-6 text-sm text-slate-600">
        Surat yang menunggu verifikasi atau sudah diverifikasi tetapi belum diteruskan.
    </p>

    <div tabindex="0" role="region" aria-label="Tabel data, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Surat</th>
                    <th class="p-4">Asal</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($surats as $surat)
                    <tr>
                        <td class="p-4">
                            <p class="font-semibold">{{ $surat->perihal }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $surat->nomor_registrasi }}
                            </p>
                        </td>
                        <td class="p-4">{{ $surat->asal_surat }}</td>
                        <td class="p-4"><span class="dashboard-badge" data-status="{{ $surat->status }}">{{ $surat->statusLabel() }}</span></td>
                        <td class="p-4">
                            <a href="{{ route('tu.pemeriksaan.show', $surat) }}"
                               class="font-semibold text-blue-700 hover:underline">
                                Periksa
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-slate-500">
                            Tidak ada surat dalam antrean.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $surats->links() }}</div>
    </section>
@endsection

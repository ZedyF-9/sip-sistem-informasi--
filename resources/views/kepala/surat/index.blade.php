@extends('layouts.pemeriksaan')

@section('title', 'Surat dari Tata Usaha')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar data">
    <p class="mb-6 text-sm text-slate-600">
        Daftar surat yang telah diverifikasi dan diteruskan oleh TU.
    </p>

    <div tabindex="0" role="region" aria-label="Tabel data, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Surat</th>
                    <th class="p-4">Asal</th>
                    <th class="p-4">Diteruskan</th>
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
                        <td class="whitespace-nowrap p-4">
                            {{ $surat->diteruskan_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                        </td>
                        <td class="p-4">
                            <a href="{{ route('kepala.surat.show', $surat) }}"
                               class="font-semibold text-blue-700 hover:underline">
                                Buka Surat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-slate-500">
                            Belum ada surat yang diteruskan oleh TU.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $surats->links() }}</div>
    </section>
@endsection

@extends('layouts.tu')

@section('title', 'Surat Masuk')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar data">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-slate-600">
            Total hasil: {{ $surats->total() }} surat
        </p>

        <a href="{{ route('tu.surat-masuk.create') }}"
           class="dashboard-button-create rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">
            + Catat Surat Fisik
        </a>
    </div>

    <form method="GET"
          action="{{ route('tu.surat-masuk.index') }}"
          class="dashboard-filter mb-6 flex flex-wrap gap-3">
        <label for="q" class="sr-only">Cari surat</label>

        <input id="q"
               name="q"
               value="{{ $q }}"
               maxlength="100"
               placeholder="Cari registrasi, nomor surat, asal, atau perihal..."
               class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3">

        <button class="rounded-xl bg-blue-700 px-5 py-3 text-white">
            Cari
        </button>

        <a href="{{ route('tu.surat-masuk.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">
            Reset
        </a>

        @error('q')
            <p class="w-full text-sm text-red-600">{{ $message }}</p>
        @enderror
    </form>

    <div tabindex="0" role="region" aria-label="Tabel data, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="p-4">Registrasi</th>
                    <th class="p-4">Perihal / Asal</th>
                    <th class="p-4">Diterima</th>
                    <th class="p-4">Metode</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($surats as $surat)
                    <tr>
                        <td class="p-4 font-medium">
                            {{ $surat->nomor_registrasi }}
                        </td>

                        <td class="p-4">
                            <p class="font-semibold">{{ $surat->perihal }}</p>
                            <p class="mt-1 text-slate-500">{{ $surat->asal_surat }}</p>
                        </td>

                        <td class="whitespace-nowrap p-4">
                            {{ $surat->tanggal_diterima->format('d/m/Y') }}
                        </td>

                        <td class="p-4">
                            {{ ucfirst($surat->metode_pengiriman) }}
                        </td>

                        <td class="p-4">
                            <span class="dashboard-badge" data-status="{{ $surat->status }}">
                                {{ $surat->statusLabel() }}
                            </span>
                        </td>

                        <td class="p-4">
                            <a href="{{ route('tu.surat-masuk.show', $surat) }}"
                               class="font-semibold text-blue-700 hover:underline">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-slate-500">
                            Belum ada surat yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $surats->links() }}
    </div>
    </section>
@endsection

@extends('layouts.tu')

@section('title', 'Surat Masuk')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar data">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-slate-600">
            Total hasil: {{ $surats->total() }} surat
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('tu.surat-masuk.create') }}"
               class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white">
                Catat Surat Masuk
            </a>
        </div>
    </div>

    <form method="GET"
          action="{{ route('tu.surat-masuk.index') }}"
          class="dashboard-filter mb-6 flex flex-wrap gap-3">
        <label for="q" class="sr-only">Cari surat</label>

        <input id="q"
               name="q"
               value="{{ $q }}"
               maxlength="100"
               placeholder="Cari nomor agenda, nomor surat, asal, atau perihal..."
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
                    <th class="p-4">Nomor urut</th>
                    <th class="p-4">Nomor / tanggal surat</th>
                    <th class="p-4">Tanggal diterima</th>
                    <th class="p-4">Alamat asal</th>
                    <th class="p-4">Perihal</th>
                    <th class="p-4">Disposisi</th>
                    <th class="p-4">Keterangan</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($surats as $surat)
                    <tr>
                        <td class="p-4 font-medium">
                            {{ $surat->nomor_agenda ?: $surat->id }}
                        </td>

                        <td class="p-4">
                            <p class="font-semibold">{{ $surat->nomor_surat ?: '—' }}</p>
                            <p class="mt-1 text-slate-500">{{ $surat->tanggal_surat->format('d/m/Y') }}</p>
                        </td>

                        <td class="whitespace-nowrap p-4">
                            {{ $surat->tanggal_diterima->format('d/m/Y') }}
                        </td>

                        <td class="p-4">{{ $surat->asal_surat }}</td>
                        <td class="p-4">{{ $surat->perihal }}</td>
                        <td class="p-4">{{ $surat->disposisis->pluck('penerima.name')->filter()->unique()->implode(', ') ?: 'Belum didisposisikan' }}</td>

                        <td class="p-4">{{ $surat->keterangan ?: '—' }}</td>

                        <td class="p-4">
                            <a href="{{ route('tu.surat-masuk.show', $surat) }}"
                               class="font-semibold text-blue-700 hover:underline">
                                Detail
                            </a>
                            <x-hapus-data :action="route('tu.surat-masuk.destroy', $surat)" :label="$surat->perihal"
                                :message="'Hapus surat masuk '.$surat->nomor_registrasi.'? Semua disposisi, tindak lanjut, riwayat, dan berkas terkait ikut dihapus permanen.'" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-slate-500">
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

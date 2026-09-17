@extends('layouts.internal')

@section('title', 'Laporan Persuratan')

@section('content')
    <form method="GET" action="{{ route('laporan.index') }}"
          class="dashboard-report-filter mb-8 grid gap-4 rounded-2xl bg-white p-6 md:grid-cols-4">
        <p class="dashboard-report-intro">Pilih jenis surat dan rentang tanggal untuk menampilkan rekapitulasi persuratan.</p>
        <div>
            <label for="jenis" class="mb-2 block text-sm font-semibold">Jenis</label>
            <select id="jenis" name="jenis" class="w-full rounded-xl border p-3">
                <option value="masuk" @selected($filters['jenis'] === 'masuk')>Surat Masuk</option>
                <option value="keluar" @selected($filters['jenis'] === 'keluar')>Surat Keluar</option>
            </select>
        </div>

        <div>
            <label for="mulai" class="mb-2 block text-sm font-semibold">Dari tanggal</label>
            <input id="mulai" name="mulai" type="date" required
                   value="{{ $filters['mulai'] }}" class="w-full rounded-xl border p-3">
        </div>

        <div>
            <label for="selesai" class="mb-2 block text-sm font-semibold">Sampai tanggal</label>
            <input id="selesai" name="selesai" type="date" required
                   value="{{ $filters['selesai'] }}" class="w-full rounded-xl border p-3">
        </div>

        <div class="flex items-end">
            <button class="dashboard-button-export w-full rounded-xl bg-blue-700 px-5 py-3 text-white">
                Tampilkan
            </button>
        </div>

        <label class="flex items-center gap-3 text-sm md:col-span-4">
            <input type="checkbox" name="arsip" value="1" @checked($filters['arsip'])>
            Hanya arsip: surat masuk selesai / surat keluar dikirim
        </label>
    </form>

    <div class="mb-6 flex flex-wrap justify-between gap-4">
        <p class="text-sm text-slate-600">Jumlah hasil: {{ $items->total() }}</p>

        <a href="{{ route('laporan.export', [
            'jenis' => $filters['jenis'],
            'mulai' => $filters['mulai'],
            'selesai' => $filters['selesai'],
            'arsip' => (int) $filters['arsip'],
        ]) }}"
           class="dashboard-button-export rounded-xl bg-blue-700 px-5 py-3 text-white">
            Unduh CSV
        </a>
    </div>

    <p class="mb-4 text-sm text-slate-500">
        Periode surat masuk berdasarkan tanggal diterima.
        Periode surat keluar berdasarkan tanggal surat.
    </p>

    <div tabindex="0" role="region" aria-label="Tabel laporan, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Nomor</th>
                    <th class="p-4">Perihal</th>
                    <th class="p-4">Asal / Tujuan</th>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="p-4">{{ $item->nomor ?: 'Belum bernomor' }}</td>
                        <td class="p-4">{{ $item->perihal }}</td>
                        <td class="p-4">{{ $item->pihak }}</td>
                        <td class="whitespace-nowrap p-4">
                            {{ \Illuminate\Support\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                        </td>
                        <td class="p-4">
                            <span class="dashboard-badge" data-status="{{ $item->status }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-slate-500">
                            Tidak ada data pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
@endsection

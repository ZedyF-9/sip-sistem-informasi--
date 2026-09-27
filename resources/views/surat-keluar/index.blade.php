@extends('layouts.internal')

@section('title', request('status') === 'dikirim' ? 'Arsip Surat Keluar' : 'Surat Keluar')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar data">
    @if (auth()->user()->role === 'koordinator_tu')
        <div class="mb-6 flex flex-wrap justify-end gap-3">
            <a href="{{ route('surat-keluar.create') }}"
               class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white">
                Buat Surat Keluar
            </a>
        </div>
    @endif
    <div class="mb-6 flex flex-wrap justify-between gap-4">
        <form method="GET" action="{{ route('surat-keluar.index') }}"
              class="dashboard-filter flex flex-wrap gap-3">
            <input name="q" value="{{ $q }}" maxlength="100"
                   aria-label="Cari surat"
                   placeholder="Cari nomor, perihal, atau tujuan"
                   class="rounded-xl border border-slate-300 bg-white p-3">

            <select name="status" aria-label="Status"
                    class="rounded-xl border border-slate-300 bg-white p-3">
                <option value="">Semua status</option>
                @foreach (['draf', 'diajukan', 'revisi', 'disetujui', 'dikirim'] as $value)
                    @if ($value !== 'draf' || auth()->user()->role === 'koordinator_tu')
                        <option value="{{ $value }}" @selected($status === $value)>
                            {{ ucfirst($value) }}
                        </option>
                    @endif
                @endforeach
            </select>

            <button class="rounded-xl bg-blue-700 px-5 py-3 text-white">Cari</button>
        </form>
    </div>

    <div tabindex="0" role="region" aria-label="Tabel data, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Nomor / Perihal</th>
                    <th class="p-4">Tujuan</th>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="p-4">
                            <p class="font-semibold">{{ $item->perihal }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $item->nomor_surat ?: 'Belum bernomor' }}
                            </p>
                        </td>
                        <td class="p-4">{{ $item->tujuan }}</td>
                        <td class="whitespace-nowrap p-4">
                            {{ $item->tanggal_surat->format('d/m/Y') }}
                        </td>
                        <td class="p-4"><span class="dashboard-badge" data-status="{{ $item->status }}">{{ ucfirst($item->status) }}</span></td>
                        <td class="p-4">
                            <a href="{{ route('surat-keluar.show', $item) }}"
                               class="font-semibold text-blue-700">Detail</a>
                            @if (auth()->user()->role === 'koordinator_tu')
                                <x-hapus-data :action="route('surat-keluar.destroy', $item)" :label="$item->perihal"
                                    :message="'Hapus surat keluar '.$item->perihal.'? Riwayat dan berkas terkait ikut dihapus permanen.'" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-slate-500">
                            Tidak ada surat yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
    </section>
@endsection

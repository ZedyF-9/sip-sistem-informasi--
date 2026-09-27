@extends('layouts.internal')

@section('title', in_array(auth()->user()->role, ['penerima_disposisi', 'waka_humas'], true)
    ? 'Disposisi Saya'
    : 'Pemantauan Disposisi')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar data">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('disposisi.index') }}"
              class="dashboard-filter flex flex-wrap gap-3">
            <label for="status" class="sr-only">Filter status</label>

            <select id="status" name="status"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-3">
                <option value="">Semua status</option>
                @foreach (['baru', 'diproses', 'selesai'] as $value)
                    <option value="{{ $value }}" @selected($status === $value)>
                        {{ ucfirst($value) }}
                    </option>
                @endforeach
            </select>

            <button class="rounded-xl bg-blue-700 px-5 py-3 text-white">
                Tampilkan
            </button>
        </form>

        @if (auth()->user()->role === 'kepala_sekolah')
            <a href="{{ route('disposisi.create') }}"
               class="dashboard-button-create rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
                + Buat Disposisi
            </a>
        @endif
    </div>

    <div tabindex="0" role="region" aria-label="Tabel data, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Surat</th>
                    <th class="p-4">Penerima</th>
                    <th class="p-4">Prioritas</th>
                    <th class="p-4">Batas waktu</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="p-4">
                            <p class="font-semibold">{{ $item->surat->perihal }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $item->surat->nomor_registrasi }}
                            </p>
                        </td>
                        <td class="p-4">{{ $item->penerima->name }}</td>
                        <td class="p-4">
                            {{ ucwords(str_replace('_', ' ', $item->prioritas)) }}
                        </td>
                        <td class="whitespace-nowrap p-4">
                            {{ $item->batas_waktu?->format('d/m/Y') ?? '—' }}

                            @if ($item->batas_waktu && $item->status !== 'selesai' && $item->batas_waktu->lt(today()))
                                <span class="mt-1 block text-xs font-semibold text-red-600">
                                    Lewat batas waktu
                                </span>
                            @endif
                        </td>
                        <td class="p-4"><span class="dashboard-badge" data-status="{{ $item->status }}">{{ ucfirst($item->status) }}</span></td>
                        <td class="p-4">
                            <a href="{{ route('disposisi.show', $item) }}"
                               class="font-semibold text-blue-700">
                                Detail
                            </a>
                            @if (in_array(auth()->user()->role, ['koordinator_tu', 'kepala_sekolah'], true))
                                <x-hapus-data :action="route('disposisi.destroy', $item)" :label="'disposisi #'.$item->id"
                                    :message="'Hapus disposisi untuk '.$item->penerima->name.'? Tindak lanjut dan lampirannya ikut dihapus permanen.'" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-slate-500">
                            Belum ada disposisi yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
    </section>
@endsection

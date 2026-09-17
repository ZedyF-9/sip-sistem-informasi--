@extends('layouts.internal')

@section('title', 'Mitra dan Kerja Sama')

@section('content')
    <div class="mb-6 flex flex-wrap justify-between gap-4">
        <form method="GET" action="{{ route('tu.mitra.index') }}" class="flex gap-3">
            <input name="q" value="{{ $q }}" maxlength="100"
                   aria-label="Cari mitra" placeholder="Cari nama atau bidang usaha"
                   class="min-w-0 rounded-xl border border-slate-300 bg-white p-3">
            <button class="rounded-xl bg-blue-700 px-5 py-3 text-white">Cari</button>
        </form>

        <a href="{{ route('tu.mitra.create') }}"
           class="rounded-xl bg-blue-700 px-5 py-3 text-white">+ Tambah Mitra</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Nama mitra</th>
                    <th class="p-4">Bidang</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Jumlah kerja sama</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="p-4 font-semibold">{{ $item->nama_mitra }}</td>
                        <td class="p-4">{{ $item->bidang_usaha }}</td>
                        <td class="p-4">{{ ucfirst($item->status) }}</td>
                        <td class="p-4">{{ $item->kerjasamas_count }}</td>
                        <td class="p-4">
                            <a href="{{ route('tu.mitra.show', $item) }}"
                               class="font-semibold text-blue-700">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-slate-500">
                            Belum ada mitra yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
@endsection
@extends('layouts.internal')

@section('title', 'Arsip Disposisi')

@section('content')
    <section class="dashboard-data-panel" aria-label="Arsip disposisi selesai">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900">Disposisi Selesai</h2>
            <p class="mt-1 text-sm text-slate-500">Disposisi selesai dan pengajuan yang ditolak Kepala Sekolah.</p>
        </div>

        <div tabindex="0" role="region" aria-label="Tabel arsip disposisi, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-4">Surat</th>
                        <th class="p-4">Penerima</th>
                        <th class="p-4">Tanggal disposisi</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr>
                            <td class="p-4">
                                <p class="font-semibold">{{ $item->surat->perihal }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $item->surat->nomor_registrasi }}</p>
                            </td>
                            <td class="p-4">{{ $item->penerima->name }}</td>
                            <td class="p-4">{{ $item->tanggal_disposisi?->format('d/m/Y') ?? '—' }}</td>
                            <td class="p-4"><span class="dashboard-badge" data-status="{{ $item->status_persetujuan === 'ditolak' ? 'ditolak' : 'selesai' }}">{{ $item->status_persetujuan === 'ditolak' ? 'Ditolak Kepala Sekolah' : ($item->status_persetujuan === 'diteruskan' ? 'Diteruskan oleh Kepala Sekolah' : 'Selesai') }}</span></td>
                            <td class="p-4">
                                <a href="{{ route('disposisi.show', $item) }}" class="font-semibold text-blue-700">Detail</a>
                                @if (in_array(auth()->user()->role, ['koordinator_tu', 'kepala_sekolah'], true))
                                    <x-hapus-data :action="route('disposisi.destroy', $item)" :label="'disposisi #'.$item->id"
                                        :message="'Hapus disposisi untuk '.$item->penerima->name.'? Tindak lanjut dan lampirannya ikut dihapus permanen.'" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-slate-500">Belum ada disposisi selesai atau ditolak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $items->links() }}</div>
    </section>
@endsection

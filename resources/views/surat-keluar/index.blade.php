@extends('layouts.internal')

@section('title', match (auth()->user()->role) {
    'kepala_tu' => 'Pemeriksaan Surat Keluar',
    'kepala_sekolah' => 'Persetujuan Surat Keluar',
    'koordinator_tu' => request('status') === 'dikirim' ? 'Arsip Surat Keluar' : 'Surat Keluar',
    default => 'Surat Keluar',
})

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
        <div>
            <h2 class="text-xl font-bold">{{ match (auth()->user()->role) {
                'kepala_tu' => 'Surat Menunggu Pemeriksaan',
                'kepala_sekolah' => 'Surat Menunggu Persetujuan',
                default => 'Buku Agenda Surat Keluar',
            } }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ auth()->user()->role === 'kepala_sekolah'
                ? 'Daftar surat yang telah lolos pemeriksaan Kepala TU dan diteruskan oleh TU.'
                : 'Arsip surat yang dihasilkan otomatis dari format baku.' }}</p>
        </div>
        <form method="GET" action="{{ route('surat-keluar.index') }}"
              class="dashboard-filter flex flex-wrap gap-3">
            <input name="q" value="{{ $q }}" maxlength="100"
                   aria-label="Cari surat"
                   placeholder="Cari nomor, perihal, atau tujuan"
                   class="rounded-xl border border-slate-300 bg-white p-3">

            <button class="rounded-xl bg-blue-700 px-5 py-3 text-white">Cari</button>
        </form>
    </div>

    <div tabindex="0" role="region" aria-label="Tabel data, geser untuk melihat seluruh kolom" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Nomor surat</th>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Alamat tujuan</th>
                    <th class="p-4">Perihal</th>
                    <th class="p-4">Keterangan</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="p-4">
                            {{ $item->nomor_surat ?: '—' }}
                        </td>
                        <td class="whitespace-nowrap p-4">
                            {{ $item->tanggal_surat->format('d/m/Y') }}
                        </td>
                        <td class="p-4">{{ $item->tujuan }}</td>
                        <td class="p-4">{{ $item->perihal }}</td>
                        <td class="p-4">{{ $item->keterangan ?: '—' }}</td>
                        <td class="p-4">
                            {{ match ($item->status) {
                                'menunggu_kepala_tu' => 'Menunggu Kepala TU',
                                'revisi' => 'Perlu revisi TU',
                                'kembali_ke_tu' => 'Menunggu TU meneruskan',
                                'diajukan' => 'Menunggu Kepala Sekolah',
                                default => ucfirst($item->status),
                            } }}
                        </td>
                        <td class="p-4">
                            <div class="dashboard-row-actions">
                            <a href="{{ route('surat-keluar.show', $item) }}"
                               class="dashboard-action-button dashboard-action-detail">Detail</a>
                            @if (auth()->user()->role === 'koordinator_tu')
                                <x-hapus-data :action="route('surat-keluar.destroy', $item)" :label="$item->perihal"
                                    :message="'Hapus surat keluar '.$item->perihal.'? Riwayat dan berkas terkait ikut dihapus permanen.'" />
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-slate-500">
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

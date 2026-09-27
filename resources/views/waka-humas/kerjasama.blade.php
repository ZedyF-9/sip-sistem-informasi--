@extends('layouts.internal')

@section('title', 'Persetujuan Kerja Sama Industri')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar pengajuan mitra industri">
        <h2 class="mb-2 text-lg font-bold">Pengajuan Mitra Industri</h2>
        <p class="mb-6 text-sm text-slate-600">Mitra baru baru dapat tampil di portal publik setelah disetujui.</p>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr><th class="p-4">Mitra</th><th class="p-4">Bidang</th><th class="p-4">Status publikasi</th><th class="p-4">Tindakan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mitras as $mitra)
                        <tr class="align-top">
                            <td class="p-4">
                                <p class="font-semibold">{{ $mitra->nama_mitra }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ $mitra->deskripsi }}</p>
                                <form method="POST" action="{{ route('waka-humas.kerjasama.mitra.approve', $mitra) }}" class="mt-3">
                                    @csrf @method('PATCH')
                                    <button type="submit" style="display:block;background:#047857;color:#fff;padding:12px 16px;border-radius:8px;font-weight:700;text-align:center">
                                        Setujui Mitra
                                    </button>
                                </form>
                            </td>
                            <td class="p-4">{{ $mitra->bidang_usaha }}</td>
                            <td class="p-4">{{ ucfirst($mitra->status) }}</td>
                            <td class="min-w-64 p-4">
                                <form method="POST" action="{{ route('waka-humas.kerjasama.mitra.reject', $mitra) }}" class="mt-3 space-y-2">
                                    @csrf @method('PATCH')
                                    <label class="sr-only" for="catatan-mitra-{{ $mitra->id }}">Alasan penolakan</label>
                                    <textarea id="catatan-mitra-{{ $mitra->id }}" name="catatan_persetujuan" required maxlength="2000" rows="2" placeholder="Alasan penolakan" class="w-full rounded-lg border border-slate-300 p-2"></textarea>
                                    <button type="submit" style="border:1px solid #fca5a5;color:#b91c1c;padding:10px 16px;border-radius:8px;font-weight:700">
                                        Tolak Mitra
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-slate-500">Tidak ada pendaftaran mitra yang menunggu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $mitras->links() }}</div>
    </section>

    <section class="dashboard-data-panel mt-8" aria-label="Daftar pengajuan kerja sama">
        <h2 class="mb-2 text-lg font-bold">Pengajuan Kerja Sama / MoU</h2>
        <p class="mb-6 text-sm text-slate-600">Kerja sama baru tampil di portal publik setelah disetujui.</p>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-4">Mitra / Kerja Sama</th>
                        <th class="p-4">Periode</th>
                        <th class="p-4">Status Persetujuan</th>
                        <th class="p-4">Dokumen</th>
                        <th class="p-4">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="align-top">
                            <td class="p-4">
                                <p class="font-semibold">{{ $item->mitra->nama_mitra }}</p>
                                <p class="mt-1">{{ $item->judul }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $item->nomor_mou ?: 'Tanpa nomor MoU' }}</p>
                                @if ($item->catatan_persetujuan)
                                    <p class="mt-2 text-sm text-red-700">Catatan: {{ $item->catatan_persetujuan }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap p-4">
                                {{ $item->tanggal_mulai->format('d/m/Y') }} – {{ $item->tanggal_selesai?->format('d/m/Y') ?? 'Tanpa batas akhir' }}
                            </td>
                            <td class="p-4"><span class="dashboard-badge" data-status="{{ $item->status_persetujuan }}">{{ ucfirst($item->status_persetujuan) }}</span></td>
                            <td class="p-4">
                                @if ($item->dokumen_mou)
                                    <a class="font-semibold text-blue-700 hover:underline" href="{{ route('waka-humas.kerjasama.download', $item) }}">Unduh MoU</a>
                                @else
                                    <span class="text-slate-500">Tidak ada</span>
                                @endif
                            </td>
                            <td class="min-w-64 p-4">
                                @if ($item->status_persetujuan === 'menunggu')
                                    <form method="POST" action="{{ route('waka-humas.kerjasama.approve', $item) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-lg bg-emerald-700 px-4 py-2 font-semibold text-white">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('waka-humas.kerjasama.reject', $item) }}" class="mt-3 space-y-2">
                                        @csrf @method('PATCH')
                                        <label class="sr-only" for="catatan-{{ $item->id }}">Alasan penolakan</label>
                                        <textarea id="catatan-{{ $item->id }}" name="catatan_persetujuan" required maxlength="2000" rows="2" placeholder="Alasan penolakan" class="w-full rounded-lg border border-slate-300 p-2"></textarea>
                                        <button class="rounded-lg border border-red-300 px-4 py-2 font-semibold text-red-700">Tolak</button>
                                    </form>
                                @else
                                    <span class="text-slate-500">Sudah ditinjau</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-slate-500">Belum ada pengajuan kerja sama.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $items->links() }}</div>
    </section>
@endsection

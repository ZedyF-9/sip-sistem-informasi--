@extends('layouts.internal')

@section('title', 'Persetujuan Mitra Industri')

@section('content')
    <section class="dashboard-data-panel" aria-label="Daftar pengajuan mitra industri">
        <h2 class="mb-2 text-lg font-bold">Pengajuan Mitra Industri</h2>
        <p class="mb-6 text-sm text-slate-600">Periksa profil, kontak, dan logo mitra sebelum menyetujui publikasinya.</p>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr><th class="p-4">Profil mitra</th><th class="p-4">Kontak</th><th class="p-4">Status publikasi</th><th class="p-4">Tindakan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mitras as $mitra)
                        <tr class="align-top">
                            <td class="p-4">
                                @if ($mitra->logo)
                                    <img src="{{ route('waka-humas.kerjasama.mitra.logo', $mitra) }}"
                                         alt="Logo {{ $mitra->nama_mitra }}"
                                         class="mb-3 h-16 w-16 rounded-lg border border-slate-200 bg-white object-contain p-2">
                                @endif
                                <p class="font-semibold">{{ $mitra->nama_mitra }}</p>
                                <p class="mt-1 text-sm font-medium text-blue-700">{{ $mitra->bidang_usaha }}</p>
                                <p class="mt-2 max-w-2xl whitespace-pre-line text-sm text-slate-600">{{ $mitra->deskripsi }}</p>
                            </td>
                            <td class="p-4">
                                <dl class="space-y-2">
                                    @if ($mitra->alamat)
                                        <div><dt class="font-semibold text-slate-700">Alamat</dt><dd>{{ $mitra->alamat }}</dd></div>
                                    @endif
                                    @if ($mitra->telepon)
                                        <div><dt class="font-semibold text-slate-700">Telepon</dt><dd>{{ $mitra->telepon }}</dd></div>
                                    @endif
                                    @if ($mitra->email)
                                        <div><dt class="font-semibold text-slate-700">Email</dt><dd><a class="text-blue-700 hover:underline" href="mailto:{{ $mitra->email }}">{{ $mitra->email }}</a></dd></div>
                                    @endif
                                    @if ($mitra->website)
                                        <div><dt class="font-semibold text-slate-700">Website</dt><dd><a class="break-all text-blue-700 hover:underline" href="{{ $mitra->website }}" target="_blank" rel="noopener noreferrer">{{ $mitra->website }}</a></dd></div>
                                    @endif
                                    @if (! $mitra->alamat && ! $mitra->telepon && ! $mitra->email && ! $mitra->website)
                                        <span class="text-slate-500">Belum ada kontak.</span>
                                    @endif
                                </dl>
                            </td>
                            <td class="p-4">{{ ucfirst($mitra->status) }}</td>
                            <td class="min-w-64 p-4">
                                <form method="POST" action="{{ route('waka-humas.kerjasama.mitra.approve', $mitra) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-3 font-semibold text-white">Setujui Mitra</button>
                                </form>
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

@endsection

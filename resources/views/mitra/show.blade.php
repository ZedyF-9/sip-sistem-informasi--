@extends('layouts.internal')

@section('title', 'Detail Mitra Industri')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('tu.mitra.index') }}"
           class="rounded-xl border bg-white px-5 py-3">Kembali</a>
        <a href="{{ route('tu.mitra.edit', $mitra) }}"
           class="rounded-xl bg-blue-700 px-5 py-3 text-white">Edit Mitra</a>
        <a href="{{ route('tu.kerjasama.create', $mitra) }}"
           class="rounded-xl bg-blue-700 px-5 py-3 text-white">+ Tambah Kerja Sama</a>

        @if ($mitra->status === 'aktif')
            <a href="{{ route('publik.mitra.show', $mitra) }}"
               class="rounded-xl border border-blue-700 px-5 py-3 text-blue-700">
                Lihat Portal Publik
            </a>
        @endif
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        @if ($mitra->logo)
            <img src="{{ route('tu.mitra.logo', $mitra) }}"
                 alt="Logo {{ $mitra->nama_mitra }}"
                 class="mb-5 h-24 w-24 rounded-xl border object-contain p-3">
        @endif

        <h2 class="text-2xl font-bold">{{ $mitra->nama_mitra }}</h2>
        <p class="mt-2 text-blue-700">{{ $mitra->bidang_usaha }}</p>

        <dl class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach ([
                'Status publikasi' => ucfirst($mitra->status),
                'Alamat' => $mitra->alamat,
                'Telepon' => $mitra->telepon,
                'Email' => $mitra->email,
                'Website' => $mitra->website,
            ] as $label => $value)
                <div>
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="mt-1 break-words">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>

        <h3 class="mt-6 font-semibold">Profil publik</h3>
        <p class="mt-3 whitespace-pre-line text-sm leading-7">{{ $mitra->deskripsi }}</p>
    </section>

    <section class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <h2 class="p-6 text-lg font-bold">Daftar Kerja Sama</h2>

        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4">Judul / Nomor MoU</th>
                    <th class="p-4">Periode</th>
                    <th class="p-4">Kondisi</th>
                    <th class="p-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($kerjasamas as $item)
                    <tr>
                        <td class="p-4">
                            <p class="font-semibold">{{ $item->judul }}</p>
                            <p class="mt-1 text-slate-500">{{ $item->nomor_mou ?: 'Tanpa nomor' }}</p>
                        </td>
                        <td class="p-4">
                            {{ $item->tanggal_mulai->format('d/m/Y') }}
                            –
                            {{ $item->tanggal_selesai?->format('d/m/Y') ?? 'Tanpa batas akhir' }}
                        </td>
                        <td class="p-4">{{ $item->labelPeriode() }}</td>
                        <td class="p-4">
                            <a href="{{ route('tu.kerjasama.edit', $item) }}"
                               class="block font-semibold text-blue-700">Edit</a>
                            @if ($item->dokumen_mou)
                                <a href="{{ route('tu.kerjasama.download', $item) }}"
                                   class="mt-2 block font-semibold text-blue-700">
                                    Unduh MoU
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-slate-500">
                            Belum ada kerja sama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-6">{{ $kerjasamas->links() }}</div>
@endsection
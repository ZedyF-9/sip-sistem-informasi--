@extends('layouts.internal')

@section('title', 'Detail Kegiatan')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('tu.kegiatan.index') }}" class="rounded-xl border border-slate-300 px-5 py-3">Kembali ke daftar kegiatan</a>
    </div>
    <section class="mb-6 rounded-2xl bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold">{{ $kegiatan->nama }}</h2>
                <p class="mt-2 text-slate-600">{{ $kegiatan->lokasi }}</p>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $kegiatan->tanggal_mulai->format('d-m-Y') }}
                    —
                    {{ $kegiatan->tanggal_selesai->format('d-m-Y') }}
                </p>
            </div>

            <a
                href="{{ route('tu.kegiatan.edit', $kegiatan) }}"
                class="rounded-xl border border-slate-300 px-4 py-2"
            >
                Edit Kegiatan
            </a>
        </div>

        <p class="mt-4 whitespace-pre-line text-sm leading-7">{{ $kegiatan->deskripsi }}</p>
    </section>

    <form
        action="{{ route('tu.sertifikat.store', $kegiatan) }}"
        method="POST"
        class="mb-8 space-y-4 rounded-2xl bg-white p-6"
    >
        @csrf

        <h2 class="text-lg font-bold">Terbitkan Sertifikat</h2>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="kode_peserta" class="mb-2 block text-sm font-semibold">
                    Kode Peserta Internal
                </label>
                <input
                    id="kode_peserta"
                    name="kode_peserta"
                    value="{{ old('kode_peserta') }}"
                    placeholder="PESERTA-001"
                    maxlength="100"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
            </div>

            <div>
                <label for="nama_penerima" class="mb-2 block text-sm font-semibold">
                    Nama Penerima
                </label>
                <input
                    id="nama_penerima"
                    name="nama_penerima"
                    value="{{ old('nama_penerima') }}"
                    maxlength="100"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
            </div>

            <div>
                <label for="peran" class="mb-2 block text-sm font-semibold">
                    Peran dalam Kegiatan
                </label>
                <input
                    id="peran"
                    name="peran"
                    value="{{ old('peran', 'Peserta') }}"
                    maxlength="100"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
            </div>
        </div>

        <label class="flex items-start gap-3 text-sm leading-6">
            <input
                type="checkbox"
                name="publikasi"
                value="1"
                required
                class="mt-1"
            >
            <span>
                Data sudah diperiksa. Nama, kegiatan, peran, nomor, dan tanggal
                sertifikat dapat dilihat oleh pemegang kode verifikasi.
            </span>
        </label>

        <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
            Terbitkan PDF dan QR
        </button>
    </form>

    <h2 class="mb-4 text-lg font-bold">Sertifikat Terbit</h2>

    <div class="space-y-4">
        @forelse ($sertifikats as $sertifikat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-bold">{{ $sertifikat->nama_penerima }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $sertifikat->nomor }}</p>
                        <p class="mt-1 text-sm">
                            {{ $sertifikat->peran }} · {{ ucfirst($sertifikat->status) }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if ($sertifikat->status === 'aktif')
                            <a
                                href="{{ route('tu.sertifikat.download', $sertifikat) }}"
                                class="rounded-xl bg-blue-700 px-4 py-2 text-sm text-white"
                            >
                                Unduh PDF
                            </a>
                        @endif

                        <a
                            href="{{ route('publik.sertifikat.hasil', $sertifikat->kode_verifikasi) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm"
                        >
                            Periksa Kode
                        </a>
                    </div>
                </div>

                @if ($sertifikat->status === 'aktif')
                    <details class="mt-5 border-t border-slate-100 pt-4">
                        <summary class="cursor-pointer text-sm font-semibold text-red-700">
                            Cabut Sertifikat
                        </summary>

                        <form
                            action="{{ route('tu.sertifikat.cabut', $sertifikat) }}"
                            method="POST"
                            class="mt-4 space-y-3"
                        >
                            @csrf
                            @method('PATCH')

                            <label
                                for="alasan-{{ $sertifikat->id }}"
                                class="block text-sm"
                            >
                                Alasan pencabutan
                            </label>

                            <textarea
                                id="alasan-{{ $sertifikat->id }}"
                                name="alasan"
                                minlength="5"
                                maxlength="1000"
                                required
                                rows="2"
                                class="w-full rounded-xl border border-slate-300 p-3"
                            ></textarea>

                            <button class="rounded-xl bg-red-700 px-4 py-2 text-sm text-white">
                                Cabut dan Nyatakan Tidak Berlaku
                            </button>
                        </form>
                    </details>
                @else
                    <p class="mt-4 text-sm text-red-700">
                        Alasan: {{ $sertifikat->alasan_pencabutan }}
                    </p>
                @endif
            </article>
        @empty
            <p class="rounded-2xl bg-white p-6 text-slate-500">
                Belum ada sertifikat.
            </p>
        @endforelse
    </div>

    <div class="mt-6">{{ $sertifikats->links() }}</div>
@endsection

@extends('layouts.internal')

@section('title', 'Detail Disposisi')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('disposisi.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">
            Kembali
        </a>

        <a href="{{ route('disposisi.surat', $disposisi) }}"
           class="rounded-xl bg-blue-700 px-5 py-3 text-white">
            Unduh Surat
        </a>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <p class="text-sm font-semibold text-blue-700">
            {{ $disposisi->surat->nomor_registrasi }}
        </p>

        <h2 class="mt-3 text-2xl font-bold">
            {{ $disposisi->surat->perihal }}
        </h2>

        <dl class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach ([
                'Pemberi' => $disposisi->pemberi->name,
                'Penerima' => $disposisi->penerima->name,
                'Asal surat' => $disposisi->surat->asal_surat,
                'Prioritas' => ucwords(str_replace('_', ' ', $disposisi->prioritas)),
                'Tanggal disposisi' => $disposisi->tanggal_disposisi->format('d/m/Y'),
                'Batas waktu' => $disposisi->batas_waktu?->format('d/m/Y'),
                'Status pekerjaan' => ucfirst($disposisi->status),
                'Status surat' => $disposisi->surat->statusLabel(),
            ] as $label => $value)
                <div>
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="mt-1 font-medium">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="mt-6 rounded-xl bg-blue-50 p-5">
            <h3 class="font-semibold text-blue-950">Instruksi Kepala Sekolah</h3>
            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-blue-900">{{ $disposisi->instruksi }}</p>
        </div>
    </section>

    @if (
        auth()->user()->role === 'penerima_disposisi'
        && (int) auth()->id() === (int) $disposisi->penerima_id
        && $disposisi->status !== 'selesai'
    )
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold">Catat Tindak Lanjut</h2>

            <form method="POST"
                  action="{{ route('disposisi.tindak-lanjut', $disposisi) }}"
                  enctype="multipart/form-data"
                  class="mt-5 space-y-5">
                @csrf

                <div>
                    <label for="status" class="mb-2 block font-semibold">
                        Status pekerjaan
                    </label>

                    <select id="status" name="status" required
                            class="w-full rounded-xl border border-slate-300 bg-white p-3">
                        <option value="diproses"
                            @selected(old('status', 'diproses') === 'diproses')>
                            Diproses
                        </option>

                        @if ($disposisi->status === 'diproses')
                            <option value="selesai" @selected(old('status') === 'selesai')>
                                Selesai
                            </option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="catatan" class="mb-2 block font-semibold">
                        Catatan pekerjaan
                    </label>

                    <textarea id="catatan" name="catatan" rows="4"
                              maxlength="5000" required
                              class="w-full rounded-xl border border-slate-300 p-3">{{ old('catatan') }}</textarea>
                </div>

                <div>
                    <label for="file_lampiran" class="mb-2 block font-semibold">
                        Lampiran hasil PDF (opsional, maksimal 5 MB)
                    </label>

                    <input id="file_lampiran" name="file_lampiran"
                           type="file" accept=".pdf,application/pdf"
                           class="w-full rounded-xl border border-slate-300 p-3">

                    <p class="mt-2 text-xs text-slate-500">
                        Jika validasi gagal, pilih ulang lampiran.
                    </p>
                </div>

                <button class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
                    Simpan Tindak Lanjut
                </button>
            </form>
        </section>
    @elseif ($disposisi->status === 'selesai')
        <p class="mt-6 rounded-xl bg-emerald-100 p-4 text-emerald-900">
            Disposisi telah selesai. Riwayat tetap dapat dibaca.
        </p>
    @endif

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-bold">Riwayat Tindak Lanjut</h2>

        <div class="mt-5 space-y-4">
            @forelse ($riwayats as $riwayat)
                <article class="rounded-xl border border-slate-200 p-4">
                    <p class="font-semibold">
                        {{ ucfirst($riwayat->status) }} — {{ $riwayat->nama_petugas }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ \Illuminate\Support\Carbon::parse($riwayat->tanggal_tindak_lanjut)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                    </p>

                    <p class="mt-3 whitespace-pre-line text-sm leading-7">{{ $riwayat->catatan }}</p>

                    @if ($riwayat->file_lampiran)
                        <a href="{{ route('disposisi.lampiran', [
                            'disposisi' => $disposisi,
                            'lampiran' => $riwayat->id,
                        ]) }}"
                           class="mt-3 inline-block text-sm font-semibold text-blue-700">
                            Unduh Lampiran Hasil
                        </a>
                    @endif
                </article>
            @empty
                <p class="text-sm text-slate-500">
                    Belum ada tindak lanjut.
                </p>
            @endforelse
        </div>
    </section>
@endsection
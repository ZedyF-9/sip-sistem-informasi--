@extends('layouts.pemeriksaan')

@section('title', 'Pemeriksaan Surat')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('tu.pemeriksaan.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">
            Kembali ke Antrean
        </a>

        @if ($surat->file_surat)
            <a href="{{ route('tu.surat-masuk.download', $surat) }}"
               class="rounded-xl bg-blue-700 px-5 py-3 text-white">
                Unduh PDF
            </a>
        @endif
    </div>

    @include('partials.detail-pemeriksaan')

    @if ($surat->status === 'menunggu_verifikasi')
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold">Hasil pemeriksaan</h2>

            <p class="mt-2 text-sm text-slate-600">
                Baca dokumen sebelum menentukan hasil pemeriksaan.
            </p>

            <form method="POST"
                  action="{{ route('tu.pemeriksaan.update', $surat) }}"
                  class="mt-5 space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="aksi" class="mb-2 block text-sm font-semibold">
                        Keputusan
                    </label>

                    <select id="aksi" name="aksi" required
                            class="w-full rounded-xl border border-slate-300 bg-white p-3">
                        <option value="">Pilih keputusan</option>
                        <option value="verifikasi" @selected(old('aksi') === 'verifikasi')>
                            Verifikasi — surat sesuai
                        </option>
                        <option value="tolak" @selected(old('aksi') === 'tolak')>
                            Tolak surat
                        </option>
                        <option value="spam" @selected(old('aksi') === 'spam')>
                            Tandai sebagai spam
                        </option>
                    </select>
                </div>

                <div>
                    <label for="catatan" class="mb-2 block text-sm font-semibold">
                        Catatan / alasan
                    </label>

                    <textarea id="catatan" name="catatan" rows="4" maxlength="2000"
                              class="w-full rounded-xl border border-slate-300 p-3">{{ old('catatan') }}</textarea>

                    <p class="mt-2 text-xs text-slate-500">
                        Wajib diisi jika surat ditolak atau ditandai spam.
                    </p>
                </div>

                <button class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
                    Simpan Hasil Pemeriksaan
                </button>
            </form>
        </section>
    @elseif ($surat->status === 'diverifikasi' && ! $surat->diteruskan_at)
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold">Teruskan surat</h2>

            <p class="mt-2 text-sm text-slate-600">
                Surat telah diverifikasi. Teruskan agar dapat diperiksa Kepala Sekolah.
            </p>

            <form method="POST"
                  action="{{ route('tu.pemeriksaan.update', $surat) }}"
                  class="mt-5">
                @csrf
                @method('PATCH')

                <input type="hidden" name="aksi" value="teruskan">

                <label for="catatan" class="mb-2 block text-sm font-semibold">
                    Catatan untuk Kepala Sekolah (opsional)
                </label>

                <textarea id="catatan" name="catatan" rows="3" maxlength="2000"
                          class="w-full rounded-xl border border-slate-300 p-3">{{ old('catatan') }}</textarea>

                <button class="mt-4 rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white">
                    Teruskan ke Kepala Sekolah
                </button>
            </form>
        </section>
    @else
        <p class="mt-6 rounded-xl bg-blue-50 p-4 text-sm text-blue-900">
            Pemeriksaan TU untuk surat ini sudah selesai.
            Riwayat keputusan tersedia di atas.
        </p>
    @endif
@endsection
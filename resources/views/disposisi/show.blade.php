@extends('layouts.internal')

@section('title', 'Detail Disposisi')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('disposisi.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">
            Kembali
        </a>

        <a href="{{ route('disposisi.surat-masuk', $disposisi) }}" class="rounded-xl bg-blue-700 px-5 py-3 text-white">Lihat PDF Asli Surat Masuk</a>
        @if ($disposisi->penerima->role !== 'kepala_sekolah')
            <a href="{{ route('disposisi.lembar', $disposisi) }}" class="rounded-xl border border-blue-700 px-5 py-3 text-blue-700">Lihat Lembar Disposisi</a>
        @endif
    </div>

    @if ($disposisi->penerima->role === 'kepala_sekolah')
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold">Permohonan TU</h2>
            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Asal surat</dt><dd class="mt-1 font-semibold">{{ $disposisi->surat->asal_surat }}</dd></div>
                <div><dt class="text-slate-500">Nomor surat / agenda</dt><dd class="mt-1 font-semibold">{{ $disposisi->surat->nomor_surat ?: '—' }} / {{ $disposisi->surat->nomor_agenda ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Tanggal diterima</dt><dd class="mt-1 font-semibold">{{ $disposisi->surat->tanggal_diterima->format('d/m/Y') }}</dd></div>
                <div><dt class="text-slate-500">Perihal</dt><dd class="mt-1 font-semibold">{{ $disposisi->surat->perihal }}</dd></div>
            </dl>
            <p class="mt-4 text-sm text-slate-600"><strong>Catatan pengajuan TU:</strong> {{ $disposisi->instruksi }}</p>
        </section>
    @else
        @include('disposisi.lembar-konten')
    @endif

    @if ($disposisi->status_persetujuan === 'menunggu')
        <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="text-lg font-bold text-amber-950">Permohonan TU Menunggu Tinjauan</h2>
            <p class="mt-2 text-sm text-amber-900">Ini baru permohonan untuk memproses surat masuk. Setelah menyetujuinya, Kepala Sekolah mengisi dan mengirim lembar disposisi resmi kepada penerima.</p>
            @if (auth()->user()->role === 'kepala_sekolah')
                <div class="mt-5 flex flex-wrap items-start gap-3">
                    <form method="POST" action="{{ route('disposisi.approve', $disposisi) }}">
                        @csrf
                        @method('PATCH')
                        <button class="rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white">Setujui Permohonan</button>
                    </form>
                    <form method="POST" action="{{ route('disposisi.reject', $disposisi) }}" class="min-w-64 flex-1">
                        @csrf
                        @method('PATCH')
                        <label for="catatan_persetujuan" class="mb-2 block text-sm font-semibold text-amber-950">Catatan jika ditolak</label>
                        <textarea id="catatan_persetujuan" name="catatan_persetujuan" rows="2" minlength="5" maxlength="2000" required class="w-full rounded-xl border border-amber-300 p-3">{{ old('catatan_persetujuan') }}</textarea>
                        <button class="mt-2 rounded-xl border border-red-300 bg-white px-5 py-3 font-semibold text-red-700">Tolak dan Kembalikan ke TU</button>
                    </form>
                </div>
            @endif
        </section>
    @elseif ($disposisi->status_persetujuan === 'disetujui' && $disposisi->penerima->role === 'kepala_sekolah' && auth()->user()->role === 'kepala_sekolah')
        <section class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-6">
            <h2 class="text-lg font-bold text-blue-950">Pengajuan Disetujui — Buat Disposisi Resmi</h2>
            <p class="mt-2 text-sm text-blue-900">Pilih penerima dan lengkapi instruksi pada format lembar disposisi. Setelah dikirim, penerima dapat membaca surat asli dan mencatat tindak lanjut.</p>
            <form method="POST" action="{{ route('disposisi.issue', $disposisi) }}" class="mt-5 space-y-5">
                @csrf
                @method('PATCH')
                <fieldset>
                    <legend class="mb-2 font-semibold">Diteruskan kepada</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($penerimas as $penerima)
                            <label class="flex items-center gap-3 rounded-xl border border-blue-200 bg-white p-3">
                                <input type="checkbox" name="penerima_ids[]" value="{{ $penerima->id }}" @checked(in_array((string) $penerima->id, array_map('strval', (array) old('penerima_ids', [])), true))>
                                <span>{{ $penerima->name }} <span class="text-xs text-slate-500">({{ $penerima->roleLabel() }})</span></span>
                            </label>
                        @endforeach
                    </div>
                    @if ($penerimas->isEmpty())<p class="mt-2 text-sm text-amber-800">Belum ada akun penerima disposisi yang aktif.</p>@endif
                </fieldset>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="sifat_surat" class="mb-2 block font-semibold">Sifat surat</label>
                        <select id="sifat_surat" name="sifat_surat" required class="w-full rounded-xl border border-slate-300 bg-white p-3">
                            <option value="biasa" @selected(old('sifat_surat', 'biasa') === 'biasa')>Biasa</option>
                            <option value="rahasia" @selected(old('sifat_surat') === 'rahasia')>Rahasia</option>
                        </select>
                    </div>
                    <div>
                        <label for="prioritas" class="mb-2 block font-semibold">Prioritas</label>
                        <select id="prioritas" name="prioritas" required class="w-full rounded-xl border border-slate-300 bg-white p-3">
                            @foreach (['biasa' => 'Biasa', 'segera' => 'Segera', 'sangat_segera' => 'Sangat Segera'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('prioritas', 'biasa') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <fieldset>
                    <legend class="mb-2 font-semibold">Dengan hormat</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach (['tanggapan_saran' => 'Tanggapan dan saran', 'proses_lanjut' => 'Proses lebih lanjut', 'koordinasi_konfirmasi' => 'Koordinasi / konfirmasi', 'lainnya' => 'Lainnya'] as $value => $label)
                            <label class="flex items-center gap-2 rounded-xl border border-blue-200 bg-white p-3"><input type="checkbox" name="arahan[]" value="{{ $value }}" @checked(in_array($value, (array) old('arahan', []), true))><span>{{ $label }}</span></label>
                        @endforeach
                    </div>
                </fieldset>
                <div>
                    <label for="instruksi" class="mb-2 block font-semibold">Catatan / instruksi</label>
                    <textarea id="instruksi" name="instruksi" rows="4" maxlength="5000" required class="w-full rounded-xl border border-slate-300 p-3">{{ old('instruksi') }}</textarea>
                </div>
                <div class="max-w-sm">
                    <label for="batas_waktu" class="mb-2 block font-semibold">Batas waktu (opsional)</label>
                    <input id="batas_waktu" name="batas_waktu" type="date" min="{{ today()->toDateString() }}" value="{{ old('batas_waktu') }}" class="w-full rounded-xl border border-slate-300 p-3">
                </div>
                <button @disabled($penerimas->isEmpty()) class="rounded-xl bg-blue-700 px-6 py-3 font-semibold text-white disabled:opacity-50">Kirim Lembar Disposisi Resmi</button>
            </form>
        </section>
    @elseif ($disposisi->status_persetujuan === 'ditolak')
        <section class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-6 text-red-900">
            <h2 class="font-bold">Pengajuan ditolak Kepala Sekolah</h2>
            <p class="mt-2 whitespace-pre-line text-sm">{{ $disposisi->catatan_persetujuan }}</p>
            <p class="mt-2 text-sm">TU dapat memperbaiki dan mengirim ulang pengajuan dari surat yang sama.</p>
        </section>
    @elseif ($disposisi->status_persetujuan === 'disetujui' && $disposisi->penerima->role === 'kepala_sekolah')
        <p class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">Pengajuan TU disetujui. Kepala Sekolah sedang menyiapkan lembar disposisi resmi.</p>
    @elseif ($disposisi->status_persetujuan === 'diteruskan')
        <p class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">Kepala Sekolah telah menerbitkan lembar disposisi resmi kepada penerima.</p>
    @endif

    @if (
        in_array(auth()->user()->role, ['kepala_sekolah', 'penerima_disposisi', 'waka_humas'], true)
        && (int) auth()->id() === (int) $disposisi->penerima_id
        && $disposisi->penerima->role !== 'kepala_sekolah'
        && $disposisi->status_persetujuan === 'disetujui'
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
                            Lihat Lampiran Hasil
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

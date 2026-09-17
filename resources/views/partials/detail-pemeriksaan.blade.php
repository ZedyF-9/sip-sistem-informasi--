<section class="rounded-2xl border border-slate-200 bg-white p-6">
    <p class="text-sm font-semibold text-blue-700">
        {{ $surat->nomor_registrasi }}
    </p>

    <h2 class="mt-3 text-2xl font-bold">{{ $surat->perihal }}</h2>

    <p class="mt-3 text-sm font-semibold text-blue-800">
        {{ $surat->statusLabel() }}
    </p>

    <dl class="mt-6 grid gap-5 md:grid-cols-2">
        @foreach ([
            'Nomor surat' => $surat->nomor_surat,
            'Jenis surat' => $surat->jenis_surat,
            'Sifat surat' => ucfirst($surat->sifat_surat),
            'Asal surat' => $surat->asal_surat,
            'Nama pengirim' => $surat->nama_pengirim,
            'Metode' => ucfirst($surat->metode_pengiriman),
            'Tanggal surat' => $surat->tanggal_surat->format('d/m/Y'),
            'Tanggal diterima' => $surat->tanggal_diterima->format('d/m/Y'),
            'Nomor agenda' => $surat->nomor_agenda,
            'Lokasi arsip' => $surat->lokasi_arsip_fisik,
            'Dicatat oleh' => $surat->pencatat?->name,
            'Diteruskan pada' => $surat->diteruskan_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i').'',
        ] as $label => $value)
            <div>
                <dt class="text-sm text-slate-500">{{ $label }}</dt>
                <dd class="mt-1 break-words font-medium">{{ $value ?: '—' }}</dd>
            </div>
        @endforeach
    </dl>

    <p class="mt-6 text-xs text-slate-500">
        Waktu penerusan dan riwayat ditampilkan dalam WIB.
    </p>
</section>

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold">Riwayat Pemeriksaan</h2>

    <p class="mt-2 text-sm text-slate-500">
        Dicatat pada
        {{ $surat->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB.
    </p>

    <div class="mt-5 space-y-4">
        @forelse ($riwayats as $riwayat)
            <article class="rounded-xl border border-slate-200 p-4">
                <p class="font-semibold">
                    {{ match ($riwayat->aksi) {
                        'verifikasi' => 'Surat diverifikasi',
                        'tolak' => 'Surat ditolak',
                        'spam' => 'Surat ditandai spam',
                        'teruskan' => 'Diteruskan ke Kepala Sekolah',
                        default => $riwayat->aksi,
                    } }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $riwayat->nama_petugas }} ·
                    {{ \Illuminate\Support\Carbon::parse($riwayat->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                </p>

                <p class="mt-2 text-sm text-slate-600">
                    {{ ucwords(str_replace('_', ' ', $riwayat->status_sebelum)) }}
                    →
                    {{ ucwords(str_replace('_', ' ', $riwayat->status_sesudah)) }}
                </p>

                @if ($riwayat->catatan)
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $riwayat->catatan }}</p>
                @endif
            </article>
        @empty
            <p class="text-sm text-slate-500">
                Belum ada tindakan pemeriksaan yang tercatat.
            </p>
        @endforelse
    </div>
</section>
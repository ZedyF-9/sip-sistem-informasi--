@php
    $namaPenerima = mb_strtolower($disposisi->penerima->name);
    $posisiPenerima = match (true) {
        $disposisi->penerima->role === 'kepala_sekolah' => 'kepala_sekolah',
        $disposisi->penerima->role === 'waka_humas' => 'waka_humas',
        str_contains($namaPenerima, 'kurikulum') => 'waka_kurikulum',
        str_contains($namaPenerima, 'kesiswaan') => 'waka_kesiswaan',
        str_contains($namaPenerima, 'sarpras') => 'waka_sarpras',
        str_contains($namaPenerima, 'humas') => 'waka_humas',
        str_contains($namaPenerima, 'osis') => 'pembina_osis',
        str_contains($namaPenerima, 'tu') => 'koordinator_tu',
        str_contains($namaPenerima, 'bos') => 'bendahara_bos',
        str_contains($namaPenerima, 'spp') => 'bendahara_spp',
        default => 'lainnya',
    };
    $arahanTerpilih = (array) ($disposisi->arahan ?? []);
@endphp

<section class="disposition-paper" style="font-family: DejaVu Sans, sans-serif; color:#111; border:1px solid #222; background:#fff; padding:14px;">
    <header style="display:table; width:100%; border-bottom:3px double #111; padding-bottom:8px; margin-bottom:8px;">
        <div style="display:table-cell; width:12%; vertical-align:middle; text-align:center;">
            <img src="data:image/jpeg;base64,{{ $logoKepri }}" alt="Lambang Kepulauan Riau" style="width:54px; height:54px;">
        </div>
        <div style="display:table-cell; width:76%; vertical-align:middle; text-align:center; line-height:1.35;">
            <strong style="font-size:13px;">PEMERINTAH PROVINSI KEPULAUAN RIAU</strong><br>
            <strong style="font-size:12px;">DINAS PENDIDIKAN</strong><br>
            <strong style="font-size:16px;">SMK NEGERI 4 TANJUNGPINANG</strong><br>
            <span style="font-size:8px;">Jalan Nusantara, Tanjungpinang, Kepulauan Riau</span>
        </div>
        <div style="display:table-cell; width:12%; vertical-align:middle; text-align:center;">
            <img src="data:image/jpeg;base64,{{ $logoSekolah }}" alt="Lambang SMK Negeri 4 Tanjungpinang" style="width:54px; height:54px;">
        </div>
    </header>

    <h1 style="font-size:14px; text-align:center; margin:8px 0 10px; letter-spacing:1px;">LEMBAR DISPOSISI</h1>

    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        <tbody>
            <tr>
                <td style="width:15%; padding:5px; border:1px solid #333;">Surat dari</td>
                <td style="width:35%; padding:5px; border:1px solid #333;">{{ $disposisi->surat->asal_surat }}</td>
                <td style="width:15%; padding:5px; border:1px solid #333;">Diterima Tgl.</td>
                <td style="width:35%; padding:5px; border:1px solid #333;">{{ $disposisi->surat->tanggal_diterima->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="padding:5px; border:1px solid #333;">No. Surat</td>
                <td style="padding:5px; border:1px solid #333;">{{ $disposisi->surat->nomor_surat ?: '—' }}</td>
                <td style="padding:5px; border:1px solid #333;">No. Agenda</td>
                <td style="padding:5px; border:1px solid #333;">{{ $disposisi->surat->nomor_agenda ?: '—' }}</td>
            </tr>
            <tr>
                <td style="padding:5px; border:1px solid #333;">Tgl. Surat</td>
                <td style="padding:5px; border:1px solid #333;">{{ $disposisi->surat->tanggal_surat->format('d/m/Y') }}</td>
                <td style="padding:5px; border:1px solid #333;">Sifat</td>
                <td style="padding:5px; border:1px solid #333;">
                    @foreach ([
                        'sangat_segera' => 'Sangat segera', 'segera' => 'Segera',
                        'rahasia' => 'Rahasia', 'biasa' => 'Biasa',
                    ] as $value => $label)
                        <span style="white-space:nowrap; margin-right:7px;">{{ $disposisi->prioritas === $value || ($value === 'rahasia' && $disposisi->surat->sifat_surat === 'rahasia') || ($value === 'biasa' && $disposisi->prioritas === 'biasa' && $disposisi->surat->sifat_surat === 'biasa') ? '☒' : '☐' }} {{ $label }}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td style="padding:5px; border:1px solid #333;">Hal</td>
                <td colspan="3" style="padding:5px; border:1px solid #333;">{{ $disposisi->surat->perihal }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:10px; margin-top:8px;">
        <tbody>
            <tr>
                <td style="width:50%; vertical-align:top; padding:8px; border:1px solid #333;">
                    <strong>Diteruskan kepada Sdr.</strong>
                    <table style="width:100%; border-collapse:collapse; margin-top:6px;">
                        @foreach ([
                            'kepala_sekolah' => 'Kepala Sekolah',
                            'waka_kurikulum' => 'Waka Kurikulum',
                            'waka_kesiswaan' => 'Waka Kesiswaan',
                            'waka_sarpras' => 'Waka Sarpras',
                            'waka_humas' => 'Waka Humas',
                            'pembina_osis' => 'Pembina OSIS',
                            'koordinator_tu' => 'Koordinator TU',
                            'bendahara_bos' => 'Bendahara Sekolah BOS',
                            'bendahara_spp' => 'Bendahara SPP',
                        ] as $value => $label)
                            <tr><td style="padding:2px 0;">{{ $posisiPenerima === $value ? '☒' : '☐' }} {{ $label }}</td></tr>
                        @endforeach
                        @if ($posisiPenerima === 'lainnya')
                            <tr><td style="padding:2px 0;">☒ Lainnya: {{ $disposisi->penerima->name }}</td></tr>
                        @else
                            <tr><td style="padding:2px 0;">☐ Lainnya: ........................................</td></tr>
                        @endif
                    </table>
                </td>
                <td style="width:50%; vertical-align:top; padding:8px; border:1px solid #333;">
                    <strong>Dengan hormat:</strong>
                    <table style="width:100%; border-collapse:collapse; margin-top:6px;">
                        @foreach ([
                            'tanggapan_saran' => 'Tanggapan dan saran',
                            'proses_lanjut' => 'Proses lebih lanjut',
                            'koordinasi_konfirmasi' => 'Koordinasi / konfirmasi',
                            'lainnya' => 'Lainnya',
                        ] as $value => $label)
                            <tr><td style="padding:3px 0;">{{ in_array($value, $arahanTerpilih, true) ? '☒' : '☐' }} {{ $label }}</td></tr>
                        @endforeach
                        @if (in_array('lainnya', $arahanTerpilih, true))
                            <tr><td style="padding:2px 0 2px 18px;">{{ $disposisi->instruksi }}</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td style="height:104px; vertical-align:top; padding:8px; border:1px solid #333;">
                    <strong>Catatan:</strong><br>
                    <div style="white-space:pre-line; margin-top:5px;">{{ $disposisi->instruksi ?: '—' }}</div>
                </td>
                <td style="height:104px; vertical-align:top; padding:8px; border:1px solid #333; text-align:center;">
                    Tanjungpinang, {{ $disposisi->tanggal_disposisi->translatedFormat('d F Y') }}<br>
                    Diajukan oleh {{ $disposisi->pemberi->roleLabel() }},<br><br>
                    <strong>{{ $disposisi->pemberi->name }}</strong><br><br>
                    Status persetujuan Kepala Sekolah:<br>
                    <strong>{{ $disposisi->status_persetujuan === 'menunggu' ? 'Menunggu persetujuan' : ($disposisi->status_persetujuan === 'disetujui' ? 'Disetujui oleh '.$disposisi->penyetuju?->name : 'Ditolak oleh '.$disposisi->penyetuju?->name) }}</strong>
                    @if ($disposisi->disetujui_at)<br>{{ $disposisi->disetujui_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB @endif
                </td>
            </tr>
        </tbody>
    </table>
</section>

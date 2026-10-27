<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 18mm 22mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 10.5pt; line-height: 1.38; }
        p { margin: 8pt 0; }
        .kop { width: 100%; border-collapse: collapse; border-bottom: 3px double #111; margin-bottom: 13pt; }
        .kop td { vertical-align: middle; padding: 0 4pt 6pt; }
        .kop img { display: block; width: auto; height: auto; max-width: 68pt; max-height: 68pt; margin: 0 auto; }
        .kop .center { text-align: center; line-height: 1.22; }
        .province { font-size: 9pt; }
        .department { font-size: 11pt; font-weight: bold; }
        .school { font-size: 14pt; font-weight: bold; }
        .contact { font-size: 7.5pt; }
        .center { text-align: center; }
        .right { text-align: right; }
        .meta { width: 100%; border-collapse: collapse; margin: 9pt 0 15pt; }
        .meta td { vertical-align: top; padding: 1pt 3pt; }
        .meta .label { width: 80pt; }
        .meta .colon { width: 10pt; }
        .body { text-align: justify; }
        .signature { width: 225pt; margin: 22pt 0 0 auto; text-align: left; }
        .signature-space { height: 48pt; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .title { text-align: center; margin: 12pt 0 16pt; line-height: 1.35; }
        .list { width: 100%; border-collapse: collapse; margin: 5pt 0 12pt; }
        .list th, .list td { padding: 2pt 5pt; vertical-align: top; }
        .list th { text-align: left; }
        .list .no { width: 28pt; }
        .list .student-class { width: 100pt; }
        .consideration, .diktum { width: 100%; border-collapse: collapse; margin: 7pt 0; }
        .consideration td, .diktum td { vertical-align: top; padding: 2pt; }
        .section-label { width: 82pt; }
        .diktum-label { width: 55pt; }
        .small { font-size: 9pt; }
        .preserve-lines { white-space: pre-line; }
    </style>
</head>
<body>
@php
    $d = $surat->data_surat ?? [];
    $value = fn (string $key, string $fallback = '-') => filled($d[$key] ?? null) ? $d[$key] : $fallback;
    $kepala = 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.';
    $pangkatKepala = 'Pembina Utama Muda';
    $nipKepala = '19770421 200502 2 011';
    $tanggal = \Illuminate\Support\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y');
    $siswa = preg_split('/\r\n|\r|\n/', trim($d['daftar_siswa'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    $kelasSiswa = preg_split('/\r\n|\r|\n/', trim($d['kelas_siswa'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    $lampiranMutasi = preg_split('/\r\n|\r|\n/', trim($d['dokumen_lampiran'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
@endphp

<table class="kop">
    <tr>
        <td style="width: 13%; text-align: center"><img src="data:image/jpeg;base64,{{ $logoKepri }}" alt="Lambang Kepulauan Riau"></td>
        <td class="center" style="width: 74%">
            <div class="province">PEMERINTAH PROVINSI KEPULAUAN RIAU</div>
            <div class="department">DINAS PENDIDIKAN</div>
            <div class="school">SMK NEGERI 4 TANJUNGPINANG</div>
            <div class="contact">Jalan Nusantara Km. 14, Batu IX, Tanjungpinang, Kepulauan Riau 29125</div>
            <div class="contact">Telepon (0771) 440844, Laman smkn4-tanjungpinang.sch.id, Pos-el smkntpi4@gmail.com</div>
        </td>
        <td style="width: 13%; text-align: center"><img src="data:image/jpeg;base64,{{ $logoSekolah }}" alt="Lambang SMKN 4"></td>
    </tr>
</table>

@if ($surat->jenis_surat === 'Surat Undangan')
    <p class="right">Tanjungpinang, {{ $tanggal }}</p>
    <table class="meta">
        <tr><td class="label">Nomor</td><td class="colon">:</td><td>{{ $surat->nomor_surat }}</td></tr>
        <tr><td>Sifat</td><td>:</td><td>Biasa</td></tr>
        <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
        <tr><td>Hal</td><td>:</td><td>{{ $surat->perihal }}</td></tr>
    </table>
    <p>Yth. {{ $value('penerima_nama') }}<br>Orang tua/wali dari {{ $value('nama_siswa') }} ({{ $value('kelas') }})<br>di {{ $value('alamat_penerima') }}</p>
    <p class="body" style="text-indent: 28pt">Sehubungan dengan {{ $value('nama_kegiatan') }} Tahun Pelajaran {{ $value('tahun_pelajaran') }}, kami mengundang Bapak/Ibu untuk hadir dalam kegiatan tersebut. Kehadiran Bapak/Ibu diperlukan untuk {{ $value('keperluan') }}.</p>
    <table class="meta">
        <tr><td class="label">Hari, tanggal</td><td>:</td><td>{{ $value('hari_tanggal_kegiatan') }}</td></tr>
        <tr><td>Waktu</td><td>:</td><td>{{ $value('waktu') }}</td></tr>
        <tr><td>Tempat</td><td>:</td><td>{{ $value('tempat_kegiatan') }}</td></tr>
    </table>
    <p class="body">Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.</p>
    <div class="signature center">
        Kepala Sekolah,<div class="signature-space"></div>
        <span class="signature-name">{{ $kepala }}</span><br>{{ $pangkatKepala }}<br>NIP {{ $nipKepala }}
    </div>
@elseif ($surat->jenis_surat === 'Surat Perintah Tugas')
    <div class="title"><strong>SURAT PERINTAH TUGAS</strong><br>NOMOR: {{ $surat->nomor_surat }}</div>
    <table class="meta"><tr><td class="label">Dasar</td><td>:</td><td>{{ $value('dasar_surat') }}</td></tr></table>
    <p class="center"><strong>MEMERINTAHKAN:</strong></p>
    <table class="meta">
        <tr><td class="label">Kepada</td><td>:</td><td>1.</td><td>Nama</td><td>:</td><td>{{ $value('nama_penerima_tugas') }}</td></tr>
        <tr><td></td><td></td><td></td><td>Pangkat/golongan</td><td>:</td><td>{{ $value('pangkat_golongan') }}</td></tr>
        <tr><td></td><td></td><td></td><td>NIPPPK/NIP</td><td>:</td><td>{{ $value('nip') }}</td></tr>
        <tr><td></td><td></td><td></td><td>Jabatan</td><td>:</td><td>{{ $value('jabatan_penerima') }}</td></tr>
    </table>
    <table class="meta"><tr><td class="label">Untuk</td><td>:</td><td>{{ $value('uraian_tugas') }} {{ $value('tanggal_kegiatan') }} di {{ $value('tempat_kegiatan') }}.</td></tr></table>
    <table class="list">
        <thead><tr><th class="no">No.</th><th>Nama</th><th class="student-class">Kelas</th></tr></thead>
        <tbody>
        @foreach ($siswa as $index => $namaSiswa)
            <tr><td>{{ $index + 1 }}.</td><td>{{ $namaSiswa }}</td><td>{{ $kelasSiswa[$index] ?? ($kelasSiswa[0] ?? '') }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <p class="body">Tugas ini harap dilaksanakan dengan penuh tanggung jawab dan setelah selesai membuat laporan.</p>
    <div class="signature">
        Ditetapkan di&nbsp;&nbsp; : {{ $value('tempat_penerbitan') }}<br>Pada tanggal&nbsp;&nbsp; : {{ $tanggal }}<br><br>
        Kepala Sekolah,<div class="signature-space"></div>
        <span class="signature-name">{{ $kepala }}</span><br>{{ $pangkatKepala }}<br>NIP {{ $nipKepala }}
    </div>
@elseif ($surat->jenis_surat === 'Surat Keputusan')
    <div class="title">
        <strong>SURAT KEPUTUSAN</strong><br>
        KEPALA SEKOLAH MENENGAH KEJURUAN NEGERI 4 TANJUNGPINANG<br>
        NOMOR {{ $surat->nomor_surat }}<br><br>
        TENTANG<br>{{ mb_strtoupper($value('nama_tugas')) }}<br>
        TAHUN PELAJARAN {{ $value('tahun_pelajaran') }}<br>
        DI SMK NEGERI 4 TANJUNGPINANG
    </div>
    <p class="center"><strong>KEPALA SMK NEGERI 4 TANJUNGPINANG</strong></p>
    <table class="consideration">
        <tr><td class="section-label"><strong>Menimbang</strong></td><td>:</td><td class="preserve-lines">{{ $value('dasar_pertimbangan', 'a. bahwa untuk membantu kelancaran pelaksanaan program kerja sekolah dan kegiatan lainnya di SMK Negeri 4 Tanjungpinang perlu menetapkan tugas tambahan sebagaimana dimaksud dalam keputusan ini;\nb. bahwa untuk memenuhi maksud tersebut perlu menetapkan Keputusan Kepala Sekolah.') }}</td></tr>
        <tr><td class="section-label"><strong>Mengingat</strong></td><td>:</td><td>
            @if (filled($d['dasar_hukum'] ?? null))
                <span class="preserve-lines">{{ $d['dasar_hukum'] }}</span>
            @else
                <ol class="small" style="margin: 0; padding-left: 18pt">
                    <li>Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;</li>
                    <li>Undang-Undang Nomor 14 Tahun 2005 tentang Guru dan Dosen;</li>
                    <li>Peraturan Pemerintah Nomor 17 Tahun 2010 tentang Pengelolaan dan Penyelenggaraan Pendidikan;</li>
                    <li>Peraturan Pemerintah Nomor 4 Tahun 2022 tentang Perubahan atas Peraturan Pemerintah Nomor 57 Tahun 2021 tentang Standar Nasional Pendidikan;</li>
                    <li>Peraturan Menteri PAN dan Reformasi Birokrasi Nomor 16 Tahun 2009 tentang Jabatan Fungsional Guru dan Angka Kreditnya;</li>
                    <li>Peraturan Menteri Pendidikan dan Kebudayaan Nomor 53 Tahun 2018 tentang Pemenuhan Beban Kerja Guru, Kepala Sekolah, dan Pengawas Sekolah;</li>
                    <li>Peraturan Menteri PAN dan Reformasi Birokrasi Nomor 21 Tahun 2024 tentang Jabatan Fungsional Guru.</li>
                </ol>
            @endif
        </td></tr>
    </table>
    <p class="center"><strong>MEMUTUSKAN</strong></p>
    <table class="diktum">
        <tr><td class="section-label"><strong>Menetapkan</strong></td><td>:</td><td>KEPUTUSAN KEPALA SMK NEGERI 4 TANJUNGPINANG TENTANG {{ mb_strtoupper($value('nama_tugas')) }} TAHUN PELAJARAN {{ $value('tahun_pelajaran') }}.</td></tr>
        <tr><td class="diktum-label"><strong>KESATU</strong></td><td>:</td><td>Menetapkan {{ $value('nama_penerima_sk') }} sebagai {{ $value('jabatan_penerima_sk') }} terhitung mulai tanggal {{ \Illuminate\Support\Carbon::parse($d['tanggal_mulai'])->translatedFormat('d F Y') }}.</td></tr>
        <tr><td class="diktum-label"><strong>KEDUA</strong></td><td>:</td><td>Segala biaya yang timbul akibat pelaksanaan keputusan ini dibebankan pada anggaran yang sesuai.</td></tr>
        <tr><td class="diktum-label"><strong>KETIGA</strong></td><td>:</td><td>Apabila terdapat kekeliruan dalam keputusan ini, akan dibetulkan sebagaimana mestinya.</td></tr>
        <tr><td class="diktum-label"><strong>KEEMPAT</strong></td><td>:</td><td>Keputusan ini berlaku sejak tanggal ditetapkan.</td></tr>
    </table>
    <div class="signature">
        Ditetapkan di&nbsp;&nbsp; : {{ $value('tempat_penerbitan', 'Tanjungpinang') }}<br>Pada tanggal&nbsp;&nbsp; : {{ $tanggal }}<br><br>
        Kepala Sekolah,<div class="signature-space"></div>
        <span class="signature-name">{{ $kepala }}</span><br>{{ $pangkatKepala }}<br>NIP {{ $nipKepala }}
    </div>
    <p class="small"><strong>Tembusan:</strong><br><span class="preserve-lines">{{ $value('tembusan', "1. Kepala Dinas Pendidikan Provinsi Kepulauan Riau\n2. Yang bersangkutan") }}</span></p>
@else
    <div class="title"><strong>SURAT REKOMENDASI MUTASI/PINDAH SISWA</strong><br>Nomor: {{ $surat->nomor_surat }}</div>
    <p class="body">Yang bertanda tangan di bawah ini:</p>
    <table class="meta">
        <tr><td class="label">Nama</td><td>:</td><td>{{ $kepala }}</td></tr>
        <tr><td>NIP</td><td>:</td><td>{{ $nipKepala }}</td></tr>
        <tr><td>Pangkat/golongan</td><td>:</td><td>{{ $pangkatKepala }}</td></tr>
        <tr><td>Jabatan</td><td>:</td><td>Kepala SMK Negeri 4 Tanjungpinang</td></tr>
    </table>
    <p class="body">Dengan ini menerangkan bahwa:</p>
    <table class="meta">
        <tr><td class="label">Nama siswa</td><td>:</td><td>{{ $value('nama_siswa') }}</td></tr>
        <tr><td>NIP / NISN</td><td>:</td><td>{{ $value('nisn_nip') }}</td></tr>
        @if (filled($d['pangkat_golongan'] ?? null))<tr><td>Pangkat/golongan</td><td>:</td><td>{{ $d['pangkat_golongan'] }}</td></tr>@endif
        @if (filled($d['jabatan'] ?? null))<tr><td>Jabatan</td><td>:</td><td>{{ $d['jabatan'] }}</td></tr>@endif
        <tr><td>Kelas/semester</td><td>:</td><td>{{ $value('kelas_semester') }}</td></tr>
        <tr><td>Sekolah asal</td><td>:</td><td>{{ $value('sekolah_asal') }}</td></tr>
        <tr><td>Alamat sekolah asal</td><td>:</td><td>{{ $value('alamat_sekolah_asal') }}</td></tr>
        <tr><td>Nama orang tua/wali</td><td>:</td><td>{{ $value('nama_orang_tua') }}</td></tr>
        <tr><td>Alamat orang tua/wali</td><td>:</td><td>{{ $value('alamat_orang_tua') }}</td></tr>
    </table>
    <p class="body">Berdasarkan pengajuan permohonan mutasi/pindah sekolah dari orang tua/wali murid atas nama siswa tersebut di atas, kami merekomendasikan yang bersangkutan untuk pindah dari {{ $value('sekolah_asal') }} ke {{ $value('sekolah_tujuan') }} dengan alasan {{ $value('alasan_mutasi') }}.</p>
    <p>Setelah melihat dan memverifikasi dokumen akademik serta alasan tujuan mutasi/pindah sekolah, dengan ini kami merekomendasikan siswa yang bersangkutan melengkapi dokumen sebagai berikut:</p>
    <ul style="margin-top: 4pt">
        @forelse ($lampiranMutasi as $lampiran)<li>{{ $lampiran }}</li>@empty<li>Dokumen pendukung sesuai ketentuan sekolah tujuan.</li>@endforelse
    </ul>
    <p class="body">Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>
    <div class="signature">
        {{ $value('tempat_penerbitan', 'Tanjungpinang') }}, {{ $tanggal }}<br>
        Kepala Sekolah,<div class="signature-space"></div>
        <span class="signature-name">{{ $kepala }}</span><br>{{ $pangkatKepala }}<br>NIP {{ $nipKepala }}
    </div>
@endif
</body>
</html>

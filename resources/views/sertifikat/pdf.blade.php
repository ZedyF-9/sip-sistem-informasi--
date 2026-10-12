<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat PKL</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; width: 841.89pt; height: 563.15pt; font-family: "DejaVu Sans", sans-serif; color: #30343b; }
        .page { position: relative; width: 841.89pt; height: 563.15pt; overflow: hidden; }
        .background { position: absolute; inset: 0; width: 841.89pt; height: 563.15pt; }
        .watermark { position: absolute; left: 326pt; top: 213pt; width: 190pt; height: 190pt; opacity: .10; }
        .logo { position: absolute; top: 30pt; width: 65pt; height: 72pt; object-fit: contain; }
        .logo-kepri { left: 49pt; }
        .logo-school { right: 48pt; }
        .title { position: absolute; top: 22pt; left: 140pt; width: 562pt; text-align: center; font-family: "DejaVu Serif", serif; font-weight: bold; font-size: 42pt; line-height: 1.08; }
        .number { position: absolute; top: 112pt; left: 175pt; width: 492pt; text-align: center; font-size: 10pt; }
        .presented { position: absolute; top: 145pt; left: 200pt; width: 442pt; text-align: center; font-size: 11pt; }
        .recipient { position: absolute; top: 174pt; left: 95pt; width: 652pt; text-align: center; font-family: "DejaVu Serif", serif; font-size: 22pt; font-style: italic; font-weight: bold; text-transform: uppercase; }
        .details { position: absolute; top: 225pt; left: 210pt; width: 430pt; font-size: 10pt; line-height: 16pt; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { vertical-align: top; }
        .details .label { width: 142pt; }
        .details .colon { width: 12pt; }
        .description { position: absolute; top: 328pt; left: 95pt; width: 652pt; text-align: center; font-size: 11pt; line-height: 17pt; }
        .signer { position: absolute; top: 405pt; width: 250pt; text-align: center; font-size: 9pt; line-height: 13pt; }
        .signer-left { left: 48pt; }
        .signer-right { right: 48pt; }
        .signer-role { min-height: 28pt; }
        .signer-space { height: 68pt; }
        .signer-name { font-weight: bold; text-decoration: underline; }
        .manual-signature-name { width: 175pt; height: 14pt; margin: 0 auto 3pt; border-bottom: 1px solid #30343b; }
        .signature-stamp { position: absolute; left: 111pt; top: 430pt; width: 94pt; height: 76pt; opacity: .77; }
        .motto { position: absolute; left: 354pt; top: 420pt; width: 134pt; height: 115pt; object-fit: contain; }
        .qr { position: absolute; right: 15pt; bottom: 14pt; width: 35pt; height: 35pt; }
        .verification { position: absolute; right: 55pt; bottom: 16pt; width: 130pt; text-align: right; color: #9299a3; opacity: .42; font-size: 6.5pt; letter-spacing: 1pt; }
    </style>
</head>
<body>
    <div class="page">
        <img class="background" src="{{ $template }}" alt="">
        <img class="watermark" src="{{ $tutWuri }}" alt="">
        <img class="logo logo-kepri" src="{{ $logoKepri }}" alt="Lambang Provinsi Kepulauan Riau">
        <img class="logo logo-school" src="{{ $logoSekolah }}" alt="Lambang SMKN 4 Tanjungpinang">

        <div class="title">Sertifikat</div>
        <div class="number">Nomor: {{ $sertifikat->nomor }}</div>
        <div class="presented">Diberikan kepada</div>
        <div class="recipient">{{ $snapshot['nama_penerima'] }}</div>

        <div class="details">
            <table>
                <tr><td class="label">Nomor Induk Siswa</td><td class="colon">:</td><td>{{ $snapshot['nis'] ?? $sertifikat->kode_peserta }}</td></tr>
                @if (!empty($snapshot['kelas']))<tr><td>Kelas</td><td>:</td><td>{{ $snapshot['kelas'] }}</td></tr>@endif
                @if (!empty($snapshot['jurusan']))<tr><td>Konsentrasi Keahlian</td><td>:</td><td>{{ $snapshot['jurusan'] }}</td></tr>@endif
                @if (!empty($snapshot['industri']))<tr><td>Tempat PKL</td><td>:</td><td>{{ $snapshot['industri'] }}</td></tr>@endif
                <tr><td>Periode PKL</td><td>:</td><td>{{ $snapshot['tanggal_mulai'] }} s.d. {{ $snapshot['tanggal_selesai'] }}</td></tr>
            </table>
        </div>

        <div class="description">
            Telah selesai mengikuti Praktik Kerja Lapangan (PKL)
            @if (!empty($snapshot['industri'])) pada <strong>{{ $snapshot['industri'] }}</strong>@endif
            pada periode {{ $snapshot['tanggal_mulai'] }} sampai dengan {{ $snapshot['tanggal_selesai'] }}.
        </div>

        <div class="signer signer-left">
            <div class="signer-role">KEPALA<br>SMK NEGERI 4 TANJUNGPINANG</div>
            <div class="signer-space"></div>
            <div class="signer-name">Yayuk Sri Mulyani Rahayu, S.Pd., M.M.</div>
            NIP 19770421 200502 2 011
        </div>
        <img class="signature-stamp" src="{{ $stempel }}" alt="Stempel dan tanda tangan kepala sekolah">

        <img class="motto" src="{{ $motto }}" alt="SMK Bisa, SMK Hebat">

        <div class="signer signer-right">
            <div class="signer-space"></div>
            <div class="manual-signature-name"></div>
        </div>

        <div class="verification">Kode verifikasi: {{ $sertifikat->kode_cetak }}</div>
        <img class="qr" src="{{ $qr }}" alt="QR verifikasi sertifikat">
    </div>
</body>
</html>

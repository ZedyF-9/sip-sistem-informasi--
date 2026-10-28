<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat PKL</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; width: 841.89pt; height: 588.76pt; font-family: "DejaVu Sans", sans-serif; color: #30343b; }
        .page { position: relative; width: 841.89pt; height: 588.76pt; overflow: hidden; }
        .background { position: absolute; inset: 0; width: 841.89pt; height: 588.76pt; }
        .watermark { position: absolute; left: 306pt; top: 197.5pt; width: 230pt; height: 221pt; opacity: .24; }
        .logo { position: absolute; top: 35pt; width: 104pt; height: auto; }
        .logo-kepri { left: 50pt; }
        .logo-school { right: 58pt; }
        .title { position: absolute; top: 32pt; left: 140pt; width: 562pt; text-align: center; font-family: "DejaVu Serif", serif; font-weight: bold; font-size: 66pt; line-height: 1.08; }
        .number { position: absolute; top: 128pt; left: 175pt; width: 492pt; text-align: center; font-size: 10pt; }
        .presented { position: absolute; top: 164pt; left: 200pt; width: 442pt; text-align: center; font-size: 10pt; }
        .recipient { position: absolute; top: 200pt; left: 95pt; width: 652pt; text-align: center; font-family: "DejaVu Serif", serif; font-size: 26.5pt; font-style: italic; font-weight: normal; text-transform: uppercase; }
        .certificate-copy { position: absolute; top: 259pt; left: 0; width: 841.89pt; }
        .details { position: static; width: 400pt; min-height: 63pt; margin-left: 240pt; font-size: 13pt; line-height: 21pt; }
        .details table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .details td { vertical-align: top; font-size: 13pt; line-height: 21pt; }
        .details .label { width: 138pt; white-space: nowrap; }
        .details .colon { width: 8pt; text-align: center; }
        .details td:last-child { width: 254pt; word-wrap: break-word; overflow-wrap: break-word; }
        .details tr:nth-child(2) td:last-child { white-space: nowrap; }
        .description { position: static; width: 592pt; margin: 12pt 0 0 125pt; text-align: center; }
        .description-intro { font-size: 14pt; line-height: 20pt; }
        .description-result { font-size: 12pt; line-height: 20pt; }
        .description .industry { font-weight: normal; text-transform: uppercase; }
        .description strong { font-weight: bold; text-transform: uppercase; }
        .signatures { position: relative; height: 124pt; margin-top: 17pt; }
        .signer { position: absolute; top: 0; width: 250pt; text-align: center; font-size: 9pt; line-height: 13pt; }
        .signer-left { left: 48pt; text-align: left; }
        .signer-right { right: 18pt; width: 205pt; text-align: left; }
        .signer-right .signer-role { font-size: 8pt; line-height: 12pt; }
        .signer-role { min-height: 28pt; }
        .signer-space { position: relative; height: 68pt; }
        .signature-line { position: absolute; bottom: 0; left: 50%; width: 175pt; margin-left: -87.5pt; border-bottom: 1px solid #30343b; }
        .signer-left .signature-line { left: 0; margin-left: 0; }
        .signer-right .signer-space { position: absolute; top: 28pt; left: 0; right: 30pt; height: 68pt; }
        .signer-right .signature-line { left: 0; right: auto; margin-left: 0; }
        .signer-name { font-weight: bold; }
        .motto { position: absolute; left: 365pt; top: 425pt; width: 112pt; height: 120pt; object-fit: contain; }
        .qr { position: absolute; right: 15pt; bottom: 14pt; width: 35pt; height: 35pt; }
        .verification-code { position: absolute; right: 59pt; bottom: 20pt; width: 112pt; text-align: right; }
        .verification-code-label { color: #52606d; font-size: 6.5pt; line-height: 9pt; }
        .verification-code-value { color: #123957; font-size: 11pt; font-weight: bold; letter-spacing: 1pt; line-height: 13pt; }
    </style>
</head>
<body>
    <div class="page">
        <img class="background" src="{{ $template }}" alt="">
        <img class="watermark" src="{{ $tutWuri }}" alt="">
        <img class="logo logo-kepri" src="{{ $logoKepri }}" alt="Lambang Provinsi Kepulauan Riau">
        <img class="logo logo-school" src="{{ $logoSekolah }}" alt="Lambang SMKN 4 Tanjungpinang">

        <div class="title">Sertifikat</div>
        <div class="number">Nomor : {{ $sertifikat->nomor }}</div>
        <div class="presented">Diberikan Kepada</div>
        <div class="recipient">{{ $snapshot['nama_penerima'] }}</div>

        <div class="certificate-copy">
        <div class="details">
            <table>
                <tr>
                    <td class="label">Nomor Induk Siswa</td>
                    <td class="colon">:</td>
                    <td>{{ $snapshot['nis'] ?? $sertifikat->kode_peserta }}</td>
                </tr>
                <tr>
                        <td class="label">Tempat / Tgl. Lahir</td>
                        <td class="colon">:</td>
                    <td>{{ ($snapshot['tempat_lahir'] ?? null) ?: '—' }} / {{ ($snapshot['tanggal_lahir'] ?? null) ?: '—' }}</td>
                </tr>
                @if (!empty($snapshot['jurusan']))
                    <tr>
                            <td class="label">Konsentrasi Keahlian</td>
                            <td class="colon">:</td>
                        <td>{{ $snapshot['jurusan'] }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="description">
            <div class="description-intro">Telah selesai mengikuti Praktik Kerja Lapangan (PKL) pada</div>
            <div class="description-result">
                @if (!empty($snapshot['industri']))<span class="industry">{{ $snapshot['industri'] }}</span> @endif
                dengan Predikat <strong>{{ $snapshot['predikat'] ?? 'SANGAT BAIK' }}</strong>
            </div>
        </div>

        <div class="signatures">
            <div class="signer signer-left">
                <div class="signer-role">KEPALA<br>SMK NEGERI 4 TANJUNGPINANG</div>
                <div class="signer-space"><div class="signature-line"></div></div>
                <div class="signer-name">Yayuk Sri Mulyani Rahayu, S.Pd., M.M.</div>
                NIP 19770421 200502 2 011
            </div>

            <div class="signer signer-right">
                <div class="signer-role">KEPALA<br>{{ $snapshot['industri'] ?? '' }}</div>
                <div class="signer-space"><div class="signature-line"></div></div>
            </div>
        </div>
        </div>

        <img class="motto" src="{{ $motto }}" alt="SMK Bisa, SMK Hebat">

        @if ($sertifikat->kode_cetak)
            <div class="verification-code">
                <div class="verification-code-label">Kode Verifikasi</div>
                <div class="verification-code-value">{{ $sertifikat->kode_cetak }}</div>
            </div>
        @endif

        <img class="qr" src="{{ $qr }}" alt="QR menuju portal verifikasi sertifikat">
    </div>
</body>
</html>

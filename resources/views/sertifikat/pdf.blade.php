<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat</title>

    <style>
        @page {
            margin: 22px;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #172554;
            font-size: 12px;
        }

        .certificate {
            border: 7px solid #172554;
            padding: 25px 35px;
            text-align: center;
        }

        .school {
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        h1 {
            margin: 14px 0 4px;
            font-size: 36px;
            letter-spacing: 5px;
        }

        .number {
            color: #475569;
            font-size: 11px;
        }

        .recipient {
            margin: 14px 0;
            font-size: 27px;
            font-weight: bold;
            word-wrap: break-word;
        }

        .activity {
            margin: 9px 0;
            font-size: 18px;
            font-weight: bold;
            word-wrap: break-word;
        }

        .muted {
            color: #475569;
        }

        table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }

        td {
            vertical-align: middle;
            width: 50%;
        }

        .qr {
            width: 92px;
            height: 92px;
        }

        .code {
            margin-top: 13px;
            font-size: 8px;
            color: #475569;
        }

        .small {
            font-size: 9px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="school">{{ $snapshot['sekolah'] }}</div>

        <h1>SERTIFIKAT</h1>

        <div class="number">Nomor: {{ $sertifikat->nomor }}</div>

        <p class="muted">Diberikan kepada</p>

        <div class="recipient">{{ $snapshot['nama_penerima'] }}</div>

        <p>
            Atas partisipasinya sebagai
            <strong>{{ $snapshot['peran'] }}</strong>
            dalam kegiatan
        </p>

        <div class="activity">{{ $snapshot['kegiatan'] }}</div>

        <p class="muted">
            {{ $snapshot['lokasi'] }}<br>
            {{ $snapshot['tanggal_mulai'] }}
            s.d.
            {{ $snapshot['tanggal_selesai'] }}
        </p>

        <table>
            <tr>
                <td>
                    <img class="qr" src="{{ $qr }}" alt="QR verifikasi">
                    <div class="small">Pindai untuk memeriksa status sertifikat.</div>
                </td>
                <td>
                    <p>Diterbitkan pada</p>
                    <p><strong>{{ $sertifikat->tanggal_terbit->format('d-m-Y') }}</strong></p>
                    <p>{{ $snapshot['sekolah'] }}</p>
                </td>
            </tr>
        </table>

        <div class="code">
            Kode verifikasi: {{ $sertifikat->kode_verifikasi }}
        </div>
    </div>
</body>
</html>
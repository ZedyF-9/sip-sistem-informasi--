<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Surat {{ ucfirst($jenis) }}</title>
    <style>
        @page { margin: 25px; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10px; color: #0f172a; }
        h2 { font-size: 19px; margin-bottom: 6px; }
        p { line-height: 1.6; }
        .muted { color: #64748b; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; vertical-align: top; word-wrap: break-word; }
        th { background: #eff6ff; text-align: left; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>

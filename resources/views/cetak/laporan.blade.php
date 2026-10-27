@extends($pdf ? 'layouts.laporan-pdf' : 'layouts.internal')

@section('title', 'Cetak Laporan / PDF')

@section('content')
    @if (! $pdf)
        <div class="dashboard-print-tools mb-6">
            <a href="{{ route('laporan.index', ['jenis' => $jenis, 'mulai' => $mulai, 'selesai' => $selesai]) }}" class="mb-6 inline-flex rounded-xl border border-slate-300 px-5 py-3">Kembali ke laporan</a>

            <form method="GET" action="{{ route('laporan.cetak') }}" class="grid gap-4 rounded-2xl bg-white p-6 md:grid-cols-3">
                <label class="grid gap-2">
                    Jenis
                    <select name="jenis">
                        <option value="masuk" @selected($jenis === 'masuk')>Surat Masuk</option>
                        <option value="keluar" @selected($jenis === 'keluar')>Surat Keluar</option>
                    </select>
                </label>

                <label class="grid gap-2">
                    Mulai
                    <input type="date" name="mulai" value="{{ $mulai }}" required>
                </label>

                <label class="grid gap-2">
                    Sampai
                    <input type="date" name="selesai" value="{{ $selesai }}" required>
                </label>

                <div class="flex flex-wrap gap-3 md:col-span-3">
                    <button name="format" value="html" class="rounded-xl bg-blue-700 px-5 py-3 text-white">Tampilkan</button>
                    <button name="format" value="pdf" class="rounded-xl border border-slate-300 px-5 py-3">Lihat PDF</button>
                    <button type="button" onclick="window.print()" class="rounded-xl border border-slate-300 px-5 py-3">Cetak</button>
                </div>
            </form>
        </div>
    @endif

    <section class="dashboard-print-report rounded-2xl bg-white p-6">
    <h2 class="text-xl font-bold">SMKN 4 Tanjungpinang</h2>
    <p class="my-4 text-sm">
        <strong>Laporan Surat {{ ucfirst($jenis) }}</strong><br>
        Periode {{ $mulai }} sampai {{ $selesai }}<br>
        Jumlah: {{ $rows->count() }} surat
    </p>

    <div class="overflow-x-auto" @if (! $pdf) tabindex="0" role="region" aria-label="Pratinjau laporan, geser untuk melihat seluruh kolom" @endif>
    <table class="w-full text-left text-sm">
        <thead>
            <tr>
                <th style="width: 5%">No.</th>
                <th style="width: 22%">Nomor</th>
                <th style="width: 30%">Perihal</th>
                <th style="width: 20%">{{ $jenis === 'masuk' ? 'Asal' : 'Tujuan' }}</th>
                <th style="width: 10%">Tanggal</th>
                <th style="width: 13%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->nomor ?: 'Belum bernomor' }}</td>
                    <td>{{ $row->perihal }}</td>
                    <td>{{ $row->pihak }}</td>
                    <td>{{ $row->tanggal }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $row->status)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada surat pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <p class="muted mt-4 text-sm text-slate-500">Dicetak pada {{ $dicetak }} WIB.</p>
    </section>
@endsection

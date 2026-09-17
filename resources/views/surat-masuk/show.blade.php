@extends('layouts.tu')

@section('title', 'Detail Surat Masuk')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('tu.surat-masuk.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">
            Kembali
        </a>

        @if ($surat->file_surat)
            <a href="{{ route('tu.surat-masuk.download', $surat) }}"
               class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
                Unduh PDF
            </a>
        @endif
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="mb-6 border-b border-slate-100 pb-6">
            <p class="text-sm font-semibold text-blue-700">
                {{ $surat->nomor_registrasi }}
            </p>

            <h2 class="mt-3 text-2xl font-bold">
                {{ $surat->perihal }}
            </h2>

            <span class="mt-4 inline-block rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-800">
                {{ $surat->statusLabel() }}
            </span>
        </div>

        <dl class="grid gap-6 md:grid-cols-2">
            @foreach ([
                'Nomor surat' => $surat->nomor_surat,
                'Jenis surat' => $surat->jenis_surat,
                'Sifat surat' => ucfirst($surat->sifat_surat),
                'Asal surat' => $surat->asal_surat,
                'Nama pengirim' => $surat->nama_pengirim,
                'Email pengirim' => $surat->email_pengirim,
                'Metode pengiriman' => ucfirst($surat->metode_pengiriman),
                'Nomor agenda' => $surat->nomor_agenda,
                'Lokasi arsip fisik' => $surat->lokasi_arsip_fisik,
                'Tanggal surat' => $surat->tanggal_surat->format('d/m/Y'),
                'Tanggal diterima' => $surat->tanggal_diterima->format('d/m/Y'),
                'Dicatat oleh' => $surat->pencatat?->name,
            ] as $label => $value)
                <div>
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="mt-2 break-words font-medium">
                        {{ $value ?: '—' }}
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>
@endsection
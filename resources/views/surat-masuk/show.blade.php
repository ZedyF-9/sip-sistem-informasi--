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
                Lihat PDF
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
                'Nomor urut / agenda' => $surat->nomor_agenda,
                'Nomor surat' => $surat->nomor_surat,
                'Tanggal surat' => $surat->tanggal_surat->format('d/m/Y'),
                'Tanggal diterima' => $surat->tanggal_diterima->format('d/m/Y'),
                'Alamat asal (pengirim)' => $surat->asal_surat,
                'Perihal' => $surat->perihal,
                'Disposisi kepada' => $surat->disposisis->pluck('penerima.name')->filter()->unique()->implode(', ') ?: 'Belum didisposisikan',
                'Keterangan' => $surat->keterangan,
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

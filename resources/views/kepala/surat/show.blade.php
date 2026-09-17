@extends('layouts.pemeriksaan')

@section('title', 'Detail Surat untuk Kepala Sekolah')

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('kepala.surat.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">
            Kembali
        </a>

        @if ($surat->file_surat)
            <a href="{{ route('kepala.surat.download', $surat) }}"
               class="rounded-xl bg-blue-700 px-5 py-3 text-white">
                Unduh PDF
            </a>
        @endif

        @if (in_array($surat->status, ['diverifikasi', 'disposisi'], true))
            <a href="{{ route('disposisi.create', ['surat_masuk_id' => $surat->id]) }}"
               class="rounded-xl bg-blue-700 px-5 py-3 text-white">
                Buat Disposisi
            </a>
        @endif

        <a href="{{ route('disposisi.index') }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-3">
            Pantau Disposisi
        </a>
    </div>

    @include('partials.detail-pemeriksaan')

    @if ($surat->status === 'selesai')
        <p class="mt-6 rounded-xl bg-emerald-100 p-4 text-emerald-900">
            Seluruh disposisi untuk surat ini telah selesai.
        </p>
    @endif
@endsection

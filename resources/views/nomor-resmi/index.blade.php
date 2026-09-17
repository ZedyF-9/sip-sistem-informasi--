@extends('layouts.internal')

@section('title', 'Nomor Resmi Surat Keluar')

@section('content')
    <p class="mb-6 rounded-xl bg-blue-50 p-4 text-sm leading-6 text-blue-900">
        Masukkan nomor dari administrasi sekolah untuk surat yang sudah disetujui.
        Pastikan nomor sesuai dengan dokumen yang akan dikirim.
        Pencatatan ini tidak mengubah isi PDF.
    </p>

    <div class="space-y-4">
        @forelse ($surats as $surat)
            <article class="rounded-2xl bg-white p-6">
                <h2 class="font-bold">{{ $surat->perihal }}</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Tujuan: {{ $surat->tujuan }}
                </p>

                <form
                    action="{{ route('tu.nomor-resmi.store', $surat) }}"
                    method="POST"
                    class="mt-4 flex flex-wrap gap-3"
                >
                    @csrf

                    <input type="hidden" name="version" value="{{ $surat->version }}">

                    <input
                        name="nomor_surat"
                        aria-label="Nomor resmi surat"
                        placeholder="Masukkan nomor resmi"
                        maxlength="100"
                        required
                        class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3"
                    >

                    <button class="rounded-xl bg-blue-700 px-5 py-3 text-white">
                        Simpan Nomor
                    </button>
                </form>
            </article>
        @empty
            <p class="rounded-2xl bg-white p-6 text-slate-500">
                Tidak ada surat disetujui yang belum bernomor.
            </p>
        @endforelse
    </div>

    <div class="mt-6">{{ $surats->links() }}</div>
@endsection
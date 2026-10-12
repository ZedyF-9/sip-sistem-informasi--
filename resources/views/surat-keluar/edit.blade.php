@extends('layouts.internal')

@section('title', 'Edit Surat Keluar')

@section('content')
    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
        <p class="font-semibold">Perbaiki surat sesuai catatan pemeriksaan, lalu ajukan ulang ke Kepala TU dari halaman detail.</p>
    </div>
    <form method="POST" action="{{ route('surat-keluar.update', $surat) }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        @method('PUT')
        @include('surat-keluar.form')
    </form>
@endsection

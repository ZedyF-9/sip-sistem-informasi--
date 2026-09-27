@extends('layouts.internal')

@section('title', 'Tambah Mitra Industri')

@section('content')
    <p class="mb-5 rounded-xl bg-blue-50 p-4 text-sm text-blue-800">Mitra yang disimpan akan masuk antrean persetujuan Waka Humas sebelum tampil di portal publik.</p>
    <form method="POST" action="{{ route('tu.mitra.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6">
        @include('mitra.form')
    </form>
@endsection

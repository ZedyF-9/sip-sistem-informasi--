@extends('layouts.internal')

@section('title', 'Buat Draf Surat Keluar')

@section('content')
    <form method="POST" action="{{ route('surat-keluar.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6">
        @include('surat-keluar.form')
    </form>
@endsection
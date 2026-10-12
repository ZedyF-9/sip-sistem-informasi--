@extends('layouts.internal')

@section('title', 'Buat Surat Keluar')

@section('content')
    <form method="POST" action="{{ route('surat-keluar.store') }}"
          class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        @include('surat-keluar.form')
    </form>
@endsection

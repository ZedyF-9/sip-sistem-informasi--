@extends('layouts.internal')

@section('title', 'Edit Draf Surat Keluar')

@section('content')
    <form method="POST" action="{{ route('surat-keluar.update', $surat) }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6">
        @include('surat-keluar.form')
    </form>
@endsection

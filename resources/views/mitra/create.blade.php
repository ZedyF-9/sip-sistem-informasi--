@extends('layouts.internal')

@section('title', 'Tambah Mitra Industri')

@section('content')
    <form method="POST" action="{{ route('tu.mitra.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6">
        @include('mitra.form')
    </form>
@endsection
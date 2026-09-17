@extends('layouts.internal')

@section('title', 'Dashboard '.auth()->user()->name)

@section('content')
    @php
        $account = auth()->user();
    @endphp

    <p class="mb-6 text-sm text-slate-600">Selamat datang, <strong>{{ $account->name }}</strong>. {{ $account->roleLabel() }}</p>

    @if ($account->role === 'koordinator_tu')
        @include('partials.dashboard-koordinator-ringkasan')
    @elseif ($account->role === 'kepala_sekolah')
        @include('partials.dashboard-kepala-ringkasan')
    @else
        @include('partials.dashboard-penerima-ringkasan')
    @endif
@endsection

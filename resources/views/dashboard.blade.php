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
    @elseif ($account->role === 'waka_humas')
        <section class="dashboard-data-panel max-w-3xl" aria-label="Persetujuan kerja sama">
            <p class="text-sm text-slate-600">Pengajuan mitra dan kerja sama menunggu persetujuan</p>
            <p class="mt-2 text-4xl font-bold">{{ $pengajuanMenunggu }}</p>
            <a href="{{ route('waka-humas.kerjasama.index') }}"
               class="mt-5 inline-flex rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white">
                Buka Persetujuan Mitra dan Kerja Sama
            </a>
        </section>
    @else
        @include('partials.dashboard-penerima-ringkasan')
    @endif
@endsection

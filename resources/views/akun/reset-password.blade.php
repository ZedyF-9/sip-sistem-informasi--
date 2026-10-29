@extends('layouts.internal')

@section('title', 'Reset Password Akun')

@section('content')
    <div class="mb-6 max-w-2xl rounded-2xl border border-blue-200 bg-blue-50 p-5">
        <p class="text-sm text-blue-700">Akun yang akan direset</p>
        <p class="mt-2 font-bold text-blue-950">{{ $akun->name }}</p>
        <p class="mt-1 text-sm text-blue-800">{{ $akun->email }}</p>
    </div>

    <form
        action="{{ route('tu.akun.password.update', $akun) }}"
        method="POST"
        class="max-w-2xl space-y-5 rounded-2xl border border-slate-200 bg-white p-6"
    >
        @csrf
        @method('PUT')

        <div>
            <label for="current_password" class="mb-2 block text-sm font-semibold">
                Password TU yang sedang login
            </label>

            <x-password-input
                id="current_password"
                name="current_password"
                autocomplete="current-password"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            />

            <p class="mt-2 text-sm text-slate-500">
                Masukkan password akun {{ auth()->user()->email }}
                untuk mengonfirmasi tindakan.
            </p>
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold">
                Password Baru untuk {{ $akun->name }}
            </label>

            <x-password-input
                id="password"
                name="password"
                autocomplete="new-password"
                minlength="12"
                maxlength="72"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            />

            <x-password-strength for="password" />
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold">
                Ulangi Password Baru
            </label>

            <x-password-input
                id="password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                minlength="12"
                maxlength="72"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            />
        </div>

        <p class="text-sm leading-6 text-slate-500">
            Password lama akun tujuan tidak diperlukan.
            Setelah berhasil, pengguna perlu login dengan password baru.
        </p>

        <div class="flex flex-wrap gap-3 pt-3">
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">
                Ganti Password Akun
            </button>

            <a
                href="{{ route('tu.akun.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 font-semibold"
            >
                Batal
            </a>
        </div>
    </form>
@endsection

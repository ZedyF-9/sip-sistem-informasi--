@extends('layouts.internal')

@section('title', 'Ganti Password Saya')

@section('content')
    <form
        action="{{ route('profil.password.update') }}"
        method="POST"
        class="max-w-2xl space-y-5 rounded-2xl border border-slate-200 bg-white p-6"
    >
        @csrf
        @method('PUT')

        <div class="rounded-xl bg-slate-50 p-4">
            <p class="font-semibold">{{ auth()->user()->name }}</p>
            <p class="mt-1 text-sm text-slate-500">
                {{ auth()->user()->email }}
            </p>
        </div>

        <div>
            <label for="current_password" class="mb-2 block text-sm font-semibold">
                Password Lama
            </label>

            <input
                id="current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold">
                Password Baru
            </label>

            <input
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
                minlength="12"
                maxlength="72"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >

            <p class="mt-2 text-sm text-slate-500">
                Minimal 12 karakter dan berbeda dari password lama.
            </p>
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold">
                Ulangi Password Baru
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                minlength="12"
                maxlength="72"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
        </div>

        <p class="text-sm text-slate-500">
            Setelah berhasil, kamu akan diarahkan ke halaman login.
        </p>

        <div class="flex flex-wrap gap-3 pt-3">
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">
                Simpan Password Baru
            </button>

            <a
                href="{{ url('/dashboard') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 font-semibold"
            >
                Batal
            </a>
        </div>
    </form>
@endsection
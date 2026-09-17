@extends('layouts.internal')

@section('title', 'Tambah Akun')

@section('content')
    <form
        action="{{ route('tu.akun.store') }}"
        method="POST"
        class="max-w-2xl space-y-5 rounded-2xl border border-slate-200 bg-white p-6"
    >
        @csrf

        <div>
            <label for="name" class="mb-2 block text-sm font-semibold">
                Nama / Jabatan
            </label>

            <input
                id="name"
                name="name"
                value="{{ old('name') }}"
                maxlength="255"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold">
                Email Login
            </label>

            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="off"
                maxlength="255"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
        </div>

        <div>
            <label for="role" class="mb-2 block text-sm font-semibold">
                Role
            </label>

            <select
                id="role"
                name="role"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
                @foreach ($roles as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('role', 'penerima_disposisi') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <p class="mt-2 text-sm text-slate-500">
                Waka, Pembina OSIS, dan Bendahara menggunakan Penerima Disposisi.
            </p>
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold">
                Password Awal
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
                Minimal 12 karakter. Akun baru langsung aktif.
            </p>
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold">
                Ulangi Password Awal
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

        <div class="flex flex-wrap gap-3 pt-3">
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">
                Buat Akun
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
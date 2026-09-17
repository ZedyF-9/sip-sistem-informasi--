@extends('layouts.internal')

@section('title', 'Edit Akun')

@section('content')
    <form
        action="{{ route('tu.akun.update', $akun) }}"
        method="POST"
        class="max-w-2xl space-y-5 rounded-2xl border border-slate-200 bg-white p-6"
    >
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="mb-2 block text-sm font-semibold">
                Nama / Jabatan
            </label>

            <input
                id="name"
                name="name"
                value="{{ old('name', $akun->name) }}"
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
                value="{{ old('email', $akun->email) }}"
                maxlength="255"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >
        </div>

        <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-sm text-slate-500">Role</p>
            <p class="mt-1 font-semibold">
                {{ $roles[$akun->role] ?? $akun->role }}
            </p>
            <p class="mt-2 text-sm text-slate-500">
                Role akun yang sudah ada tidak diubah melalui formulir ini.
            </p>
        </div>

        <div>
            <label for="is_active" class="mb-2 block text-sm font-semibold">
                Status Akun
            </label>

            @if ($akun->role === 'koordinator_tu')
                <input type="hidden" name="is_active" value="1">

                <p class="rounded-xl bg-blue-50 p-4 text-sm text-blue-800">
                    Akun Koordinator TU dipertahankan aktif.
                </p>
            @else
                <select
                    id="is_active"
                    name="is_active"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >
                    <option
                        value="1"
                        @selected((string) old('is_active', (int) $akun->is_active) === '1')
                    >
                        Aktif
                    </option>

                    <option
                        value="0"
                        @selected((string) old('is_active', (int) $akun->is_active) === '0')
                    >
                        Nonaktif
                    </option>
                </select>
            @endif
        </div>

        <p class="text-sm leading-6 text-slate-500">
            Setelah perubahan disimpan, pengguna perlu login kembali.
            Akun nonaktif tidak bisa login. Riwayat surat dan disposisinya tetap tersimpan.
        </p>

        <div class="flex flex-wrap gap-3 pt-3">
            <button class="rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">
                Simpan Perubahan
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
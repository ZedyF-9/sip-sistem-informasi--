@extends('layouts.internal')

@section('title', 'Kelola Akun')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-slate-600">
            Kelola akses pengguna internal sekolah.
        </p>

        <a
            href="{{ route('tu.akun.create') }}"
            class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
        >
            Tambah Akun
        </a>
    </div>

    <form
        action="{{ route('tu.akun.index') }}"
        method="GET"
        class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4"
    >
        <input
            type="search"
            name="q"
            value="{{ $q }}"
            maxlength="100"
            aria-label="Cari nama atau email"
            placeholder="Cari nama atau email..."
            class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"
        >

        <select
            name="role"
            aria-label="Filter role"
            class="rounded-xl border border-slate-300 px-4 py-3"
        >
            <option value="">Semua role</option>

            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected($role === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button class="rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white">
            Cari
        </button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">Email</th>
                    <th class="px-5 py-4">Role</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Tindakan</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($akuns as $akun)
                    <tr>
                        <td class="px-5 py-4 font-semibold">
                            {{ $akun->name }}
                        </td>

                        <td class="px-5 py-4">{{ $akun->email }}</td>

                        <td class="px-5 py-4">
                            {{ $roles[$akun->role] ?? $akun->role }}
                        </td>

                        <td class="px-5 py-4">
                            <span @class([
                                'rounded-full px-3 py-1 text-xs font-semibold',
                                'bg-emerald-50 text-emerald-700' => $akun->is_active,
                                'bg-slate-100 text-slate-600' => ! $akun->is_active,
                            ])>
                                {{ $akun->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            @if ((int) $akun->id === (int) auth()->id())
                                <a
                                    href="{{ route('profil.password.edit') }}"
                                    class="font-semibold text-blue-700 hover:underline"
                                >
                                    Password Saya
                                </a>
                            @else
                                <div class="flex flex-wrap gap-4">
                                    <a
                                        href="{{ route('tu.akun.edit', $akun) }}"
                                        class="font-semibold text-blue-700 hover:underline"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="{{ route('tu.akun.password.edit', $akun) }}"
                                        class="font-semibold text-amber-700 hover:underline"
                                    >
                                        Reset Password
                                    </a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                            Akun tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $akuns->links() }}
    </div>
@endsection
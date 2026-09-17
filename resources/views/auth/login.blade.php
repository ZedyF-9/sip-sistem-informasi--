<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login — {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="flex flex-col justify-between bg-blue-950 p-8 text-white lg:p-16">
            <a href="{{ route('beranda') }}" class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white font-bold text-blue-950">
                    SIP
                </span>

                <span class="font-semibold">
                    SMKN 4 Tanjungpinang
                </span>
            </a>

            <div class="max-w-lg py-12 lg:py-20">
                <p class="text-sm font-semibold uppercase tracking-widest text-blue-200">
                    Sistem Informasi Persuratan
                </p>

                <h1 class="mt-5 text-4xl font-bold leading-tight lg:text-5xl">
                    Administrasi surat yang tertata.
                </h1>

                <p class="mt-6 leading-8 text-blue-100">
                    Kelola surat masuk, surat keluar, disposisi,
                    dan layanan pendukung sekolah melalui
                    akses sesuai tanggung jawab Anda.
                </p>
            </div>

            <p class="text-sm text-blue-200">
                Portal internal SMKN 4 Tanjungpinang
            </p>
        </section>

        <section class="flex items-center justify-center px-6 py-12 lg:p-16">
            <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold text-blue-700">
                    AKSES INTERNAL
                </p>

                <h2 class="mt-3 text-3xl font-bold text-slate-900">
                    Selamat datang
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Masuk menggunakan akun yang diberikan oleh pengelola.
                </p>

                @if (session('status'))
                    <div
                        role="status"
                        class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800"
                    >
                        {{ session('status') }}
                    </div>
                @endif

                <form
                    action="{{ route('login.store') }}"
                    method="POST"
                    class="mt-8 space-y-5"
                >
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold">
                            Email
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            maxlength="255"
                            required
                            autofocus
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                        >

                        @error('email')
                            <p role="alert" class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold">
                            Password
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            maxlength="72"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                        >

                        @error('password')
                            <p role="alert" class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white transition hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700"
                    >
                        Masuk
                    </button>
                </form>

                <p class="mt-6 text-center text-sm leading-6 text-slate-500">
                    Jika mengalami kendala akun, hubungi Koordinator TU.
                </p>

                <div class="mt-6 border-t border-slate-100 pt-6 text-center">
                    <a
                        href="{{ route('beranda') }}"
                        class="text-sm font-semibold text-blue-700 hover:underline"
                    >
                        Kembali ke portal publik
                    </a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
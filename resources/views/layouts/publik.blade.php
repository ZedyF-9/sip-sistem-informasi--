<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Portal Publik') — SIP SMKN 4</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-5 px-6 py-5">
            <a href="{{ route('beranda') }}" class="font-bold text-blue-950">
                SIP · SMKN 4 Tanjungpinang
            </a>

            <nav aria-label="Navigasi publik" class="flex flex-wrap items-center gap-5 text-sm font-semibold">
                @foreach ([
                    ['Beranda', 'beranda', 'beranda'],
                    ['Kirim Surat', 'publik.kirim', 'publik.kirim|publik.store|publik.berhasil'],
                    ['Lacak Surat', 'publik.lacak', 'publik.lacak*'],
                    ['Mitra', 'publik.mitra.index', 'publik.mitra.*'],
                    ['Verifikasi Sertifikat', 'publik.sertifikat.form', 'publik.sertifikat.*'],
                ] as [$label, $routeName, $pattern])
                    @php($active = request()->routeIs(...explode('|', $pattern)))
                    <a href="{{ route($routeName) }}"
                       @if ($active) aria-current="page" @endif
                       @class(['rounded px-1 py-2 transition hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-600', 'text-blue-700 underline underline-offset-8' => $active])>
                        {{ $label }}
                    </a>
                @endforeach

                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-white"
                    >
                        Dashboard
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-xl border border-slate-300 px-4 py-2 hover:bg-slate-50">Keluar</button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-white"
                    >
                        Login Internal
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-6 py-8">
        @yield('content')
    </main>

    <footer class="mt-10 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-6 text-sm text-slate-500">
            Sistem Informasi Persuratan · SMKN 4 Tanjungpinang
        </div>
    </footer>
</body>
</html>

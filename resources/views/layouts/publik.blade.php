<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Portal Publik') — SIP SMKN 4</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-shell public-shell antialiased">
    <a href="#public-content" class="dashboard-skip">Lewati ke konten</a>
    <header class="public-header">
        <div class="public-header-inner">
            <a href="{{ route('beranda') }}" class="dashboard-brand public-brand">
                <img src="{{ asset('reference/image.jpg') }}" alt="Logo SMKN 4 Tanjungpinang"
                     width="60" height="60" class="school-logo" fetchpriority="high">
                <span><strong>SIP Sekolah</strong><small>SMKN 4 Tanjungpinang</small></span>
            </a>

            <nav aria-label="Navigasi publik" class="public-nav">
                @foreach ([
                    ['Beranda', 'beranda', 'beranda'],
                    ['Mitra', 'publik.mitra.index', 'publik.mitra.*'],
                    ['Verifikasi Sertifikat', 'publik.sertifikat.form', 'publik.sertifikat.*'],
                ] as [$label, $routeName, $pattern])
                    @php($active = request()->routeIs(...explode('|', $pattern)))
                    <a href="{{ route($routeName) }}"
                       @if ($active) aria-current="page" @endif
                       class="public-nav-link">
                        {{ $label }}
                    </a>
                @endforeach

                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="public-account-link"
                    >
                        Dashboard
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dashboard-logout">Keluar</button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="public-account-link"
                    >
                        Login Internal
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="public-content" class="dashboard-content public-content" tabindex="-1">
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="public-footer-brand">
            <img src="{{ asset('reference/image.jpg') }}" alt="" width="48" height="48" loading="lazy">
            <span><strong>SMKN 4 Tanjungpinang</strong><small>Sistem Informasi Persuratan</small></span>
        </div>
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') — SIP Sekolah</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-shell antialiased">
    @php
        $user = auth()->user();

        $navigation = match ($user->role) {
            'koordinator_tu' => [
                ['Dashboard', 'tu.dashboard', []],
                ['Surat Masuk', 'tu.surat-masuk.index', []],
                ['Antrean Pemeriksaan', 'tu.pemeriksaan.index', []],
                ['Pemantauan Disposisi', 'disposisi.index', []],
                ['Surat Keluar', 'surat-keluar.index', []],
                ['Nomor Resmi', 'tu.nomor-resmi.index', []],
                ['Arsip Surat Keluar', 'surat-keluar.index', ['status' => 'dikirim']],
                ['Cetak Laporan / PDF', 'laporan.cetak', []],
                ['Mitra dan Kerja Sama', 'tu.mitra.index', []],
                ['Kegiatan dan Sertifikat', 'tu.kegiatan.index', []],
                ['Kelola Akun', 'tu.akun.index', []],
            ],
            'kepala_sekolah' => [
                ['Dashboard', 'kepala.dashboard', []],
                ['Surat dari TU', 'kepala.surat.index', []],
                ['Pemantauan Disposisi', 'disposisi.index', []],
                ['Persetujuan Surat Keluar', 'surat-keluar.index', ['status' => 'diajukan']],
                ['Surat Keluar', 'surat-keluar.index', []],
                ['Cetak Laporan / PDF', 'laporan.cetak', []],
            ],
            'waka_humas' => [
                ['Dashboard', 'waka-humas.dashboard', []],
                ['Persetujuan Mitra dan Kerja Sama', 'waka-humas.kerjasama.index', []],
                ['Disposisi Saya', 'disposisi.index', []],
                ['Sedang Diproses', 'disposisi.index', ['status' => 'diproses']],
                ['Disposisi Selesai', 'disposisi.index', ['status' => 'selesai']],
            ],
            default => [
                ['Dashboard', 'penerima.dashboard', []],
                ['Disposisi Saya', 'disposisi.index', []],
                ['Sedang Diproses', 'disposisi.index', ['status' => 'diproses']],
                ['Disposisi Selesai', 'disposisi.index', ['status' => 'selesai']],
            ],
        };

        $currentRoute = request()->route()->getName();
        $hasExactMenu = collect($navigation)->contains(fn ($item) => $item[1] === $currentRoute);
    @endphp

    <aside id="dashboard-sidebar" class="dashboard-sidebar" aria-label="Navigasi internal">
        @include('partials.dashboard-brand')
        <nav class="dashboard-nav" aria-label="Menu internal">
            <p class="dashboard-nav-label">Menu utama</p>
            @foreach ($navigation as [$label, $routeName, $parameters])
                @php
                    $module = str_ends_with($routeName, '.index') ? substr($routeName, 0, -6) : null;
                    $matchesPage = request()->routeIs($routeName)
                        || (! $hasExactMenu && $module && request()->routeIs($module.'.*'))
                        || ($routeName === 'tu.mitra.index' && request()->routeIs('tu.kerjasama.*'))
                        || ($routeName === 'tu.surat-masuk.index' && request()->routeIs('tu.perbaikan.*'));
                    $statusMenus = collect($navigation)->where(1, $routeName)->pluck('2.status')->filter()->all();
                    $active = $matchesPage && (isset($parameters['status'])
                        ? request('status') === $parameters['status']
                        : ! in_array(request('status'), $statusMenus, true));
                    $icon = match (true) {
                        str_ends_with($routeName, '.dashboard') => 'grid',
                        str_contains($routeName, 'surat-masuk'), str_contains($routeName, 'kepala.surat') => 'inbox',
                        str_contains($routeName, 'surat-keluar') => 'send',
                        str_contains($routeName, 'laporan') => 'chart',
                        str_contains($routeName, 'akun') => 'user',
                        str_contains($routeName, 'mitra') => 'globe',
                        default => 'document',
                    };
                @endphp

                <a
                    href="{{ route($routeName, $parameters) }}"
                    class="dashboard-nav-link"
                    @if ($active) aria-current="page" @endif
                >
                    <x-dashboard-icon :name="$icon" /><span>{{ $label }}</span>
                </a>
            @endforeach

            <a
                href="{{ route('profil.password.edit') }}"
                class="dashboard-nav-link"
                @if (request()->routeIs('profil.password.*')) aria-current="page" @endif
            >
                <x-dashboard-icon name="user" /><span>Ganti Password</span>
            </a>

            <div class="mt-4 border-t border-slate-200 pt-4">
                <a
                    href="{{ route('beranda') }}"
                    class="dashboard-nav-link"
                >
                    <x-dashboard-icon name="globe" /><span>Portal Publik</span>
                </a>
            </div>
        </nav>
        <div class="dashboard-sidebar-account">
            <span class="dashboard-avatar"><x-dashboard-icon name="user" /></span>
            <div><p>{{ $user->name }}</p><p>{{ $user->roleLabel() }}</p></div>
        </div>
    </aside>
    <div class="dashboard-backdrop" data-navigation-backdrop hidden></div>
    <div class="dashboard-workspace">
        @include('partials.dashboard-topbar')
        <main id="dashboard-content" class="dashboard-content" tabindex="-1">

            @if (session('status'))
                <div role="status" class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Periksa kembali:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>

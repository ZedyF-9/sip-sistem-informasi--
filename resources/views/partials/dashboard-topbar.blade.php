<a class="dashboard-skip" href="#dashboard-content">Lewati ke konten</a>
<header class="dashboard-topbar">
    <button type="button" class="dashboard-nav-toggle" data-navigation-toggle aria-controls="dashboard-sidebar" aria-expanded="false" aria-label="Buka navigasi">
        <x-dashboard-icon name="menu" />
    </button>
    <div class="dashboard-page-heading">
        <p>Sistem Informasi Persuratan</p>
        <h1>@yield('title')</h1>
    </div>
    <div class="dashboard-topbar-account">
        <span class="dashboard-avatar"><x-dashboard-icon name="user" /></span>
        <span>{{ auth()->user()->name }}</span>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="dashboard-logout"><x-dashboard-icon name="logout" /><span>Keluar</span></button>
    </form>
</header>

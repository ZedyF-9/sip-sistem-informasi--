<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Internal | SMKN 4 Tanjungpinang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page antialiased">
    <header class="login-header">
        <a href="{{ route('beranda') }}" class="login-wordmark">SIP Sekolah</a>
        <a href="{{ route('beranda') }}" class="login-return">Kembali ke portal publik <span aria-hidden="true">&nearr;</span></a>
    </header>

    <main class="login-main">
        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-identity">
                <img src="{{ asset('reference/image.jpg') }}"
                     alt="Logo SMKN 4 Tanjungpinang"
                     width="96" height="96" class="login-logo" fetchpriority="high">
                <p class="login-school">SMKN 4 Tanjungpinang</p>
                <h1 id="login-title">Login internal</h1>
                <p class="login-description">Masuk dengan akun yang diberikan oleh pengelola.</p>
            </div>

            @if (session('status'))
                <div role="status" class="login-message">{{ session('status') }}</div>
            @endif

            <form action="{{ route('login.store') }}" method="POST" class="login-form">
                @csrf
                <div class="login-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}" autocomplete="username"
                           maxlength="255" required autofocus
                           @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                    @error('email')
                        <p id="email-error" role="alert" class="login-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <x-password-input id="password" name="password"
                                      autocomplete="current-password" maxlength="72" required
                                      :invalid="$errors->has('password')" />
                    @error('password')
                        <p id="password-error" role="alert" class="login-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="login-submit">Masuk</button>
            </form>

            <p class="login-help">Jika mengalami kendala akun,<br>hubungi Koordinator TU.</p>
        </section>
    </main>

    <footer class="login-footer">Sistem Informasi Persuratan &middot; SMKN 4 Tanjungpinang</footer>
</body>
</html>

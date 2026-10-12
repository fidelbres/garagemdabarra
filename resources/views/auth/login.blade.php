<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Entrar · Garagem da Barra</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('theme') || 'light'</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
<button class="theme-toggle login-theme-toggle" type="button" data-theme-toggle aria-label="Alternar tema"><span class="theme-icon">◐</span><span class="theme-label">Tema</span></button>
<main class="login-shell">
    <section class="login-brand">
        <span class="login-original" style="background-image: url('{{ asset('images/garagem-da-barra.png') }}')" role="img" aria-label="Garagem da Barra — Carros, histórias e liberdade"></span>
        <p>CARROS · HISTÓRIAS · LIBERDADE</p>
    </section>
    <section class="login-card">
        <span class="eyebrow">ACESSO À GARAGEM</span>
        <h2>Bem-vindo de volta</h2>
        <p>Entre para organizar seus carros, histórias e serviços.</p>
        @if($errors->any())
            <div class="flash error">{{ $errors->first() }}</div>
        @endif
        <a class="google-login" href="{{ route('auth.google.redirect') }}">
            <svg aria-hidden="true" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.41Z"/>
                <path fill="#34A853" d="M12 22c2.7 0 4.98-.9 6.63-2.36l-3.24-2.54c-.9.6-2.05.96-3.39.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z"/>
                <path fill="#FBBC05" d="M6.39 13.93A6 6 0 0 1 6.08 12c0-.67.11-1.32.31-1.93V7.45H3.04A10 10 0 0 0 2 12c0 1.61.38 3.14 1.04 4.55l3.35-2.62Z"/>
                <path fill="#EA4335" d="M12 5.94c1.47 0 2.79.5 3.83 1.5l2.87-2.87A9.63 9.63 0 0 0 12 2a10 10 0 0 0-8.96 5.45l3.35 2.62C7.18 7.7 9.39 5.94 12 5.94Z"/>
            </svg>
            Entrar com Google
        </a>
        <div class="login-divider"><span>ou</span></div>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label>E-mail<input autofocus required type="email" name="email" value="{{ old('email') }}"></label>
            <label>Senha<input required type="password" name="password"></label>
            <label class="remember"><input type="checkbox" name="remember" value="1"> Manter conectado</label>
            <button class="btn primary full">Entrar no painel</button>
        </form>
        <small>Sua coleção e o histórico dos seus carros em um só lugar.</small>
    </section>
</main>
</body>
</html>

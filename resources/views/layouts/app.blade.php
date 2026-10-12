<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel') · Garagem da Barra</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('theme') || 'light'</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="brand brand-logo"><span class="brand-original" style="background-image: url('{{ asset('images/garagem-da-barra.png') }}')" role="img" aria-label="Garagem da Barra — Carros, histórias e liberdade"></span></a>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span>⌂</span> Visão geral</a>
            <p>MINHA GARAGEM</p>
            <a href="{{ route('proprietarios.index') }}" class="{{ request()->routeIs('proprietarios.*') ? 'active' : '' }}"><span>♙</span> Colecionadores</a>
            <a href="{{ route('veiculos.index') }}" class="{{ request()->routeIs('veiculos.*') ? 'active' : '' }}"><span>◇</span> Veículos</a>
            <a href="{{ route('manutencoes.index') }}" class="{{ request()->routeIs('manutencoes.*') ? 'active' : '' }}"><span>⚒</span> Serviços realizados</a>
            <a href="{{ route('pecas.index') }}" class="{{ request()->routeIs('pecas.*') ? 'active' : '' }}"><span>◈</span> Peças compradas</a>
            <a href="{{ route('prestadores.index') }}" class="{{ request()->routeIs('prestadores.*') ? 'active' : '' }}"><span>⌖</span> Prestadores e empresas</a>
            <a href="{{ route('financeiro.index') }}" class="{{ request()->routeIs('financeiro.*') ? 'active' : '' }}"><span>R$</span> Gastos da coleção</a>
            <a href="{{ route('tipos-manutencao.index') }}" class="{{ request()->routeIs('tipos-manutencao.*') ? 'active' : '' }}"><span>≡</span> Catálogo de serviços</a>
            <p>EM BREVE</p>
            <a class="disabled"><span>▣</span> Agenda e visitas</a>
            <a class="disabled"><span>✉</span> Alertas e WhatsApp</a>
        </nav>
        <div class="sidebar-foot"><span class="status-dot"></span><div><strong>Sistema online</strong><small>Ambiente local</small></div></div>
    </aside>
    <main class="main">
        <header class="topbar">
            <button class="menu-button" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <div><span class="eyebrow">GARAGEM DA BARRA · VILA VELHA — ES</span></div>
            <div class="topbar-actions">
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Alternar tema"><span class="theme-icon">◐</span><span class="theme-label">Tema</span></button>
                <div class="user-chip"><span>{{ mb_strtoupper(mb_substr(auth()->user()->name,0,2)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->tipo) }}</small></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" title="Sair">↪</button></form></div>
            </div>
        </header>
        <div class="content">
            @if(session('sucesso'))<div class="flash success">✓ {{ session('sucesso') }}</div>@endif
            @if($errors->any())<div class="flash error"><strong>Revise os dados:</strong><ul>@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
    </main>
</body>
</html>

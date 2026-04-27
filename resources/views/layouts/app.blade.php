<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @livewireStyles
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-brand">
                {{ config('app.name') }}
            </div>
            <nav class="sidebar-menu">
                <div class="menu-item">
                    <a href="{{ route('users.index') }}"
                        class="menu-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                </div>
                <div class="menu-item">
                    <a href="{{ route('colaboradores.index') }}"
                        class="menu-link {{ request()->routeIs('colaboradores.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i> Colaboradores
                    </a>
                </div>
                <div class="menu-item">
                    <a href="{{ route('pontos.index') }}"
                        class="menu-link {{ request()->routeIs('pontos.*') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i> Registro de Ponto
                    </a>
                </div>
                <div class="menu-item">
                    <a href="{{ route('relatorios.index') }}"
                        class="menu-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i> Relatórios
                    </a>
                </div>
            </nav>
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="menu-link text-left">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </aside>

        <main class="main-content">
            <header class="header-bar">
                <h2 class="page-title">@yield('title', 'Dashboard')</h2>
                <div class="user-nav">
                    <div class="user-info-text">
                        <div class="font-semibold text-sm">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-muted">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            </header>

            <div class="content-area">
                @if(session('success'))
                    <div class="alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>

</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') - MeuBolso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/meubolso.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark navbar-meubolso">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('painel.index') }}">MeuBolso</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    @php
                        $menus = [
                            'painel.index'     => 'Visão Geral',
                            'transacoes.index' => 'Transações',
                            'orcamentos.index' => 'Orçamentos',
                            'relatorios.index' => 'Relatórios',
                        ];
                    @endphp
                    @foreach ($menus as $rota => $nome)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($rota) ? 'active' : '' }}" href="{{ route($rota) }}">{{ $nome }}</a>
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-light small d-none d-md-inline opacity-75">Olá, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('sucesso'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('sucesso') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @yield('conteudo')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

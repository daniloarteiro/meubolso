<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') - MeuBolso</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icone-meubolso.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/meubolso.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-meubolso sticky-top">
        <div class="container-fluid container-meubolso">
            <a class="navbar-brand d-inline-flex align-items-center gap-2 me-lg-4" href="{{ route('painel.index') }}">
                <img src="{{ asset('images/logo-meubolso-escura.svg') }}" alt="" width="28" height="30" aria-hidden="true">
                <span><span class="fw-normal">Meu</span><span class="fw-bold">Bolso</span></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto gap-1 my-2 my-lg-0">
                    @php
                        $mesNav = request()->input('mes');
                        $mesQuery = (is_string($mesNav) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mesNav))
                            ? ['mes' => $mesNav]
                            : [];
                        $menus = [
                            'painel.index'     => ['nome' => 'Visão Geral', 'icone' => 'bi-grid-1x2'],
                            'transacoes.index' => ['nome' => 'Transações',  'icone' => 'bi-arrow-left-right'],
                            'orcamentos.index' => ['nome' => 'Orçamentos',  'icone' => 'bi-bullseye'],
                            'relatorios.index' => ['nome' => 'Relatórios',  'icone' => 'bi-bar-chart-line'],
                        ];
                    @endphp
                    @foreach ($menus as $rota => $item)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($rota) ? 'active' : '' }}" href="{{ route($rota, $mesQuery) }}">
                                <i class="bi {{ $item['icone'] }}" aria-hidden="true"></i>
                                <span>{{ $item['nome'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="d-flex flex-wrap align-items-center gap-2 pt-2 pt-lg-0">
                    @php
                        $inicial = mb_strtoupper(mb_substr(trim(auth()->user()->name), 0, 1));
                    @endphp
                    <a href="{{ route('profile.edit') }}" class="nav-perfil-pill" title="Meu Perfil">
                        <span class="nav-perfil-inicial">{{ $inicial }}</span>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm px-3">
                            <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid container-meubolso py-4">
        @if (session('sucesso'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 rounded-3" role="alert">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <div>{{ session('sucesso') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
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

    @include('components.modal-confirmacao')
    @include('components.botao-ajuda')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

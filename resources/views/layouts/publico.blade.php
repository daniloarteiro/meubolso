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
            <a class="navbar-brand d-inline-flex align-items-center gap-2 me-lg-4" href="{{ url('/') }}">
                <img src="{{ asset('images/logo-meubolso-escura.svg') }}" alt="" width="28" height="30" aria-hidden="true">
                <span><span class="fw-normal">Meu</span><span class="fw-bold">Bolso</span></span>
            </a>

            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                <a href="{{ url('/') }}" class="btn btn-outline-light btn-sm px-3">
                    <i class="bi bi-house me-1" aria-hidden="true"></i>Início
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">
                    Entrar
                </a>
                <a href="{{ route('cadastro') }}" class="btn btn-warning btn-sm px-3 fw-bold text-dark">
                    Criar conta
                </a>
            </div>
        </div>
    </nav>

    <main class="container-fluid container-meubolso py-4">
        @yield('conteudo')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

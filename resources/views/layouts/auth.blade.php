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
<body class="auth-page">
    <main class="auth-shell">
        <aside class="auth-aside">
            <div>
                <a href="{{ url('/') }}" class="auth-brand text-white">
                    <img src="{{ asset('images/logo-meubolso-escura.svg') }}" alt="" width="36" height="38" aria-hidden="true">
                    <span><span class="fw-normal">Meu</span><span class="fw-bold">Bolso</span></span>
                </a>
            </div>

            <div class="my-4">
                <h1 class="auth-aside-titulo font-display">Saiba para onde seu dinheiro vai.</h1>
                <p class="auth-aside-texto">
                    Acompanhe entradas, despesas e limites por categoria sem planilhas complicadas.
                </p>
            </div>

            <div class="auth-resumo-card">
                <div class="auth-resumo-item">
                    <span class="auth-resumo-icone"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                    <span>Resumo mensal com saldo e gráficos claros</span>
                </div>
                <div class="auth-resumo-item">
                    <span class="auth-resumo-icone"><i class="bi bi-bullseye" aria-hidden="true"></i></span>
                    <span>Alertas antes de estourar o orçamento</span>
                </div>
                <div class="auth-resumo-item">
                    <span class="auth-resumo-icone"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i></span>
                    <span>Relatório mensal pronto para exportar em PDF</span>
                </div>
            </div>
        </aside>

        <section class="auth-panel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ url('/') }}" class="auth-brand d-lg-none" style="font-size: 20px;">
                    <img src="{{ asset('images/logo-meubolso.svg') }}" alt="" width="32" height="34" aria-hidden="true">
                    <span><span class="fw-normal" style="color: var(--verde-escuro)">Meu</span><span class="fw-bold" style="color: var(--verde)">Bolso</span></span>
                </a>
                <a href="{{ url('/') }}" class="auth-voltar ms-auto">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Voltar ao início
                </a>
            </div>

            @yield('conteudo')
        </section>
    </main>

    @stack('scripts')
</body>
</html>

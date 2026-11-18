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
    <div class="auth-box px-3">
        <h1 class="h3 text-center fw-bold mb-1" style="color: var(--verde-escuro)">MeuBolso</h1>
        <p class="text-center text-muted mb-4">Saiba para onde seu dinheiro vai.</p>
        <div class="card p-4">
            @yield('conteudo')
        </div>
    </div>
</body>
</html>

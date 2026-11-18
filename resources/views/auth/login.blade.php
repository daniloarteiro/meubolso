@extends('layouts.auth')
@section('titulo', 'Entrar')

@section('conteudo')
    <div class="mb-4">
        <h2 class="h3 font-display fw-bold mb-1" style="color: var(--tinta)">Entrar na sua conta</h2>
        <p class="text-muted small mb-0">Acesse seu painel para acompanhar gastos e metas do mês.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success small d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">E-mail</label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="seuemail@exemplo.com"
                autocomplete="email"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0" for="password">Senha</label>
                <a href="{{ route('password.request') }}" class="small text-decoration-none fw-medium">Esqueci a senha</a>
            </div>
            <div class="input-group">
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="Digite sua senha"
                    autocomplete="current-password"
                    required
                >
                <button
                    type="button"
                    class="btn btn-senha-olho"
                    id="toggleSenhaLogin"
                    aria-label="Mostrar ou ocultar senha"
                >
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label small text-muted" for="remember">Manter conectado neste navegador</label>
        </div>

        <button type="submit" class="btn btn-primary auth-btn-submit w-100">
            Entrar no MeuBolso
        </button>
    </form>

    <p class="text-center mt-4 mb-0 small text-muted">
        Ainda não tem conta?
        <a href="{{ route('cadastro') }}" class="fw-semibold text-decoration-none">Criar conta</a>
    </p>
@endsection

@push('scripts')
<script>
    const btnToggle = document.getElementById('toggleSenhaLogin');
    const campoSenha = document.getElementById('password');
    if (btnToggle && campoSenha) {
        btnToggle.addEventListener('click', () => {
            const mostrar = campoSenha.type === 'password';
            campoSenha.type = mostrar ? 'text' : 'password';
            btnToggle.innerHTML = mostrar
                ? '<i class="bi bi-eye-slash" aria-hidden="true"></i>'
                : '<i class="bi bi-eye" aria-hidden="true"></i>';
        });
    }
</script>
@endpush

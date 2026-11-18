@extends('layouts.auth')
@section('titulo', 'Recuperar senha')

@section('conteudo')
    <div class="mb-4">
        <h2 class="h3 font-display fw-bold mb-1" style="color: var(--tinta)">Esqueceu sua senha?</h2>
        <p class="text-muted small mb-0">Informe seu e-mail e enviaremos um link para você cadastrar uma nova senha.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success small d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="email">E-mail</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="seuemail@exemplo.com" autocomplete="email" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary auth-btn-submit w-100">Enviar link de recuperação</button>
    </form>

    <p class="text-center mt-4 mb-0 small text-muted">
        Lembrou a senha? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Voltar para o login</a>
    </p>
@endsection

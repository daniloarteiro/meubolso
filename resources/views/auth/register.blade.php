@extends('layouts.auth')
@section('titulo', 'Cadastro')

@section('conteudo')
    <div class="mb-4">
        <h2 class="h3 font-display fw-bold mb-1" style="color: var(--tinta)">Criar conta</h2>
        <p class="text-muted small mb-0">Comece a organizar suas receitas, despesas e metas.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.textContent = 'Enviando confirmação...';">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">Nome</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Seu nome" autocomplete="name" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">E-mail</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="seuemail@exemplo.com" autocomplete="email" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Senha</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Mínimo de 8 caracteres" autocomplete="new-password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Mínimo de 8 caracteres.</div>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirmar senha</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repita a senha" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-primary auth-btn-submit w-100">Criar conta</button>
    </form>

    <p class="text-center mt-4 mb-0 small text-muted">
        Já tem conta? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Entrar</a>
    </p>
@endsection

@extends('layouts.auth')
@section('titulo', 'Confirmar senha')

@section('conteudo')
    <div class="mb-4">
        <h2 class="h3 font-display fw-bold mb-1" style="color: var(--tinta)">Confirmar senha</h2>
        <p class="text-muted small mb-0">Esta é uma área segura. Confirme sua senha antes de continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="password">Senha</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary auth-btn-submit w-100">Confirmar</button>
    </form>
@endsection

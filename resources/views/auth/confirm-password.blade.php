@extends('layouts.auth')
@section('titulo', 'Confirmar senha')

@section('conteudo')
    <h2 class="h5 mb-2">Confirmar senha</h2>
    <p class="text-muted small mb-3">Esta é uma área segura. Confirme sua senha antes de continuar.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="password">Senha</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Confirmar</button>
    </form>
@endsection

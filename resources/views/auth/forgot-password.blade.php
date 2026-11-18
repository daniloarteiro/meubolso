@extends('layouts.auth')
@section('titulo', 'Recuperar senha')

@section('conteudo')
    <h2 class="h5 mb-2">Esqueceu sua senha?</h2>
    <p class="text-muted small mb-3">Informe seu e-mail e enviaremos um link para você cadastrar uma nova senha.</p>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">E-mail</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Enviar link de recuperação</button>
    </form>
    <p class="text-center mt-3 mb-0 small"><a href="{{ route('login') }}">Voltar para o login</a></p>
@endsection

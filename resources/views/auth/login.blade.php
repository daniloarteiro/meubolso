@extends('layouts.auth')
@section('titulo', 'Entrar')

@section('conteudo')
    <h2 class="h5 mb-3">Entrar na sua conta</h2>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">E-mail</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Senha</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex justify-content-between mb-3 small">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Lembrar de mim</label>
            </div>
            <a href="{{ route('password.request') }}">Esqueci a senha</a>
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
    <p class="text-center mt-3 mb-0 small">Não tem conta? <a href="{{ route('cadastro') }}">Cadastre-se</a></p>
@endsection

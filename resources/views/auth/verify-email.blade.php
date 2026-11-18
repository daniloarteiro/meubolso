@extends('layouts.auth')
@section('titulo', 'Verificar e-mail')

@section('conteudo')
    <h2 class="h5 mb-2">Verifique seu e-mail</h2>
    <p class="text-muted small mb-3">Antes de continuar, clique no link de verificação enviado para o seu e-mail.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small">
            Um novo link de verificação foi enviado para o seu e-mail.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Reenviar e-mail</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Sair</button>
        </form>
    </div>
@endsection

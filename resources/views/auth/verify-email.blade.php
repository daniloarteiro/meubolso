@extends('layouts.auth')
@section('titulo', 'Verificar e-mail')

@section('conteudo')
    <div class="mb-4">
        <h2 class="h3 font-display fw-bold mb-1" style="color: var(--tinta)">Verifique seu e-mail</h2>
        <p class="text-muted small mb-0">Antes de continuar, clique no link de verificação enviado para o seu e-mail.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>Um novo link de verificação foi enviado para o seu e-mail.</span>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm px-3">Reenviar e-mail</button>
        </form>

        <div class="d-flex gap-2">
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm px-3">Corrigir e-mail</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm px-3">Sair</button>
            </form>
        </div>
    </div>
@endsection

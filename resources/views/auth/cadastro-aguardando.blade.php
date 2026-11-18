@extends('layouts.auth')
@section('titulo', 'Confirme seu e-mail')

@section('conteudo')
    <div class="mb-4">
        <h2 class="h3 font-display fw-bold mb-1" style="color: var(--tinta)">Confirme seu e-mail</h2>
        <p class="text-muted small mb-0">
            Enviamos um link de confirmação para <strong>{{ $email }}</strong>.
            Sua conta só será criada após clicar nesse link (válido por 60 minutos).
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success small d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>Um novo link de confirmação foi enviado para o seu e-mail.</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger small rounded-3">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form method="POST" action="{{ route('cadastro.reenviar') }}" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.textContent = 'Enviando...';">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm px-3">Reenviar e-mail</button>
        </form>

        <a href="{{ route('cadastro') }}" class="btn btn-outline-secondary btn-sm px-3">Usar outro e-mail</a>
    </div>
@endsection

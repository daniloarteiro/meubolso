@extends('layouts.app')
@section('titulo', 'Meu Perfil')

@section('conteudo')
    <div class="mb-4">
        <h1 class="h3 mb-0 fw-bold">Meu Perfil</h1>
        <p class="text-muted small mb-0 mt-1">Gerencie seus dados de acesso e segurança da conta.</p>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success small d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>Dados da conta atualizados com sucesso.</span>
        </div>
    @elseif (session('status') === 'password-updated')
        <div class="alert alert-success small d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>Senha atualizada com sucesso.</span>
        </div>
    @elseif (session('status') === 'verification-link-sent')
        <div class="alert alert-success small d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>Um novo link de verificação foi enviado para o seu e-mail.</span>
        </div>
    @endif

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    @php
        $inicial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
    @endphp

    <div class="card p-3 p-lg-4 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="perfil-avatar-grande">{{ $inicial }}</span>
                <div>
                    <div class="fw-bold fs-5">{{ $user->name }}</div>
                    <div class="text-muted small">{{ $user->email }}</div>
                </div>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">
                    <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>E-mail não verificado
                </span>
            @else
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="bi bi-patch-check me-1" aria-hidden="true"></i>Conta verificada
                </span>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card p-3 p-lg-4 h-100">
                <h2 class="h6 fw-bold mb-3">Dados da conta</h2>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="name">Nome</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="email">E-mail</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="alert alert-warning small py-2 mb-3 rounded-3">
                            Seu e-mail ainda não foi verificado.
                            <button type="submit" form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                                Reenviar link de verificação
                            </button>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm px-3">Salvar dados</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-3 p-lg-4 h-100">
                <h2 class="h6 fw-bold mb-3">Alterar senha</h2>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="current_password">Senha atual</label>
                        <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" name="current_password" required>
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="password">Nova senha</label>
                        <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="password" name="password" required>
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="password_confirmation">Confirmar nova senha</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Atualizar senha</button>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-perigo p-3 p-lg-4">
                <h2 class="h6 fw-bold text-danger mb-1">Excluir conta</h2>
                <p class="text-muted small mb-3">Ao excluir sua conta, todos os seus lançamentos e metas serão apagados permanentemente.</p>
                <form
                    method="POST"
                    action="{{ route('profile.destroy') }}"
                    class="row g-2 align-items-end"
                    data-confirm-titulo="Excluir conta permanentemente?"
                    data-confirm-mensagem="Todos os seus lançamentos, categorias e metas serão apagados e não poderão ser recuperados."
                    data-confirm-botao="Excluir conta"
                >
                    @csrf
                    @method('DELETE')
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold" for="delete_password">Confirme sua senha</label>
                        <input type="password" class="form-control form-control-sm @error('password', 'userDeletion') is-invalid @enderror" id="delete_password" name="password" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-danger btn-sm px-3">Excluir conta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

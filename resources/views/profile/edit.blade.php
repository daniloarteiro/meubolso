@extends('layouts.app')
@section('titulo', 'Meu Perfil')

@section('conteudo')
    <h1 class="h4 mb-3">Meu Perfil</h1>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card p-3">
                <h2 class="h6 mb-3">Dados da conta</h2>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label" for="name">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Salvar dados</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-3">
                <h2 class="h6 mb-3">Alterar senha</h2>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Senha atual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Nova senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirmar nova senha</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Atualizar senha</button>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-3">
                <h2 class="h6 text-danger mb-2">Excluir conta</h2>
                <p class="text-muted small mb-3">Ao excluir sua conta, todos os seus lançamentos e metas serão apagados.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="row g-2 align-items-end" onsubmit="return confirm('Tem certeza que deseja excluir sua conta?')">
                    @csrf
                    @method('DELETE')
                    <div class="col-sm-4">
                        <label class="form-label small" for="delete_password">Confirme sua senha</label>
                        <input type="password" class="form-control form-control-sm" id="delete_password" name="password" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Excluir conta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

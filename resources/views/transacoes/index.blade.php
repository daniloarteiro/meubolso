@extends('layouts.app')
@section('titulo', 'Transações')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Transações</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLancamento">+ Nova transação</button>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('transacoes.index') }}" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <label class="form-label" for="de">De</label>
                <input type="date" class="form-control" id="de" name="de" value="{{ request('de') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="ate">Até</label>
                <input type="date" class="form-control" id="ate" name="ate" value="{{ request('ate') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="tipo">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="receita" {{ request('tipo') === 'receita' ? 'selected' : '' }}>Receitas</option>
                    <option value="despesa" {{ request('tipo') === 'despesa' ? 'selected' : '' }}>Despesas</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="categoria_id">Categoria</label>
                <select class="form-select" id="categoria_id" name="categoria_id">
                    <option value="">Todas</option>
                    @foreach ($categorias as $c)
                        <option value="{{ $c->id }}" {{ (string) request('categoria_id') === (string) $c->id ? 'selected' : '' }}>
                            {{ $c->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('transacoes.index') }}" class="btn btn-outline-secondary">Limpar</a>
        </div>
    </form>

    {{-- Tabela --}}
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th class="text-end">Valor</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transacoes as $l)
                        <tr>
                            <td>{{ $l->data->format('d/m/Y') }}</td>
                            <td>{{ $l->descricao }}</td>
                            <td>{{ $l->categoria->nome }}</td>
                            <td class="text-end {{ $l->tipo }}">
                                {{ $l->tipo == 'receita' ? '+' : '−' }} R$ {{ number_format($l->valor, 2, ',', '.') }}
                            </td>
                            <td class="text-end text-nowrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary btn-editar"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar"
                                    data-url="{{ route('transacoes.update', $l) }}"
                                    data-tipo="{{ $l->tipo }}"
                                    data-descricao="{{ $l->descricao }}"
                                    data-valor="{{ $l->valor }}"
                                    data-data="{{ $l->data->format('Y-m-d') }}"
                                    data-categoria="{{ $l->categoria_id }}"
                                >Editar</button>

                                <form method="POST" action="{{ route('transacoes.destroy', $l) }}" class="d-inline" onsubmit="return confirm('Deseja excluir esta transação?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma transação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Janela de novo lançamento --}}
    <div class="modal fade" id="modalLancamento" tabindex="-1" aria-labelledby="tituloModal" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('transacoes.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5" id="tituloModal">Novo lançamento</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="m_tipo">Tipo</label>
                        <select class="form-select" id="m_tipo" required>
                            <option value="despesa">Despesa</option>
                            <option value="receita">Receita</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="m_descricao">Descrição</label>
                        <input type="text" class="form-control" id="m_descricao" name="descricao" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label" for="m_valor">Valor (R$)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="m_valor" name="valor" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="m_data">Data</label>
                            <input type="date" class="form-control" id="m_data" name="data" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="m_categoria">Categoria</label>
                        <select class="form-select" id="m_categoria" name="categoria_id" required>
                            @foreach ($categorias as $c)
                                <option value="{{ $c->id }}" data-tipo="{{ $c->tipo }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar lançamento</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Janela de edição --}}
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="tituloModalEditar" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="formEditar" action="#" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title h5" id="tituloModalEditar">Editar lançamento</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="e_tipo">Tipo</label>
                        <select class="form-select" id="e_tipo" required>
                            <option value="despesa">Despesa</option>
                            <option value="receita">Receita</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="e_descricao">Descrição</label>
                        <input type="text" class="form-control" id="e_descricao" name="descricao" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label" for="e_valor">Valor (R$)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="e_valor" name="valor" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="e_data">Data</label>
                            <input type="date" class="form-control" id="e_data" name="data" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="e_categoria">Categoria</label>
                        <select class="form-select" id="e_categoria" name="categoria_id" required>
                            @foreach ($categorias as $c)
                                <option value="{{ $c->id }}" data-tipo="{{ $c->tipo }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function filtrarCategoriasPorTipo(selectTipo, selectCategoria, valorPreferido = null) {
        const tipo = selectTipo.value;
        let primeiraVisivel = null;

        Array.from(selectCategoria.options).forEach(opt => {
            const compativel = opt.dataset.tipo === tipo;
            opt.hidden = !compativel;
            opt.disabled = !compativel;

            if (compativel && !primeiraVisivel) {
                primeiraVisivel = opt.value;
            }
        });

        if (valorPreferido && Array.from(selectCategoria.options).some(opt => opt.value === String(valorPreferido) && !opt.disabled)) {
            selectCategoria.value = String(valorPreferido);
        } else if (primeiraVisivel) {
            selectCategoria.value = primeiraVisivel;
        }
    }

    const mTipo = document.getElementById('m_tipo');
    const mCategoria = document.getElementById('m_categoria');
    const eTipo = document.getElementById('e_tipo');
    const eCategoria = document.getElementById('e_categoria');

    filtrarCategoriasPorTipo(mTipo, mCategoria);
    mTipo.addEventListener('change', () => filtrarCategoriasPorTipo(mTipo, mCategoria));
    eTipo.addEventListener('change', () => filtrarCategoriasPorTipo(eTipo, eCategoria));

    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('formEditar').action = btn.dataset.url;
            eTipo.value = btn.dataset.tipo;
            document.getElementById('e_descricao').value = btn.dataset.descricao;
            document.getElementById('e_valor').value = btn.dataset.valor;
            document.getElementById('e_data').value = btn.dataset.data;
            filtrarCategoriasPorTipo(eTipo, eCategoria, btn.dataset.categoria);
        });
    });
</script>
@endpush

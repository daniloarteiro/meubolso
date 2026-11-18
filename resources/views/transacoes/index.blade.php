@extends('layouts.app')
@section('titulo', 'Transações')

@section('conteudo')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Transações</h1>
            <p class="text-muted small mb-0 mt-1">Consulte, filtre e gerencie suas entradas e saídas.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLancamento">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nova transação
        </button>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('transacoes.index') }}" class="card p-3 p-lg-4 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" for="de">De</label>
                <input type="date" class="form-control form-control-sm" id="de" name="de" value="{{ is_string(request('de')) ? request('de') : '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" for="ate">Até</label>
                <input type="date" class="form-control form-control-sm" id="ate" name="ate" value="{{ is_string(request('ate')) ? request('ate') : '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" for="tipo">Tipo</label>
                <select class="form-select form-select-sm" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="receita" {{ request('tipo') === 'receita' ? 'selected' : '' }}>Receitas</option>
                    <option value="despesa" {{ request('tipo') === 'despesa' ? 'selected' : '' }}>Despesas</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1" for="categoria_id">Categoria</label>
                <select class="form-select form-select-sm" id="categoria_id" name="categoria_id">
                    <option value="">Todas</option>
                    @foreach ($categorias as $c)
                        <option value="{{ $c->id }}" {{ is_scalar(request('categoria_id')) && (string) request('categoria_id') === (string) $c->id ? 'selected' : '' }}>
                            {{ $c->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end mt-2 mt-md-0">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 flex-md-grow-0 px-3">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filtrar
                </button>
                <a href="{{ route('transacoes.index') }}" class="btn btn-outline-secondary btn-sm px-3">Limpar</a>
            </div>
        </div>
    </form>

    {{-- Tabela --}}
    <div class="card p-3 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 fw-bold mb-0">Lançamentos</h2>
            <span class="text-muted small">{{ count($transacoes) }} {{ count($transacoes) === 1 ? 'registro' : 'registros' }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-meubolso align-middle mb-0">
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
                            <td class="text-muted text-nowrap">{{ \Carbon\Carbon::parse($l->data)->format('d/m/Y') }}</td>
                            <td class="fw-medium">{{ $l->descricao }}</td>
                            <td>
                                <span class="cat-pill">
                                    <span class="cat-bolinha" style="--cat-cor: {{ $l->categoria_cor ?? '#15803d' }}"></span>
                                    {{ $l->categoria_nome }}
                                </span>
                            </td>
                            <td class="text-end valor-num fw-semibold text-nowrap {{ $l->tipo }}">
                                {{ $l->tipo == 'receita' ? '+' : '−' }} R$ {{ number_format($l->valor, 2, ',', '.') }}
                            </td>
                            <td class="text-end text-nowrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary btn-editar"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar"
                                    data-url="{{ route('transacoes.update', $l->id) }}"
                                    data-tipo="{{ $l->tipo }}"
                                    data-descricao="{{ $l->descricao }}"
                                    data-valor="{{ $l->valor }}"
                                    data-data="{{ \Carbon\Carbon::parse($l->data)->format('Y-m-d') }}"
                                    data-categoria="{{ $l->categoria_id }}"
                                >
                                    <i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar
                                </button>

                                <form method="POST" action="{{ route('transacoes.destroy', $l->id) }}" class="d-inline" onsubmit="return confirm('Deseja excluir esta transação?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash3 me-1" aria-hidden="true"></i>Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-receipt d-block fs-3 mb-2 opacity-50" aria-hidden="true"></i>
                                Nenhuma transação encontrada para os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Janela de novo lançamento --}}
    <div class="modal fade" id="modalLancamento" tabindex="-1" aria-labelledby="tituloModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('transacoes.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-bold" id="tituloModal">Novo lançamento</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" for="m_tipo">Tipo</label>
                        <select class="form-select" id="m_tipo" required>
                            <option value="despesa">Despesa</option>
                            <option value="receita">Receita</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" for="m_descricao">Descrição</label>
                        <input type="text" class="form-control" id="m_descricao" name="descricao" placeholder="Ex.: Almoço, Mercado, Salário..." required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold small" for="m_valor">Valor (R$)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="m_valor" name="valor" placeholder="0,00" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold small" for="m_data">Data</label>
                            <input type="date" class="form-control" id="m_data" name="data" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small" for="m_categoria">Categoria</label>
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
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="formEditar" action="#" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-bold" id="tituloModalEditar">Editar lançamento</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" for="e_tipo">Tipo</label>
                        <select class="form-select" id="e_tipo" required>
                            <option value="despesa">Despesa</option>
                            <option value="receita">Receita</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" for="e_descricao">Descrição</label>
                        <input type="text" class="form-control" id="e_descricao" name="descricao" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold small" for="e_valor">Valor (R$)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="e_valor" name="valor" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold small" for="e_data">Data</label>
                            <input type="date" class="form-control" id="e_data" name="data" required>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small" for="e_categoria">Categoria</label>
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
    function filtrarOpcoesPorTipo(selectTipo, selectCategoria) {
        const tipoAtual = selectTipo.value;
        let primeiraCompativel = null;

        Array.from(selectCategoria.options).forEach(opt => {
            const compativel = opt.dataset.tipo === tipoAtual;
            opt.hidden = !compativel;
            opt.disabled = !compativel;
            if (compativel && !primeiraCompativel) {
                primeiraCompativel = opt;
            }
        });

        const selecionada = selectCategoria.options[selectCategoria.selectedIndex];
        if (!selecionada || selecionada.dataset.tipo !== tipoAtual) {
            if (primeiraCompativel) {
                selectCategoria.value = primeiraCompativel.value;
            }
        }
    }

    function vincularTipoECategoria(selectTipo, selectCategoria) {
        const selecionadaInicial = selectCategoria.options[selectCategoria.selectedIndex];
        if (selecionadaInicial && selecionadaInicial.dataset.tipo) {
            selectTipo.value = selecionadaInicial.dataset.tipo;
        }
        filtrarOpcoesPorTipo(selectTipo, selectCategoria);

        selectTipo.addEventListener('change', () => {
            filtrarOpcoesPorTipo(selectTipo, selectCategoria);
        });
    }

    const mTipo = document.getElementById('m_tipo');
    const mCategoria = document.getElementById('m_categoria');
    const eTipo = document.getElementById('e_tipo');
    const eCategoria = document.getElementById('e_categoria');

    vincularTipoECategoria(mTipo, mCategoria);
    vincularTipoECategoria(eTipo, eCategoria);

    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('formEditar').action = btn.dataset.url;
            eTipo.value = btn.dataset.tipo;
            filtrarOpcoesPorTipo(eTipo, eCategoria);
            document.getElementById('e_descricao').value = btn.dataset.descricao;
            document.getElementById('e_valor').value = btn.dataset.valor;
            document.getElementById('e_data').value = btn.dataset.data;
            eCategoria.value = btn.dataset.categoria;
        });
    });

    if (new URLSearchParams(window.location.search).get('novo') === '1') {
        const modalEl = document.getElementById('modalLancamento');
        if (modalEl && window.bootstrap) {
            new bootstrap.Modal(modalEl).show();
        }
    }
</script>
@endpush

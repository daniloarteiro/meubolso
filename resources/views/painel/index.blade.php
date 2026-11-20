@extends('layouts.app')
@section('titulo', 'Visão Geral')

@section('conteudo')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">{{ $mesAtualLabel }}</h1>
            <p class="text-muted small mb-0 mt-1">
                {{ $ehMesAtual ? 'Resumo das suas finanças no mês corrente.' : 'Resumo das suas finanças no mês selecionado.' }}
            </p>
        </div>

        <form method="GET" action="{{ route('painel.index') }}" class="filtro-mes-grupo">
            <label class="filtro-mes-rotulo" for="mes_painel">Mês</label>
            <input type="month" class="filtro-mes-input" id="mes_painel" name="mes" value="{{ $mes }}" aria-label="Mês de referência">
            <button type="submit" class="filtro-mes-btn">Ver</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card kpi-card p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-medium">Saldo do mês</span>
                    <span class="kpi-icone kpi-icone-saldo"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                </div>
                <div class="valor-grande {{ $saldo < 0 ? 'despesa' : '' }}">
                    {{ $saldo < 0 ? '− ' : '' }}R$ {{ number_format(abs($saldo), 2, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi-card p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-medium">Receitas</span>
                    <span class="kpi-icone kpi-icone-receita"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                </div>
                <div class="valor-grande receita">+ R$ {{ number_format($receitas, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kpi-card p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-medium">Despesas</span>
                    <span class="kpi-icone kpi-icone-despesa"><i class="bi bi-arrow-down-right" aria-hidden="true"></i></span>
                </div>
                <div class="valor-grande despesa">− R$ {{ number_format($despesas, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card p-3 p-lg-4 h-100">
                <h2 class="h6 fw-bold mb-3">Gastos por categoria</h2>
                <div style="position: relative; height: 270px;">
                    <canvas id="graficoCategorias"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card p-3 p-lg-4 h-100">
                <h2 class="h6 fw-bold mb-3">Receitas e despesas nos últimos meses</h2>
                <div style="position: relative; height: 270px;">
                    <canvas id="graficoMeses"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 fw-bold mb-0">Últimas transações</h2>
            <a href="{{ route('transacoes.index', ['mes' => $mes]) }}" class="small fw-semibold text-decoration-none">
                Ver todas <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-meubolso align-middle mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th class="text-end">Valor</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                {{ $ehMesAtual ? 'Nenhuma transação cadastrada ainda.' : 'Nenhuma transação cadastrada neste mês.' }}
                                <a href="{{ route('transacoes.index', ['novo' => 1]) }}" class="fw-semibold text-decoration-none ms-1">Adicionar {{ $ehMesAtual ? 'a primeira' : 'transação' }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const dados = {
            categorias: @json($categorias),
            meses: @json($meses)
        };
    </script>
    <script src="{{ asset('js/painel.js') }}"></script>
@endpush

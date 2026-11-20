@extends('layouts.app')
@section('titulo', 'Relatórios')

@section('conteudo')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Relatório mensal</h1>
            <p class="text-muted small mb-0 mt-1">{{ $mesLabel }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" action="{{ route('relatorios.index') }}" class="filtro-mes-grupo">
                <label class="filtro-mes-rotulo" for="mes">Mês</label>
                <input type="month" class="filtro-mes-input" id="mes" name="mes" value="{{ $mes }}" aria-label="Mês de referência">
                <button type="submit" class="filtro-mes-btn">Ver</button>
            </form>
            <a href="{{ route('relatorios.pdf', ['mes' => $mes]) }}" class="btn btn-primary btn-acao-topo">
                Exportar PDF
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
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
        <div class="col-md-4">
            <div class="card kpi-card p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-medium">Saldo</span>
                    <span class="kpi-icone kpi-icone-saldo"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                </div>
                <div class="valor-grande {{ $saldo < 0 ? 'despesa' : '' }}">
                    {{ $saldo < 0 ? '− ' : '' }}R$ {{ number_format(abs($saldo), 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 p-lg-4">
        <h2 class="h6 fw-bold mb-3">Despesas por categoria</h2>
        <div class="table-responsive">
            <table class="table table-meubolso align-middle mb-0">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th style="min-width: 170px;">Participação</th>
                        <th class="text-end">Gasto</th>
                        <th class="text-end">% das despesas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $c)
                        <tr>
                            <td>
                                <span class="cat-pill">
                                    <span class="cat-bolinha" style="--cat-cor: {{ $c['cor'] ?? '#15803d' }}"></span>
                                    {{ $c['nome'] }}
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 8px; border-radius: 999px; background-color: #eee9dc;">
                                    <div class="progress-bar" style="width: {{ min($c['percentual'], 100) }}%; background-color: {{ $c['cor'] ?? '#15803d' }}; border-radius: 999px;"></div>
                                </div>
                            </td>
                            <td class="text-end valor-num fw-semibold">R$ {{ number_format($c['gasto'], 2, ',', '.') }}</td>
                            <td class="text-end valor-num text-muted">{{ $c['percentual'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-bar-chart d-block fs-3 mb-2 opacity-50" aria-hidden="true"></i>
                                Nenhuma despesa registrada neste mês.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

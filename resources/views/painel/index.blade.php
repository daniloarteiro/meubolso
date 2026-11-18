@extends('layouts.app')
@section('titulo', 'Visão Geral')

@section('conteudo')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ $mesAtualLabel }}</h1>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Mês Atual</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3">
                <div class="text-muted">Saldo do mês</div>
                <div class="valor-grande">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="text-muted">Receitas</div>
                <div class="valor-grande receita">+ R$ {{ number_format($receitas, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="text-muted">Despesas</div>
                <div class="valor-grande despesa">− R$ {{ number_format($despesas, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card p-3 h-100">
                <h2 class="h6 mb-3">Gastos por categoria</h2>
                <div style="position: relative; height: 260px;">
                    <canvas id="graficoCategorias"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card p-3 h-100">
                <h2 class="h6 mb-3">Receitas e despesas nos últimos meses</h2>
                <div style="position: relative; height: 260px;">
                    <canvas id="graficoMeses"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 mb-0">Últimas transações</h2>
            <a href="{{ route('transacoes.index') }}">Ver todas</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <tbody>
                    @forelse ($transacoes as $l)
                        <tr>
                            <td>{{ $l->data->format('d/m/Y') }}</td>
                            <td>{{ $l->descricao }}</td>
                            <td class="text-muted">{{ $l->categoria->nome }}</td>
                            <td class="text-end {{ $l->tipo }}">
                                {{ $l->tipo == 'receita' ? '+' : '−' }} R$ {{ number_format($l->valor, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Nenhuma transação cadastrada ainda.</td>
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

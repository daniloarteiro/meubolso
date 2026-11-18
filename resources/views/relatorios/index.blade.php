@extends('layouts.app')
@section('titulo', 'Relatórios')

@section('conteudo')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0">Relatório mensal</h1>
            <small class="text-muted">{{ $mesLabel }}</small>
        </div>
        <form method="GET" action="{{ route('relatorios.index') }}" class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label mb-0" for="mes">Mês</label>
                <input type="month" class="form-control" id="mes" name="mes" value="{{ $mes }}">
            </div>
            <button type="submit" class="btn btn-outline-secondary">Ver</button>
            <a href="{{ route('relatorios.pdf', ['mes' => $mes]) }}" class="btn btn-primary">Exportar PDF</a>
        </form>
    </div>

    <div class="row g-3 mb-4">
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
        <div class="col-md-4">
            <div class="card p-3">
                <div class="text-muted">Saldo</div>
                <div class="valor-grande">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <h2 class="h6">Despesas por categoria</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th class="text-end">Gasto</th>
                        <th class="text-end">% das despesas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $c)
                        <tr>
                            <td>{{ $c['nome'] }}</td>
                            <td class="text-end">R$ {{ number_format($c['gasto'], 2, ',', '.') }}</td>
                            <td class="text-end">{{ $c['percentual'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Nenhuma despesa registrada neste mês.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

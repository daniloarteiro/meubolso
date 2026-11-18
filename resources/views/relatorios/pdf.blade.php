<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório {{ $mesLabel }} - MeuBolso</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .topo { border-bottom: 2px solid #15803d; padding-bottom: 10px; margin-bottom: 18px; }
        .topo h1 { margin: 0; color: #14532d; font-size: 20px; }
        .topo p { margin: 4px 0 0; color: #6b7280; }
        .resumo { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .resumo td { border: 1px solid #d1d5db; padding: 10px; width: 33.3%; }
        .rotulo { font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .valor { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .receita { color: #15803d; }
        .despesa { color: #dc2626; }
        h2 { font-size: 14px; color: #14532d; margin: 18px 0 8px; }
        table.lista { width: 100%; border-collapse: collapse; }
        table.lista th, table.lista td { border-bottom: 1px solid #e5e7eb; padding: 7px 6px; text-align: left; }
        table.lista th { background: #f3f4f6; font-size: 11px; }
        .direita { text-align: right; }
    </style>
</head>
<body>
    <div class="topo">
        <h1>MeuBolso - Relatório Mensal</h1>
        <p>Período: {{ $mesLabel }} | Usuário: {{ $usuario->name }}</p>
    </div>

    <table class="resumo">
        <tr>
            <td>
                <div class="rotulo">Receitas</div>
                <div class="valor receita">+ R$ {{ number_format($receitas, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="rotulo">Despesas</div>
                <div class="valor despesa">- R$ {{ number_format($despesas, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="rotulo">Saldo</div>
                <div class="valor">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <h2>Despesas por categoria</h2>
    <table class="lista">
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="direita">Gasto</th>
                <th class="direita">% das despesas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categorias as $c)
                <tr>
                    <td>{{ $c['nome'] }}</td>
                    <td class="direita">R$ {{ number_format($c['gasto'], 2, ',', '.') }}</td>
                    <td class="direita">{{ $c['percentual'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Nenhuma despesa registrada neste mês.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Lançamentos do mês</h2>
    <table class="lista">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th class="direita">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transacoes as $l)
                <tr>
                    <td>{{ $l->data->format('d/m/Y') }}</td>
                    <td>{{ $l->descricao }}</td>
                    <td>{{ $l->categoria->nome }}</td>
                    <td class="direita {{ $l->tipo }}">
                        {{ $l->tipo == 'receita' ? '+' : '-' }} R$ {{ number_format($l->valor, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhuma transação registrada neste mês.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

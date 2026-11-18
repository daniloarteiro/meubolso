<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class PainelController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $agora = now();
        $inicioMes = $agora->copy()->startOfMonth()->toDateString();
        $fimMes = $agora->copy()->endOfMonth()->toDateString();

        $receitas = (float) $user->transacoes()
            ->where('tipo', 'receita')
            ->whereBetween('data', [$inicioMes, $fimMes])
            ->sum('valor');

        $despesas = (float) $user->transacoes()
            ->where('tipo', 'despesa')
            ->whereBetween('data', [$inicioMes, $fimMes])
            ->sum('valor');

        $saldo = $receitas - $despesas;

        $transacoes = $user->transacoes()
            ->with('categoria')
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Gastos do mês agrupados por categoria para o gráfico de rosca
        $gastosPorCat = $user->transacoes()
            ->where('tipo', 'despesa')
            ->whereBetween('data', [$inicioMes, $fimMes])
            ->selectRaw('categoria_id, SUM(valor) as total')
            ->groupBy('categoria_id')
            ->pluck('total', 'categoria_id');

        $categorias = $user->categorias()
            ->where('tipo', 'despesa')
            ->orderBy('nome')
            ->get()
            ->map(function ($cat) use ($gastosPorCat) {
                return [
                    'nome' => $cat->nome,
                    'cor' => $cat->cor,
                    'gasto' => (float) ($gastosPorCat[$cat->id] ?? 0),
                ];
            })
            ->values();

        // Histórico dos últimos 6 meses para o gráfico de barras
        $siglasMeses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];

        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $dataMes = $agora->copy()->subMonthsNoOverflow($i);
            $ini = $dataMes->copy()->startOfMonth()->toDateString();
            $fim = $dataMes->copy()->endOfMonth()->toDateString();

            $meses[] = [
                'label' => $siglasMeses[$dataMes->month],
                'receitas' => (float) $user->transacoes()
                    ->where('tipo', 'receita')
                    ->whereBetween('data', [$ini, $fim])
                    ->sum('valor'),
                'despesas' => (float) $user->transacoes()
                    ->where('tipo', 'despesa')
                    ->whereBetween('data', [$ini, $fim])
                    ->sum('valor'),
            ];
        }

        $nomesMeses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        $mesAtualLabel = $nomesMeses[$agora->month] . ' de ' . $agora->year;

        return view('painel.index', compact(
            'transacoes',
            'categorias',
            'meses',
            'receitas',
            'despesas',
            'saldo',
            'mesAtualLabel'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PainelController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $agora = now();
        $inicioMes = $agora->copy()->startOfMonth()->toDateString();
        $fimMes = $agora->copy()->endOfMonth()->toDateString();

        // Totais de receitas e despesas do mês atual
        $totais = DB::select("
            SELECT
                COALESCE(SUM(CASE WHEN c.tipo = 'receita' THEN t.valor ELSE 0 END), 0) AS receitas,
                COALESCE(SUM(CASE WHEN c.tipo = 'despesa' THEN t.valor ELSE 0 END), 0) AS despesas
            FROM transacoes t
            INNER JOIN categorias c ON c.id = t.categoria_id
            WHERE t.user_id = ?
              AND DATE(t.data) BETWEEN ? AND ?
        ", [$user->id, $inicioMes, $fimMes])[0];

        $receitas = (float) $totais->receitas;
        $despesas = (float) $totais->despesas;
        $saldo = $receitas - $despesas;

        // Últimas 5 transações do usuário
        $transacoes = DB::select("
            SELECT
                t.id,
                t.data,
                t.descricao,
                t.valor,
                c.nome AS categoria_nome,
                c.tipo
            FROM transacoes t
            INNER JOIN categorias c ON c.id = t.categoria_id
            WHERE t.user_id = ?
            ORDER BY t.data DESC, t.id DESC
            LIMIT 5
        ", [$user->id]);

        // Gastos do mês agrupados por categoria para o gráfico de rosca
        $categoriasRows = DB::select("
            SELECT
                c.nome,
                c.cor,
                COALESCE(SUM(t.valor), 0) AS gasto
            FROM categorias c
            LEFT JOIN transacoes t
                ON t.categoria_id = c.id
               AND t.user_id = c.user_id
               AND DATE(t.data) BETWEEN ? AND ?
            WHERE c.user_id = ?
              AND c.tipo = 'despesa'
            GROUP BY c.id, c.nome, c.cor
            ORDER BY c.nome ASC
        ", [$inicioMes, $fimMes, $user->id]);

        $categorias = collect($categoriasRows)
            ->map(fn ($cat) => [
                'nome' => $cat->nome,
                'cor' => $cat->cor,
                'gasto' => (float) $cat->gasto,
            ])
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

            $rowMes = DB::select("
                SELECT
                    COALESCE(SUM(CASE WHEN c.tipo = 'receita' THEN t.valor ELSE 0 END), 0) AS receitas,
                    COALESCE(SUM(CASE WHEN c.tipo = 'despesa' THEN t.valor ELSE 0 END), 0) AS despesas
                FROM transacoes t
                INNER JOIN categorias c ON c.id = t.categoria_id
                WHERE t.user_id = ?
                  AND DATE(t.data) BETWEEN ? AND ?
            ", [$user->id, $ini, $fim])[0];

            $meses[] = [
                'label' => $siglasMeses[$dataMes->month],
                'receitas' => (float) $rowMes->receitas,
                'despesas' => (float) $rowMes->despesas,
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

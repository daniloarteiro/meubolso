<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $dados = $this->montarDados($request);

        return view('relatorios.index', $dados);
    }

    public function pdf(Request $request)
    {
        $dados = $this->montarDados($request);
        $dados['usuario'] = $request->user();

        $pdf = Pdf::loadView('relatorios.pdf', $dados);

        return $pdf->download("relatorio-meubolso-{$dados['mes']}.pdf");
    }

    private function montarDados(Request $request): array
    {
        $user = $request->user();
        $mes = $request->input('mes');

        if (! is_string($mes) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        $dataBase = Carbon::createFromFormat('!Y-m', $mes);
        $inicio = $dataBase->copy()->startOfMonth()->toDateString();
        $fim = $dataBase->copy()->endOfMonth()->toDateString();

        // Totais de receitas e despesas do mês selecionado
        $totais = DB::select("
            SELECT
                COALESCE(SUM(CASE WHEN c.tipo = 'receita' THEN t.valor ELSE 0 END), 0) AS receitas,
                COALESCE(SUM(CASE WHEN c.tipo = 'despesa' THEN t.valor ELSE 0 END), 0) AS despesas
            FROM transacoes t
            INNER JOIN categorias c ON c.id = t.categoria_id
            WHERE t.user_id = ?
              AND DATE(t.data) BETWEEN ? AND ?
        ", [$user->id, $inicio, $fim])[0];

        $receitas = (float) $totais->receitas;
        $despesas = (float) $totais->despesas;
        $saldo = $receitas - $despesas;

        // Despesas agrupadas por categoria com percentual
        $categoriasRows = DB::select("
            SELECT
                c.nome,
                c.cor,
                SUM(t.valor) AS gasto
            FROM transacoes t
            INNER JOIN categorias c ON c.id = t.categoria_id
            WHERE t.user_id = ?
              AND c.tipo = 'despesa'
              AND DATE(t.data) BETWEEN ? AND ?
            GROUP BY c.id, c.nome, c.cor
            HAVING SUM(t.valor) > 0
            ORDER BY gasto DESC, c.nome ASC
        ", [$user->id, $inicio, $fim]);

        $categorias = collect($categoriasRows)
            ->map(function ($cat) use ($despesas) {
                $gasto = (float) $cat->gasto;
                $percentual = $despesas > 0 ? round(($gasto / $despesas) * 100) : 0;

                return [
                    'nome' => $cat->nome,
                    'cor' => $cat->cor,
                    'gasto' => $gasto,
                    'percentual' => $percentual,
                ];
            })
            ->values();

        // Lançamentos do mês para exibição no PDF
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
              AND DATE(t.data) BETWEEN ? AND ?
            ORDER BY t.data DESC, t.id DESC
        ", [$user->id, $inicio, $fim]);

        $nomesMeses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        $mesLabel = $nomesMeses[$dataBase->month] . ' de ' . $dataBase->year;

        return compact('mes', 'mesLabel', 'receitas', 'despesas', 'saldo', 'categorias', 'transacoes');
    }
}

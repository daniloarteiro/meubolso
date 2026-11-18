<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $mes = $request->input('mes', now()->format('Y-m'));

        if (! preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        $dataBase = Carbon::createFromFormat('Y-m', $mes);
        $inicio = $dataBase->copy()->startOfMonth()->toDateString();
        $fim = $dataBase->copy()->endOfMonth()->toDateString();

        $receitas = (float) $user->transacoes()
            ->whereHas('categoria', fn ($q) => $q->where('tipo', 'receita'))
            ->whereBetween('data', [$inicio, $fim])
            ->sum('valor');

        $despesas = (float) $user->transacoes()
            ->whereHas('categoria', fn ($q) => $q->where('tipo', 'despesa'))
            ->whereBetween('data', [$inicio, $fim])
            ->sum('valor');

        $saldo = $receitas - $despesas;

        $gastosPorCat = $user->transacoes()
            ->whereHas('categoria', fn ($q) => $q->where('tipo', 'despesa'))
            ->whereBetween('data', [$inicio, $fim])
            ->selectRaw('categoria_id, SUM(valor) as total')
            ->groupBy('categoria_id')
            ->pluck('total', 'categoria_id');

        $categorias = $user->categorias()
            ->where('tipo', 'despesa')
            ->orderBy('nome')
            ->get()
            ->map(function ($cat) use ($gastosPorCat, $despesas) {
                $gasto = (float) ($gastosPorCat[$cat->id] ?? 0);
                $percentual = $despesas > 0 ? round(($gasto / $despesas) * 100) : 0;

                return [
                    'nome' => $cat->nome,
                    'gasto' => $gasto,
                    'percentual' => $percentual,
                ];
            })
            ->filter(fn ($c) => $c['gasto'] > 0)
            ->sortByDesc('gasto')
            ->values();

        $transacoes = $user->transacoes()
            ->with('categoria')
            ->whereBetween('data', [$inicio, $fim])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->get();

        $nomesMeses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        $mesLabel = $nomesMeses[$dataBase->month] . ' de ' . $dataBase->year;

        return compact('mes', 'mesLabel', 'receitas', 'despesas', 'saldo', 'categorias', 'transacoes');
    }
}

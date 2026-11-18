<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrcamentoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $mes = $request->input('mes', now()->format('Y-m'));

        if (! preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        $inicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth()->toDateString();
        $fim = Carbon::createFromFormat('Y-m', $mes)->endOfMonth()->toDateString();

        $gastosPorCat = $user->transacoes()
            ->whereHas('categoria', fn ($q) => $q->where('tipo', 'despesa'))
            ->whereBetween('data', [$inicio, $fim])
            ->selectRaw('categoria_id, SUM(valor) as total')
            ->groupBy('categoria_id')
            ->pluck('total', 'categoria_id');

        $metasPorCat = $user->orcamentos()
            ->where('mes_referencia', $mes)
            ->get()
            ->keyBy('categoria_id');

        $categorias = $user->categorias()
            ->where('tipo', 'despesa')
            ->orderBy('nome')
            ->get()
            ->map(function ($cat) use ($gastosPorCat, $metasPorCat) {
                $meta = $metasPorCat->get($cat->id);

                return [
                    'id' => $cat->id,
                    'nome' => $cat->nome,
                    'gasto' => (float) ($gastosPorCat[$cat->id] ?? 0),
                    'limite' => $meta ? (float) $meta->limite : null,
                    'orcamento_id' => $meta?->id,
                ];
            });

        return view('orcamentos.index', compact('categorias', 'mes'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'categoria_id' => 'required|integer',
            'limite' => 'required|numeric|min:0.01',
            'mes_referencia' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $mes = $dados['mes_referencia'] ?? now()->format('Y-m');
        $categoria = $request->user()->categorias()->findOrFail($dados['categoria_id']);

        if ($categoria->tipo !== 'despesa') {
            throw ValidationException::withMessages([
                'categoria_id' => 'Orçamentos só podem ser definidos para categorias de despesa.',
            ]);
        }

        Orcamento::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'categoria_id' => $categoria->id,
                'mes_referencia' => $mes,
            ],
            [
                'limite' => $dados['limite'],
            ]
        );

        return redirect()
            ->route('orcamentos.index', ['mes' => $mes])
            ->with('sucesso', 'Meta mensal salva com sucesso!');
    }

    public function destroy(Request $request, Orcamento $orcamento)
    {
        abort_if($orcamento->user_id !== $request->user()->id, 403);

        $mes = $orcamento->mes_referencia;
        $orcamento->delete();

        return redirect()
            ->route('orcamentos.index', ['mes' => $mes])
            ->with('sucesso', 'Meta removida com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $rows = DB::select("
            SELECT
                c.id,
                c.nome,
                COALESCE(SUM(t.valor), 0) AS gasto,
                o.limite,
                o.id AS orcamento_id
            FROM categorias c
            LEFT JOIN transacoes t
                ON t.categoria_id = c.id
               AND t.user_id = c.user_id
               AND DATE(t.data) BETWEEN ? AND ?
            LEFT JOIN orcamentos o
                ON o.categoria_id = c.id
               AND o.user_id = c.user_id
               AND o.mes_referencia = ?
            WHERE c.user_id = ?
              AND c.tipo = 'despesa'
            GROUP BY c.id, c.nome, o.limite, o.id
            ORDER BY c.nome ASC
        ", [$inicio, $fim, $mes, $user->id]);

        $categorias = collect($rows)->map(fn ($row) => [
            'id' => (int) $row->id,
            'nome' => $row->nome,
            'gasto' => (float) $row->gasto,
            'limite' => $row->limite !== null ? (float) $row->limite : null,
            'orcamento_id' => $row->orcamento_id !== null ? (int) $row->orcamento_id : null,
        ]);

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

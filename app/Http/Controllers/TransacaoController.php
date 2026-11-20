<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filtros = ['t.user_id = ?'];
        $params = [$user->id];

        $de = $request->input('de');
        $ate = $request->input('ate');
        $temDe = is_string($de) && $de !== '';
        $temAte = is_string($ate) && $ate !== '';

        $mes = $request->input('mes');
        if (! $temDe && ! $temAte && is_string($mes) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            $dataMes = Carbon::createFromFormat('!Y-m', $mes);
            $filtros[] = 'DATE(t.data) BETWEEN ? AND ?';
            $params[] = $dataMes->copy()->startOfMonth()->toDateString();
            $params[] = $dataMes->copy()->endOfMonth()->toDateString();
        } else {
            if ($temDe) {
                $filtros[] = 'DATE(t.data) >= ?';
                $params[] = $de;
            }

            if ($temAte) {
                $filtros[] = 'DATE(t.data) <= ?';
                $params[] = $ate;
            }
        }

        $tipo = $request->input('tipo');
        if (is_string($tipo) && $tipo !== '') {
            $filtros[] = 'c.tipo = ?';
            $params[] = $tipo;
        }

        $categoriaId = $request->input('categoria_id');
        if (is_scalar($categoriaId) && (string) $categoriaId !== '') {
            $filtros[] = 't.categoria_id = ?';
            $params[] = ctype_digit((string) $categoriaId)
                ? (int) $categoriaId
                : 0;
        }

        $where = implode(' AND ', $filtros);
        $porPagina = 10;
        $paginaInput = $request->input('page', 1);
        $pagina = (is_scalar($paginaInput) && ctype_digit((string) $paginaInput) && (int) $paginaInput > 0)
            ? (int) $paginaInput
            : 1;

        $total = (int) DB::select("
            SELECT COUNT(*) AS total
            FROM transacoes t
            INNER JOIN categorias c ON c.id = t.categoria_id
            WHERE {$where}
        ", $params)[0]->total;

        $ultimaPagina = max(1, (int) ceil($total / $porPagina));
        if ($pagina > $ultimaPagina) {
            $pagina = $ultimaPagina;
        }
        $offset = ($pagina - 1) * $porPagina;

        $rows = DB::select("
            SELECT
                t.id,
                t.categoria_id,
                t.descricao,
                t.valor,
                t.data,
                c.nome AS categoria_nome,
                c.cor AS categoria_cor,
                c.tipo
            FROM transacoes t
            INNER JOIN categorias c ON c.id = t.categoria_id
            WHERE {$where}
            ORDER BY t.data DESC, t.id DESC
            LIMIT ? OFFSET ?
        ", [...$params, $porPagina, $offset]);

        $transacoes = new LengthAwarePaginator(
            $rows,
            $total,
            $porPagina,
            $pagina,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $categorias = DB::select("
            SELECT id, nome, tipo, cor
            FROM categorias
            WHERE user_id = ?
            ORDER BY tipo ASC, nome ASC
        ", [$user->id]);

        return view('transacoes.index', compact('transacoes', 'categorias'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'descricao' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria_id' => 'required|integer',
        ]);

        // Garante que a categoria pertence ao usuário logado
        $categoria = $request->user()->categorias()->findOrFail($dados['categoria_id']);

        $request->user()->transacoes()->create([
            'descricao' => $dados['descricao'],
            'valor' => $dados['valor'],
            'data' => $dados['data'],
            'categoria_id' => $categoria->id,
        ]);

        return redirect()
            ->route('transacoes.index')
            ->with('sucesso', 'Transação adicionada com sucesso!');
    }

    public function update(Request $request, Transacao $transacao)
    {
        abort_if($transacao->user_id !== $request->user()->id, 403);

        $dados = $request->validate([
            'descricao' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria_id' => 'required|integer',
        ]);

        $categoria = $request->user()->categorias()->findOrFail($dados['categoria_id']);

        $transacao->update([
            'descricao' => $dados['descricao'],
            'valor' => $dados['valor'],
            'data' => $dados['data'],
            'categoria_id' => $categoria->id,
        ]);

        return redirect()
            ->route('transacoes.index')
            ->with('sucesso', 'Transação atualizada com sucesso!');
    }

    public function destroy(Request $request, Transacao $transacao)
    {
        abort_if($transacao->user_id !== $request->user()->id, 403);

        $transacao->delete();

        return redirect()
            ->route('transacoes.index')
            ->with('sucesso', 'Transação excluída com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filtros = ['t.user_id = ?'];
        $params = [$user->id];

        $de = $request->input('de');
        if (is_string($de) && $de !== '') {
            $filtros[] = 'DATE(t.data) >= ?';
            $params[] = $de;
        }

        $ate = $request->input('ate');
        if (is_string($ate) && $ate !== '') {
            $filtros[] = 'DATE(t.data) <= ?';
            $params[] = $ate;
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

        $transacoes = DB::select("
            SELECT
                t.id,
                t.categoria_id,
                t.descricao,
                t.valor,
                t.data,
                c.nome AS categoria_nome,
                c.tipo
            FROM transacoes t
            INNER JOIN categorias c ON c.id = t.categoria_id
            WHERE {$where}
            ORDER BY t.data DESC, t.id DESC
        ", $params);

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

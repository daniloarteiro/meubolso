<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use Illuminate\Http\Request;

class TransacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $transacoes = $user->transacoes()
            ->with('categoria')
            ->when($request->filled('de'), fn ($q) => $q->whereDate('data', '>=', $request->de))
            ->when($request->filled('ate'), fn ($q) => $q->whereDate('data', '<=', $request->ate))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->categoria_id))
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->get();

        $categorias = $user->categorias()
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        return view('transacoes.index', compact('transacoes', 'categorias'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'tipo' => 'required|in:receita,despesa',
            'descricao' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria_id' => 'required|integer',
        ]);

        // Garante que a categoria pertence ao usuário logado
        $categoria = $request->user()->categorias()->findOrFail($dados['categoria_id']);

        $request->user()->transacoes()->create([
            'tipo' => $dados['tipo'],
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
            'tipo' => 'required|in:receita,despesa',
            'descricao' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0.01',
            'data' => 'required|date',
            'categoria_id' => 'required|integer',
        ]);

        $categoria = $request->user()->categorias()->findOrFail($dados['categoria_id']);

        $transacao->update([
            'tipo' => $dados['tipo'],
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'user_id',
        'nome',
        'tipo',
        'cor',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transacoes(): HasMany
    {
        return $this->hasMany(Transacao::class);
    }

    public function orcamentos(): HasMany
    {
        return $this->hasMany(Orcamento::class);
    }

    // Cria as categorias iniciais quando um usuário se cadastra
    public static function criarPadraoPara(User $user): void
    {
        $padroes = [
            ['nome' => 'Alimentação', 'tipo' => 'despesa', 'cor' => '#15803d'],
            ['nome' => 'Transporte',  'tipo' => 'despesa', 'cor' => '#4ade80'],
            ['nome' => 'Mensalidade', 'tipo' => 'despesa', 'cor' => '#86efac'],
            ['nome' => 'Lazer',       'tipo' => 'despesa', 'cor' => '#f59e0b'],
            ['nome' => 'Outros',      'tipo' => 'despesa', 'cor' => '#94a3b8'],
            ['nome' => 'Salário',     'tipo' => 'receita', 'cor' => '#16a34a'],
            ['nome' => 'Freelance',   'tipo' => 'receita', 'cor' => '#22c55e'],
        ];

        foreach ($padroes as $item) {
            $user->categorias()->firstOrCreate(
                ['nome' => $item['nome']],
                ['tipo' => $item['tipo'], 'cor' => $item['cor']]
            );
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transacao extends Model
{
    protected $table = 'transacoes';

    protected $fillable = [
        'user_id',
        'categoria_id',
        'descricao',
        'valor',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Tipo derivado da categoria vinculada (fonte única da verdade)
    public function getTipoAttribute(): ?string
    {
        return $this->categoria?->tipo;
    }
}

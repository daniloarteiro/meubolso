<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orcamento extends Model
{
    protected $table = 'orcamentos';

    protected $fillable = [
        'user_id',
        'categoria_id',
        'mes_referencia',
        'limite',
    ];

    protected function casts(): array
    {
        return [
            'limite' => 'decimal:2',
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
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->string('mes_referencia', 7); // formato YYYY-MM
            $table->decimal('limite', 10, 2);
            $table->timestamps();

            $table->unique(['user_id', 'categoria_id', 'mes_referencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};

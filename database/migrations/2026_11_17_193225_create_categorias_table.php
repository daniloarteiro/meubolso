<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nome', 80);
            $table->string('tipo', 20)->default('despesa'); // receita ou despesa
            $table->string('cor', 20)->default('#15803d');
            $table->timestamps();

            $table->unique(['user_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};

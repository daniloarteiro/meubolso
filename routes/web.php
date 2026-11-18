<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\PainelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\TransacaoController;
use Illuminate\Support\Facades\Route;

// Redirecionamento inicial
Route::redirect('/', '/painel');

// Alias em português para a tela de cadastro do Breeze
Route::get('/cadastro', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('cadastro');

// ---------- ROTAS DO SISTEMA ----------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('painel.index'))->name('dashboard');

    Route::get('/painel', [PainelController::class, 'index'])->name('painel.index');

    Route::get('/transacoes', [TransacaoController::class, 'index'])->name('transacoes.index');
    Route::post('/transacoes', [TransacaoController::class, 'store'])->name('transacoes.store');
    Route::put('/transacoes/{transacao}', [TransacaoController::class, 'update'])->name('transacoes.update');
    Route::delete('/transacoes/{transacao}', [TransacaoController::class, 'destroy'])->name('transacoes.destroy');

    Route::get('/orcamentos', [OrcamentoController::class, 'index'])->name('orcamentos.index');
    Route::post('/orcamentos', [OrcamentoController::class, 'store'])->name('orcamentos.store');
    Route::delete('/orcamentos/{orcamento}', [OrcamentoController::class, 'destroy'])->name('orcamentos.destroy');

    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/relatorios/pdf', [RelatorioController::class, 'pdf'])->name('relatorios.pdf');

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

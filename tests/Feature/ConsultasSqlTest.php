<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Orcamento;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultasSqlTest extends TestCase
{
    use RefreshDatabase;

    public function test_painel_usa_db_select_parametrizado_e_isola_usuarios(): void
    {
        $user = User::factory()->create();
        $outro = User::factory()->create();
        Categoria::criarPadraoPara($user);
        Categoria::criarPadraoPara($outro);

        $catSalario = $user->categorias()->where('nome', 'Salário')->first();
        $catAlimentacao = $user->categorias()->where('nome', 'Alimentação')->first();
        $catOutro = $outro->categorias()->where('nome', 'Salário')->first();

        $hoje = now()->format('Y-m-d');
        $mesAnterior = now()->subMonthNoOverflow()->startOfMonth()->addDays(4)->format('Y-m-d');

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'descricao' => 'Salário atual',
            'valor' => 2000.00,
            'data' => $hoje,
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Supermercado',
            'valor' => 350.50,
            'data' => $hoje,
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'descricao' => 'Salário anterior',
            'valor' => 1500.00,
            'data' => $mesAnterior,
        ]);

        // Dados de outro usuário não devem interferir
        Transacao::create([
            'user_id' => $outro->id,
            'categoria_id' => $catOutro->id,
            'descricao' => 'Receita alheia',
            'valor' => 9999.00,
            'data' => $hoje,
        ]);

        $res = $this->actingAs($user)->get(route('painel.index'));
        $res->assertOk()
            ->assertViewHas('receitas', 2000.0)
            ->assertViewHas('despesas', 350.5)
            ->assertViewHas('saldo', 1649.5)
            ->assertSee('Supermercado')
            ->assertDontSee('Receita alheia');

        $cats = collect($res->viewData('categorias'))->keyBy('nome');
        $this->assertSame(350.5, $cats->get('Alimentação')['gasto']);
        $this->assertSame(0.0, $cats->get('Transporte')['gasto']);
        $this->assertFalse($cats->has('Salário'));

        $meses = $res->viewData('meses');
        $this->assertCount(6, $meses);
        $this->assertSame(1500.0, $meses[4]['receitas']);
        $this->assertSame(2000.0, $meses[5]['receitas']);
        $this->assertSame(350.5, $meses[5]['despesas']);
    }

    public function test_painel_retorna_zeros_em_estado_vazio(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);

        $res = $this->actingAs($user)->get(route('painel.index'));
        $res->assertOk()
            ->assertViewHas('receitas', 0.0)
            ->assertViewHas('despesas', 0.0)
            ->assertViewHas('saldo', 0.0)
            ->assertSee('Nenhuma transação cadastrada ainda.');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Orcamento;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeuBolsoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_nao_acessa_rotas_internas(): void
    {
        $this->get('/painel')->assertRedirect('/login');
        $this->get('/transacoes')->assertRedirect('/login');
        $this->get('/orcamentos')->assertRedirect('/login');
        $this->get('/relatorios')->assertRedirect('/login');
    }

    public function test_cadastro_cria_categorias_padrao(): void
    {
        $this->post('/register', [
            'name' => 'Maria Silva',
            'email' => 'maria@exemplo.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertRedirect(route('painel.index', absolute: false));

        $user = User::where('email', 'maria@exemplo.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->categorias()->count() >= 5);
    }

    public function test_usuario_pode_criar_editar_filtrar_e_excluir_transacao(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $catAlimentacao = $user->categorias()->where('nome', 'Alimentação')->first();
        $catSalario = $user->categorias()->where('nome', 'Salário')->first();

        // Criar despesa
        $this->actingAs($user)->post(route('transacoes.store'), [
            'tipo' => 'despesa',
            'descricao' => 'Almoço',
            'valor' => 45.50,
            'data' => now()->format('Y-m-d'),
            'categoria_id' => $catAlimentacao->id,
        ])->assertRedirect(route('transacoes.index'));

        $transacao = Transacao::where('user_id', $user->id)->first();
        $this->assertNotNull($transacao);
        $this->assertEquals('Almoço', $transacao->descricao);

        // Editar
        $this->actingAs($user)->put(route('transacoes.update', $transacao), [
            'tipo' => 'receita',
            'descricao' => 'Estágio',
            'valor' => 1500.00,
            'data' => now()->format('Y-m-d'),
            'categoria_id' => $catSalario->id,
        ])->assertRedirect(route('transacoes.index'));

        $this->assertDatabaseHas('transacoes', [
            'id' => $transacao->id,
            'descricao' => 'Estágio',
            'tipo' => 'receita',
        ]);

        // Filtro por tipo
        $this->actingAs($user)
            ->get(route('transacoes.index', ['tipo' => 'receita']))
            ->assertOk()
            ->assertSee('Estágio');

        // Excluir
        $this->actingAs($user)
            ->delete(route('transacoes.destroy', $transacao))
            ->assertRedirect(route('transacoes.index'));

        $this->assertDatabaseMissing('transacoes', ['id' => $transacao->id]);
    }

    public function test_usuario_nao_pode_alterar_ou_excluir_transacao_de_outro(): void
    {
        $dono = User::factory()->create();
        $outro = User::factory()->create();
        Categoria::criarPadraoPara($dono);
        Categoria::criarPadraoPara($outro);

        $catDono = $dono->categorias()->first();
        $catOutro = $outro->categorias()->first();

        $transacao = Transacao::create([
            'user_id' => $dono->id,
            'categoria_id' => $catDono->id,
            'tipo' => 'despesa',
            'descricao' => 'Conta privada',
            'valor' => 100,
            'data' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($outro)->put(route('transacoes.update', $transacao), [
            'tipo' => 'despesa',
            'descricao' => 'Invadido',
            'valor' => 200,
            'data' => now()->format('Y-m-d'),
            'categoria_id' => $catOutro->id,
        ])->assertForbidden();

        $this->actingAs($outro)->delete(route('transacoes.destroy', $transacao))
            ->assertForbidden();
    }

    public function test_orcamentos_e_relatorio_pdf_funcionam(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $cat = $user->categorias()->where('nome', 'Alimentação')->first();
        $mes = now()->format('Y-m');

        $this->actingAs($user)->post(route('orcamentos.store'), [
            'categoria_id' => $cat->id,
            'limite' => 600,
            'mes_referencia' => $mes,
        ])->assertRedirect(route('orcamentos.index', ['mes' => $mes]));

        $this->assertDatabaseHas('orcamentos', [
            'user_id' => $user->id,
            'categoria_id' => $cat->id,
            'mes_referencia' => $mes,
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $cat->id,
            'tipo' => 'despesa',
            'descricao' => 'Mercado',
            'valor' => 250,
            'data' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user)->get('/painel')->assertOk()->assertSee('Mercado');
        $this->actingAs($user)->get('/orcamentos')->assertOk()->assertSee('Alimentação');
        $this->actingAs($user)->get('/relatorios')->assertOk()->assertSee('250,00');

        $pdf = $this->actingAs($user)->get(route('relatorios.pdf', ['mes' => $mes]));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('content-type'));
    }

    public function test_painel_e_relatorio_derivam_receitas_e_despesas_da_categoria(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $catSalario = $user->categorias()->where('nome', 'Salário')->first();
        $catAlimentacao = $user->categorias()->where('nome', 'Alimentação')->first();
        $hoje = now()->format('Y-m-d');

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'tipo' => 'despesa', // propositalmente divergente para provar derivacao pela categoria
            'descricao' => 'Bolsa estágio',
            'valor' => 1500.00,
            'data' => $hoje,
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'tipo' => 'receita', // propositalmente divergente para provar derivacao pela categoria
            'descricao' => 'Jantar',
            'valor' => 250.00,
            'data' => $hoje,
        ]);

        $resPainel = $this->actingAs($user)->get('/painel');
        $resPainel->assertOk()
            ->assertViewHas('receitas', 1500.0)
            ->assertViewHas('despesas', 250.0)
            ->assertViewHas('saldo', 1250.0);
    }
}

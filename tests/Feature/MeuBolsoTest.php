<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Orcamento;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

        // Criar despesa sem enviar tipo redundante
        $this->actingAs($user)->post(route('transacoes.store'), [
            'descricao' => 'Almoço',
            'valor' => 45.50,
            'data' => now()->format('Y-m-d'),
            'categoria_id' => $catAlimentacao->id,
        ])->assertRedirect(route('transacoes.index'));

        $transacao = Transacao::where('user_id', $user->id)->first();
        $this->assertNotNull($transacao);
        $this->assertEquals('Almoço', $transacao->descricao);
        $this->assertEquals('despesa', $transacao->tipo);

        // Editar mudando para categoria de receita
        $this->actingAs($user)->put(route('transacoes.update', $transacao), [
            'descricao' => 'Estágio',
            'valor' => 1500.00,
            'data' => now()->format('Y-m-d'),
            'categoria_id' => $catSalario->id,
        ])->assertRedirect(route('transacoes.index'));

        $this->assertDatabaseHas('transacoes', [
            'id' => $transacao->id,
            'descricao' => 'Estágio',
            'categoria_id' => $catSalario->id,
        ]);

        // Filtro por tipo derivado da categoria (exclui despesa, inclui receita)
        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Café',
            'valor' => 12.00,
            'data' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user)
            ->get(route('transacoes.index', ['tipo' => 'receita']))
            ->assertOk()
            ->assertSee('Estágio')
            ->assertDontSee('Café');

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
            'descricao' => 'Conta privada',
            'valor' => 100,
            'data' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($outro)->put(route('transacoes.update', $transacao), [
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
            'descricao' => 'Bolsa estágio',
            'valor' => 1500.00,
            'data' => $hoje,
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Jantar',
            'valor' => 250.00,
            'data' => $hoje,
        ]);

        $resPainel = $this->actingAs($user)->get('/painel');
        $resPainel->assertOk()
            ->assertViewHas('receitas', 1500.0)
            ->assertViewHas('despesas', 250.0)
            ->assertViewHas('saldo', 1250.0);

        $mes = now()->format('Y-m');
        $resRelatorio = $this->actingAs($user)->get(route('relatorios.index', ['mes' => $mes]));
        $resRelatorio->assertOk()
            ->assertViewHas('receitas', 1500.0)
            ->assertViewHas('despesas', 250.0)
            ->assertViewHas('saldo', 1250.0);

        $pdf = $this->actingAs($user)->get(route('relatorios.pdf', ['mes' => $mes]));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('content-type'));
    }

    public function test_orcamento_rejeita_categoria_de_receita(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $catSalario = $user->categorias()->where('nome', 'Salário')->first();
        $mes = now()->format('Y-m');

        $this->actingAs($user)
            ->post(route('orcamentos.store'), [
                'categoria_id' => $catSalario->id,
                'limite' => 500,
                'mes_referencia' => $mes,
            ])
            ->assertSessionHasErrors('categoria_id');

        $this->assertDatabaseMissing('orcamentos', [
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'mes_referencia' => $mes,
        ]);
    }

    public function test_excluir_orcamento_preserva_transacoes_da_categoria(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $catAlimentacao = $user->categorias()->where('nome', 'Alimentação')->first();
        $mes = now()->format('Y-m');

        $orcamento = Orcamento::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'mes_referencia' => $mes,
            'limite' => 600,
        ]);

        $transacao = Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Supermercado',
            'valor' => 100,
            'data' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user)
            ->delete(route('orcamentos.destroy', $orcamento))
            ->assertRedirect(route('orcamentos.index', ['mes' => $mes]));

        $this->assertDatabaseMissing('orcamentos', ['id' => $orcamento->id]);
        $this->assertDatabaseHas('transacoes', ['id' => $transacao->id]);
    }

    public function test_schema_normalizado_sem_tipo_em_transacoes_e_com_indices_esperados(): void
    {
        $this->assertFalse(Schema::hasColumn('transacoes', 'tipo'));
        $this->assertTrue(Schema::hasColumn('transacoes', 'categoria_id'));

        $orcamentoIndexes = collect(Schema::getIndexes('orcamentos'));
        $this->assertTrue(
            $orcamentoIndexes->contains(fn ($idx) => $idx['columns'] === ['user_id', 'mes_referencia']),
            'Índice (user_id, mes_referencia) não encontrado em orcamentos.'
        );
        $this->assertTrue(
            $orcamentoIndexes->contains(fn ($idx) => $idx['unique'] && $idx['columns'] === ['user_id', 'categoria_id', 'mes_referencia']),
            'Unique (user_id, categoria_id, mes_referencia) não encontrada em orcamentos.'
        );

        $user = User::factory()->create();

        // Mesmo nome permitido em tipos distintos; duplicado no mesmo tipo bloqueado
        $catDespesa = Categoria::create(['user_id' => $user->id, 'nome' => 'Reembolso', 'tipo' => 'despesa', 'cor' => '#111111']);
        $catReceita = Categoria::create(['user_id' => $user->id, 'nome' => 'Reembolso', 'tipo' => 'receita', 'cor' => '#222222']);
        $this->assertNotEquals($catDespesa->id, $catReceita->id);

        $this->expectException(QueryException::class);
        Categoria::create(['user_id' => $user->id, 'nome' => 'Reembolso', 'tipo' => 'despesa', 'cor' => '#333333']);
    }

    public function test_categoria_com_transacoes_nao_pode_ser_excluida(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $cat = $user->categorias()->where('nome', 'Alimentação')->first();

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $cat->id,
            'descricao' => 'Lanche',
            'valor' => 20,
            'data' => now()->format('Y-m-d'),
        ]);

        $this->expectException(QueryException::class);
        $cat->delete();
    }
}

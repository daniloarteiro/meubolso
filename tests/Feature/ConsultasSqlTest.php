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

    public function test_relatorio_e_pdf_usam_db_select_parametrizado_e_isolam_usuarios(): void
    {
        $user = User::factory()->create();
        $outro = User::factory()->create();
        Categoria::criarPadraoPara($user);
        Categoria::criarPadraoPara($outro);

        $catSalario = $user->categorias()->where('nome', 'Salário')->first();
        $catAlimentacao = $user->categorias()->where('nome', 'Alimentação')->first();
        $catTransporte = $user->categorias()->where('nome', 'Transporte')->first();
        $catOutro = $outro->categorias()->where('nome', 'Alimentação')->first();

        $hoje = now()->format('Y-m-d');
        $mes = now()->format('Y-m');

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'descricao' => 'Freela API',
            'valor' => 1000.00,
            'data' => $hoje,
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Almoço',
            'valor' => 300.00,
            'data' => $hoje,
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catTransporte->id,
            'descricao' => 'Passagem',
            'valor' => 100.00,
            'data' => $hoje,
        ]);

        Transacao::create([
            'user_id' => $outro->id,
            'categoria_id' => $catOutro->id,
            'descricao' => 'Gasto de outro usuário',
            'valor' => 5000.00,
            'data' => $hoje,
        ]);

        $res = $this->actingAs($user)->get(route('relatorios.index', ['mes' => $mes]));
        $res->assertOk()
            ->assertViewHas('receitas', 1000.0)
            ->assertViewHas('despesas', 400.0)
            ->assertViewHas('saldo', 600.0)
            ->assertSee('Alimentação')
            ->assertSee('Transporte');

        $cats = $res->viewData('categorias');
        $this->assertCount(2, $cats);
        $this->assertSame('Alimentação', $cats[0]['nome']);
        $this->assertSame(300.0, $cats[0]['gasto']);
        $this->assertSame(75.0, (float) $cats[0]['percentual']);
        $this->assertSame(25.0, (float) $cats[1]['percentual']);

        $pdf = $this->actingAs($user)->get(route('relatorios.pdf', ['mes' => $mes]));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('content-type'));
    }

    public function test_relatorio_trata_mes_vazio_sem_divisao_por_zero(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $mes = now()->format('Y-m');

        $res = $this->actingAs($user)->get(route('relatorios.index', ['mes' => $mes]));
        $res->assertOk()
            ->assertViewHas('receitas', 0.0)
            ->assertViewHas('despesas', 0.0)
            ->assertViewHas('saldo', 0.0)
            ->assertSee('Nenhuma despesa registrada neste mês.');

        $this->assertCount(0, $res->viewData('categorias'));
        $this->assertCount(0, $res->viewData('transacoes'));

        $pdf = $this->actingAs($user)->get(route('relatorios.pdf', ['mes' => $mes]));
        $pdf->assertOk();
    }

    public function test_transacoes_index_filtra_por_periodo_tipo_categoria_e_isola_usuarios(): void
    {
        $user = User::factory()->create();
        $outro = User::factory()->create();
        Categoria::criarPadraoPara($user);
        Categoria::criarPadraoPara($outro);

        $catAlimentacao = $user->categorias()->where('nome', 'Alimentação')->first();
        $catTransporte = $user->categorias()->where('nome', 'Transporte')->first();
        $catSalario = $user->categorias()->where('nome', 'Salário')->first();
        $catOutro = $outro->categorias()->where('nome', 'Alimentação')->first();

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Compra antiga',
            'valor' => 50,
            'data' => '2026-10-10',
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Compra no período',
            'valor' => 120,
            'data' => '2026-11-15',
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catTransporte->id,
            'descricao' => 'Uber no período',
            'valor' => 35,
            'data' => '2026-11-18',
        ]);

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'descricao' => 'Salário novembro',
            'valor' => 2500,
            'data' => '2026-11-05',
        ]);

        Transacao::create([
            'user_id' => $outro->id,
            'categoria_id' => $catOutro->id,
            'descricao' => 'Lançamento privado alheio',
            'valor' => 999,
            'data' => '2026-11-15',
        ]);

        // Filtro por período (de/ate) e tipo despesa
        $resPeriodo = $this->actingAs($user)->get(route('transacoes.index', [
            'de' => '2026-11-01',
            'ate' => '2026-11-30',
            'tipo' => 'despesa',
        ]));
        $resPeriodo->assertOk()
            ->assertSee('Compra no período')
            ->assertSee('Uber no período')
            ->assertDontSee('Compra antiga')
            ->assertDontSee('Salário novembro')
            ->assertDontSee('Lançamento privado alheio');

        // Verifica ordenação data DESC, id DESC
        $rows = $resPeriodo->viewData('transacoes');
        $this->assertCount(2, $rows);
        $this->assertSame('Uber no período', $rows[0]->descricao);
        $this->assertSame('Compra no período', $rows[1]->descricao);

        // Filtro por categoria_id específica
        $resCat = $this->actingAs($user)->get(route('transacoes.index', [
            'categoria_id' => $catAlimentacao->id,
        ]));
        $resCat->assertOk()
            ->assertSee('Compra antiga')
            ->assertSee('Compra no período')
            ->assertDontSee('Uber no período');
    }

    public function test_transacoes_index_neutraliza_payload_de_sql_injection_nos_filtros(): void
    {
        $user = User::factory()->create();
        $outro = User::factory()->create();
        Categoria::criarPadraoPara($user);
        Categoria::criarPadraoPara($outro);

        $catOutro = $outro->categorias()->first();
        Transacao::create([
            'user_id' => $outro->id,
            'categoria_id' => $catOutro->id,
            'descricao' => 'Dado secreto alheio',
            'valor' => 500,
            'data' => '2026-11-18',
        ]);

        $res = $this->actingAs($user)->get(route('transacoes.index', [
            'de' => "2026-01-01' OR '1'='1",
            'tipo' => "despesa' OR '1'='1",
            'categoria_id' => '1 OR 1=1',
        ]));

        $res->assertOk()
            ->assertDontSee('Dado secreto alheio');
        $this->assertCount(0, $res->viewData('transacoes'));
    }
}

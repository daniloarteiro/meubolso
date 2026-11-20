<?php

namespace Tests\Feature;

use App\Mail\ConfirmarCadastroMail;
use App\Models\Categoria;
use App\Models\Orcamento;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        Mail::fake();

        $this->post('/register', [
            'name' => 'Maria Silva',
            'email' => 'maria.silva@gmail.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertRedirect(route('cadastro.aguardando', absolute: false));

        // Antes de clicar no link, nada é gravado no banco
        $this->assertDatabaseMissing('users', ['email' => 'maria.silva@gmail.com']);

        // Permite reenviar link antes da confirmação
        $this->post(route('cadastro.reenviar'))
            ->assertSessionHas('status', 'verification-link-sent');

        $urlConfirmacao = null;
        Mail::assertSent(ConfirmarCadastroMail::class, function (ConfirmarCadastroMail $mail) use (&$urlConfirmacao) {
            $urlConfirmacao = $mail->urlConfirmacao;

            return $mail->hasTo('maria.silva@gmail.com');
        });

        // Token adulterado não cria usuário
        $this->get($urlConfirmacao.'x')->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'maria.silva@gmail.com']);

        // Após confirmar no link, usuário e categorias padrão são criados
        $this->get($urlConfirmacao)->assertRedirect(route('painel.index', absolute: false));

        $user = User::where('email', 'maria.silva@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->categorias()->count() >= 5);

        // Usuário com e-mail pendente de verificação (ex.: após trocar no perfil) é bloqueado nas rotas financeiras
        $naoVerificado = User::factory()->unverified()->create();
        $this->actingAs($naoVerificado)->get('/painel')->assertRedirect(route('verification.notice'));
        $this->actingAs($naoVerificado)->get('/transacoes')->assertRedirect(route('verification.notice'));
        $this->actingAs($naoVerificado)->get('/orcamentos')->assertRedirect(route('verification.notice'));
        $this->actingAs($naoVerificado)->get('/relatorios')->assertRedirect(route('verification.notice'));
    }

    public function test_cadastro_rejeita_email_invalido_ou_dominio_inexistente(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Teste Invalido',
            'email' => 'naoexiste@dominio-inexistente-meubolso-99999.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertSessionHasErrors('email');

        $this->from('/register')->post('/register', [
            'name' => 'Teste Sem Tld',
            'email' => 'alguem@abc',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'naoexiste@dominio-inexistente-meubolso-99999.com']);
        $this->assertDatabaseMissing('users', ['email' => 'alguem@abc']);
    }

    public function test_perfil_rejeita_email_com_dominio_inexistente(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => $user->name,
            'email' => 'novo@dominio-inexistente-meubolso-99999.com',
        ])->assertSessionHasErrors('email');

        $this->assertNotSame('novo@dominio-inexistente-meubolso-99999.com', $user->fresh()->email);
    }

    public function test_mensagens_de_validacao_aparecem_traduzidas_em_portugues(): void
    {
        app()->setLocale('pt_BR');

        $response = $this->from('/cadastro')->post('/register', [
            'name' => 'Arteiro',
            'email' => 'testeteste@gmail.com',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertRedirect('/cadastro');
        $response->assertSessionHasErrors('password');

        $erros = session('errors')->get('password');
        $this->assertNotEmpty($erros);
        $this->assertStringNotContainsString('validation.', implode(' ', $erros));
        $this->assertStringContainsString('8 caracteres', implode(' ', $erros));
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

        $this->actingAs($user)
            ->get(route('transacoes.index', ['tipo' => 'despesa']))
            ->assertOk()
            ->assertSee('Café')
            ->assertDontSee('Estágio');

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

        $painelCats = collect($resPainel->viewData('categorias'))->keyBy('nome');
        $this->assertEquals(250.0, $painelCats->get('Alimentação')['gasto']);
        $this->assertFalse($painelCats->has('Salário'));

        $ultimoMes = collect($resPainel->viewData('meses'))->last();
        $this->assertEquals(1500.0, $ultimoMes['receitas']);
        $this->assertEquals(250.0, $ultimoMes['despesas']);

        $mes = now()->format('Y-m');
        $resOrcamento = $this->actingAs($user)->get(route('orcamentos.index', ['mes' => $mes]));
        $resOrcamento->assertOk()->assertSee('250,00');
        $orcCats = collect($resOrcamento->viewData('categorias'))->keyBy('nome');
        $this->assertEquals(250.0, $orcCats->get('Alimentação')['gasto']);
        $this->assertFalse($orcCats->has('Salário'));

        $resRelatorio = $this->actingAs($user)->get(route('relatorios.index', ['mes' => $mes]));
        $resRelatorio->assertOk()
            ->assertViewHas('receitas', 1500.0)
            ->assertViewHas('despesas', 250.0)
            ->assertViewHas('saldo', 1250.0);
        $relCats = collect($resRelatorio->viewData('categorias'))->keyBy('nome');
        $this->assertEquals(250.0, $relCats->get('Alimentação')['gasto']);

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

        $categoriaCol = collect(Schema::getColumns('transacoes'))->firstWhere('name', 'categoria_id');
        $this->assertNotNull($categoriaCol);
        $this->assertFalse($categoriaCol['nullable']);

        $categoriaFk = collect(Schema::getForeignKeys('transacoes'))
            ->first(fn ($fk) => $fk['columns'] === ['categoria_id'] && $fk['foreign_table'] === 'categorias');
        $this->assertNotNull($categoriaFk);
        $this->assertEquals('restrict', strtolower($categoriaFk['on_delete']));

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

        // Garante NOT NULL em categoria_id no banco
        try {
            Transacao::create([
                'user_id' => $user->id,
                'categoria_id' => null,
                'descricao' => 'Sem categoria',
                'valor' => 10,
                'data' => now()->format('Y-m-d'),
            ]);
            $this->fail('Esperava QueryException ao inserir transação sem categoria_id.');
        } catch (QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // Mesmo nome permitido em tipos distintos; duplicado no mesmo tipo bloqueado
        $catDespesa = Categoria::create(['user_id' => $user->id, 'nome' => 'Reembolso', 'tipo' => 'despesa', 'cor' => '#111111']);
        $catReceita = Categoria::create(['user_id' => $user->id, 'nome' => 'Reembolso', 'tipo' => 'receita', 'cor' => '#222222']);
        $this->assertNotEquals($catDespesa->id, $catReceita->id);

        // Garante ON DELETE RESTRICT quando há transação vinculada
        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catDespesa->id,
            'descricao' => 'Compra teste',
            'valor' => 25,
            'data' => now()->format('Y-m-d'),
        ]);
        try {
            $catDespesa->delete();
            $this->fail('Esperava QueryException por ON DELETE RESTRICT.');
        } catch (QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

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

    public function test_modal_de_transacoes_renderiza_atributos_para_filtro_por_tipo(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $catSalario = $user->categorias()->where('nome', 'Salário')->first();

        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'descricao' => 'Salário mensal',
            'valor' => 2000,
            'data' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('transacoes.index'));
        $response->assertOk()
            ->assertSee('data-tipo="despesa"', false)
            ->assertSee('data-tipo="receita"', false)
            ->assertSee('data-categoria="'.$catSalario->id.'"', false);
    }

    public function test_barra_de_navegacao_exibe_link_para_meu_perfil(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);

        $this->actingAs($user)
            ->get(route('painel.index'))
            ->assertOk()
            ->assertSee('href="'.route('profile.edit').'"', false);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dados da conta')
            ->assertSee('Alterar senha')
            ->assertSee('Excluir conta');
    }

    public function test_transacoes_pagina_ate_10_itens_por_pagina_e_preserva_filtros(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $cat = $user->categorias()->where('nome', 'Alimentação')->first();

        for ($i = 1; $i <= 15; $i++) {
            Transacao::create([
                'user_id' => $user->id,
                'categoria_id' => $cat->id,
                'descricao' => sprintf('Compra %02d', $i),
                'valor' => 10 + $i,
                'data' => sprintf('2026-10-%02d', $i),
            ]);
        }

        // Página 1 mostra os 10 mais recentes (Compra 15 até Compra 06)
        $this->actingAs($user)
            ->get(route('transacoes.index'))
            ->assertOk()
            ->assertSee('15 registros')
            ->assertSee('Mostrando 1 a 10 de 15 registros')
            ->assertSee('Compra 15')
            ->assertSee('Compra 06')
            ->assertDontSee('Compra 05');

        // Página 2 mostra os 5 restantes (Compra 05 até Compra 01)
        $this->actingAs($user)
            ->get(route('transacoes.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Mostrando 11 a 15 de 15 registros')
            ->assertSee('Compra 05')
            ->assertSee('Compra 01')
            ->assertDontSee('Compra 06');

        // Filtro por mês + tipo preserva parâmetros nos links de paginação
        $this->actingAs($user)
            ->get(route('transacoes.index', ['mes' => '2026-10', 'tipo' => 'despesa']))
            ->assertOk()
            ->assertSee('mes=2026-10')
            ->assertSee('tipo=despesa');
    }

    public function test_selecao_de_mes_funciona_no_painel_e_em_transacoes(): void
    {
        $user = User::factory()->create();
        Categoria::criarPadraoPara($user);
        $catAlimentacao = $user->categorias()->where('nome', 'Alimentação')->first();
        $catSalario = $user->categorias()->where('nome', 'Salário')->first();

        // Outubro de 2026
        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catSalario->id,
            'descricao' => 'Salário Outubro',
            'valor' => 2000,
            'data' => '2026-10-05',
        ]);
        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Mercado Outubro',
            'valor' => 350,
            'data' => '2026-10-12',
        ]);

        // Novembro de 2026
        Transacao::create([
            'user_id' => $user->id,
            'categoria_id' => $catAlimentacao->id,
            'descricao' => 'Jantar Novembro',
            'valor' => 120,
            'data' => '2026-11-10',
        ]);

        // Painel filtrando Outubro de 2026
        $this->actingAs($user)
            ->get(route('painel.index', ['mes' => '2026-10']))
            ->assertOk()
            ->assertSee('Outubro de 2026')
            ->assertSee('Salário Outubro')
            ->assertSee('Mercado Outubro')
            ->assertDontSee('Jantar Novembro');

        // Mês inválido no painel cai com segurança no mês atual
        $this->actingAs($user)
            ->get(route('painel.index', ['mes' => 'invalido']))
            ->assertOk();

        // Transações filtrando Outubro de 2026 via ?mes=
        $this->actingAs($user)
            ->get(route('transacoes.index', ['mes' => '2026-10']))
            ->assertOk()
            ->assertSee('2 registros')
            ->assertSee('Salário Outubro')
            ->assertSee('Mercado Outubro')
            ->assertDontSee('Jantar Novembro');
    }

    public function test_driver_brevo_envia_email_via_api_https(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.brevo.com/v3/smtp/email' => \Illuminate\Support\Facades\Http::response(['messageId' => '<teste@brevo>'], 201),
        ]);

        config([
            'mail.default' => 'brevo',
            'services.brevo.key' => 'xkeysib-teste-123',
            'mail.from.address' => 'remetente@meubolso.com',
            'mail.from.name' => 'MeuBolso',
        ]);

        \Illuminate\Support\Facades\Mail::raw('Olá do MeuBolso', function ($message) {
            $message->to('destino@example.com', 'Danilo')
                ->subject('Teste Brevo');
        });

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'xkeysib-teste-123')
                && $request['sender']['email'] === 'remetente@meubolso.com'
                && $request['to'][0]['email'] === 'destino@example.com'
                && $request['subject'] === 'Teste Brevo';
        });
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_raiz_exibe_tela_de_apresentacao_para_visitante(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Saiba para onde seu dinheiro')
            ->assertSee('Categorias prontas e coloridas para receitas e despesas.')
            ->assertSee('#como-funciona')
            ->assertSee('#recursos')
            ->assertSee(route('login'))
            ->assertSee(route('cadastro'));
    }

    public function test_raiz_exibe_tela_de_apresentacao_para_usuario_autenticado(): void
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($usuario)->get('/');

        $response->assertOk()
            ->assertSee('Saiba para onde seu dinheiro')
            ->assertSee(route('login'))
            ->assertSee(route('cadastro'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_raiz_redireciona_para_painel(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/painel');
    }
}

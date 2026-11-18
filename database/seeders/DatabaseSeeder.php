<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Orcamento;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'danilo@meubolso.com'],
            [
                'name' => 'Danilo',
                'password' => Hash::make('12345678'),
            ]
        );

        Categoria::criarPadraoPara($user);

        if ($user->transacoes()->exists()) {
            return;
        }

        $cats = $user->categorias()->pluck('id', 'nome');
        $agora = now();
        $mesAtual = $agora->format('Y-m');

        // Lançamentos do mês atual
        $lancamentos = [
            ['data' => $agora->copy()->startOfMonth()->addDays(9), 'descricao' => 'Estágio',           'categoria' => 'Salário',     'valor' => 1800.00],
            ['data' => $agora->copy()->startOfMonth()->addDays(8), 'descricao' => 'Mensalidade',       'categoria' => 'Mensalidade', 'valor' => 780.00],
            ['data' => $agora->copy()->startOfMonth()->addDays(7), 'descricao' => 'Almoço no centro',  'categoria' => 'Alimentação', 'valor' => 32.50],
            ['data' => $agora->copy()->startOfMonth()->addDays(6), 'descricao' => 'Supermercado',      'categoria' => 'Alimentação', 'valor' => 417.50],
            ['data' => $agora->copy()->startOfMonth()->addDays(5), 'descricao' => 'Site para cliente', 'categoria' => 'Freelance',   'valor' => 700.00],
            ['data' => $agora->copy()->startOfMonth()->addDays(4), 'descricao' => 'Ônibus (recarga)',  'categoria' => 'Transporte',  'valor' => 220.00],
            ['data' => $agora->copy()->startOfMonth()->addDays(2), 'descricao' => 'Cinema e lanche',   'categoria' => 'Lazer',       'valor' => 310.00],
            ['data' => $agora->copy()->startOfMonth()->addDays(1), 'descricao' => 'Papelaria',         'categoria' => 'Outros',      'valor' => 90.00],
        ];

        // Histórico de meses anteriores para o gráfico comparativo
        for ($i = 1; $i <= 5; $i++) {
            $mesPassado = $agora->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $lancamentos[] = [
                'data' => $mesPassado->copy()->addDays(4),
                'descricao' => 'Estágio',
                'categoria' => 'Salário',
                'valor' => 1800 + ($i * 100),
            ];
            $lancamentos[] = [
                'data' => $mesPassado->copy()->addDays(10),
                'descricao' => 'Despesas gerais',
                'categoria' => 'Alimentação',
                'valor' => 1500 + ($i * 90),
            ];
        }

        foreach ($lancamentos as $item) {
            Transacao::create([
                'user_id' => $user->id,
                'categoria_id' => $cats[$item['categoria']],
                'descricao' => $item['descricao'],
                'valor' => $item['valor'],
                'data' => $item['data']->toDateString(),
            ]);
        }

        // Metas mensais de exemplo
        $metas = [
            'Alimentação' => 600,
            'Transporte' => 250,
            'Mensalidade' => 800,
            'Lazer' => 300,
            'Outros' => 200,
        ];

        foreach ($metas as $nome => $limite) {
            Orcamento::create([
                'user_id' => $user->id,
                'categoria_id' => $cats[$nome],
                'mes_referencia' => $mesAtual,
                'limite' => $limite,
            ]);
        }
    }
}

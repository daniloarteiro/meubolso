<?php

namespace App\Http\Controllers;

class AjudaController extends Controller
{
    public function index()
    {
        $secoes = [
            [
                'id' => 'primeiros-passos',
                'titulo' => 'Primeiros passos',
                'icone' => 'bi-compass',
                'itens' => [
                    [
                        'pergunta' => 'O que é o MeuBolso e como ele funciona?',
                        'resposta' => 'O MeuBolso é um sistema simples de controle financeiro pessoal. Nele você registra suas receitas (entradas) e despesas (saídas), define limites mensais por categoria e acompanha o resumo do mês com gráficos e relatórios em PDF.',
                    ],
                    [
                        'pergunta' => 'Como criar uma conta e confirmar meu e-mail?',
                        'resposta' => 'Na tela de cadastro, informe seu nome, e-mail e uma senha com pelo menos 8 caracteres. Enviaremos um link de confirmação para o seu e-mail (válido por 60 minutos). Sua conta e as categorias padrão são criadas automaticamente assim que você clica nesse link.',
                    ],
                    [
                        'pergunta' => 'O que aparece na tela Visão Geral (Painel)?',
                        'resposta' => 'A Visão Geral mostra os três indicadores principais do mês selecionado (Saldo do mês, Total de receitas e Total de despesas), as 5 transações mais recentes, um gráfico de rosca com a divisão das despesas por categoria e um gráfico de barras comparando receitas e despesas dos últimos 6 meses.',
                    ],
                    [
                        'pergunta' => 'Como consultar meses anteriores ou futuros no Painel?',
                        'resposta' => 'No topo da tela Visão Geral existe o filtro "Mês". Escolha o mês desejado e clique em "Ver" para atualizar os valores, a lista resumida e os gráficos para aquele período. Você também pode clicar em "Voltar ao mês atual" quando estiver vendo outro mês.',
                    ],
                ],
            ],
            [
                'id' => 'transacoes',
                'titulo' => 'Transações e Categorias',
                'icone' => 'bi-arrow-left-right',
                'itens' => [
                    [
                        'pergunta' => 'Como lançar uma nova receita ou despesa?',
                        'resposta' => 'Acesse a aba "Transações" e clique em "Nova transação". Preencha a descrição, o valor, a data e escolha a categoria. O sistema identifica automaticamente se o lançamento é uma receita ou uma despesa pela categoria selecionada.',
                    ],
                    [
                        'pergunta' => 'Quais categorias já vêm prontas no sistema?',
                        'resposta' => 'Ao confirmar sua conta, o MeuBolso cria 7 categorias iniciais: Alimentação, Transporte, Mensalidade, Lazer e Outros (para despesas) e Salário e Freelance (para receitas).',
                    ],
                    [
                        'pergunta' => 'Como editar, excluir ou filtrar minhas transações?',
                        'resposta' => 'Na tela "Transações", cada linha da tabela possui botões de editar e excluir à direita. Acima da tabela você pode filtrar por data inicial ("De"), data final ("Até"), tipo (Receita ou Despesa) e categoria. A listagem é paginada de 10 em 10 lançamentos.',
                    ],
                ],
            ],
            [
                'id' => 'orcamentos',
                'titulo' => 'Orçamentos e Alertas',
                'icone' => 'bi-bullseye',
                'itens' => [
                    [
                        'pergunta' => 'Como funcionam as metas mensais de orçamento?',
                        'resposta' => 'Na aba "Orçamentos", você pode definir um limite de gasto mensal para cada categoria de despesa (por exemplo, Alimentação ou Lazer). O sistema soma automaticamente todas as despesas lançadas naquela categoria dentro do mês selecionado e compara com o limite.',
                    ],
                    [
                        'pergunta' => 'Como funcionam os alertas de 80% e 100%?',
                        'resposta' => 'A barra de progresso muda de cor conforme o consumo da meta no mês: verde enquanto estiver abaixo de 80%, amarela com o aviso "Atenção" entre 80% e 99%, e vermelha com o status "Ultrapassada" ao atingir 100% ou mais, exibindo exatamente quanto foi excedido.',
                    ],
                    [
                        'pergunta' => 'Se eu excluir um orçamento, minhas transações são apagadas?',
                        'resposta' => 'Não. Remover uma meta apaga apenas o limite planejado daquela categoria para o mês selecionado. Todas as transações já cadastradas continuam salvas normalmente.',
                    ],
                ],
            ],
            [
                'id' => 'relatorios',
                'titulo' => 'Relatórios e PDF',
                'icone' => 'bi-bar-chart-line',
                'itens' => [
                    [
                        'pergunta' => 'Como gerar o relatório do mês e baixar em PDF?',
                        'resposta' => 'Acesse a aba "Relatórios", selecione o mês de referência no topo e clique em "Ver". Para salvar ou imprimir, clique no botão "Exportar PDF": o sistema gera e baixa um arquivo com o resumo do mês (receitas, despesas e saldo), o detalhamento por categoria e a lista de lançamentos do período.',
                    ],
                ],
            ],
            [
                'id' => 'conta',
                'titulo' => 'Conta e Segurança',
                'icone' => 'bi-shield-check',
                'itens' => [
                    [
                        'pergunta' => 'Como alterar meu nome, e-mail ou senha?',
                        'resposta' => 'Clique no seu nome no canto superior direito da barra de navegação para abrir a tela "Meu Perfil". Lá você pode atualizar nome e e-mail, definir uma nova senha ou excluir sua conta. Caso altere o e-mail, será necessário confirmar o novo endereço pelo link enviado.',
                    ],
                    [
                        'pergunta' => 'Esqueci minha senha. Como recuperar o acesso?',
                        'resposta' => 'Na tela de login, clique em "Esqueci minha senha", informe o e-mail cadastrado e enviaremos um link para você cadastrar uma nova senha com segurança.',
                    ],
                ],
            ],
        ];

        return view('ajuda.index', compact('secoes'));
    }
}

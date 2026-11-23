<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>MeuBolso – Saiba para onde seu dinheiro vai</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icone-meubolso.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/apresentacao.css') }}" rel="stylesheet">
</head>
<body>
    <div class="w">
        <header class="top">
            <a href="{{ url('/') }}" class="lg">
                <img src="{{ asset('images/logo-meubolso.svg') }}" alt="" width="38" height="41" aria-hidden="true">
                <span class="lg-texto"><span class="lg-meu">Meu</span><span class="lg-bolso">Bolso</span></span>
            </a>

            <nav class="nv">
                <a href="#recursos">Recursos</a>
                <a href="#como-funciona">Como funciona</a>
                <a href="{{ route('login') }}" class="b b3" style="padding:10px 22px">Entrar</a>
            </nav>
        </header>

        <section class="hero">
            <div>
                <span class="pill">Controle de gastos pessoais</span>
                <h1>Saiba para onde seu dinheiro <span class="st">vai.</span></h1>
                <p class="hero-sub">
                    Anote o que entra e o que sai, ponha um limite em cada categoria e veja o mês inteiro em uma tela. Simples como olhar dentro do bolso.
                </p>

                <div class="hero-acoes">
                    <a href="{{ route('login') }}" class="b b1">
                        Entrar no MeuBolso <span class="hero-seta">→</span>
                    </a>
                    <a href="{{ route('cadastro') }}" class="b b3">Criar conta</a>
                </div>

                <p class="hero-nota">Feito por estudantes, para quem vive de orçamento apertado.</p>
            </div>

            <div class="hero-ilustracao">
                <div class="bolso-palco">
                    <div class="bolso-cartao">
                        <div class="bolso-mes">Novembro de 2026</div>
                        <div class="bolso-rotulo">Saldo do mês</div>
                        <div class="bolso-saldo">R$ 650,00</div>
                        <div class="bolso-resumo">
                            <span style="color:var(--v)">+ R$ 2.500</span>
                            <span style="color:#c2373b">− R$ 1.850</span>
                        </div>
                        <div class="bolso-barras">
                            <i style="height:46px;background:var(--v)"></i>
                            <i style="height:34px;background:#f4a3a5"></i>
                            <i style="height:52px;background:var(--v)"></i>
                            <i style="height:40px;background:#f4a3a5"></i>
                            <i style="height:62px;background:var(--v)"></i>
                            <i style="height:36px;background:#f4a3a5"></i>
                        </div>
                    </div>

                    <img src="{{ asset('images/bolso.svg') }}" alt="" class="bolso-svg" aria-hidden="true">

                    <div class="bolso-meta">
                        <div class="bolso-meta-topo">
                            <span>Transporte</span>
                            <span style="color:#92400e">88% da meta</span>
                        </div>
                        <div class="pg" style="margin-top:8px">
                            <i style="width:88%;background:var(--y)"></i>
                        </div>
                    </div>

                    <div class="bolso-alerta">Lazer: 103% da meta</div>
                    <div class="bolso-moeda">$</div>
                </div>
            </div>
        </section>
    </div>

    <div class="strip">
        RECEITAS &nbsp;·&nbsp; DESPESAS &nbsp;·&nbsp; METAS &nbsp;·&nbsp; GRÁFICOS &nbsp;·&nbsp; RELATÓRIOS EM PDF &nbsp;·&nbsp; RECEITAS &nbsp;·&nbsp; DESPESAS &nbsp;·&nbsp; METAS
    </div>

    <div class="w">
        <section id="como-funciona" class="sec">
            <div class="k">Como funciona</div>
            <h2 class="big">Três hábitos. Nenhuma planilha.</h2>
            <div class="steps">
                <div>
                    <div class="n">1</div>
                    <h3>Anote</h3>
                    <p>Registre cada receita e despesa em segundos, no computador ou no celular.</p>
                </div>
                <div>
                    <div class="n">2</div>
                    <h3>Organize</h3>
                    <p>Separe por categorias do seu jeito e defina quanto pode gastar em cada uma.</p>
                </div>
                <div>
                    <div class="n">3</div>
                    <h3>Acompanhe</h3>
                    <p>Veja o saldo, os gráficos e receba o aviso antes de estourar a meta.</p>
                </div>
            </div>
        </section>

        <section id="recursos" class="sec">
            <div class="k">Recursos</div>
            <h2 class="big">Tudo o que cabe no bolso, e nada além.</h2>
            <div class="bento">
                <div class="cell c6 r2" style="background:var(--g);color:var(--c)">
                    <h3>Dashboard do mês</h3>
                    <p style="color:#cfe3d6;max-width:380px">
                        Saldo, receitas, despesas e a comparação dos últimos seis meses, tudo numa só tela.
                    </p>
                    <div class="bento-barras">
                        <i style="height:60%;background:#2f7d5d"></i>
                        <i style="height:48%;background:var(--y)"></i>
                        <i style="height:72%;background:#2f7d5d"></i>
                        <i style="height:55%;background:var(--y)"></i>
                        <i style="height:90%;background:#2f7d5d"></i>
                        <i style="height:62%;background:var(--y)"></i>
                    </div>
                </div>

                <div class="cell c6" style="background:#fff;border:2px solid #e8dfcc">
                    <h3>Metas com alerta</h3>
                    <p style="color:var(--m)">O sistema avisa quando você chega perto do limite.</p>
                    <div class="pg"><i style="width:45%;background:var(--v)"></i></div>
                    <div class="pg"><i style="width:88%;background:var(--y)"></i></div>
                    <div class="pg"><i style="width:100%;background:var(--r)"></i></div>
                </div>

                <div class="cell c3" style="background:var(--y)">
                    <h3>Categorias</h3>
                    <p>Categorias prontas e coloridas para receitas e despesas.</p>
                </div>

                <div class="cell c3" style="background:#e4efe7">
                    <h3>Relatório em PDF</h3>
                    <p style="color:var(--m)">O resumo do mês pronto para guardar.</p>
                </div>
            </div>
        </section>

        <section class="cta">
            <h2>Chegue ao fim do mês sabendo para onde o dinheiro foi.</h2>
            <a href="{{ route('login') }}" class="b b2" style="font-size:18px;padding:16px 32px">Entrar no MeuBolso →</a>
        </section>
    </div>
    @include('components.botao-ajuda')
</body>
</html>

# Registro de Progresso e Instruções - MeuBolso (Laravel)

## 1. Visão Geral e Estilo do Projeto
- **Projeto:** MeuBolso – Sistema Web de Controle de Gastos Pessoais (disciplina **Back-End Framework**, 4º período).
- **Proposta original:** `C:\Users\danil\Downloads\PROPOSTA_MeuBolso.docx`
- **Protótipo front-end (preservado intacto):** `C:\Users\danil\OneDrive\Documentos\Projetos\meubolso-front`
- **Projeto Laravel oficial:** `C:\Users\danil\OneDrive\Documentos\Projetos\meubolso`

### Regra de Estilo de Código (IMPORTANTE)
- Manter o código limpo, direto e didático no padrão **MVC clássico de projeto acadêmico (4º período)**:
  - Sem *over-engineering* (não criar Repositories, Services, DTOs, Actions ou FormRequests desnecessários).
  - Validação direta nos Controllers (`$request->validate(...)`), consultas claras com Eloquent e verificação simples de dono (`abort_if(... !== auth()->id(), 403)`).
  - Nomenclatura de domínio e comentários curtos em português, sem comentários artificiais ou excesso de PHPDoc.
  - Front-end 100% em **Blade + Bootstrap 5 (CDN) + `public/css/meubolso.css` + `public/js/painel.js`** (sem `npm`, Vite ou Tailwind).

---

## 2. Ambiente Configurado na Máquina
- **PHP 8.3.36** instalado via `winget` (`PHP.PHP.8.3`), com `php.ini` configurado (`openssl`, `pdo_sqlite`, `pdo_mysql`, `mysqli`, `mbstring`, `gd`, `zip`, `curl`, `fileinfo`, `intl`).
- **Composer 2.11.2** instalado junto ao PHP (`composer` disponível no PATH).
- **MySQL Server 8.0.46 + MySQL Workbench** instalados via MySQL Installer for Windows:
  - Serviço configurado com nome **`MySQL`**, rodando na porta **`3306`**.
  - *(Obs.: existia um serviço antigo chamado `MySQL80` que foi parado via `services.msc` para liberar a porta 3306; ele deve ficar desativado para não conflitar.)*

---

## 3. O que Já Foi Feito no `meubolso`
1. **Projeto e Pacotes:**
   - Laravel `v13.43.0`, Laravel Breeze `v2.4.2`, `barryvdh/laravel-dompdf` `v3.1.2`.
   - Limpeza concluída: removidos `package.json`, `node_modules`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js` e views/componentes Tailwind não usados.
2. **Banco de Dados (Migrations, Models e MySQL 3FN):**
   - Tabelas: `users`, `categorias` (`UNIQUE(user_id, tipo, nome)`), `transacoes` (normalizada em 3FN sem coluna `tipo` redundante; tipo derivado via `categoria_id`), `orcamentos` (`UNIQUE(user_id, categoria_id, mes_referencia)` + `INDEX(user_id, mes_referencia)`).
   - Models: `App\Models\User`, `App\Models\Categoria` (com `criarPadraoPara(User $user)`), `App\Models\Transacao` (com accessor `getTipoAttribute()`), `App\Models\Orcamento`.
   - Seeder (`DatabaseSeeder`): usuário demo `danilo@meubolso.com` / senha `12345678`, com 7 categorias padrão, 18 lançamentos de exemplo e 5 metas mensais.
   - `.env` conectado ao banco **MySQL `meubolso`** (`127.0.0.1:3306`, usuário `root`), já migrado e semeado (`php artisan migrate:fresh --seed`); `.env.example` documentado.
3. **Autenticação, Controllers e Views:**
   - Auth completa com Breeze adaptada para Bootstrap 5 (`login`, `register`/`cadastro`, `forgot-password`, `reset-password`, `confirm-password`, `verify-email`, `logout` via `POST` e `profile/edit` com link **Meu Perfil** na navbar).
   - Consultas de leitura (`SELECT`) nos 4 controllers principais convertidas para **SQL explícito parametrizado (`DB::select("SELECT ...", [...])`)** com bindings `?` (escritas `store`/`update`/`destroy` mantidas no Eloquent):
     - `PainelController`: resumo do mês (saldo, receitas, despesas), últimas 5 transações e dados para os dois gráficos (`Chart.js` em `public/js/painel.js`).
     - `TransacaoController`: listagem com filtros (`de`, `ate`, `tipo`, `categoria_id`), criação (`POST`), edição (`PUT` via modal com filtro dinâmico JS de categorias por tipo) e exclusão (`DELETE`).
     - `OrcamentoController`: metas mensais por categoria `despesa` (rejeita categoria de receita no `store`), cálculo real dos gastos do mês, alertas (80% / 100%) e remoção apenas da meta (preservando transações).
     - `RelatorioController`: resumo por mês de referência (com proteção `!Y-m` e array params) e exportação em **PDF** (`GET /relatorios/pdf` usando `relatorios/pdf.blade.php` + DomPDF).
4. **Testes Automatizados e Specs (`.specs/`):**
   - Repositório Git local inicializado (`master`), com specs verificadas em `.specs/features/normalizacao-banco-mysql/` e `.specs/features/consultas-sql-controllers/` + `.specs/STATE.md` (`AD-001`) + `.specs/LESSONS.md`.
   - `php artisan test`: **46 testes passaram** (`261` asserções), cobrindo Breeze Auth, redirecionamentos, CRUD, normalização 3FN, SQL parametrizado, isolamento entre usuários, orçamentos, PDF e edge cases.

---

## 4. Onde Paramos e Próximos Passos ao Retornar
**Status atual:** Banco MySQL `meubolso` criado, migrado e semeado; todas as telas usando `DB::select` parametrizado; suíte de 46 testes verde e verificada.

**Ao retomar amanhã:**
1. Testar o sistema manualmente no navegador com `php artisan serve` (`http://127.0.0.1:8000`, login `danilo@meubolso.com` / `12345678`) e revisar se quer algum ajuste visual ou funcional.
2. Definir com o usuário se haverá novas features (ex.: tela futura de CRUD de categorias personalizadas) ou se já partimos para revisão final/entrega (README, preparação e `git push` para o GitHub mediante autorização).

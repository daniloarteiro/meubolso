# Normalização do Banco, MER Definitivo e Conexão MySQL Tasks

## Execution Protocol (MANDATORY -- do not skip)

Implement these tasks with the `tlc-spec-driven` skill: **activate it by name and follow its Execute flow and Critical Rules.** Do not search for skill files by filesystem path. The skill is the source of truth for the full flow (per-task cycle, sub-agent delegation, the walk, check_proofs, the Verifier and the screens sub-agent).

**If the skill cannot be activated, STOP and tell the user - do not proceed without it.**

---

**Design**: skipped - alteração direta no padrão MVC Laravel existente (decisão `AD-001` em `.specs/STATE.md`)
**Status**: In Progress
**Approved**: 2026-11-18 - user: "ok vamos prosseguir então e usando a opção A eu te passo a senha na hora"
**Build**: `php artisan test`
**Run feature**: `php vendor/bin/phpunit --testdox tests/Feature/MeuBolsoTest.php`

---

## Test Coverage Matrix

> Generated from codebase, project guidelines, and spec - confirm before Execute. Guidelines found: `AGENTS.md`, `phpunit.xml`, `tests/Feature/MeuBolsoTest.php`.

| Code Layer | Required Test Type | Coverage Expectation | Location Pattern | Run Command |
| ---------- | ------------------ | -------------------- | ---------------- | ----------- |
| Migration / Schema | none | Schema validado via build gate e testes de integração | `database/migrations/*.php` | `php artisan test` |
| Model / Seeder | feature | Integridade de atributos/relações e seeder completo | `tests/Feature/MeuBolsoTest.php` | `php vendor/bin/phpunit --testdox tests/Feature/MeuBolsoTest.php` |
| Controller | feature | Rotas em escopo: happy path + edge cases + 403/404/422 | `tests/Feature/MeuBolsoTest.php` | `php vendor/bin/phpunit --testdox tests/Feature/MeuBolsoTest.php` |
| Blade View / Layout | feature | Renderização HTML, links e estado inicial; walk para JS | `tests/Feature/MeuBolsoTest.php` | `php vendor/bin/phpunit --testdox tests/Feature/MeuBolsoTest.php` |
| Config / Env | none | Variáveis validadas via build gate e `migrate --seed` no MySQL | `.env`, `.env.example` | `php artisan test` |

## Gate Check Commands

> Generated from codebase - confirm before Execute.

| Gate Level | When to Use | Command |
| ---------- | ----------- | ------- |
| Quick | Após tarefas focadas em um único arquivo/teste | `php vendor/bin/phpunit --testdox tests/Feature/MeuBolsoTest.php` |
| Full | Após tarefas de controller/integração | `php artisan test` |
| Build | Ao final de cada fase ou tarefa de schema/config | `php artisan test` |

---

## Execution Plan

Phases are ordered and run sequentially - each phase completes before the next begins, and tasks within a phase execute in order.

### Phase 1: Índices de Schema e Consultas de Leitura/Orçamento

Atualiza chaves/índices independentes e migra leituras e validações para depender de `categorias.tipo`.

```
T1 → T2 → T3 → T4 → T5
```

### Phase 2: Remoção de `transacoes.tipo`, UI e Conexão MySQL

Remove a coluna redundante `transacoes.tipo`, adiciona o filtro dinâmico no modal de transações, expõe o link de perfil e conecta o ambiente ao MySQL `meubolso`.

```
T6 → T7 → T8 → T9 → T10
```

---

## Task Breakdown

### T1: Ajustar chave única em `categorias` para `(user_id, tipo, nome)`

**What**: Substituir `unique(['user_id', 'nome'])` por `unique(['user_id', 'tipo', 'nome'])` na migration de `categorias`.
**Where**: `database/migrations/2026_11_17_193225_create_categorias_table.php`
**Depends on**: None
**Reuses**: Estrutura existente da migration `create_categorias_table`
**Requirement**: DB-02

**Done when**:

- [x] Migration define `unique(['user_id', 'tipo', 'nome'])` em `categorias`
- [x] Gate check passes: `php artisan test`
- [x] Test count: 30 tests pass (no silent deletions)

**Tests**: none
**Gate**: build

**Commit**: `refactor(db): update categorias unique key to user_id, tipo and nome`

---

### T2: Adicionar índice `(user_id, mes_referencia)` em `orcamentos`

**What**: Incluir `index(['user_id', 'mes_referencia'])` na migration de `orcamentos` mantendo a chave única existente.
**Where**: `database/migrations/2026_11_17_193227_create_orcamentos_table.php`
**Depends on**: T1
**Reuses**: Estrutura existente da migration `create_orcamentos_table`
**Requirement**: DB-03

**Done when**:

- [x] Migration define `index(['user_id', 'mes_referencia'])` além da unique `(user_id, categoria_id, mes_referencia)`
- [x] Gate check passes: `php artisan test`
- [x] Test count: 30 tests pass (no silent deletions)

**Tests**: none
**Gate**: build

**Commit**: `perf(db): add composite index on orcamentos user_id and mes_referencia`

---

### T3: Derivar tipo via `categoria` nas consultas do `PainelController`

**What**: Substituir filtros `where('tipo', ...)` em `transacoes` por `whereHas('categoria', ...)` no `PainelController` e cobrir o resumo no `MeuBolsoTest`.
**Where**: `app/Http/Controllers/PainelController.php`
**Depends on**: T2
**Reuses**: Relacionamento `Transacao::categoria()` e eager loading `with('categoria')`
**Requirement**: TRX-02

**Done when**:

- [x] Receitas, despesas e histórico de 6 meses no `PainelController` consultam `categorias.tipo` via relacionamento
- [x] Teste em `tests/Feature/MeuBolsoTest.php` valida cálculo de receitas, despesas e saldo no painel derivado da categoria
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 31 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `refactor(painel): derive transaction type from categoria relation`

---

### T4: Derivar tipo via `categoria` nas consultas do `RelatorioController`

**What**: Substituir filtros `where('tipo', ...)` em `transacoes` por `whereHas('categoria', ...)` no `RelatorioController` e cobrir em `MeuBolsoTest`.
**Where**: `app/Http/Controllers/RelatorioController.php`
**Depends on**: T3
**Reuses**: Relacionamento `Transacao::categoria()`
**Requirement**: TRX-02

**Done when**:

- [x] `RelatorioController::montarDados` consulta receitas e despesas via `categorias.tipo`
- [x] Teste em `tests/Feature/MeuBolsoTest.php` valida totais de receita/despesa no relatório e PDF derivados da categoria
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 31 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `refactor(relatorios): derive transaction type from categoria relation`

---

### T5: Restringir `OrcamentoController@store` a categorias de despesa

**What**: Validar que a `categoria_id` enviada para `POST /orcamentos` pertence ao usuário e tem `tipo === 'despesa'`, e adicionar testes de rejeição e preservação de transações ao remover meta.
**Where**: `app/Http/Controllers/OrcamentoController.php`
**Depends on**: T4
**Reuses**: Validação direta com `$request->validate()` e `withErrors` no padrão MVC do projeto
**Requirement**: ORC-01, ORC-02

**Done when**:

- [x] `OrcamentoController@store` rejeita categoria com `tipo !== 'despesa'` retornando erro de validação em `categoria_id` sem gravar no banco
- [x] Testes em `tests/Feature/MeuBolsoTest.php` cobrem rejeição de categoria de receita e preservação das transações após `DELETE /orcamentos/{orcamento}`
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 33 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `fix(orcamentos): restrict budgets to despesa categories`

---

### T6: Remover `transacoes.tipo` do schema, model `Transacao` e `TransacaoController`

**What**: Remover a coluna `tipo` da migration `create_transacoes_table`, remover `'tipo'` de `Transacao::$fillable` adicionando accessor `tipo` via `categoria`, atualizar `TransacaoController` (filtro por `whereHas('categoria')` e criação/edição apenas com `categoria_id`) e atualizar `DatabaseSeeder` e `MeuBolsoTest`.
**Where**: `app/Http/Controllers/TransacaoController.php`
**Depends on**: Phase 1
**Reuses**: Eloquent `belongsTo(Categoria::class)` e `whereHas('categoria')`
**Requirement**: DB-01, TRX-01, TRX-02, TRX-03

**Done when**:

- [x] `transacoes` não possui coluna `tipo` na migration e `Transacao` expõe `tipo` via accessor da categoria
- [x] `TransacaoController` filtra `tipo` via `categoria` e persiste transações sem `transacoes.tipo`; `DatabaseSeeder` cria lançamentos sem campo `tipo`
- [x] `tests/Feature/MeuBolsoTest.php` verifica ausência da coluna `transacoes.tipo`, criação/edição sem `tipo`, filtro por `tipo` via categoria, índices e constraint `categorias`
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 35 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `refactor(transacoes): drop redundant transacoes.tipo column and derive from categoria`

---

### T7: Filtrar categorias dinamicamente por tipo nos modais de transação

**What**: Adicionar `data-tipo` nas opções de categoria dos modais de criação e edição em `transacoes/index.blade.php` e script JS para filtrar as categorias visíveis pelo `Tipo` selecionado (pré-selecionando o tipo da categoria ao abrir edição).
**Where**: `resources/views/transacoes/index.blade.php`
**Depends on**: T6
**Reuses**: Script `.btn-editar` existente no `@push('scripts')` de `transacoes/index.blade.php`
**Requirement**: UI-01

**Done when**:

- [x] Modal de criação inicia exibindo apenas categorias compatíveis com o `Tipo` selecionado e alterna opções ao mudar entre `despesa` e `receita`
- [x] Modal de edição pré-seleciona `Tipo` igual a `transacao.categoria.tipo`, filtra opções compatíveis e mantém a `categoria_id` da transação selecionada
- [x] Teste em `tests/Feature/MeuBolsoTest.php` valida atributos `data-tipo` e `data-categoria` renderizados no HTML de `/transacoes`
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 36 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `feat(ui): filter transaction modal categories by selected tipo`

---

### T8: Adicionar link para Meu Perfil na barra de navegação

**What**: Transformar a identificação do usuário na navbar de `layouts/app.blade.php` em link clicável para `route('profile.edit')` ("Meu Perfil").
**Where**: `resources/views/layouts/app.blade.php`
**Depends on**: T7
**Reuses**: Rota nomeada `profile.edit` existente do Breeze
**Requirement**: NAV-01

**Done when**:

- [ ] Navbar autenticada renderiza link clicável apontando para `route('profile.edit')`
- [ ] Teste em `tests/Feature/MeuBolsoTest.php` valida presença do link `/profile` no `/painel` e retorno HTTP 200 em `/profile`
- [ ] Gate check passes: `php artisan test`
- [ ] Test count: >= 37 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `feat(nav): add Meu Perfil link to authenticated navbar`

---

### T9: Documentar variáveis MySQL no `.env.example`

**What**: Atualizar `.env.example` com os valores padrão documentados de conexão MySQL para `meubolso` (`127.0.0.1`, `3306`, `meubolso`, `root`, senha vazia).
**Where**: `.env.example`
**Depends on**: T8
**Reuses**: Estrutura de variáveis Laravel existente em `.env.example`
**Requirement**: ENV-01

**Done when**:

- [ ] `.env.example` contém `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=meubolso`, `DB_USERNAME=root`, `DB_PASSWORD=` sem segredos reais
- [ ] Gate check passes: `php artisan test`
- [ ] Test count: >= 37 tests pass (no silent deletions)

**Tests**: none
**Gate**: build

**Commit**: `docs(env): document mysql connection defaults in .env.example`

---

### T10: Configurar `.env` para MySQL e migrar/semear banco `meubolso`

**What**: Atualizar `.env` (ignorado pelo git) com as credenciais locais do MySQL `meubolso`, executar `php artisan migrate:fresh --seed` no MySQL e validar a aplicação no servidor local.
**Where**: `.env`
**Depends on**: T9
**Reuses**: `DatabaseSeeder` e serviço `MySQL` ativo na porta `3306`
**Requirement**: ENV-01, ENV-02

**Done when**:

- [ ] `.env` aponta para `DB_CONNECTION=mysql` e `DB_DATABASE=meubolso`
- [ ] `php artisan migrate:fresh --seed` executa sem erros no MySQL `meubolso` criando tabelas e usuário demo `danilo@meubolso.com`
- [ ] Gate check passes: `php artisan test`
- [ ] Test count: >= 37 tests pass (no silent deletions)

**Tests**: none
**Gate**: build

**Commit**: `chore(env): verify mysql setup and mark tasks complete`

---

## Checks

**C1** - `transacoes` omite a coluna `tipo` e mantém FK `categoria_id` não nula com `ON DELETE RESTRICT` (DB-01, AC 1)
Proof: run feature | ✔ Schema normalizado sem tipo em transacoes e com indices esperados

**C2** - `categorias` impõe chave única composta `(user_id, tipo, nome)` (DB-02, AC 2)
Proof: run feature | ✔ Schema normalizado sem tipo em transacoes e com indices esperados

**C3** - `orcamentos` possui índice `(user_id, mes_referencia)` além da unique `(user_id, categoria_id, mes_referencia)` (DB-03, AC 3)
Proof: run feature | ✔ Schema normalizado sem tipo em transacoes e com indices esperados

**C4** - Criação e edição de transação persistem dados apenas com `categoria_id` sem gravar `transacoes.tipo` (TRX-01, AC 4)
Proof: run feature | ✔ Usuario pode criar editar filtrar e excluir transacao

**C5** - Painel, orçamentos e relatório mensal/PDF derivam `receita` e `despesa` da `categorias.tipo` (TRX-02, AC 5)
Proof: run feature | ✔ Painel e relatorio derivam receitas e despesas da categoria

**C6** - Filtro `GET /transacoes?tipo=receita|despesa` retorna apenas transações cuja categoria tem o `tipo` correspondente (TRX-03, AC 6)
Proof: run feature | ✔ Usuario pode criar editar filtrar e excluir transacao

**C7** - Modal de criação e edição em `/transacoes` exibe apenas categorias de `despesa` enquanto `despesa` está selecionado (UI-01, AC 1)
Proof: walk - abrir modal Nova transacao e Editar transacao com tipo Despesa e confirmar opcoes visiveis apenas de despesa

**C8** - Alterar `Tipo` para `receita` no modal em `/transacoes` filtra as opções visíveis para apenas categorias de `receita` (UI-01, AC 2)
Proof: walk - alternar Tipo para Receita no modal e confirmar que apenas Salario e Freelance permanecem visiveis e selecionados

**C9** - Abrir edição de uma transação pré-seleciona o `Tipo` correspondente a `transacao.categoria.tipo` e mantém a `categoria_id` selecionada (UI-01, AC 3)
Proof: walk - clicar Editar em transacao de receita e de despesa e confirmar Tipo e Categoria pre-selecionados

**C10** - `POST /orcamentos` com categoria de `receita` é rejeitado com erro em `categoria_id` e não grava meta (ORC-01, AC 4)
Proof: run feature | ✔ Orcamento rejeita categoria de receita

**C11** - `DELETE /orcamentos/{orcamento}` remove somente a meta e preserva todas as `transacoes` da categoria (ORC-02, AC 5)
Proof: run feature | ✔ Excluir orcamento preserva transacoes da categoria

**C12** - `.env` aponta `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=meubolso` (ENV-01, AC 1)
Proof: `php -r "require 'vendor/autoload.php'; Dotenv\Dotenv::createImmutable('.')->load(); exit(($_ENV['DB_CONNECTION']==='mysql' && $_ENV['DB_HOST']==='127.0.0.1' && $_ENV['DB_PORT']==='3306' && $_ENV['DB_DATABASE']==='meubolso') ? 0 : 1);"`

**C13** - `.env.example` documenta variáveis MySQL para `meubolso` sem segredos reais (ENV-01, AC 2)
Proof: `php -r "$c=file_get_contents('.env.example'); exit((str_contains($c,'DB_CONNECTION=mysql') && str_contains($c,'DB_DATABASE=meubolso') && preg_match('/^DB_PASSWORD=\s*$/m',$c)) ? 0 : 1);"`

**C14** - `php artisan migrate --seed` cria todas as tabelas no MySQL `meubolso` e semeia `danilo@meubolso.com` com 7 categorias, transações e orçamentos (ENV-02, AC 3)
Proof: `php artisan migrate:fresh --seed --no-interaction`

**C15** - Navbar autenticada exibe link clicável para `route('profile.edit')` (NAV-01, AC 1)
Proof: run feature | ✔ Barra de navegacao exibe link para meu perfil

**C16** - Link de perfil leva para `/profile` com HTTP 200 exibindo formulários da conta (NAV-01, AC 2)
Proof: run feature | ✔ Barra de navegacao exibe link para meu perfil

---

## Handoff

The feature's live state, written as it happens - a compaction gives no warning.

**Settled mid-build:** Na T10 usar Opção A: usuário informará a senha local do MySQL na hora para atualizar `.env` e rodar `php artisan migrate:fresh --seed`. Executando inline sem subagentes de batch.
**Dispatched:** none
**Walked:** none

---

## Phase Execution Map

```
Phase 1 → Phase 2

Phase 1:  T1 → T2 → T3 → T4 → T5
Phase 2:  T6 → T7 → T8 → T9 → T10
```

---

## Task Granularity Check

| Task | Scope | Status |
| ---- | ----- | ------ |
| T1 | 1 migration (`create_categorias_table`) | ✅ Granular |
| T2 | 1 migration (`create_orcamentos_table`) | ✅ Granular |
| T3 | 1 controller (`PainelController`) + teste | ✅ Granular |
| T4 | 1 controller (`RelatorioController`) + teste | ✅ Granular |
| T5 | 1 controller (`OrcamentoController`) + teste | ✅ Granular |
| T6 | Remoção coesa de `transacoes.tipo` (migration + model + `TransacaoController` + seeder + testes) | ⚠️ Merge de dependência circular (tasks.md) |
| T7 | 1 view (`transacoes/index.blade.php`) + teste | ✅ Granular |
| T8 | 1 layout (`layouts/app.blade.php`) + teste | ✅ Granular |
| T9 | 1 arquivo (`.env.example`) | ✅ Granular |
| T10 | 1 arquivo (`.env`) + execução MySQL | ✅ Granular |

## Diagram-Definition Cross-Check

| Task | Depends On (task body) | Diagram Shows | Status |
| ---- | ---------------------- | ------------- | ------ |
| T1 | None | início Phase 1 | ✅ Match |
| T2 | T1 | T1 → T2 | ✅ Match |
| T3 | T2 | T2 → T3 | ✅ Match |
| T4 | T3 | T3 → T4 | ✅ Match |
| T5 | T4 | T4 → T5 | ✅ Match |
| T6 | Phase 1 | Phase 1 → Phase 2, início Phase 2 | ✅ Match |
| T7 | T6 | T6 → T7 | ✅ Match |
| T8 | T7 | T7 → T8 | ✅ Match |
| T9 | T8 | T8 → T9 | ✅ Match |
| T10 | T9 | T9 → T10 | ✅ Match |

## Test Co-location Validation

| Task | Code Layer Created/Modified | Matrix Requires | Task Says | Status |
| ---- | --------------------------- | --------------- | --------- | ------ |
| T1 | Migration / Schema | none | none | ✅ OK |
| T2 | Migration / Schema | none | none | ✅ OK |
| T3 | Controller | feature | feature | ✅ OK |
| T4 | Controller | feature | feature | ✅ OK |
| T5 | Controller | feature | feature | ✅ OK |
| T6 | Controller + Model + Migration | feature | feature | ✅ OK |
| T7 | Blade View | feature | feature | ✅ OK |
| T8 | Blade View / Layout | feature | feature | ✅ OK |
| T9 | Config / Env | none | none | ✅ OK |
| T10 | Config / Env | none | none | ✅ OK |

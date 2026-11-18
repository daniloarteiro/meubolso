# Consultas SQL Explícitas nos Controllers Tasks

## Execution Protocol (MANDATORY -- do not skip)

Implement these tasks with the `tlc-spec-driven` skill: **activate it by name and follow its Execute flow and Critical Rules.** Do not search for skill files by filesystem path. The skill is the source of truth for the full flow (per-task cycle, sub-agent delegation, the walk, check_proofs, the Verifier and the screens sub-agent).

**If the skill cannot be activated, STOP and tell the user - do not proceed without it.**

---

**Design**: skipped - refatoração direta das queries de leitura nos 4 controllers MVC para `DB::select` parametrizado
**Status**: Done
**Approved**: 2026-11-18 - user: "sim"
**Build**: `php artisan test`
**Run sql**: `php vendor/bin/phpunit --testdox tests/Feature/ConsultasSqlTest.php`

---

## Test Coverage Matrix

> Generated from codebase, project guidelines, and spec - confirm before Execute. Guidelines found: `AGENTS.md`, `phpunit.xml`, `tests/Feature/MeuBolsoTest.php`.

| Code Layer | Required Test Type | Coverage Expectation | Location Pattern | Run Command |
| ---------- | ------------------ | -------------------- | ---------------- | ----------- |
| Controller | feature | Todos os endpoints de leitura (`painel`, `transacoes`, `orcamentos`, `relatorios`, `relatorios/pdf`): valores exatos, filtros, isolamento por `user_id`, estado vazio e payload malicioso | `tests/Feature/ConsultasSqlTest.php` | `php vendor/bin/phpunit --testdox tests/Feature/ConsultasSqlTest.php` |
| Blade View | feature | Renderização correta de datas, categorias, tipos e atributos `data-*` a partir do retorno de `DB::select` | `tests/Feature/ConsultasSqlTest.php` | `php vendor/bin/phpunit --testdox tests/Feature/ConsultasSqlTest.php` |

## Gate Check Commands

> Generated from codebase - confirm before Execute.

| Gate Level | When to Use | Command |
| ---------- | ----------- | ------- |
| Quick | Após cada controller convertido para `DB::select` | `php vendor/bin/phpunit --testdox tests/Feature/ConsultasSqlTest.php` |
| Full | Após alteração de controller + view | `php artisan test` |
| Build | Ao final da fase | `php artisan test` |

---

## Execution Plan

Phases are ordered and run sequentially - each phase completes before the next begins, and tasks within a phase execute in order.

### Phase 1: Conversão das Consultas de Leitura para `DB::select`

```
T1 → T2 → T3 → T4
```

---

## Task Breakdown

### T1: Converter consultas de leitura do `PainelController` para `DB::select`

**What**: Substituir consultas Eloquent de `PainelController@index` (totais do mês, últimas 5 transações, gastos por categoria e série de 6 meses) por `DB::select` parametrizado com `?` e adaptar `painel/index.blade.php`, adicionando testes em `tests/Feature/ConsultasSqlTest.php`.
**Where**: `app/Http/Controllers/PainelController.php`
**Depends on**: None
**Reuses**: Estrutura da view `resources/views/painel/index.blade.php` e chaves `categorias`/`meses` esperadas por `public/js/painel.js`
**Requirement**: SQL-01

**Done when**:

- [x] `PainelController@index` executa apenas `DB::select` com placeholders `?` e `user_id = ?` nas leituras
- [x] `resources/views/painel/index.blade.php` renderiza últimas 5 transações com data `d/m/Y`, categoria e classe/sinal de tipo
- [x] `tests/Feature/ConsultasSqlTest.php` valida totais `float`, gráficos, estado vazio e isolamento entre usuários no `/painel`
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 39 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `refactor(painel): use parameterized DB::select queries`

---

### T2: Converter consultas de leitura do `RelatorioController` para `DB::select`

**What**: Substituir consultas Eloquent de `RelatorioController@montarDados` (totais do mês, despesas por categoria com percentual e lista de transações do mês) por `DB::select` parametrizado com `?` e adaptar `relatorios/pdf.blade.php`, adicionando testes em `tests/Feature/ConsultasSqlTest.php`.
**Where**: `app/Http/Controllers/RelatorioController.php`
**Depends on**: T1
**Reuses**: Lógica de formatação de mês e `Pdf::loadView('relatorios.pdf', ...)`
**Requirement**: SQL-02

**Done when**:

- [x] `RelatorioController@montarDados` executa apenas `DB::select` com placeholders `?` e `user_id = ?` nas leituras
- [x] `resources/views/relatorios/pdf.blade.php` renderiza lançamentos do mês com data `d/m/Y`, categoria e tipo
- [x] `tests/Feature/ConsultasSqlTest.php` valida totais `float`, percentuais, estado vazio (sem divisão por zero), isolamento e PDF no `/relatorios`
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 41 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `refactor(relatorios): use parameterized DB::select queries`

---

### T3: Converter consultas de leitura do `TransacaoController@index` para `DB::select`

**What**: Substituir consultas Eloquent de `TransacaoController@index` (listagem filtrada por `de`, `ate`, `tipo`, `categoria_id` e lista de categorias do usuário) por `DB::select` parametrizado com `?` e adaptar `transacoes/index.blade.php`, mantendo `store`/`update`/`destroy` no Eloquent.
**Where**: `app/Http/Controllers/TransacaoController.php`
**Depends on**: T2
**Reuses**: Filtros e modais existentes em `resources/views/transacoes/index.blade.php`
**Requirement**: SQL-03

**Done when**:

- [x] `TransacaoController@index` executa `DB::select` com bindings `?` para todos os filtros (`de`, `ate`, `tipo`, `categoria_id`) e ordenação `data DESC, id DESC`
- [x] `resources/views/transacoes/index.blade.php` renderiza tabela e botões `.btn-editar` com todos os `data-*` idênticos
- [x] `tests/Feature/ConsultasSqlTest.php` valida filtros `de`/`ate`/`tipo`/`categoria_id`, ordenação, isolamento entre usuários e payload de SQL injection
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 43 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `refactor(transacoes): use parameterized DB::select in index`

---

### T4: Converter consultas de leitura do `OrcamentoController@index` para `DB::select`

**What**: Substituir consultas Eloquent de `OrcamentoController@index` por `DB::select` parametrizado com `?` (LEFT JOIN em `transacoes` e `orcamentos` por `user_id` e período), mantendo `store`/`destroy` no Eloquent.
**Where**: `app/Http/Controllers/OrcamentoController.php`
**Depends on**: T3
**Reuses**: Estrutura de retorno `$categorias` esperada por `resources/views/orcamentos/index.blade.php`
**Requirement**: SQL-04

**Done when**:

- [x] `OrcamentoController@index` executa `DB::select` parametrizado com `user_id = ?` e `tipo = 'despesa'`
- [x] `tests/Feature/ConsultasSqlTest.php` valida categorias `despesa`, soma `float` de gastos, limites `float|null`, estado vazio e isolamento entre usuários em `/orcamentos`
- [x] Gate check passes: `php artisan test`
- [x] Test count: >= 45 tests pass (no silent deletions)

**Tests**: feature
**Gate**: full

**Commit**: `refactor(orcamentos): use parameterized DB::select in index`

---

## Checks

**C1** - `PainelController@index` calcula totais do mês, gráfico de rosca, série de 6 meses e últimas 5 transações via `DB::select` parametrizado isolado por usuário (SQL-01, AC 1, AC 3)
Proof: run sql | ✔ Painel usa DB select parametrizado e isola usuarios

**C2** - `PainelController` retorna `0.0` em todos os totais quando o usuário não possui transações no mês (SQL-01, Edge Case)
Proof: run sql | ✔ Painel retorna zeros em estado vazio

**C3** - `RelatorioController` calcula totais, percentuais por categoria e lançamentos do mês via `DB::select` parametrizado e gera PDF (SQL-02, AC 2, AC 3)
Proof: run sql | ✔ Relatorio e pdf usam DB select parametrizado e isolam usuarios

**C4** - `RelatorioController` retorna `0.0` e lista vazia sem erro de divisão por zero quando não há transações no mês (SQL-02, Edge Case)
Proof: run sql | ✔ Relatorio trata mes vazio sem divisao por zero

**C5** - `TransacaoController@index` filtra por `de`, `ate`, `tipo` e `categoria_id` via `DB::select` parametrizado ordenado por `data DESC, id DESC` e isolado por usuário (SQL-03, AC 1, AC 3)
Proof: run sql | ✔ Transacoes index filtra por periodo tipo categoria e isola usuarios

**C6** - Payloads maliciosos nos parâmetros de filtro em `/transacoes` são tratados como bindings `?` sem quebrar ou vazar dados (SQL-03, Edge Case)
Proof: run sql | ✔ Transacoes index neutraliza payload de sql injection nos filtros

**C7** - `OrcamentoController@index` lista todas as categorias `despesa` do usuário com `gasto` (`float`) e `limite` (`float|null`) via `DB::select` parametrizado e isolado por usuário (SQL-04, AC 2, AC 4)
Proof: run sql | ✔ Orcamentos index usa DB select parametrizado e isola usuarios

**C8** - Usuário sem transações e sem metas no mês recebe `gasto = 0.0` e `limite = null` em todas as categorias `despesa` em `/orcamentos` (SQL-04, Edge Case)
Proof: run sql | ✔ Orcamentos index retorna gasto zero e limite nulo quando vazio

---

## Handoff

The feature's live state, written as it happens - a compaction gives no warning.

**Settled mid-build:** none
**Dispatched:** Verifier (sub-agent `self`) após commit do walk
**Walked:** Login como `danilo@meubolso.com` contra MySQL `meubolso` em `http://127.0.0.1:8012`. `/painel` exibiu "Novembro de 2026" e 5 transações; `/transacoes` listou lançamentos e filtrou modais (`despesa` 5 opções, `receita` 2 opções, edição pré-selecionando `6` e `3`); `/orcamentos` exibiu 5 metas; `/relatorios` e `/relatorios/pdf` retornaram 200 (`application/pdf`).

---

## Phase Execution Map

```
Phase 1

Phase 1:  T1 → T2 → T3 → T4
```

---

## Task Granularity Check

| Task | Scope | Status |
| ---- | ----- | ------ |
| T1 | 1 controller (`PainelController`) + adaptação da view + testes | ✅ Granular |
| T2 | 1 controller (`RelatorioController`) + adaptação da view PDF + testes | ✅ Granular |
| T3 | 1 controller (`TransacaoController@index`) + adaptação da view + testes | ✅ Granular |
| T4 | 1 controller (`OrcamentoController@index`) + testes | ✅ Granular |

## Diagram-Definition Cross-Check

| Task | Depends On (task body) | Diagram Shows | Status |
| ---- | ---------------------- | ------------- | ------ |
| T1 | None | início Phase 1 | ✅ Match |
| T2 | T1 | T1 → T2 | ✅ Match |
| T3 | T2 | T2 → T3 | ✅ Match |
| T4 | T3 | T3 → T4 | ✅ Match |

## Test Co-location Validation

| Task | Code Layer Created/Modified | Matrix Requires | Task Says | Status |
| ---- | --------------------------- | --------------- | --------- | ------ |
| T1 | Controller + View | feature | feature | ✅ OK |
| T2 | Controller + View | feature | feature | ✅ OK |
| T3 | Controller + View | feature | feature | ✅ OK |
| T4 | Controller | feature | feature | ✅ OK |

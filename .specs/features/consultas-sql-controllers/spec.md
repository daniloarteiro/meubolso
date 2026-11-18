# Consultas SQL Explícitas nos Controllers Specification

**Approved**: 2026-11-18 - user: "sim"

## Problem Statement

As consultas de leitura dos 4 controllers principais (`PainelController`, `TransacaoController`, `OrcamentoController`, `RelatorioController`) usam sintaxe Eloquent encadeada, dificultando a visualização direta dos comandos `SELECT` e `JOIN` executados no MySQL. Converter essas consultas de leitura para `DB::select("SELECT ...", [...])` parametrizado torna o SQL explícito e didático nas 4 telas sem perder segurança nem compatibilidade com os testes.

## Goals

- [ ] Substituir todas as consultas de leitura (`SELECT`) de `PainelController`, `TransacaoController`, `OrcamentoController` e `RelatorioController` por `DB::select("SELECT ...", [...])` com bindings `?`.
- [ ] Manter comportamento idêntico em todas as telas, gráficos, filtros, orçamentos, relatórios e PDF, compatível com MySQL 8 (`ONLY_FULL_GROUP_BY`) e SQLite (`php artisan test`).
- [ ] Manter `store`, `update` e `destroy` usando Eloquent ORM conforme escolha do usuário.

## Out of Scope

Explicitly excluded. Documented to prevent scope creep.

| Feature | Reason |
| ------- | ------ |
| Reescrever `store`, `update` ou `destroy` para `DB::insert`/`DB::update`/`DB::delete` | Usuário escolheu manter escritas no Eloquent (opção 1) |
| Alterações de schema ou novas colunas | Banco MySQL `meubolso` já está normalizado em 3FN |
| Reescrever controllers de autenticação do Breeze | Fora do domínio financeiro das 4 telas principais |

---

## Assumptions & Open Questions

| Assumption / decision | Chosen default | Rationale | Confirmed? |
| --------------------- | -------------- | --------- | ---------- |
| Escopo de SQL explícito | Apenas consultas de leitura (`DB::select`) nos 4 controllers de domínio | Usuário selecionou opção 1 explicitamente | y |
| Segurança contra SQL Injection | Uso obrigatório de placeholders `?` com array de bindings em todo `DB::select` | Impede injeção de SQL em filtros de usuário e datas | y |
| Compatibilidade MySQL 8 + SQLite | SQL padrão (`INNER JOIN`, `LEFT JOIN`, `COALESCE`, `SUM`, `CASE WHEN`, `DATE()`) e `GROUP BY` completo | Roda no MySQL 8 (`ONLY_FULL_GROUP_BY`) e na suíte PHPUnit (SQLite `:memory:`) | y |
| Adaptação mínima das views | Ler `$l->categoria_nome`, `$l->tipo` e `\Carbon\Carbon::parse($l->data)->format('d/m/Y')` | Mantém saída HTML e atributos idênticos usando objetos retornados por `DB::select` | y |

**Open questions:** none - all resolved or logged above.

---

## User Stories

### P1: Consultas SQL Explícitas no Painel e Relatórios ⭐ MVP

**User Story**: As a desenvolvedor do MeuBolso, I want que `PainelController` e `RelatorioController` executem consultas `DB::select("SELECT ...", [...])` explícitas so that o SQL executado em cada card, gráfico e PDF fique legível diretamente nos controllers.

**Why P1**: Cobre os dois painéis analíticos principais do sistema.

**Acceptance Criteria**:

1. The `PainelController@index` method SHALL compute monthly totals (`receitas`, `despesas`, `saldo`), donut chart spending by `despesa` category, 6-month bar chart series, and latest 5 transactions using parameterized `DB::select` queries with `?` bindings and `user_id = ?`.
2. The `RelatorioController@montarDados` method SHALL compute monthly totals (`receitas`, `despesas`, `saldo`), category expense breakdown with percentage, and monthly transaction list using parameterized `DB::select` queries with `?` bindings and `user_id = ?`.
3. WHEN an authenticated user opens `/painel`, `/relatorios`, or `/relatorios/pdf` THEN the system SHALL return the same numerical totals (as `float`), percentages, and transaction rows as before, isolating data strictly by `auth()->id()`.

**Independent Test**: Executar os testes de painel e relatórios/PDF contra SQLite e validar as telas no navegador conectado ao MySQL `meubolso`.

---

### P1: Consultas SQL Explícitas em Transações e Orçamentos ⭐ MVP

**User Story**: As a desenvolvedor do MeuBolso, I want que `TransacaoController@index` e `OrcamentoController@index` executem consultas `DB::select("SELECT ...", [...])` explícitas so that filtros de extrato e acompanhamento de metas mostrem claramente seus `SELECT` e `JOIN`.

**Why P1**: Completa as 4 telas principais do sistema com SQL explícito e seguro.

**Acceptance Criteria**:

1. The `TransacaoController@index` method SHALL query filtered transactions (`de`, `ate`, `tipo` via `categorias.tipo`, `categoria_id`) and the user's categories using parameterized `DB::select` queries with `?` bindings and `user_id = ?`.
2. The `OrcamentoController@index` method SHALL query expense categories, monthly spending totals, and monthly budget limits using parameterized `DB::select` queries with `?` bindings and `user_id = ?`.
3. WHEN an authenticated user filters `/transacoes` by `de`, `ate`, `tipo`, or `categoria_id` THEN the system SHALL return only matching transactions owned by `auth()->id()`, ordered by `data DESC, id DESC`.
4. WHEN an authenticated user opens `/orcamentos` for a reference month THEN the system SHALL display all `despesa` categories owned by `auth()->id()` with their calculated spent amount (`float`) and budget limit (`float|null`).

**Independent Test**: Executar os testes de filtro de transações e metas em `/orcamentos`, além de validar valores extremos e filtros combinados.

---

## Edge Cases

- IF a user has zero transactions or zero budgets in the selected month THEN `DB::select` aggregations SHALL return `0.0` for `receitas`, `despesas`, `saldo`, and `gasto` without division-by-zero or null errors.
- IF `TransacaoController@index` receives SQL-injection payloads in `de`, `ate`, `tipo`, or `categoria_id` THEN parameterized bindings `?` SHALL treat them strictly as bound parameter values without altering SQL structure.
- IF `PainelController`, `TransacaoController`, `OrcamentoController`, or `RelatorioController` queries run for user A when user B also has transactions and budgets THEN no record owned by user B SHALL appear in user A's results.

---

## Implicit-Requirement Dimensions Sweep (Medium Scope)

- **Input validation & bounds:** Parâmetros de filtro (`de`, `ate`, `tipo`, `categoria_id`, `mes`) são passados via bindings `?` ou normalizados por regex (`^\d{4}-\d{2}$`).
- **Auth boundaries:** Toda query `DB::select` filtra explicitamente por `user_id = ?` usando `$request->user()->id`.
- **Remaining dimensions (external calls, concurrency, observability, lifecycle):** N/A because this is a read-query refactor inside existing authenticated MVC controllers without schema or external integration changes.

---

## Requirement Traceability

| Requirement ID | Story | Phase | Status |
| -------------- | ----- | ----- | ------ |
| SQL-01 | P1: Consultas SQL Explícitas no Painel e Relatórios | Tasks | Verified |
| SQL-02 | P1: Consultas SQL Explícitas no Painel e Relatórios | Tasks | Verified |
| SQL-03 | P1: Consultas SQL Explícitas em Transações e Orçamentos | Tasks | Verified |
| SQL-04 | P1: Consultas SQL Explícitas em Transações e Orçamentos | Tasks | Pending |

**Coverage:** 4 total, 0 mapped to tasks, 4 unmapped

---

## Sources

- Solicitação do usuário no chat (opção 1: `DB::select` parametrizado nas consultas de leitura das 4 telas, mantendo escrita no Eloquent).

---

## Success Criteria

- [ ] Todos os 4 controllers (`PainelController`, `TransacaoController`, `OrcamentoController`, `RelatorioController`) utilizam `DB::select("SELECT ...", [...])` parametrizado em suas consultas de leitura.
- [ ] Todos os testes (`php artisan test`) passam com 0 falhas e cobertura dos filtros de data (`de`/`ate`), isolamento por usuário e estado vazio.
- [ ] Telas `/painel`, `/transacoes`, `/orcamentos`, `/relatorios` e `/relatorios/pdf` funcionam normalmente no navegador conectado ao MySQL `meubolso`.

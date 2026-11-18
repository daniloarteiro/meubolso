# LESSONS - auto-maintained by scripts/lessons.py

> Machine-owned. Do NOT hand-edit. Changes are overwritten on the next `lessons.py` write.
> Canonical state lives in `.specs/lessons.json`. Edit lessons only via the script.
> promote_threshold=2 distinct features · window_days=45 · quarantine_threshold=2

## Confirmed (load these at Plan/Checks)

Corroborated across multiple features. Safe to apply as guidance.

_none_

## Candidates (under observation - do NOT load as guidance yet)

Seen once or not yet corroborated. Tracked, not trusted.

### L-001 - Assert column nullability and foreign key delete rule explicitly when testing schema constraints
- signal: `weak_test` · recurrence: 1 feature(s) · scope: `tests,migrations` · harmful: 0
- features: normalizacao-banco-mysql
- evidence: tests/Feature/MeuBolsoTest.php:258-259 (C1) (tests,migrations)
- last seen: 2026-11-18T05:58:54Z

### L-002 - Assert derived totals and view data across every page and chart named by the criterion
- signal: `weak_test` · recurrence: 1 feature(s) · scope: `tests,controllers` · harmful: 0
- features: normalizacao-banco-mysql
- evidence: tests/Feature/MeuBolsoTest.php:162-202 (C5) (tests,controllers)
- last seen: 2026-11-18T05:58:54Z

### L-003 - Exercise every enum branch in filter tests rather than only one branch
- signal: `weak_test` · recurrence: 1 feature(s) · scope: `tests,controllers` · harmful: 0
- features: normalizacao-banco-mysql
- evidence: tests/Feature/MeuBolsoTest.php:83-87 (C6) (tests,controllers)
- last seen: 2026-11-18T05:58:54Z

### L-004 - Seed more items than a query LIMIT and assert exact item order including tie-breakers
- signal: `weak_test` · recurrence: 1 feature(s) · scope: `tests,controllers` · harmful: 0
- features: consultas-sql-controllers
- evidence: tests/Feature/ConsultasSqlTest.php:16-81 (C1) (tests,controllers)
- last seen: 2026-11-18T06:36:39Z

### L-005 - Assert every query output passed to a view including out-of-range and cross-user exclusions
- signal: `weak_test` · recurrence: 1 feature(s) · scope: `tests,controllers` · harmful: 0
- features: consultas-sql-controllers
- evidence: tests/Feature/ConsultasSqlTest.php:96-161 (C3) (tests,controllers)
- last seen: 2026-11-18T06:36:39Z

### L-006 - Include same-key rows in ordering tests to exercise secondary tie-breaker columns
- signal: `weak_test` · recurrence: 1 feature(s) · scope: `tests,controllers` · harmful: 0
- features: consultas-sql-controllers
- evidence: tests/Feature/ConsultasSqlTest.php:203-252 (C5) (tests,controllers)
- last seen: 2026-11-18T06:36:39Z

### L-007 - Guard query string inputs with is_string before regex or SQL binding and parse Y-m with !Y-m
- signal: `ac_gap` · recurrence: 1 feature(s) · scope: `controllers,views` · harmful: 0
- features: consultas-sql-controllers
- evidence: RelatorioController.php:38, OrcamentoController.php:22, TransacaoController.php:17 (controllers,views)
- last seen: 2026-11-18T06:36:40Z

## Quarantined (failed when applied - ignore)

A confirmed lesson that recurred alongside failure. Kept for the maintainer to review.

_none_

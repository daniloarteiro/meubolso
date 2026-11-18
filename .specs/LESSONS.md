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

## Quarantined (failed when applied - ignore)

A confirmed lesson that recurred alongside failure. Kept for the maintainer to review.

_none_

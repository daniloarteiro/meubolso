# consultas-sql-controllers verification

**Verdict**: FAIL
**Proofs**: `proofs.md` at 87d3a89
**Verifier**: independent sub-agent (author != verifier)

## Checks

| Check | Test | Holds |
| --- | --- | --- |
| C1 | `tests/Feature/ConsultasSqlTest.php` `test_painel_usa_db_select_parametrizado_e_isola_usuarios` | no - creates only 3 user transactions and never asserts `LIMIT 5` or `ORDER BY data DESC, id DESC` on `transacoes` |
| C2 | `tests/Feature/ConsultasSqlTest.php` `test_painel_retorna_zeros_em_estado_vazio` | yes |
| C3 | `tests/Feature/ConsultasSqlTest.php` `test_relatorio_e_pdf_usam_db_select_parametrizado_e_isolam_usuarios` | no - never asserts `viewData('transacoes')` (count, month filter, order, or cross-user isolation) or PDF text |
| C4 | `tests/Feature/ConsultasSqlTest.php` `test_relatorio_trata_mes_vazio_sem_divisao_por_zero` | yes |
| C5 | `tests/Feature/ConsultasSqlTest.php` `test_transacoes_index_filtra_por_periodo_tipo_categoria_e_isola_usuarios` | no - exercises `data DESC` on distinct dates only, never proving the `id DESC` tie-breaker on same-day rows |
| C6 | `tests/Feature/ConsultasSqlTest.php` `test_transacoes_index_neutraliza_payload_de_sql_injection_nos_filtros` | yes |
| C7 | `tests/Feature/ConsultasSqlTest.php` `test_orcamentos_index_usa_db_select_parametrizado_e_isola_usuarios` | yes |
| C8 | `tests/Feature/ConsultasSqlTest.php` `test_orcamentos_index_retorna_gasto_zero_e_limite_nulo_quando_vazio` | yes |
| C9 | `tests/Feature/ConsultasSqlTest.php` `test_parametros_em_array_e_mes_invalido_nao_geram_erro_500_nem_overflow` | yes |

## Findings

| Finding | Evidence | Fix | Closed |
| --- | --- | --- | --- |
| C1 does not verify `LIMIT 5` or ordering of latest 5 transactions | `tests/Feature/ConsultasSqlTest.php:16-81` creates only 3 transactions for `$user` and only asserts `assertSee('Supermercado')`; removing `LIMIT 5` or flipping `ORDER BY` still passes | Add >= 6 transactions (including same-day tie) in `test_painel_usa_db_select_parametrizado_e_isola_usuarios` and assert `count === 5` and exact `data DESC, id DESC` order | `ad2d450` (C1) |
| C3 does not verify `RelatorioController` monthly `transacoes` query or user isolation of `transacoes` | `tests/Feature/ConsultasSqlTest.php:96-161` never reads `$res->viewData('transacoes')`; dropping `WHERE t.user_id = ?` or `DATE(t.data) BETWEEN ? AND ?` on the 3rd `DB::select` still passes | Assert `$res->viewData('transacoes')` count, order (`data DESC, id DESC`), month boundary, and exclusion of `$outro`'s transaction (plus an out-of-month transaction) | `ad2d450` (C3) |
| C5 does not prove same-day `id DESC` tie-breaker ordering | `tests/Feature/ConsultasSqlTest.php:203-252` uses distinct dates (`2026-11-15` and `2026-11-18`), so removing `, t.id DESC` in `TransacaoController@index` still passes | Add two transactions on the same date in `test_transacoes_index_filtra_por_periodo_tipo_categoria_e_isola_usuarios` and assert higher `id` appears first | `ad2d450` (C5) |
| Array query parameters (`mes[]`, `de[]`, `ate[]`, `tipo[]`) trigger HTTP 500 `TypeError` | Own-example requests `/relatorios?mes[]=2026-11`, `/orcamentos?mes[]=2026-11`, and `/transacoes?de[]=2026-01-01` returned HTTP 500 at `HEAD` (`preg_match` and PDO binding receive `array` instead of `string`) | Guard `mes` with `is_string($mes)` in `RelatorioController@montarDados` and `OrcamentoController@index`, and `is_string(...)` on `de`/`ate`/`tipo` in `TransacaoController@index` (with feature test coverage) | `ad2d450` (C9) |
| Month parameter `mes` overflows silently on invalid month (`2026-13`) or 31st-of-month (`2026-02` on `2026-03-31`) | Own-example probe at `HEAD` returned `Janeiro de 2027` for `mes=2026-13` and `Março de 2026` for `mes=2026-02` when `Carbon::setTestNow('2026-03-31')` (pre-existing `Carbon::createFromFormat('Y-m', $mes)` behavior in `RelatorioController.php:38` and `OrcamentoController.php:22`) | Parse `$mes` with `Carbon::createFromFormat('!Y-m', $mes)` and validate month `1..12` (falling back to current month on invalid `Y-m`), with a boundary test | `ad2d450` (C9) |

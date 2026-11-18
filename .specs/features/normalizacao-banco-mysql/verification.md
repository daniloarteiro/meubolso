# normalizacao-banco-mysql verification

**Verdict**: FAIL
**Proofs**: `proofs.md` at d1c2415
**Verifier**: independent sub-agent (author != verifier)

## Checks

| Check | Test | Holds |
| --- | --- | --- |
| C1 | `tests/Feature/MeuBolsoTest.php` `test_schema_normalizado_sem_tipo_em_transacoes_e_com_indices_esperados` | no - asserts `transacoes.tipo` absence and `categoria_id` existence, not non-nullability or `ON DELETE RESTRICT` |
| C2 | `tests/Feature/MeuBolsoTest.php` `test_schema_normalizado_sem_tipo_em_transacoes_e_com_indices_esperados` | yes |
| C3 | `tests/Feature/MeuBolsoTest.php` `test_schema_normalizado_sem_tipo_em_transacoes_e_com_indices_esperados` | yes |
| C4 | `tests/Feature/MeuBolsoTest.php` `test_usuario_pode_criar_editar_filtrar_e_excluir_transacao` | yes |
| C5 | `tests/Feature/MeuBolsoTest.php` `test_painel_e_relatorio_derivam_receitas_e_despesas_da_categoria` | no - does not assert `/orcamentos` spent calculation (nor Painel chart arrays) |
| C6 | `tests/Feature/MeuBolsoTest.php` `test_usuario_pode_criar_editar_filtrar_e_excluir_transacao` | no - tests only `?tipo=receita`, not `?tipo=despesa` |
| C7 | `walk` (`resources/views/transacoes/index.blade.php` + `test_modal_de_transacoes_renderiza_atributos_para_filtro_por_tipo`) | yes |
| C8 | `walk` (`resources/views/transacoes/index.blade.php` `#m_tipo` / `#e_tipo` `change` listener) | yes |
| C9 | `walk` (`resources/views/transacoes/index.blade.php` `.btn-editar` handler + `test_modal_de_transacoes_renderiza_atributos_para_filtro_por_tipo`) | yes |
| C10 | `tests/Feature/MeuBolsoTest.php` `test_orcamento_rejeita_categoria_de_receita` | yes |
| C11 | `tests/Feature/MeuBolsoTest.php` `test_excluir_orcamento_preserva_transacoes_da_categoria` | yes |
| C12 | CLI `Dotenv\Dotenv::createImmutable(".")->load()` check against `.env` | yes |
| C13 | CLI check against `.env.example` | yes |
| C14 | CLI `php artisan migrate:fresh --seed --no-interaction` (verified against MySQL `meubolso`) | yes |
| C15 | `tests/Feature/MeuBolsoTest.php` `test_barra_de_navegacao_exibe_link_para_meu_perfil` | yes |
| C16 | `tests/Feature/MeuBolsoTest.php` `test_barra_de_navegacao_exibe_link_para_meu_perfil` | yes |

## Findings

| Finding | Evidence | Fix | Closed |
| --- | --- | --- | --- |
| C1 test does not verify `categoria_id` is non-nullable or has `ON DELETE RESTRICT` | `tests/Feature/MeuBolsoTest.php:258-259` only calls `Schema::hasColumn('transacoes', 'categoria_id')`; `test_categoria_com_transacoes_nao_pode_ser_excluida` is separate and also does not test `NOT NULL` | Assert `nullable === false` on `transacoes.categoria_id` (and reject null insert) plus `ON DELETE RESTRICT` in C1's test | `2b0ac62` (C1) |
| C5 test omits `/orcamentos` spent derivation and Painel chart data | `tests/Feature/MeuBolsoTest.php:162-202` only checks `/painel` totals, `/relatorios` totals, and `/relatorios/pdf` 200; `test_orcamentos_e_relatorio_pdf_funcionam:154` only checks `assertSee('Alimentação')` without spent value | Request `/orcamentos` in `test_painel_e_relatorio_derivam_receitas_e_despesas_da_categoria` and assert calculated despesa spent (`250,00`) and Painel `$categorias`/`$meses` values | `2b0ac62` (C5) |
| C6 test only checks `?tipo=receita` filter and omits `?tipo=despesa` | `tests/Feature/MeuBolsoTest.php:83-87` requests `route('transacoes.index', ['tipo' => 'receita'])` only | Add assertion for `GET /transacoes?tipo=despesa` showing `Café` and excluding `Estágio` | `2b0ac62` (C6) |

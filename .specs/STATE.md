# STATE

## Decisions

### AD-001
- **Decision**: Manter `tipo` (`receita` | `despesa`) exclusivamente na tabela `categorias`, removendo a coluna `tipo` de `transacoes`.
- **Reason**: Elimina redundância (3FN) e impede conflito estrutural entre o tipo da transação e o tipo da categoria no MySQL; telas de Orçamentos e Painel já dependem de `categorias.tipo`.
- **Trade-off**: Consultas de transações por tipo passam a usar `JOIN` ou `whereHas('categoria')`, e caso no futuro exista edição de categoria, não se deve alterar `tipo` de categoria com lançamentos.
- **Scope**: Migrations, Models (`Categoria`, `Transacao`), Controllers (`PainelController`, `TransacaoController`, `OrcamentoController`, `RelatorioController`), Views e Seeder.
- **Date**: 2026-11-18
- **Status**: active

## Handoff

- **Feature**: .specs/features/normalizacao-banco-mysql
- **Phase / Task**: Done (T1–T10 concluídas, Verifier auditou e achados fechados)
- **Completed**: T1, T2, T3, T4, T5, T6, T7, T8, T9, T10
- **In-progress**: none
- **Next step**: Testar no navegador (`php artisan serve`) se desejar e definir próxima feature ou push
- **Blockers**: none
- **Uncommitted files**: none
- **Branch**: master

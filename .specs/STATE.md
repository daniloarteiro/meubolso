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

- **Feature**: .specs/features/consultas-sql-controllers
- **Phase / Task**: Done (T1–T4 concluídas, Verifier auditou e achados fechados)
- **Completed**: T1, T2, T3, T4
- **In-progress**: none
- **Next step**: Testar no navegador (`php artisan serve`) ou iniciar próxima feature
- **Blockers**: none
- **Uncommitted files**: none
- **Branch**: master

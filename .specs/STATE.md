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
- **Phase / Task**: Specify - aguardando aprovação da spec pelo usuário
- **Completed**: none
- **In-progress**: none
- **Next step**: Aprovar `spec.md` e prosseguir para `tasks.md`
- **Blockers**: none
- **Uncommitted files**: .specs/STATE.md, .specs/features/normalizacao-banco-mysql/context.md, .specs/features/normalizacao-banco-mysql/spec.md
- **Branch**: none (git ainda não inicializado; usuário autorizou `git init` antes da execução)

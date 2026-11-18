# STATE

## Decisions

### AD-001
- **Decision**: Manter `tipo` (`receita` | `despesa`) exclusivamente na tabela `categorias`, removendo a coluna `tipo` de `transacoes`.
- **Reason**: Elimina redundância (3FN) e impede conflito estrutural entre o tipo da transação e o tipo da categoria no MySQL; telas de Orçamentos e Painel já dependem de `categorias.tipo`.
- **Trade-off**: Consultas de transações por tipo passam a usar `JOIN` ou `whereHas('categoria')`, e caso no futuro exista edição de categoria, não se deve alterar `tipo` de categoria com lançamentos.
- **Scope**: Migrations, Models (`Categoria`, `Transacao`), Controllers (`PainelController`, `TransacaoController`, `OrcamentoController`, `RelatorioController`), Views e Seeder.
- **Date**: 2026-11-18
- **Status**: active

### AD-002
- **Decision**: Na tela de apresentação (`/`), alterar o texto do card "Categorias" de `"Crie as suas, com a cor que quiser."` para `"Categorias prontas e coloridas para receitas e despesas."`, mantendo o layout e estilo da fonte vinculante.
- **Reason**: Pergunta ao usuário: "O card 'Categorias' no arquivo diz 'Crie as suas, com a cor que quiser.', mas o sistema hoje usa 7 categorias padrão (sem tela de criação ainda). Como prefere tratar esse texto?" — Opção escolhida: "Ajustar apenas o texto desse card para refletir o sistema atual (ex.: \"Categorias prontas e coloridas para receitas e despesas.\"), sem mudar o visual".
- **Trade-off**: A landing page reflete com precisão as funcionalidades existentes hoje, evitando prometer uma tela de criação de categorias que ainda não existe.
- **Scope**: View da tela de apresentação (`/`).
- **Date**: 2026-11-18
- **Status**: active (AD-002)

## Handoff

- **Feature**: .specs/features/consultas-sql-controllers (concluída após .specs/features/normalizacao-banco-mysql)
- **Phase / Task**: Sessão encerrada em 2026-11-18 — ambas as features concluídas e verificadas (`validate_verification.py` 0 erros)
- **Completed**: normalizacao-banco-mysql (T1–T10), consultas-sql-controllers (T1–T4)
- **In-progress**: none
- **Next step**: Rodar `php artisan serve`, validar o sistema manualmente no navegador com o usuário (`danilo@meubolso.com` / `12345678`) contra o MySQL `meubolso`, decidir se entra CRUD de categorias personalizadas e preparar repositório remoto (GitHub) se autorizado.
- **Blockers**: none
- **Uncommitted files**: none
- **Branch**: master (commit `7234692` + atualização de handoff)

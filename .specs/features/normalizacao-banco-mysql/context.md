# Normalização do Banco, MER Definitivo e Conexão MySQL Context

**Gathered:** 2026-11-18
**Spec:** `.specs/features/normalizacao-banco-mysql/spec.md`
**Status:** Ready for spec approval

---

## Feature Boundary

Consolidar o modelo relacional (MER 3FN) do MeuBolso removendo a redundância `transacoes.tipo`, ajustar chaves/índices no MySQL, garantir integridade nas regras de negócio (orçamentos apenas para despesas, filtro dinâmico de categorias no modal de transações e link para Meu Perfil) e conectar a aplicação ao banco MySQL `meubolso` com dados de demonstração.

---

## Implementation Decisions

### Normalização do atributo `tipo` (3FN)

- Usuário escolheu manter `tipo` exclusivamente em `categorias` e derivar o tipo das transações via consulta/relacionamento (`categoria_id`), removendo `transacoes.tipo` para evitar conflito.
- O campo `Tipo` permanece na UI dos modais de Nova/Editar Transação apenas como filtro dinâmico em JavaScript para exibir só as categorias do tipo selecionado ("se eu clicar em receita o campo de categoria só vai aparecer o da receita e quando for despesa aquilo que da despesa").

### Categorias padrão vs. CRUD de categorias

- Usuário decidiu manter apenas as 7 categorias padrão criadas pelo sistema no cadastro (`Alimentação`, `Transporte`, `Mensalidade`, `Lazer`, `Outros`, `Salário`, `Freelance`), sem tela de criar/editar/excluir categorias nesta etapa ("por enquanto sim se for ter uma tela disso futuramente ai fazemos a alteração tanto criando a tela quanto no banco").
- Na tela de Orçamentos, excluir uma meta remove somente o registro em `orcamentos`, preservando intactas todas as transações daquela categoria.

### Ambiente MySQL e Git local

- Usuário confirmou inicializar o repositório Git local (`git init` + commit base) para acompanhar as tarefas com commits atômicos (sem `git push`).
- Usuário confirmou popular o banco MySQL `meubolso` com `php artisan migrate --seed` (usuário de teste `danilo@meubolso.com` + dados de exemplo).

### Agent's Discretion

- Uso de accessor ou helper no Model `Transacao` para leitura limpa de `$transacao->tipo` nas views, mantendo padrão MVC simples de 4º período.
- Posicionamento e estilo Bootstrap 5 do link "Meu Perfil" na barra de navegação superior (`layouts/app.blade.php`).

### Declined / Undiscussed Gray Areas → Assumptions

- Nenhuma área recusada; todas as decisões de produto foram respondidas pelo usuário.

---

## Specific References

- Proposta original: `C:\Users\danil\Downloads\PROPOSTA_MeuBolso.docx`
- Instruções do projeto: `AGENTS.md`

---

## Deferred Ideas

- Tela de CRUD para categorias personalizadas (criar/editar/excluir categorias pelo usuário) — adiada para iteração futura conforme decisão do usuário.

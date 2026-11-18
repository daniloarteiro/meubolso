# Tela de Apresentação Context

**Gathered:** 2026-11-18
**Spec:** `.specs/features/tela-apresentacao/spec.md`
**Status:** Ready for spec approval

---

## Feature Boundary

Integrar ao projeto Laravel o layout da página HTML de apresentação (`MeuBolso – Tela de apresentação.html`) como porta de entrada na rota `/`, conectando navegação por âncoras e rotas de autenticação (`/login` e `/cadastro`) sem alterar os fluxos internos do sistema.

---

## Implementation Decisions

### Comportamento da rota `/` para visitante e usuário autenticado

- Exibir a tela de apresentação sempre igual em `/` para todos (visitantes e usuários logados), mantendo os links para `/login` e `/cadastro` (usuários já logados que clicarem em `/login` ou `/cadastro` já são redirecionados para `/painel` pelo middleware `guest` do Breeze).

### Navegação entre tela de apresentação e autenticação

- Ligar os links do topo `"Recursos"` e `"Como funciona"` às âncoras `#recursos` e `#como-funciona`.
- Ligar os botões `"Entrar"` (topo), `"Entrar no MeuBolso →"` (hero) e `"Entrar no MeuBolso →"` (CTA final) para `/login` (`route('login')`).
- Ligar o botão `"Criar conta"` (hero) para `/cadastro` (`route('cadastro')`).
- Manter todo o restante dos textos e estrutura visual fiéis ao HTML enviado, sem alterar as telas de Login e Cadastro.

### Texto do card "Categorias" (divergência do arquivo original)

- Ajustar apenas o texto do card `"Categorias"` no bento grid para `"Categorias prontas e coloridas para receitas e despesas."`, preservando o layout, cores e tipografia originais (registrado em `AD-002`).

### Agent's Discretion

- Organização dos arquivos Blade e CSS estático (`resources/views/apresentacao.blade.php` e `public/css/apresentacao.css` ou CSS dedicado sem conflitar com Bootstrap/`meubolso.css`, sem `npm`, Vite ou Tailwind).

### Declined / Undiscussed Gray Areas → Assumptions

- Nenhuma área em aberto; todas as decisões de produto foram respondidas pelo usuário.

---

## Specific References

- Arquivo fonte fornecido pelo usuário: `C:\Users\danil\Downloads\MeuBolso – Tela de apresentação.html`
- Captura renderizada vinculante: `.specs/features/tela-apresentacao/captures/apresentacao-desktop.png`

---

## Deferred Ideas

- CRUD de categorias personalizadas (mencionado em `AGENTS.md` como possível feature futura; fora do escopo desta tela).

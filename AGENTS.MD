# Registro de Progresso e Instruções - MeuBolso (Laravel)

## 1. Visão Geral e Estilo do Projeto
- **Projeto:** MeuBolso – Sistema Web de Controle de Gastos Pessoais (disciplina **Back-End Framework**, 4º período).
- **Proposta original:** `C:\Users\danil\Downloads\PROPOSTA_MeuBolso.docx`
- **Protótipo front-end (preservado intacto):** `C:\Users\danil\OneDrive\Documentos\Projetos\meubolso-front`
- **Projeto Laravel oficial:** `C:\Users\danil\OneDrive\Documentos\Projetos\meubolso`

### Regra de Estilo de Código (IMPORTANTE)
- Manter o código limpo, direto e didático no padrão **MVC clássico de projeto acadêmico (4º período)**:
  - Sem *over-engineering* (não criar Repositories, Services, DTOs, Actions ou FormRequests desnecessários).
  - Validação direta nos Controllers (`$request->validate(...)`), consultas claras com Eloquent e verificação simples de dono (`abort_if(... !== auth()->id(), 403)`).
  - Nomenclatura de domínio e comentários curtos em português, sem comentários artificiais ou excesso de PHPDoc.
  - Front-end 100% em **Blade + Bootstrap 5 (CDN) + `public/css/meubolso.css` + `public/js/painel.js`** (sem `npm`, Vite ou Tailwind).

---

## 2. Ambiente Configurado na Máquina
- **PHP 8.3.36** instalado via `winget` (`PHP.PHP.8.3`), com `php.ini` configurado (`openssl`, `pdo_sqlite`, `pdo_mysql`, `mysqli`, `mbstring`, `gd`, `zip`, `curl`, `fileinfo`, `intl`).
- **Composer 2.11.2** instalado junto ao PHP (`composer` disponível no PATH).
- **MySQL Server 8.0.46 + MySQL Workbench** instalados via MySQL Installer for Windows:
  - Serviço configurado com nome **`MySQL`**, rodando na porta **`3306`**.
  - *(Obs.: existia um serviço antigo chamado `MySQL80` que foi parado via `services.msc` para liberar a porta 3306; ele deve ficar desativado para não conflitar.)*

---

## 3. O que Já Foi Feito no `meubolso`
1. **Projeto e Pacotes:**
   - Laravel `v13.43.0`, Laravel Breeze `v2.4.2`, `barryvdh/laravel-dompdf` `v3.1.2`.
   - Limpeza concluída: removidos `package.json`, `node_modules`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js` e views/componentes Tailwind não usados.
2. **Banco de Dados (Migrations e Models):**
   - Tabelas: `users`, `categorias`, `transacoes`, `orcamentos`.
   - Models: `App\Models\User`, `App\Models\Categoria` (com `criarPadraoPara(User $user)`), `App\Models\Transacao`, `App\Models\Orcamento`.
   - Seeder (`DatabaseSeeder`): usuário demo `danilo@meubolso.com` / senha `12345678`, com categorias padrão, lançamentos de exemplo e metas mensais.
   - Atualmente o `.env` ainda está apontando para **SQLite** (`database/database.sqlite`, já populado e funcionando para testes locais).
3. **Autenticação, Controllers e Views:**
   - Auth completa com Breeze adaptada para Bootstrap 5 (`login`, `register`/`cadastro`, `forgot-password`, `reset-password`, `confirm-password`, `verify-email`, `logout` via `POST` e `profile/edit`).
   - `PainelController`: resumo do mês (saldo, receitas, despesas), últimas 5 transações e dados dinâmicos para os dois gráficos (`Chart.js` em `public/js/painel.js`).
   - `TransacaoController`: listagem com filtros (`de`, `ate`, `tipo`, `categoria_id`), criação (`POST`), edição (`PUT` via modal) e exclusão (`DELETE`).
   - `OrcamentoController`: metas mensais por categoria com cálculo real dos gastos do mês, alertas (80% / 100%) e remoção de meta.
   - `RelatorioController`: resumo por mês de referência e exportação em **PDF** (`GET /relatorios/pdf` usando `relatorios/pdf.blade.php` + DomPDF).
4. **Testes Automatizados:**
   - `php artisan test`: **30 testes passaram** (`99` asserções), cobrindo Breeze Auth, redirecionamentos, CRUD de transações, isolamento entre usuários, orçamentos e geração de PDF.

---

## 4. Onde Paramos e Próximos Passos ao Retornar
**Status atual:** A instalação do **MySQL Server 8.0.46 + MySQL Workbench** acabou de concluir todos os passos com check verde (parou na tela final de clicar em **Finish**). O banco `meubolso` ainda **não foi criado** no MySQL e o `.env` ainda está usando SQLite.

**Ao retomar:**
1. Confirmar que o instalador do MySQL foi finalizado e o serviço `MySQL` está ativo na porta `3306`.
2. Atualizar o `.env` em `C:\Users\danil\OneDrive\Documentos\Projetos\meubolso\.env` para usar MySQL (`DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=meubolso`, `DB_USERNAME=root`, `DB_PASSWORD=<senha do root>`).
3. Criar o database `meubolso` no MySQL e rodar `php artisan migrate --seed` (ou `php artisan migrate` se o usuário preferir banco zerado).
4. Testar o sistema no navegador com `php artisan serve` conectado ao MySQL (login, transações, orçamentos, gráficos e download de PDF) e verificar com o usuário próximos ajustes (ex.: cadastro de categorias personalizadas, `.env.example` documentado e preparação para subir no GitHub).

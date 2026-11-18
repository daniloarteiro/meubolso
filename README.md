# MeuBolso - Sistema Web de Controle de Gastos Pessoais

Aplicação web desenvolvida em **PHP + Laravel** para controle de receitas, despesas, metas mensais por categoria e relatórios em PDF.

## Requisitos
- PHP 8.2+
- Composer
- SQLite (para rodar rápido localmente) ou MySQL (XAMPP / Laragon)

## Como rodar o projeto

1. Entre na pasta do projeto e instale as dependências:
   ```bash
   composer install
   ```
2. Copie o arquivo de configuração e gere a chave da aplicação:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Crie as tabelas e os dados iniciais de exemplo:
   ```bash
   php artisan migrate --seed
   ```
4. Suba o servidor local:
   ```bash
   php artisan serve
   ```
5. Acesse no navegador: `http://127.0.0.1:8000`

### Usuário de exemplo (criado pelo seeder)
- **E-mail:** `danilo@meubolso.com`
- **Senha:** `12345678`

## Usando com MySQL (XAMPP / Laragon)
No arquivo `.env`, troque a configuração de banco para MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meubolso
DB_USERNAME=root
DB_PASSWORD=
```
Depois crie o banco `meubolso` no phpMyAdmin e rode `php artisan migrate --seed`.

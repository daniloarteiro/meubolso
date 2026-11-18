# normalizacao-banco-mysql proofs

**HEAD**: 269b9eb
**Tree**: clean
**Shell**: C:/Program Files/Git\bin\bash.exe
**Result**: 17 pass, 0 fail

| Check | Proof | Result |
| --- | --- | --- |
| C1 | `run feature / ✔ Schema normalizado sem tipo em transacoes e com indices esperados` | pass |
| C2 | `run feature / ✔ Schema normalizado sem tipo em transacoes e com indices esperados` | pass |
| C3 | `run feature / ✔ Schema normalizado sem tipo em transacoes e com indices esperados` | pass |
| C4 | `run feature / ✔ Usuario pode criar editar filtrar e excluir transacao` | pass |
| C5 | `run feature / ✔ Painel e relatorio derivam receitas e despesas da categoria` | pass |
| C6 | `run feature / ✔ Usuario pode criar editar filtrar e excluir transacao` | pass |
| C7 | `walk - abrir modal Nova transacao e Editar transacao com tipo Despesa e confirmar opcoes visiveis apenas de de` | pass (Walked) |
| C8 | `walk - alternar Tipo para Receita no modal e confirmar que apenas Salario e Freelance permanecem visiveis e se` | pass (Walked) |
| C9 | `walk - clicar Editar em transacao de receita e de despesa e confirmar Tipo e Categoria pre-selecionados` | pass (Walked) |
| C10 | `run feature / ✔ Orcamento rejeita categoria de receita` | pass |
| C11 | `run feature / ✔ Excluir orcamento preserva transacoes da categoria` | pass |
| C12 | `php -r 'require "vendor/autoload.php"; Dotenv\Dotenv::createImmutable(".")->load(); exit(($_ENV["DB_CONNECTION` | pass |
| C13 | `php -r '$c=file_get_contents(".env.example"); exit((str_contains($c,"DB_CONNECTION=mysql") && str_contains($c,` | pass |
| C14 | `php artisan migrate:fresh --seed --no-interaction` | pass |
| C15 | `run feature / ✔ Barra de navegacao exibe link para meu perfil` | pass |
| C16 | `run feature / ✔ Barra de navegacao exibe link para meu perfil` | pass |
| Build | `php artisan test` | pass |

## Output

`Run feature` - exit 0

```
 ✔ Visitante nao acessa rotas internas
 ✔ Cadastro cria categorias padrao
 ✔ Usuario pode criar editar filtrar e excluir transacao
 ✔ Usuario nao pode alterar ou excluir transacao de outro
 ✔ Orcamentos e relatorio pdf funcionam
 ✔ Painel e relatorio derivam receitas e despesas da categoria
 ✔ Orcamento rejeita categoria de receita
 ✔ Excluir orcamento preserva transacoes da categoria
 ✔ Schema normalizado sem tipo em transacoes e com indices esperados
 ✔ Categoria com transacoes nao pode ser excluida
 ✔ Modal de transacoes renderiza atributos para filtro por tipo
 ✔ Barra de navegacao exibe link para meu perfil

OK (12 tests, 91 assertions)
{"tool":"phpunit","result":"passed","tests":12,"passed":12,"assertions":91,"duration_ms":1188}
```

`php -r 'require "vendor/autoload.php"; Dotenv\Dotenv::createImmutable(".")->load(); exit(($_ENV["DB_CONNECTION` - exit 0

```
(no output)
```

`php -r '$c=file_get_contents(".env.example"); exit((str_contains($c,"DB_CONNECTION=mysql") && str_contains($c,` - exit 0

```
(no output)
```

`php artisan migrate:fresh --seed --no-interaction` - exit 0

```
 INFO Preparing database. 

 Creating migration table .. 15.99ms DONE

 INFO Running migrations. 

 0001_01_01_000000_create_users_table .. 64.53ms DONE
 0001_01_01_000001_create_cache_table .. 46.22ms DONE
 0001_01_01_000002_create_jobs_table .. 66.86ms DONE
 2026_11_17_193225_create_categorias_table .. 61.73ms DONE
 2026_11_17_193226_create_transacoes_table .. 89.78ms DONE
 2026_11_17_193227_create_orcamentos_table .. 112.96ms DONE


 INFO Seeding database.
```

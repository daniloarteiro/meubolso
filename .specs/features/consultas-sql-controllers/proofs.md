# consultas-sql-controllers proofs

**HEAD**: 7234692
**Tree**: clean
**Shell**: C:/Program Files/Git\bin\bash.exe
**Result**: 10 pass, 0 fail

| Check | Proof | Result |
| --- | --- | --- |
| C1 | `run sql / ✔ Painel usa db select parametrizado e isola usuarios` | pass |
| C2 | `run sql / ✔ Painel retorna zeros em estado vazio` | pass |
| C3 | `run sql / ✔ Relatorio e pdf usam db select parametrizado e isolam usuarios` | pass |
| C4 | `run sql / ✔ Relatorio trata mes vazio sem divisao por zero` | pass |
| C5 | `run sql / ✔ Transacoes index filtra por periodo tipo categoria e isola usuarios` | pass |
| C6 | `run sql / ✔ Transacoes index neutraliza payload de sql injection nos filtros` | pass |
| C7 | `run sql / ✔ Orcamentos index usa db select parametrizado e isola usuarios` | pass |
| C8 | `run sql / ✔ Orcamentos index retorna gasto zero e limite nulo quando vazio` | pass |
| C9 | `run sql / ✔ Parametros em array e mes invalido nao geram erro 500 nem overflow` | pass |
| Build | `php artisan test` | pass |

## Output

`Run sql` - exit 0

```
Time: 00:01.129, Memory: 66.00 MB

Consultas Sql (Tests\Feature\ConsultasSql)
 ✔ Painel usa db select parametrizado e isola usuarios
 ✔ Painel retorna zeros em estado vazio
 ✔ Relatorio e pdf usam db select parametrizado e isolam usuarios
 ✔ Relatorio trata mes vazio sem divisao por zero
 ✔ Transacoes index filtra por periodo tipo categoria e isola usuarios
 ✔ Transacoes index neutraliza payload de sql injection nos filtros
 ✔ Orcamentos index usa db select parametrizado e isola usuarios
 ✔ Orcamentos index retorna gasto zero e limite nulo quando vazio
 ✔ Parametros em array e mes invalido nao geram erro 500 nem overflow

OK (9 tests, 108 assertions)
{"tool":"phpunit","result":"passed","tests":9,"passed":9,"assertions":108,"duration_ms":1130}
```

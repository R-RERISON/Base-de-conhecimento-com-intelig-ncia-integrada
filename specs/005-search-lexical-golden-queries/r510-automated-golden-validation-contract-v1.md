# R-510 — Automated Golden Validation Contract v1

**Status:** ACTIVE  
**Aplica-se a:** T513/T514  
**Princípio:** automatizar toda validação objetiva; reservar decisão humana somente para ambiguidade semântica real.

## 1. Fronteira de autoridade

O validator **não cria uma nova verdade Golden**.

Ele pode confirmar automaticamente a continuidade de uma expectativa quando:
- a expectativa já possui origem humana/histórica governada;
- o expected post permanece existente/publicado;
- o expected permanece dentro de `max_rank`;
- a consulta está integralmente representada semanticamente;
- não existe concorrente material à frente;
- não há falha do Content Extractor.

Ele não pode:
- inventar query Golden;
- trocar `expected_post_id`;
- transformar concorrente em expected;
- aceitar caso ambíguo;
- inferir uso real a partir de variante sintética.

## 2. Estados T513

### AUTO_PASS
Confirmação automática do expected/max_rank existente.

Requisitos mínimos:
- expected existe;
- status publish;
- Content Extractor sem erro;
- rank > 0 e <= max_rank;
- semantic query coverage = 100%;
- expected rank = 1;
- nenhuma ambiguidade material;
- algum sinal direto de title/Summary/native ou exact phrase.

Para seed histórico de paridade, severity recomendada: `blocking`.

### REVIEW_REQUIRED
Apenas exceção.

Gatilhos:
- expected rank > 1;
- cobertura semântica incompleta;
- concorrente forte à frente;
- single-token ambiguity;
- ausência de sinal direto suficiente.

O validator entrega evidência comparativa para que a revisão seja sobre a exceção, não sobre toda a suíte.

### AUTO_FAIL
Quebra objetiva:
- expected ausente;
- expected não publicado;
- extração falhou;
- expected não recuperado;
- expected fora de max_rank.

AUTO_FAIL é NO-GO para congelamento da suite.

## 3. Validation score

`validation_score` é uma heurística **exclusiva de detecção de ambiguidade**.

Sinais:
- semantic coverage: 45%;
- title coverage: 30%;
- Summary coverage: 15%;
- native coverage: 10%;
- exact title phrase: bônus 15.

Ele:
- não é Search score;
- não é ranker v1;
- não pode ser reutilizado silenciosamente em G-530;
- não substitui Golden max_rank.

## 4. T514 — Diversity Validator

Classificação automática:
- termo simples;
- product token/mixed case;
- composto/versão;
- frase;
- sigla;
- linguagem natural.

Sinais estruturais automáticos:
- Summary dependent;
- Elementor/mixed semantic gap.

Classes condicionais de uso real:
- typo/variation;
- alias/synonym.

Essas duas últimas só contam como reais quando possuem provenance real/curada.

## 5. Synthetic robustness

O validator pode derivar:
- lowercase;
- uppercase;
- extra whitespace;
- futuras transformações determinísticas aprovadas.

Toda variante deve conter:
- `origin=synthetic`;
- referência ao Golden base;
- expected/max_rank herdados;
- resultado separado da Golden Suite real.

Synthetic PASS mede robustez do normalizer/retrieval, mas não prova que usuários realmente pesquisam daquela forma.

## 6. Gate

T513 pode ser fechado automaticamente se:
- safety PASS;
- AUTO_FAIL = 0;
- REVIEW_REQUIRED = 0.

Se REVIEW_REQUIRED > 0:
- T513 = PASS WITH REVIEW ITEMS;
- somente esses casos exigem decisão humana;
- demais AUTO_PASS permanecem resolvidos.

T514:
- validator calcula cobertura;
- classes faltantes permanecem explícitas;
- não se inventa consulta real para fechar lacuna.

## 7. Segurança

Runner:
- read-only;
- sem schema;
- sem persistência;
- sem query logging;
- sem rede;
- sem mudança global de hooks Search;
- fingerprint editorial before/after;
- independente do ASI.

## 8. Critério de evolução

O objetivo é reduzir revisão humana ao mínimo semanticamente necessário, sem permitir que o próprio ranking gere a expectativa que depois usa para se aprovar.

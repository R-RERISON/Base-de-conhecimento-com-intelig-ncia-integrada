# G-520 / T522 — Ranking Contract v1

**Status:** FROZEN  
**Algorithm version:** `lexical-ranker-v1.0.0`

## Objetivo

Ranking lexical determinístico, explicável e independente de IA/vetor.

## Candidate retrieval

Candidate retrieval não define relevância final.

Modo v1:
1. usar Projection pronta;
2. strict pass: todos os tokens devem ocorrer em pelo menos um campo pesquisável;
3. relaxed pass: se o strict pass não produzir candidatos suficientes, aceitar qualquer token;
4. merge por post_id;
5. revalidar WordPress status/capability;
6. ranquear apenas candidatos autorizados.

Bounds:
- query tokens: máximo 16;
- candidate pool: máximo 200;
- result limit default 20, máximo 50.

## Sinais

Cobertura de tokens por campo é `matched_unique_query_tokens / unique_query_tokens`.

Pesos:

| Sinal | Máximo |
|---|---:|
| exact title phrase | +100 |
| title coverage | 60 |
| summary coverage | 30 |
| heading coverage | 22 |
| taxonomy coverage | 16 |
| body coverage | 10 |
| global token coverage | 40 |
| exact summary phrase | +12 |
| exact heading phrase | +8 |
| exact taxonomy phrase | +5 |
| exact body phrase | +3 |

Cálculo de coverage:
`weight * coverage_fraction`.

`score` final é arredondado em 4 casas.

## Exact phrase

Usar fronteira lexical sobre texto normalizado. Substring interna não vale.

Exemplo:
- `estrutura` casa `estrutura ctc`;
- `estrutura` não casa `infraestrutura`.

## Global coverage

Token é globalmente coberto quando aparece em ao menos um campo do documento.

## Matched signals

O resultado registra somente sinais realmente acionados, por exemplo:
- `exact_title_phrase`;
- `title:1.0000`;
- `summary:0.3333`;
- `global:1.0000`.

## Tie-break

1. score DESC;
2. global coverage DESC;
3. exact title phrase DESC;
4. title coverage DESC;
5. post_id ASC.

Recência não participa do v1.

## Invariantes

- nenhum peso herdado do ASI;
- nenhum boost por usuário;
- nenhum popularity boost;
- nenhuma telemetria;
- nenhum hardcoded alias;
- nenhuma IA;
- nenhuma aleatoriedade;
- mesmo documento/query/version => mesmo score/rank.

## Golden

Qualquer alteração em pesos, sinais ou tie-break exige nova `algorithm_version` e torna evidência anterior STALE.

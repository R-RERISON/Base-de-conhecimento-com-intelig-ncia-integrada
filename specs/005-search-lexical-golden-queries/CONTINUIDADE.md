# Continuidade — SPEC-005 Search Lexical e Golden Queries

## Repositório

- repo: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`;
- branch: `spec005-search-lexical-golden-queries`;
- base: `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`;
- SPEC-004: CLOSED/main;
- SPEC-005: ATIVA / DISCOVERY.

## Decisão central

Lexical primeiro. Golden Queries antes de qualquer evolução de ranking, semantic search, vetores ou IA.

## Baseline atual

O novo plugin não possui Search Retrieval canônico. A única pesquisa existente é a Knowledge List administrativa via `WP_Query s`.

Não há tabela Search, Golden runtime, analytics, vector ou IA.

## Referência histórica

ASI 4.6.8 @ `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

Preservar contratos, não copiar arquitetura histórica.

## Guardrails

- WordPress é autoridade;
- Content Extractor é a origem semântica para derivados;
- Projection é reconstruível;
- nenhuma tabela antes de benchmark;
- nenhum item/deep-link no v1 sem evidência;
- nenhuma queue/analytics;
- nenhuma busca global do tema interceptada sem decisão;
- nenhuma IA/vetor;
- Golden vazia != PASS;
- blocking Golden FAIL = NO-GO;
- runtime engine bloqueado até R-500 + R-510 + G-520.

## Próximo passo exato

R-500:
1. criar diagnóstico read-only de baseline;
2. recontar corpus atual;
3. executar conjunto inicial de consultas contra `WP_Query s`;
4. comparar cobertura com Content Extractor;
5. medir p50/p95;
6. classificar gaps;
7. decidir superfície inicial e necessidade de Projection.

Em paralelo, R-510 deve montar Golden Dataset v1 com consultas reais e expected posts revisados por humano.

> Quem não sabe onde está, não sabe para onde quer ir.

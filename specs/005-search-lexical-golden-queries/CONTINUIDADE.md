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
1. instalar e executar o build T502 `0.5.0-r500-t502.1`;
2. baixar o JSON `bdc-kb-spec005-r500-baseline-*.json`;
3. validar `t502_read_only_safety_pass=true`;
4. interpretar corpus, coverage, ranking e p50/p95;
5. registrar evidência ambiental T502;
6. classificar gaps e avançar T503–T508;
7. em paralelo, continuar R-510 Golden Dataset.

Em paralelo, R-510 deve montar Golden Dataset v1 com consultas reais e expected posts revisados por humano.

> Quem não sabe onde está, não sabe para onde quer ir.


## Build ambiental atual

`0.5.0-r500-t502.1`  
SHA-256 `0b92d355be79c23e6837fa4b11cd6982674b643795f3e8de4f4949b9c47f5b86`.

Menu temporário:
`Base de Conhecimento -> Diagnóstico Search R-500`.

PASS esperado:
`gate_result.t502_read_only_safety_pass=true`.


## Fechamento R-500

R-500: **PASS/CLOSED**.

A busca atual da lista (`s + modified DESC`) não pode ser promovida como Search engine. A busca nativa sem esse override é muito superior nos probes, mas ainda não cobre integralmente o Content Extractor/Summary. Por isso a nova arquitetura seguirá com Search Document semântico + Golden, preservando WordPress como autoridade/fallback.

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


## Build atual — R-510/T510

`0.5.0-r510-t510.1`  
SHA-256 `3b4b4e0ec308c5914ce155e740228ff4b0f735fd76fd1b30b930bce384ce77d3`.

Próximo passo:
1. instalar sobre T502;
2. abrir `Golden Discovery R-510`;
3. executar e baixar JSON;
4. validar safety;
5. revisar candidates do ASI;
6. avançar T511–T516.


## T510 ambiental

PASS ambiental em 2026-09-18.

Conjunto legado:
- 6 Golden ativas;
- todas post-level;
- todas expected posts existentes/publicados;
- todas severity legacy = warning;
- legacy last run PASS 6/6 em algorithm 4.5.0;
- nenhum erro/safety violation.

Fixture candidate:
`fixtures/golden-candidates-legacy-v1.json`.

## Próximo passo atual

**NÃO instalar `0.5.0-r510-t511.1` — SUPERSEDED.**

Executar somente T511 independente `0.5.0-r510-t511.2`:
- `Base de Conhecimento -> Golden Baseline R-510`;
- baixar JSON;
- comparar as 6 Golden nos 3 modos;
- somente depois fazer T513 human review de expected/max_rank/severity;
- T514 precisa complementar diversidade se necessário;
- R-510 permanece OPEN.


## Independência ASI — decisão canônica

ASI é benchmark/fonte histórica, não dependência.

T510 foi a última leitura deliberada do storage ASI para capturar as 6 Golden. A partir daí os dados pertencem à SPEC-005 em fixture própria.

Todo runtime posterior deve passar sem ASI instalado/ativo. Antes do RC:
- static scan zero referências ASI;
- ASI desativado em homologação;
- Golden PASS;
- rebuild do índice próprio PASS.

ADR: `adr-005-001-zero-runtime-dependency-asi.md`.

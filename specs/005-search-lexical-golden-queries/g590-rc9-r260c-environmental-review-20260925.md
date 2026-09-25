# G-590 RC9 / R-260C — Environmental Review

**Evidence:** `bdc-kb-spec005-g590-section-20260925-180631.json`  
**Generated:** 2026-09-25T18:06:28Z  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.9`  
**Build:** `g590.9-ea7203b6c2ab`

## Gate result

- T590-14 PASS
- T590-15 FAIL
- T590-16 PASS
- T590-17 PASS
- T590-18 PASS
- T590-19 FAIL / OPEN

T590-15 permanece o único blocker. R-260C não redefine o gate.

## Lifecycle / regression

- corpus: 623/623;
- extractor errors: 0;
- rebuild explícito: PASS;
- pass1: 623 NO_CHANGE / 0 writes;
- pass2: 623 NO_CHANGE / 0 writes;
- determinism mismatch: 0;
- Projection ready;
- Golden post-level PASS;
- source kinds de probe integralmente cobertos.

## R-260C result

R-260B preservado:
- deterministic candidates: 548;
- promotable_shadow: 211.

Anchor feasibility sobre os 211 promotable:

- paragraph_unique: 131;
- paragraph_ambiguous: 80;
- block_unique_nonparagraph: 0;
- block_ambiguous: 0;
- not_rendered_exact: 0.

Taxas:
- paragraph unique: 62.0853%;
- ambiguous: 37.9147%;
- rendered exact coverage: 100%.

Distribuição dos promotable:
- legacy_html: 156 — 95 unique / 61 ambiguous;
- Elementor: 50 — 33 unique / 17 ambiguous;
- mixed: 4 — 2 unique / 2 ambiguous;
- Gutenberg: 1 — 1 unique / 0 ambiguous.

## Performance / safety

- p50: 188.0791 ms;
- p95: 305.8531 ms;
- max: 341.8281 ms;
- budget: p95 <= 900 ms / max <= 1500 ms;
- technical failures: 0;
- editorial fingerprint: equal;
- corpus IDs: equal;
- no editorial write;
- no external network;
- no ASI runtime dependency;
- parent ranker frozen.

## Decision

**R-260C = PASS / DISCOVERY CLOSED.**

A evidência prova que:
1. todos os 211 promotable possuem representação exata no HTML renderizado;
2. 131 já possuem alvo paragraph inequívoco;
3. 80 possuem múltiplas ocorrências exatas e não podem usar first/nth occurrence;
4. ausência de target não é o problema;
5. Deep-Link Contract v2 é viável somente em modo fail-closed.

Não é autorizado promover os 80 ambiguous por heurística posicional.

## R-260D1

Antes de runtime promotion, abrir diagnóstico contextual:
- título exato;
- dois blocos corporais fonte adjacentes;
- comparação exata e ordenada contra os blocos imediatamente posteriores a cada ocorrência renderizada;
- exatamente um match contextual => `context_unique`;
- zero/múltiplos => unresolved.

Contrato: `specs/004-content-extractor-knowledge-document/r260d-contextual-anchor-feasibility-contract-v1.md`.

R-260D1 não altera Search Section Projector, Anchor Manager, Content Extractor, KD ou conteúdo editorial.

## RC10

Candidato preparado:
- Product Version: `0.5.1-rc.10`;
- Build ID: `g590.10-cf1374fd85de`;
- source commit: `cf1374fd85de56c8d31192948a15d7e274eee1eb`;
- runner blob: `e9e9d05ffa13b141631ff29c236ae1192ee9fca3`;
- R260D1 blob: `c37380e1aa329c43289fcaa9959a448a8295fdbe`;
- ZIP SHA-256: `456922cf831ef171a9d1ad8970bf3506294e1ac297e739bd690bc100ab603d5c`;
- files: 86;
- PHP: 73/73;
- JS: 3/3;
- JSON: 2/2;
- active requires: 69/69;
- R260D1 behavior: 15/15;
- deterministic build: 2/2 identical;
- delta vs RC9: 1 added / 2 modified / 0 deleted;
- RC9 inherited byte-identical: 83/83.

Next: executar RC10 e validar com `validate-r260d-evidence.php`. G-590 permanece OPEN.

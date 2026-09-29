# G-590 RC10 / R-260D1 — Environmental Review

**Evidence:** `bdc-kb-spec005-g590-section-20260928-111102.json`  
**Generated:** 2026-09-28T11:09:15Z  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.10`  
**Build:** `g590.10-cf1374fd85de`

## Gate result

- T590-14 PASS
- T590-15 FAIL / OPEN
- T590-16 PASS
- T590-17 PASS
- T590-18 PASS
- T590-19 FAIL / OPEN

G-590 permanece OPEN. D1 não redefine T590-15.

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

## R-260D1 result

Sobre os 211 `promotable_shadow`:

- title_unique: 131;
- context_unique: 66;
- context_ambiguous: 0;
- context_insufficient: 2;
- context_not_matched: 12;
- title_not_rendered: 0;
- effective_unique_count: 197;
- effective_unique_rate: 93.3649%;
- unresolved_remaining_count: 14.

Ganho contextual:
- 66/80 targets anteriormente ambíguos foram resolvidos deterministicamente;
- nenhum permaneceu `context_ambiguous`;
- nenhum target deixou de existir no HTML renderizado.

Distribuição:
- legacy_html: 156 promotable / 144 effective / 12 unresolved;
- Elementor: 50 / 48 / 2;
- mixed: 4 / 4 / 0;
- Gutenberg: 1 / 1 / 0.

## Performance / safety

- p50: 190.0358 ms;
- p95: 341.861 ms;
- max: 350.6229 ms;
- budget: p95 <= 900 ms / max <= 1500 ms;
- technical failures: 0;
- editorial fingerprint: equal;
- corpus IDs: equal;
- no editorial write;
- no external network;
- no ASI runtime dependency;
- parent ranker frozen.

## Decision

**R-260D1 = PASS / DISCOVERY CLOSED.**

D1 autoriza R-260D2 porque:
1. contextual matching resolveu 66/80 duplicidades renderizadas sem heurística posicional;
2. 197/211 unique-label promotable possuem target comprovado;
3. os 14 restantes podem permanecer Section `unresolved`;
4. retrieval e navigability continuam separados;
5. nenhum dado exige mutação editorial.

## Extensão da decisão para todos os body-bearing

R-260A/R-260B já provaram:
- deterministic candidates: 548;
- TOC-like: 77;
- body-bearing: 289;
- uncertain: 182;
- body-bearing = 211 unique-label promotable + 78 duplicate-label candidates.

O contrato de Section já preserva identidade distinta mesmo quando o título é duplicado. Portanto os 78 duplicate-label body-bearing não devem desaparecer do retrieval; eles podem existir como Sections `unresolved`.

Capacity foi reavaliada para headings + **todos os 289 body-bearing**:
- nenhum post excede `MAX_SECTIONS=64`;
- pior cenário observado <=55 Sections.

## R-260D2

Arquitetura congelada:
**Option C — Dedicated Structural Projection shared by consumers**.

Contratos:
- `../004-content-extractor-knowledge-document/r260d2-structural-runtime-promotion-contract-v1.md`;
- `g590-deep-link-contract-v2.md`;
- `g590-evidence-contract-addendum-v1.5.md`.

T590-15 v1.5 deixa de usar raw `strong_numbered_without_heading_context==0` como proxy universal e passa a exigir zero perda de estrutura determinística body-bearing comprovada, mantendo os raw counters como telemetria obrigatória.

## RC11

Runtime candidate preparado a partir do source commit:
`6952f6c29aca2ec0dec4ee4b19b0edf2e5a08563`.

- Product Version: `0.5.1-rc.11`;
- Build ID: `g590.11-6952f6c29aca`;
- ZIP SHA-256: `49c2a96bef202c5f866499539055d1f9b78b14fe2fc840c69cf3cc47f1b63e76`;
- files: 88;
- PHP: 75/75;
- JS: 3/3;
- JSON: 2/2;
- active requires: 71/71;
- R-260D2 behavior: 18/18;
- Evidence Contract v1.5 fail-closed: 13/13;
- GitHub runtime blob parity: 7/7;
- deterministic build: 2/2 byte-identical;
- delta RC10→RC11: +2 / ~6 / -0;
- inherited byte-identical: 80/86.

Protected runtime byte-identical ao RC10:
- Content Extractor;
- Knowledge Document;
- lexical-ranker-v1.0.0;
- Section Ranker;
- Section Service;
- Search Service;
- Search Projection Repository.

G-590 só pode fechar após evidência ambiental RC11.

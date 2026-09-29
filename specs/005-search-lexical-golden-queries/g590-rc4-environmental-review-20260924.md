# G-590 RC4 — Environmental Evidence Review

**Evidence:** `bdc-kb-spec005-g590-section-20260924-142106.json`  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.4`  
**Build:** `g590.4-5d74569e70bb`

## Resultado do gate

- T590-14 Cross-SPEC regression: **PASS**
- T590-15 Coverage audit: **FAIL**
- T590-16 Section Golden/Deep-Link: **FAIL**
- T590-17 Lifecycle/schema: **PASS**
- T590-18 Security/performance/safety: **PASS**
- T590-19 G-590: **FAIL / OPEN**

## Baselines preservadas

Lifecycle:
- schema 1.1 PASS;
- 623 rows;
- prepare sem implicit rebuild;
- explicit rebuild PASS;
- pass1 623 processed / 0 written / 623 NO_CHANGE;
- pass2 623 processed / 0 written / 623 NO_CHANGE;
- determinism 623 / 0 mismatch.

Coverage base:
- corpus 623/623 analisado;
- extractor errors 0;
- 772 Sections;
- 620 generated anchors;
- 152 unresolved.

Section/deep-link probes executados:
- 12 elegíveis;
- section query failures 0;
- deep-link failures 0;
- visible text changes 0.

Performance:
- p50 206.1319 ms;
- p95 376.817 ms;
- max 392.0569 ms;
- p95 budget 900 ms;
- max budget 1500 ms;
- technical failures 0.

Golden:
- status PASS;
- blocking failures 0;
- warning failures 0;
- technical failures 0.

Safety:
- editorial fingerprint igual before/after;
- corpus IDs iguais;
- no ASI;
- no network;
- no editorial write;
- parent ranker frozen.

## T590-15 — análise

RC4 registrou 9.639 nodes numerados não-heading, dos quais 6.054 sem heading contextual.

Review do código demonstrou que o contador tratava todos os nodes de `Numbered_Hierarchy_Resolver` como strong signal, inclusive `explicit_dom` list items.

Isso não corresponde ao contrato semântico do gate.

Classificação: **EVIDENCE CONTRACT DEFECT**.

Correção: Evidence Contract v1.3.

## T590-16 — análise

Generated anchors por source kind:
- Elementor: 235;
- Gutenberg: 17;
- legacy_html: 368.

`unprobed_source_kinds=['gutenberg']`.

Os 12 probes que foram formulados passaram integralmente.

O selector v1.2 descartava repeated-title candidate quando não encontrava rare section token. A ausência de probe não prova falha do runtime Search.

Classificação: **EVIDENCE PROBE SELECTION GAP**.

Correção: fallback `repeated_title_runtime_probe`, sem auto-pass.

## Disposição

G-590 permanece OPEN.

Não reabrir SPEC-004 nesta evidência.

Primeiro executar RC5 com Evidence Contract v1.3:
- se strong hierarchy gap = 0 e Gutenberg probe passar, fechar os dois false negatives;
- se strong hierarchy gap > 0, revisar amostras concretas e somente então decidir reabertura SPEC-004;
- se Gutenberg runtime probe falhar, investigar Search/Section Retrieval real.

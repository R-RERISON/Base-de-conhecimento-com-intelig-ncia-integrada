# G-590 RC11 / R-260D2 — Environmental Review

**Evidence:** `bdc-kb-spec005-g590-section-20260928-121622.json`  
**Generated:** 2026-09-28T11:53:24Z  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.11`  
**Build:** `g590.11-6952f6c29aca`

## Gate result

- T590-14 PASS
- T590-15 FAIL / OPEN
- T590-16 PASS
- T590-17 PASS
- T590-18 PASS
- T590-19 FAIL / OPEN

G-590 permanece OPEN.

## Lifecycle

- schema 1.1.0 íntegro;
- pre-update Projection em `search-section-projection-v1.0.0`;
- prepare marcou degraded por version mismatch, sem implicit rebuild;
- explicit rebuild PASS;
- 623/623 processados;
- pass1: 623 writes esperados pela mudança de Section Projection;
- pass2: 623 NO_CHANGE / 0 writes;
- determinism mismatch: 0;
- state_after ready em `search-section-projection-v1.1.0`;
- errors=[] / throwables=[].

## Runtime structural projection

RC11 comprovou que a promoção runtime em si está íntegra:

- deterministic_candidate_count: 548;
- body_bearing_count: 289;
- canonical_projected_count: 289;
- runtime_projected_count: 289;
- duplicate_body_count: 78;
- generated anchors: 263;
- unresolved anchors: 26;
- unsafe_promotion_count: 0;
- projection_gap_count: 0;
- projection_extra_count: 0;
- identity_collision_count: 0;
- overflow_post_count: 0;
- overflow_total: 0;
- generated_anchor_materialization_failed: 0;
- visible_text_changed: 0;
- repository_error_count: 0.

## Único blocker

A partição runtime foi:

- TOC suppressed: 62;
- body projected: 289;
- uncertain: 197;
- existing-heading redundant: 0.

Mas R-260A canônico registra:

- TOC signal: 77;
- body signal: 289;
- uncertain: 182.

Diferença exata:
- 15 candidates foram classificados runtime como `uncertain` quando deveriam ser `toc_suppressed`;
- body-bearing e Sections projetadas não foram afetados.

Posts com diferença:
- 574 / Elementor: 14→12 TOC;
- 600 / Elementor: 4→0;
- 1307 / legacy_html: 2→0;
- 36492 / Elementor: 4→1;
- 36532 / legacy_html: 1→0;
- 36549 / Elementor: 3→0.

## Root cause

R-260A:
1. coleta `label_occurrences/token_occurrences` sobre **todos os hierarchy nodes**;
2. depois filtra o subset de candidates;
3. usa ocorrências posteriores, mesmo quando o node posterior não é candidate runtime.

Structural Projection v1.0.0:
1. filtrava candidates primeiro;
2. calculava occurrences apenas dentro do candidate subset.

Consequência:
- quando uma repetição posterior possuía heading context ou outra condição que a retirava do candidate subset, o candidate early perdia `later_same_label`;
- 15 TOC signals viraram uncertain;
- Evidence Contract v1.5 corretamente bloqueou T590-15.

## Classificação do incidente

**FAIL CONTROLADO / CLASSIFICATION PARITY DRIFT.**

Não é:
- perda de Section;
- regressão de ranking;
- deep-link failure;
- lifecycle failure;
- performance failure;
- safety failure.

O gate funcionou como projetado.

## Performance / safety

- p50: 154.7291 ms;
- p95: 231.3879 ms;
- max: 238.5709 ms;
- technical_failure_count: 0;
- performance PASS;
- editorial fingerprint equal;
- corpus IDs equal;
- no ASI;
- no network;
- no editorial write;
- parent ranker frozen;
- candidate path lightweight.

## RC12 correction

Structural Projection `v1.0.1`:
- occurrence population passa a usar todos os hierarchy nodes, exatamente como R-260A;
- candidate filtering continua posterior;
- novo regression trava paridade candidate/TOC/body/uncertain;
- 289 body-bearing permanecem os mesmos;
- nenhum outro runtime é alterado.

RC12:
- Product Version `0.5.1-rc.12`;
- Build ID `g590.12-61681200f115`;
- source commit `61681200f1152d98caf949568cfd99810c83e57a`;
- ZIP SHA-256 `eeeb4222ed2e68ed6a488d088df8d3e61bd6313d87ace09f06f2cb8173b55026`;
- files 88;
- PHP 75/75;
- JS 3/3;
- JSON 2/2;
- D2 behavior 23/23;
- R-260A ↔ runtime parity 4/4;
- Evidence v1.5 13/13;
- deterministic 2/2;
- delta RC11→RC12: +0 / ~2 / -0;
- inherited byte-identical: 86/88.

## Disposition

- RC11 = FAIL CONTROLADO;
- R-260D2 runtime promotion permanece arquitetura aceita;
- Structural Projection v1.0.0 superseded by v1.0.1;
- T590-15 permanece OPEN;
- T590-19 permanece OPEN;
- próximo passo: RC12 environmental rerun.

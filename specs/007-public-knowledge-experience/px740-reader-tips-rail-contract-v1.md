# PX-740 — Reader / Tips / Rail Contract v1

**Status:** ACTIVE / DISCOVERY  
**Date:** 2026-10-05  
**SPEC:** 007 — Public Knowledge Experience  
**Prerequisite:** PX-730 `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`

## Objective

Harden the candidate Article Reader composition while preserving canonical WordPress content rendering and current external ownership boundaries.

PX-740 does not redesign the Reader from zero. It starts from the accepted UX-005 Reader, the current `Public_Article_Read_Model`, `Public_Article_Content`, GRE Helpful Tips/Summary behavior, GAC and WP Unified Indexer callbacks.

## Canonical content boundary

Frozen:
- WordPress loop remains canonical;
- candidate Reader continues to call the canonical `the_content` pipeline;
- raw `post_content` replacement is prohibited;
- GAC and WPUI compatible callbacks must remain effective;
- BDC may suppress duplicate external renderers only for the candidate Reader request and only after the same underlying data is represented intentionally by the BDC shell.

## Helpful Tips ownership

Frozen baseline:
- canonical storage remains `_bdc_es_helpful_tips`;
- BDC `Helpful_Tips_Store` is the canonical API/physical-key owner inside the current plugin runtime;
- GRE remains an external legacy renderer over compatible data and is not the canonical Reader owner;
- BDC Reader reads and presents canonical Tips data;
- PX-740 must not migrate, rewrite or normalize stored Tips values;
- no duplicate Helpful Tips renderer may appear in the candidate Reader.

## Executive Summary Rail ownership

Frozen after discovery:
- BDC `Summary_Store`, `Classification_Store` and `Knowledge_Facts_Store` provide the canonical sources used by the Reader summary projection;
- `Public_Article_Read_Model::summary_items()` is the BDC-owned read-model composition boundary;
- GRE remains the external legacy renderer on the legacy surface;
- BDC Reader owns candidate Summary Rail presentation;
- candidate-only duplicate suppression behavior;
- empty/missing summary behavior;
- responsive rail behavior.

No storage migration is authorized.

## Reader composition contract

Candidate Reader must preserve this order:
1. BDC public shell/header;
2. Reader breadcrumb/title/meta;
3. BDC Helpful Tips presentation when canonical data exists;
4. canonical WordPress article content via `the_content`;
5. Executive Summary Rail when canonical summary data exists.

Third-party compatible content produced through `the_content` remains inside the document region.

## Compatibility contract

At minimum preserve:
- WordPress core content filters;
- GAC post-actions/contribution bridge;
- WP Unified Indexer anchors;
- BDC Search Anchor Manager;
- shortcode/block rendering;
- source kinds currently observed in the corpus: legacy HTML, plain text, Elementor, mixed and Gutenberg.

GRE duplicate Tips/Rail renderers may be suppressed only inside candidate Reader requests.

## Empty/degraded states

Required:
- missing Tips does not affect article rendering;
- missing Summary does not affect article rendering;
- empty canonical content produces the existing explicit Reader empty state;
- one optional projection failing must not suppress canonical article content.

## Accessibility/responsive baseline

Required:
- Tips and Rail use semantic section/aside relationships;
- heading hierarchy remains deterministic;
- mobile layout must not obscure article content;
- Rail movement must not cause focus traps or content reordering in the DOM.

Full accessibility certification remains PX-760.

## Invariants

PX-740 must NOT:
- mutate `post_content`;
- mutate `_elementor_data`;
- migrate Helpful Tips/Summary storage;
- remove GAC/WPUI callbacks globally;
- deactivate GRE/GAC/WPUI/Entra;
- alter Search ranking/schema;
- alter Home/Astra/snippets;
- enable public cutover.

## First bounded slice

Before runtime changes:
1. inventory `Public_Article_Read_Model`;
2. inventory `Public_Article_Content`;
3. inventory current GRE Tips/Summary callback/data ownership;
4. inventory GAC/WPUI/BDC callbacks required through `the_content`;
5. compare current Reader output against this contract;
6. identify only concrete duplication/isolation/responsive gaps;
7. add static regression coverage.

No speculative redesign.


## Discovery freeze — 2026-10-05

### Current Reader pipeline

Candidate Reader:
1. enters the real WordPress loop;
2. calls `Public_Article_Content::capture_current_loop()`;
3. that method calls canonical `the_content()`;
4. BDC candidate request suppresses only GRE duplicate Helpful Tips / Summary renderers;
5. GAC and WPUI callbacks remain in `the_content`;
6. BDC renders Tips and Summary from BDC read-model/store APIs.

### Current hook compatibility

Current homologation inventory confirms:
- GAC PostActions at priority 12;
- GAC KnowledgeBridge at priority 13;
- GRE Helpful Tips legacy renderer at priority 15;
- WP Unified Indexer anchors at priority 20;
- BDC Search Anchor Manager at priority 25;
- GRE legacy Summary renderer at priority 30.

Candidate-only suppression removes GRE priority 15/30 duplication while preserving GAC/WPUI/BDC content-pipeline callbacks.

### Ownership correction

Earlier wording that treated Helpful Tips data ownership as external was imprecise.

Frozen:
- `Helpful_Tips_Store` owns the BDC canonical API for `_bdc_es_helpful_tips`;
- `Summary_Store` + Classification/Facts stores own BDC summary inputs;
- GRE is an external legacy renderer/compatibility dependency, not the candidate Reader data owner.

### Known gap PX740-GAP-001 — dual Rail movement strategy

Current assets contain two competing movement strategies:
- base Reader CSS defines `.bdc-reader-summary { position: sticky; ... }`;
- later v5 CSS overrides the rail to `position: relative`;
- `public-search.js::initReaderRail()` then moves the rail using scroll/resize listeners and `translate3d()`.

This creates unnecessary coupling between Search JavaScript and Reader layout, duplicates browser-native sticky behavior, and increases regression surface.

Disposition:
- do not change until the regression contract is frozen;
- preferred bounded correction is CSS-native sticky with no scroll-driven transform if homologation confirms parity.

### Known risk PX740-RISK-002 — legacy chrome stripping heuristic

`Public_Article_Content::strip_duplicate_legacy_chrome()` post-processes canonical `the_content` HTML using a DOM/text heuristic.

It is retained for compatibility in this gate until regression evidence proves it can be narrowed or removed. PX-740 must not broaden this heuristic.


## PX740-GAP-001 resolution — 2026-10-05

Implemented bounded correction:
- removed `initReaderRail()` from Search JS;
- removed scroll/resize listeners and `translate3d` Rail movement;
- restored CSS-native `position: sticky` as the only desktop follow behavior;
- retained mobile `position: static` reflow at <=1040px;
- retained print static flow.

Not changed:
- canonical `the_content`;
- `strip_duplicate_legacy_chrome()`;
- Helpful Tips/Summary data;
- Reader template structure;
- GAC/WPUI callbacks;
- GRE candidate-only duplicate suppression.


## Human smoke correction — px740.1

Observed in real WordPress homologation:
- Reader renders;
- Summary Rail renders;
- Summary Rail does not follow scroll.

Therefore the CSS-native sticky-only resolution of PX740-GAP-001 is rejected.

Revised frozen approach:
- Search JS must remain free of Reader scroll logic;
- Reader follow behavior belongs in dedicated `assets/js/public-reader.js`;
- desktop Rail uses bounded relative translation inside `bdc-reader-summary-slot`;
- scroll work is requestAnimationFrame-throttled;
- ResizeObserver refreshes limits when article/Rail dimensions change;
- <=1040px Rail returns to normal document flow;
- the Rail may not escape the vertical bounds of its slot.

`px740.1` is superseded and cannot close PX-740.

# PX-740 — Reader / Tips / Rail Inventory

**Date:** 2026-10-05  
**Status:** DISCOVERY_FROZEN  
**SPEC:** 007 — Public Knowledge Experience

## Reader composition

Current candidate template:
- obtains the queried post;
- builds `Public_Article_Read_Model`;
- enters the canonical WordPress loop;
- captures content through `Public_Article_Content::capture_current_loop()`;
- renders BDC Tips before the canonical document region;
- renders BDC Summary Rail alongside the article when structured data exists.

## Canonical content boundary

`Public_Article_Content::capture_current_loop()` calls `the_content()`.

This preserves WordPress and compatible third-party filtering. No raw `post_content` replacement is used by the candidate template.

## Hook compatibility matrix

| Priority | Owner | Callback role | Candidate disposition |
| --- | --- | --- | --- |
| 12 | GAC | PostActions | PRESERVE |
| 13 | GAC | KnowledgeBridge | PRESERVE |
| 15 | GRE | Helpful Tips legacy renderer | SUPPRESS candidate-only to avoid duplicate |
| 20 | WPUI | anchor injection | PRESERVE |
| 25 | BDC | Search Anchor Manager | PRESERVE |
| 30 | GRE | Executive Summary legacy renderer | SUPPRESS candidate-only to avoid duplicate |

No global callback removal is authorized.

## Helpful Tips ownership

- physical key: `_bdc_es_helpful_tips`;
- BDC canonical API: `Helpful_Tips_Store`;
- candidate rendering: BDC Reader;
- legacy compatibility renderer: GRE;
- storage migration: NOT AUTHORIZED.

## Summary Rail ownership

BDC Reader composition uses:
- `Summary_Store`;
- `Classification_Store`;
- `Knowledge_Facts_Store`;
- `Public_Article_Read_Model::summary_items()`.

GRE remains the legacy renderer on the legacy surface.

## Source-kind regression scope

Current environment requires compatibility with:
- legacy HTML;
- plain text;
- Elementor;
- mixed;
- Gutenberg.

## PX740-GAP-001 — dual rail positioning

Observed:
- base CSS declares sticky Summary Rail;
- later CSS overrides it to relative positioning;
- JS scroll/resize code translates the Rail manually.

Impact:
- duplicated layout strategy;
- Search/Reader concern coupling;
- higher scroll-jank and regression surface;
- more difficult sticky-offset reasoning.

Preferred bounded correction:
- CSS-native sticky;
- remove scroll-driven transform logic;
- preserve mobile static reflow and print static flow.

## PX740-RISK-002 — legacy chrome stripping

`Public_Article_Content::strip_duplicate_legacy_chrome()` heuristically removes one rendered subtree when title + legacy metadata markers match.

Disposition:
- retain unchanged during first PX-740 slice;
- protect with regression contract;
- do not broaden detection rules;
- revisit only with representative corpus evidence.

# G-590 RC6 — Environmental Evidence Review

**Evidence:** `bdc-kb-spec005-g590-section-20260924-172925.json`  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.6`  
**Build:** `g590.6-27223c6ce89e`

## Gate result

- T590-14 Cross-SPEC regression: **PASS**
- T590-15 Coverage audit: **FAIL**
- T590-16 Section Golden/Deep-Link: **PASS**
- T590-17 Lifecycle/schema: **PASS**
- T590-18 Security/performance/safety: **PASS**
- T590-19 G-590: **FAIL / OPEN**

## T590-16 closed

Canonical authorization alignment removed the artificial Gutenberg gap.

Evidence:
- generated anchors: Elementor 235 / Gutenberg 17 / legacy_html 368;
- generated probe-eligible anchors: identical 235 / 17 / 368;
- required source kinds: Elementor / Gutenberg / legacy_html;
- unprobed source kinds: none;
- selected probes: Elementor 10 / Gutenberg 1 / legacy_html 1;
- section query failures: 0;
- deep-link failures: 0;
- visible-text changes: 0.

T590-16 is therefore considered **PASS** under the frozen G-590 contracts.

## T590-15 isolated blocker

Strong numbered non-heading nodes:
- total: 1,677;
- with heading context: 383;
- without heading context: 1,294.

By source kind, without heading context:
- legacy_html: 1,060 (~81.9%);
- Elementor: 178 (~13.8%);
- mixed: 53 (~4.1%);
- Gutenberg: 3 (~0.2%).

By confidence:
- ambiguous: 746;
- deterministic: 548.

Affected posts:
- any strong no-heading gap: 189;
- deterministic paragraph recovery candidates: 94 posts;
- deterministic candidate count: 548.

The deterministic candidates are not safe to auto-promote. RC5/RC6 samples demonstrate both duplicated outline-like occurrences and later body occurrences.

## Architectural disposition

SPEC-004 remains reopened only in **R-260 DISCOVERY/read-only**.

Current leading hypothesis: a dedicated versioned structural projection shared by downstream consumers, rather than changing Content Extractor/KD hashes or implementing Search-only semantics.

This is not yet frozen.

R-260A must first separate:
- duplicated TOC-like sequences;
- body-bearing pseudo-headings;
- uncertain cases.

## Stable controls

- corpus 623/623;
- extractor errors 0;
- rebuild two passes, 0 writes, 0 mismatches;
- Golden PASS;
- p95 305.5241 ms / max 312.0661 ms;
- editorial fingerprint unchanged;
- no ASI;
- no external network;
- no editorial write.

## RC7 purpose

RC7 adds only the pure read-only `R260_Hierarchy_Profiler`.

It reports per-post bounded structural metadata:
- candidate count;
- early position;
- later same-label/token occurrence;
- body span before next structural boundary;
- TOC signal;
- body signal;
- uncertain signal.

It does not alter:
- Content Extractor;
- Knowledge Document;
- Search Section Projector;
- Search Section Ranker;
- Search Section Service;
- Anchor Manager;
- parent lexical ranker.

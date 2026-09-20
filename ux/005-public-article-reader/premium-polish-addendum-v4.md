# UX-004/UX-005 — Premium Polish Addendum v4

**Status:** FROZEN FOR HOMOLOGATION  
**Date:** 2026-09-20  
**Candidate:** `0.5.0-ux004005.6`

## 1. Scope

This iteration is visual/product polish only.

Do not reopen:
- Search architecture/ranking;
- GAC integration;
- Summary follow-scroll algorithm;
- Header functional parity;
- route/cutover strategy.

The v5 functional baseline is preserved.

## 2. Visual authority

The Public Experience must follow:
- `ux/002-mockup-visual-foundation/visual-contract-v2.md`;
- canonical BDC tokens;
- no generic WordPress appearance;
- no card-for-everything composition;
- metadata must not compete with primary content;
- borders before heavy shadows;
- desktop + 782px + 520px behavior.

## 3. Search premium treatment

Preserve:
- input-driven results;
- 2-character minimum;
- 180ms debounce;
- stale request cancellation;
- candidate Reader links;
- Ctrl/Cmd+K.

Refine:
- document-search result language instead of admin-list/card language;
- compact rank;
- category as metadata chip;
- title as primary scan target;
- excerpt limited to two lines;
- subtle arrow affordance;
- own scroll area for long result sets;
- loading state with restrained progress indicator;
- reduced-motion fallback.

The Home server-rendered result path and live result path must use the same visual hierarchy.

## 4. Home premium treatment

Preserve the search-first composition.

Refine only:
- slightly tighter top rhythm;
- quieter title weight;
- refined Search border/focus/shadow;
- less visual weight in frequent topics;
- premium result panel;
- subtle Explore interaction.

Do not reintroduce:
- metrics dashboard;
- large marketing hero;
- mandatory category cards above the fold.

## 5. Header premium treatment

Preserve:
- all five quick links;
- Entra profile menu contract;
- responsive quick-links menu;
- Article Search row.

Refine:
- quieter pill borders;
- smaller shadows;
- more restrained hover;
- Search row integrated with the BDC surface.

## 6. Article Reader premium treatment

Preserve:
- GAC;
- WPUI;
- Helpful Tips data;
- Summary rail;
- follow-scroll behavior;
- no-Summary single column;
- legacy chrome sanitizer.

Refine:
- remove card treatment from the document heading;
- keep only a restrained context separator;
- protect white article surface against the gray application canvas;
- reduce nested-card effect;
- Helpful Tips becomes an integrated information callout;
- stronger reading rhythm and heading scale;
- prose/list maximum line length around 82ch where compatible;
- tables/media retain full available width;
- Executive Summary becomes visually quieter without changing its follow-scroll behavior.

## 7. Acceptance

The v6 candidate is judged primarily on:
1. visual coherence with the BDC admin product;
2. Search scanability;
3. low visual noise;
4. article readability;
5. reduced cardification;
6. GAC and Summary behavior unchanged;
7. Header functional parity unchanged.

No public cutover is authorized by this addendum.

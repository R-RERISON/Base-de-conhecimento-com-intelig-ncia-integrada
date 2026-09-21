# UX-004/UX-005 — Search-first Public Experience Addendum v2

**Status:** FROZEN FOR IMPLEMENTATION  
**Date:** 2026-09-20  
**Supersedes visual composition of:** redesign addendum v1 only; data/parity contracts remain valid.

## 1. Core rule

The public Base de Conhecimento is a retrieval product, not a portal/marketing site.

Search is the dominant public action.

## 2. Home contract refinement

The Home must resemble a search product:
- low visual noise;
- minimal first viewport;
- centered dominant query field;
- no metric dashboard;
- no mandatory large hero card;
- no mandatory category card grid above the fold.

Word Cloud, categories, Latest and Popular remain required capabilities but may be exposed through:
- compact suggestions;
- progressive disclosure;
- secondary exploration region.

Capability preservation does not require simultaneous visual exposure.

## 3. Search everywhere

Search is part of the shared Public Experience shell.

Required on Article Reader:
- visible compact query field in header;
- no return to Home required;
- Ctrl/Cmd+K focuses Search;
- results stay within candidate Reader navigation in preview;
- public cutover later uses the authorization-safe Public Search facade.

Required on Home:
- large primary query field;
- Ctrl/Cmd+K focuses it.

## 4. Preview navigation

During UX-004/UX-005 preview:
- Home search results -> Article Reader preview;
- Latest -> Article Reader preview;
- Popular -> Article Reader preview;
- topic suggestions -> Home candidate search;
- breadcrumb Home -> Home preview.

This fixes the `.2` validation gap where candidate discovery could route back to the legacy article surface.

## 5. Article Reader refinement

Visual hierarchy:
- clean white/document canvas;
- breadcrumb;
- article title;
- minimal metadata;
- content;
- compact Tips;
- sticky Executive Summary Rail.

Avoid:
- large marketing hero;
- repeated title cards;
- unnecessary nested panels;
- excessive gradients;
- large visual ornaments.

## 6. Research-inspired future enhancement

After public authorization facade:
- instant suggestions / search-as-you-type;
- keyboard result navigation;
- section/deep-link results;
- full command palette behavior.

These enhancements must reuse the BDC Search engine/contracts rather than introducing a second retrieval engine.

## 7. Acceptance

`ux004005.3` is the first candidate implementing this addendum.

Human review focuses on:
- search prominence;
- visual quietness;
- article readability;
- global Search usability;
- candidate-to-candidate navigation;
- no legacy duplicate chrome.

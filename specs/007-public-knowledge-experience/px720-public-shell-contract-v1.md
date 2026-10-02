# PX-720 — Public Shell Contract v1

**Status:** CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION  
**Date:** 2026-10-02  
**SPEC:** 007 — Public Knowledge Experience

## Objective

Harden the existing BDC candidate Home/Header/Reader shell into a stable plugin-owned public shell without changing public cutover state.

PX-720 builds on the already accepted preview; it does not redesign from zero.

## Baseline

Use:
- current Public_Experience orchestration;
- current Public Home preview template;
- current Article Reader preview template;
- Public Auth Bridge;
- Public_Home_Read_Model;
- Public_Search_Facade;
- public-foundation/header/home/article CSS;
- public-search.js.

## PX720-01 — Shell ownership

Public shell must remain fully plugin-owned in candidate mode:
- header;
- quick links;
- auth presentation bridge;
- Home content shell;
- Reader shell;
- candidate Search surfaces;
- scoped CSS/JS.

Do not depend on Elementor page markup for candidate rendering.

## PX720-02 — Route contract

Candidate routes remain:
- nonce-gated;
- manage_options-only;
- no public cutover.

Home candidate navigation must remain candidate-to-candidate.

Reader result links must remain candidate Reader links.

No page_on_front write.

## PX720-03 — Header contract

Header must provide:
- BDC brand;
- exactly five quick links:
  - Página Inicial;
  - Consulta Avançada;
  - Telefones;
  - Links Úteis;
  - POSTI;
- Entra profile bridge;
- responsive quick-link toggle;
- Article-only compact global Search row.

Quick-link ownership must be centralized in one internal configuration method/constant surface, not duplicated across templates.

## PX720-04 — Theme isolation

Candidate CSS must:
- scope visual rules under BDC candidate roots;
- avoid requiring Astra Additional CSS for correct candidate layout;
- avoid changing global Astra/Elementor styles;
- preserve third-party content rendered inside the Reader.

PX-720 may add candidate-level resets only under BDC scope.

PX-720 does not remove Astra Additional CSS globally.

## PX720-05 — Accessibility shell baseline

Required:
- skip-to-content link on Home and Reader;
- visible keyboard focus;
- semantic nav/header/main landmarks;
- mobile quick-link toggle with aria-expanded;
- Ctrl/Cmd+K Search remains functional;
- Escape closes quick navigation;
- no focus trap.

Full accessibility certification belongs to PX-760.

## PX720-06 — Reader pipeline boundary

Reader continues to:
- enter canonical WordPress loop;
- call the_content;
- preserve GAC/WPUI/other compatible callbacks;
- suppress only duplicate GRE Tips/Rail renderers inside candidate request.

No raw post_content replacement.

## PX720-07 — Shell versioning

Expose an internal candidate shell version:
- `public-shell-v1.0.0`.

It may be surfaced in:
- body data/class;
- localized JS config;
- diagnostic acceptance JSON.

Do not use it as the plugin product version.

## PX720-08 — Regression coverage

At minimum validate:
- Home candidate loads;
- Reader candidate loads;
- Entra bridge available/fallback safe;
- quick links exact count/order;
- Search candidate navigation preserved;
- candidate shell loads when Astra-specific BDC Additional CSS is conceptually ignored;
- no public route/cutover mutation.

## Out of scope

PX-720 does NOT:
- change lexical ranking;
- change Search schema;
- change canonical editorial storage;
- change Tips/Summary storage;
- alter Home snippet;
- alter page 41395;
- remove Astra CSS;
- deactivate GRE/GAC/WPUI/Entra;
- enable public cutover.

## Acceptance

PX-720 PASS requires:
- candidate shell implementation complete;
- static/package regression evidence;
- WordPress candidate smoke PASS;
- no public legacy mutation;
- PX-720 evidence artifact.

Next:
- PX-730 Live Search.


## Closeout disposition — 2026-10-02

PX-720 closed as `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION` by explicit Product Owner authorization to freeze the closure ZIP and proceed to PX-730.

Closure artifact:
- `base-conhecimento-inteligencia-integrada-0.6.0-dev-px720-final.zip`;
- SHA-256 `360d4d6cfaf78ffb1b78238ad34fb96b65154a400397554fd9c523006bc28fd9`.

Accepted exceptions, not PASS:
- exact-artifact PHP lint;
- post-package WordPress Home/Reader/Entra/keyboard smoke.

These exceptions are carried to PX-790 Cutover Readiness and cannot be inferred as satisfied by later feature work.

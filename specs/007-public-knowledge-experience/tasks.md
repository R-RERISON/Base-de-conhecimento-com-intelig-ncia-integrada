# SPEC-007 — Tasks

## PX-700 Environmental Inventory

- [x] PX700-00 activate SPEC-007 / branch / inherited foundation.
- [x] PX700-01 contract — `px700-environmental-inventory-contract-v1.md`.
- [x] PX700-02 current WordPress/theme/front-page inventory.
- [x] PX700-03 external plugin ownership refresh.
- [x] PX700-04 Home Code Snippet fingerprint/behavior refresh.
- [x] PX700-05 Astra Additional CSS fingerprint/dependency refresh.
- [x] PX700-06 public hook/article pipeline refresh.
- [x] PX700-07 corpus/source-kind + Helpful Tips refresh.
- [x] PX700-08 BDC preview manual smoke — Home/Reader/Entra PASS.
- [x] PX700-09 Ledger input review — no promotions; dependencies confirmed.
- [x] PX700-10 close PX-700 — PASS.

## PX-710 Information Architecture

- [x] PX710-01 ownership map frozen.
- [x] PX710-02 Search/Auth ownership frozen.
- [x] PX710-03 Home latest/popular/category semantics frozen.
- [x] PX710-04 Reader/the_content boundary frozen.
- [x] PX710-05 Helpful Tips / Executive Summary Rail ownership frozen.
- [x] PX710-06 environment dependency disposition frozen.
- [x] PX710-07 first PX-720 slice bounded.
- [x] PX710-08 close PX-710 — PASS.

Closed contract: `px710-closeout-20261002.md`.

Historical planned items:
- reconcile current environment against UX-004/UX-005 frozen contracts;
- define final Home/Reader component ownership;
- define preview -> cutover-safe route contract;
- define Header/Auth ownership;
- freeze latest/popular/category data semantics;
- freeze Article Reader/Tips/Rail composition;
- preserve canonical Search Service.

## PX-720 Public Shell — CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION

- [x] PX720-01 contract — `px720-public-shell-contract-v1.md`.
- [x] PX720-02 shell version constant `public-shell-v1.0.0`.
- [x] PX720-03 quick links centralized.
- [x] PX720-04 skip-to-content on Home/Reader.
- [x] PX720-05 header toggle aria-controls/nav ID.
- [x] PX720-06 candidate shell markers/body class.
- [x] PX720-07 static regression contract added.
- [x] PX720-08 package/build for homologation — deterministic ZIP/static/structural PASS; exact-artifact PHP lint accepted as recorded exception.
- [x] PX720-09 WordPress candidate smoke — accepted validation exception by Product Owner for gate progression; not recorded as PASS.
- [x] PX720-10 close PX-720 — `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`.

## PX-730 Live Search — CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION

- [x] PX730-01 contract — `px730-live-search-contract-v1.md`.
- [x] PX730-02 reconcile existing `public-search.js` against canonical Search facade.
- [x] PX730-03 freeze Home/Reader live-search interaction states.
- [x] PX730-04 freeze candidate result navigation and accessibility behavior.
- [x] PX730-05 define static regression contract — `tests/unit/spec007-px730-live-search-contract.php`.
- [x] PX730-06 implement first bounded runtime slice — UI state hardening only; ranker/schema untouched.
- [x] PX730-07 package/smoke/closeout — WordPress smoke PASS; exact ZIP PHP lint carried as accepted exception.

## PX-740 Reader / Tips / Rail — ACTIVE / DISCOVERY

- [x] PX740-01 contract — `px740-reader-tips-rail-contract-v1.md`.
- [x] PX740-02 inventory current Reader composition and external hook ownership.
- [x] PX740-03 freeze Helpful Tips ownership/rendering boundary.
- [x] PX740-04 freeze Executive Summary Rail ownership/rendering boundary.
- [x] PX740-05 freeze Reader canonical-content/the_content compatibility matrix.
- [x] PX740-06 define static regression contract — `tests/unit/spec007-px740-reader-contract.php`.
- [x] PX740-07 implement first bounded runtime slice — resolve PX740-GAP-001 with CSS-native sticky Rail; Search JS decoupled from Reader scroll.
- [ ] PX740-08 package/smoke/closeout — `px740.3` Rail visual PASS; corpus/source-kind regression runner pending.

## Explicitly not active yet
- PX-750 Theme/Snippet Independence;
- PX-760 Accessibility/Responsive;
- PX-770 Performance;
- PX-780 Human Product Acceptance;
- PX-790 Cutover Readiness.


PX-700 click-to-run runner prepared — 2026-10-01:
- artifact: `bdc-spec007-px700-inventory-runner-1.0.0.zip`;
- SHA-256: `1cea9cab8e846883b8b476c11166061a740b48e8851b59cf752e2c1189a87fa9`;
- ZIP integrity PASS;
- PHP lint 1/1 PASS;
- read-only static contract 12/12 PASS;
- BDC p650.4 is not modified by this companion;
- [ ] execute runner in homologation WordPress and return JSON.


PX-700 automated state: `TECHNICAL_PASS / HUMAN_SMOKE_PENDING`.
Evidence review: `evidence/spec007-px700-environmental-inventory-review-20261002.md`.


PX-700 human evidence: `evidence/spec007-px700-human-smoke-pass-20261002.json`.
PX-710 closeout: `specs/007-public-knowledge-experience/px710-closeout-20261002.md`.


### PX720-08 package evidence — 2026-10-02

- [x] source hardening commit `11017d10ef326bfaf80fdef46a091c56446c3dcd`;
- [x] deterministic candidate `px720.1` materialized;
- [x] artifact branch `spec007-px720-homologation-artifact`;
- [x] artifact commit `044f106869df0acc81b46eb454cfea7229dc88e5`;
- [x] ZIP SHA-256 `360d4d6cfaf78ffb1b78238ad34fb96b65154a400397554fd9c523006bc28fd9`;
- [x] 102 distributed files / 48 engineering files excluded;
- [x] static + structural package checks PASS;
- [ ] PHP lint of the exact published ZIP in an execution environment with the artifact available;
- [ ] WordPress candidate smoke.

Evidence: `evidence/spec007-px720-package-ready-20261002.json`.

PX-720 remains ACTIVE. No cutover or retirement is authorized.


### PX-720 FINAL closeout — 2026-10-02

- closure status: `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`;
- final ZIP: `base-conhecimento-inteligencia-integrada-0.6.0-dev-px720-final.zip`;
- artifact branch: `spec007-px720-homologation-artifact`;
- artifact commit: `3e3074825b85eaa1476744046591c17513a08162`;
- SHA-256: `360d4d6cfaf78ffb1b78238ad34fb96b65154a400397554fd9c523006bc28fd9`;
- byte-identical to accepted structural candidate `px720.1`;
- exact-artifact PHP lint: accepted exception, NOT PASS;
- WordPress Home/Reader/Entra/keyboard smoke: accepted exception, NOT PASS;
- exceptions remain release-readiness debt and must be discharged before PX-790/cutover readiness;
- no public cutover, retirement, ranker/schema change or legacy mutation authorized.

Closeout:
- `px720-closeout-20261002.md`;
- `evidence/spec007-px720-closeout-20261002.json`.

PX-730 Live Search is now ACTIVE / DISCOVERY.


### PX-730 first bounded slice

Implemented:
- per-form live-search request state via WeakMap;
- debounce + AbortController preserved;
- monotonic request ID/stale-response guard;
- recoverable request failure separated from zero-results;
- Home/Reader aria-live + aria-busy consistency;
- Escape cancels/hides active live panel;
- clear action cancels pending request before reset/navigation;
- server-rendered invalid/technical state distinct from empty result;
- canonical Public_Search_Facade/Lexical_Ranker unchanged.

Next:
- run PX-730 static contract;
- package deterministic `px730.1`;
- install/smoke Home + Reader Search;
- close PX-730 only after candidate evidence.


### PX-730 package candidate — 2026-10-05

- [x] source commit `231bd82e7d0f8bf02b044c7385f25a4d5f40be8d`;
- [x] static-equivalent Live Search contract PASS;
- [x] JavaScript syntax PASS;
- [x] canonical `Public_Search_Facade` blob unchanged: `def1156ff0ce4f66a1b633353a9d1d5804f4bc18`;
- [x] deterministic candidate `px730.1`;
- [x] artifact branch `spec007-px730-homologation-artifact`;
- [x] artifact commit `a500bfc1f07c162c145cbf38890b535be27c03e5`;
- [x] ZIP SHA-256 `5993f7d329ea2c161c5cc885d4bbc06771716997cedae7f3335cef23ca56beb6`;
- [x] 102 distributed files / inherited P-650 pruning preserved;
- [x] exactly 3 runtime replacements: JS orchestrator, Public Experience renderer, foundation CSS;
- [ ] exact-package PHP lint;
- [ ] WordPress Home/Reader Live Search smoke;
- [ ] PX-730 closeout.

Evidence:
- `evidence/spec007-px730-package-ready-20261005.json`.

PX-730 remains ACTIVE.


### PX-730 FINAL closeout — 2026-10-05

- Product Owner human smoke: PASS;
- closure status: `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`;
- final ZIP: `base-conhecimento-inteligencia-integrada-0.6.0-dev-px730-final.zip`;
- artifact branch: `spec007-px730-homologation-artifact`;
- artifact commit: `bf4410f6c107e3daeffa4a2f2faf539dc123e2d2`;
- SHA-256: `5993f7d329ea2c161c5cc885d4bbc06771716997cedae7f3335cef23ca56beb6`;
- byte-identical to tested candidate `px730.1`;
- exact-artifact PHP lint: accepted exception, NOT PASS;
- exception remains release-readiness debt and must be discharged before PX-790/cutover readiness;
- no cutover/ranker/schema/editorial/legacy-retirement mutation authorized.

Closeout:
- `px730-closeout-20261005.md`;
- `evidence/spec007-px730-human-smoke-pass-20261005.json`;
- `evidence/spec007-px730-closeout-20261005.json`.

PX-740 Reader / Tips / Rail is now ACTIVE / DISCOVERY.


### PX-740 discovery — 2026-10-05

Frozen inventory:
- candidate uses canonical WordPress loop + `the_content()`;
- GAC/WPUI/BDC anchor callbacks remain preserved;
- GRE duplicate Tips/Summary renderers are suppressed only in candidate Article requests;
- BDC `Helpful_Tips_Store` owns `_bdc_es_helpful_tips` API;
- BDC Summary/Classification/Facts stores feed the Reader Summary projection;
- all observed source kinds remain in regression scope.

Known gaps:
- PX740-GAP-001: dual Summary Rail movement strategy (CSS sticky + later relative + JS transform);
- PX740-RISK-002: legacy chrome stripping heuristic retained but must not broaden.

Inventory:
- `px740-reader-inventory-20261005.md`.


### PX-740 first bounded runtime slice — 2026-10-05

Resolved:
- PX740-GAP-001;
- removed `initReaderRail()` scroll/resize/translate3d logic from `public-search.js`;
- restored CSS-native `position:sticky` as the single desktop Rail strategy;
- retained <=1040px static reflow and print static flow;
- no changes to Reader template, content capture, Tips/Summary stores, GAC/WPUI hooks or GRE candidate suppression.

Pending:
- static-equivalent regression verification;
- deterministic `px740.1` package;
- WordPress representative Reader smoke.


### PX-740 package candidate — 2026-10-05

- [x] source commit `ff578126b7f9fc83d8a66715282dba463751aac5`;
- [x] Reader static-equivalent contract PASS;
- [x] JavaScript syntax PASS;
- [x] PX740-GAP-001 resolved;
- [x] deterministic candidate `px740.1`;
- [x] artifact branch `spec007-px740-homologation-artifact`;
- [x] artifact commit `1357749409f3add6e3c4243d930b5f3044630cb8`;
- [x] ZIP SHA-256 `bb55c4fd7f89e7cdccbb1e9feb3ae3eb225f4190ff6bf4e90e5c620552646fd7`;
- [x] 102 distributed files;
- [x] exactly 2 runtime replacements: `public-search.js` + `public-article.css`;
- [ ] exact-package PHP lint;
- [ ] WordPress representative Reader smoke;
- [ ] PX-740 closeout.

Evidence:
- `evidence/spec007-px740-package-ready-20261005.json`.


### PX-740 homologation regression — px740.1

Product Owner visual smoke identified:
- Reader content renders correctly;
- Executive Summary Rail renders;
- FAIL: Summary Rail does not accompany page scroll.

Disposition:
- `px740.1` is SUPERSEDED / NOT CLOSABLE;
- CSS-native sticky-only strategy rejected by real WordPress homologation;
- correction moved to `px740.2` architecture: dedicated Reader JS, separate from Search JS.


### PX-740 corrected candidate — px740.2

- [x] `px740.1` marked SUPERSEDED after human smoke FAIL on Rail follow;
- [x] dedicated `assets/js/public-reader.js`;
- [x] Search JS remains Reader-free;
- [x] bounded requestAnimationFrame Rail translation;
- [x] ResizeObserver layout recalculation;
- [x] <=1040px normal document flow preserved;
- [x] deterministic package `px740.2`;
- [x] artifact commit `6375d13dbfe8689e62bd30b427bcc5a5d74a450b`;
- [x] SHA-256 `c6dc316f5b51342410cbbc47521a6bd39b170a4bd188366c34c420375eb509be`;
- [x] 103 distributed files;
- [ ] exact-package PHP lint;
- [ ] Product Owner Rail-follow retest;
- [ ] representative Reader smoke;
- [ ] PX-740 closeout.


### PX-740 visual stability regression — px740.2

Product Owner confirmed:
- PASS: Executive Summary Rail follows scroll;
- FAIL: visible flicker on scroll, perceived as repeated reload/repaint.

Disposition:
- `px740.2` is functionally correct but visually SUPERSEDED;
- new visual defect `PX740-VIS-001`;
- per-pixel `translate3d` strategy rejected for premium closeout;
- `px740.3` uses discrete `flow -> fixed -> bottom` state transitions.


### PX-740 visual-stability candidate — px740.3

- [x] `px740.2` functional PASS for follow behavior;
- [x] `px740.2` superseded due `PX740-VIS-001` flicker;
- [x] removed per-pixel transform writes;
- [x] discrete `flow / fixed / bottom` states;
- [x] Search JS remains decoupled;
- [x] deterministic candidate `px740.3`;
- [x] artifact commit `2154901dc35075b8bf63f1b0e39e1a59d9329e83`;
- [x] SHA-256 `7c64e939ce82b3b5ad2988c152bea95e7111578916ce897753d37944ddf7c606`;
- [x] 103 distributed files;
- [ ] exact-package PHP lint;
- [ ] Product Owner visual-stability retest;
- [ ] representative Reader smoke;
- [ ] PX-740 closeout.


### PX-740 px740.3 human visual validation — 2026-10-05

Product Owner confirmed:
- PASS: Summary Rail follows scroll;
- PASS: flicker eliminated;
- PASS: visual motion considered correct.

Remaining before closeout:
- current corpus/source-kind technical regression;
- representative visual samples selected from the current corpus;
- exact-package PHP lint remains tracked debt.

Runner source:
- `tools/homologation/spec007/px740-reader-regression-runner.php`.

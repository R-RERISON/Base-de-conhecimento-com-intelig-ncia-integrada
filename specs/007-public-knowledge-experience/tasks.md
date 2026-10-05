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

## PX-730 Live Search — ACTIVE / DISCOVERY

- [x] PX730-01 contract — `px730-live-search-contract-v1.md`.
- [x] PX730-02 reconcile existing `public-search.js` against canonical Search facade.
- [x] PX730-03 freeze Home/Reader live-search interaction states.
- [x] PX730-04 freeze candidate result navigation and accessibility behavior.
- [x] PX730-05 define static regression contract — `tests/unit/spec007-px730-live-search-contract.php`.
- [x] PX730-06 implement first bounded runtime slice — UI state hardening only; ranker/schema untouched.
- [ ] PX730-07 package/smoke/closeout — package `px730.1` ready; WordPress smoke pending.

## Explicitly not active yet

- PX-740 Reader/Tips/Rail;
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

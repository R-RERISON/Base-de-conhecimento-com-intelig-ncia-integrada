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

## PX-720 Public Shell — ACTIVE

- [x] PX720-01 contract — `px720-public-shell-contract-v1.md`.
- [x] PX720-02 shell version constant `public-shell-v1.0.0`.
- [x] PX720-03 quick links centralized.
- [x] PX720-04 skip-to-content on Home/Reader.
- [x] PX720-05 header toggle aria-controls/nav ID.
- [x] PX720-06 candidate shell markers/body class.
- [x] PX720-07 static regression contract added.
- [ ] PX720-08 package/build for homologation — ZIP/static PASS; exact-package PHP lint pending.
- [ ] PX720-09 WordPress candidate smoke.
- [ ] PX720-10 close PX-720.

## Explicitly not active yet

- PX-730 Live Search;
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

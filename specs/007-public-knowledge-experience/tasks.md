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

## PX-740 Reader / Tips / Rail — CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION

- [x] PX740-01 contract — `px740-reader-tips-rail-contract-v1.md`.
- [x] PX740-02 inventory current Reader composition and external hook ownership.
- [x] PX740-03 freeze Helpful Tips ownership/rendering boundary.
- [x] PX740-04 freeze Executive Summary Rail ownership/rendering boundary.
- [x] PX740-05 freeze Reader canonical-content/the_content compatibility matrix.
- [x] PX740-06 define static regression contract — `tests/unit/spec007-px740-reader-contract.php`.
- [x] PX740-07 implement first bounded runtime slice — resolve PX740-GAP-001 with CSS-native sticky Rail; Search JS decoupled from Reader scroll.
- [x] PX740-08 package/smoke/closeout — final runtime/visual PASS on `px740.5.1-safe-recovery`; exact-final-ZIP PHP lint retained as accepted PX-790 debt.

## PX-750 Theme/Snippet Independence — ACTIVE / DISCOVERY

- [x] PX750-01 contract — `px750-theme-snippet-independence-contract-v1.md`.
- [x] PX750-02 baseline dependency inventory — ENV-001/ENV-002 reconciled from PX-700/PX-710 plus current candidate static audit.
- [x] PX750-03 implement preview-only isolation mode; no persistent theme/snippet mutation.
- [x] PX750-04 static regression contract for isolation boundary — source checks PASS 22/22 + PHP lint PASS.
- [x] PX750-05 package deterministic `px750.1` — exact ZIP PHP lint + clean WP 6.9.4 activation PASS.
- [ ] PX750-06 Product Owner Home/Reader isolation smoke.
- [ ] PX750-07 closeout.

## Explicitly not active yet
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


### PX-740 corpus regression runner — 1.0.0

- [x] read-only source: `tools/homologation/spec007/px740-reader-regression-runner.php`;
- [x] static safety contract: 20/20 PASS;
- [x] deterministic ZIP;
- [x] artifact branch `spec007-px740-regression-runner-artifact`;
- [x] artifact commit `7e9282d905c29ccccacc8346cb98bf78f8943066`;
- [x] ZIP `bdc-spec007-px740-reader-regression-runner-1.0.0.zip`;
- [x] SHA-256 `0a52bcc7d4560eb108a044b48067d5bcbbd569ff8681f6f2efccebb9375abd68`;
- [x] no `the_content`/shortcode execution;
- [x] no post/meta/option/theme/plugin writes;
- [ ] PHP lint in executable environment;
- [ ] execute in homologation and return JSON;
- [ ] review selected visual sample;
- [ ] PX-740 closeout.


### PX-740 supplemental current evidence — PX-700 environmental run 2026-10-05

Uploaded artifact:
- `spec007-px700-environmental-inventory-20261005-120703.json`;
- SHA-256 `e7092aeb9339a05daafedd644f7eb4f0e2804e65eb311f2697af7a36b02b9867`;
- runner gate `PX-700`;
- runner status `PASS_DISCOVERY`;
- generated `2026-10-05T12:07:03+00:00`.

Accepted as supplemental PX-740 evidence:
- [x] current corpus count: 606;
- [x] current source kinds present: legacy_html 528 / plain_text 41 / elementor 32 / mixed 2 / gutenberg 3;
- [x] no empty/error/unknown posts in scan;
- [x] representative post IDs exported per source kind;
- [x] Helpful Tips current coverage: 7 posts, canonical shape `{title,content}`;
- [x] current `the_content` pipeline contains GAC PostActions, GAC KnowledgeBridge, GRE Helpful Tips, WPUI anchors, BDC Search Anchor Manager and GRE Summary renderer;
- [x] runner safety read-only / no `the_content` execution / no mutation.

Not proven by this PX-700 artifact:
- [ ] current Summary present/absent sample selection;
- [ ] px740.3 `public-reader.js` flow/fixed/bottom asset checks;
- [ ] final visual sample across source kinds/data states.

Therefore the dedicated PX-740 runner remains required, but only for the remaining evidence.


### PX-740 dedicated runner result — 2026-10-05T14:21:57Z

Artifact:
- `spec007-px740-reader-regression-20261005-142157.json`;
- runner status: `TECHNICAL_PASS_VISUAL_SAMPLE_PENDING`;
- WordPress 6.9.4 / PHP 8.5.11 / BDC 0.6.0-dev;
- 606 published posts.

Technical PASS:
- [x] required source kinds present;
- [x] Tips sample present;
- [x] Summary sample present;
- [x] no-Summary sample present;
- [x] GAC signal present;
- [x] WPUI signal present;
- [x] BDC Search Anchor signal present;
- [x] GRE Tips signal present;
- [x] GRE Summary signal present;
- [x] all Reader files present;
- [x] `flow` state present;
- [x] `fixed` state present;
- [x] `bottom` state present;
- [x] no `translate3d`;
- [x] runner safety read-only / no `the_content` execution / no mutation.

Minimal final visual matrix:
- [ ] #396 — legacy_html + Summary;
- [ ] #367 — plain_text;
- [ ] #36431 — elementor + Helpful Tips;
- [ ] #515 — mixed;
- [ ] #358 — gutenberg + no Summary.

These 5 replace the original 8-condition matrix by overlap and still cover:
- all 5 source kinds;
- Tips present;
- Summary present;
- Summary absent.

PX-740 closeout requires only Product Owner visual PASS for this 5-post matrix plus the already accepted Rail PASS.


### PX-740 premium visual refinement v7 — 2026-10-05

Product Owner rejected visual closeout as not yet premium/final.

Status:
- [x] screenshot-driven visual diagnosis;
- [x] v7 design direction frozen;
- [x] Home visual system refinement implemented;
- [x] Header/chrome refinement implemented;
- [x] Reader editorial refinement implemented;
- [x] Summary Rail visual integration implemented;
- [x] internal Preview admin refinement implemented;
- [x] functional ownership boundaries preserved;
- [x] final visual sample matrix aligned to 396 / 367 / 36431 / 515 / 358;
- [x] static visual contract — PASS 12/12;
- [x] deterministic px740.4 package;
- [ ] Product Owner visual comparison;
- [ ] PX-740 closeout.

Contract:
- `px740-premium-visual-refinement-v7.md`.


### PX-740 premium visual candidate — px740.4

- [x] source commit `625a5606d1ea66a95f8e5875cdb5c6cce758a252`;
- [x] visual contract PASS 12/12;
- [x] Search JS syntax PASS;
- [x] Reader JS syntax PASS;
- [x] deterministic package;
- [x] artifact branch `spec007-px740-homologation-artifact`;
- [x] artifact commit `cf455bdd5bc7d1c7068d8a302188be85c0dbfde4`;
- [x] SHA-256 `b55c23026beb5721c1b22644af9909ebc84f7ee7a6e0e8132cd7dae343d4934a`;
- [x] 103 distributed files;
- [x] six visual/runtime replacements only;
- [ ] Product Owner visual comparison;
- [ ] exact-package PHP lint;
- [ ] PX-740 closeout.


### PX-740 Premium Product UI v8 — 2026-10-05

Trigger:
- Product Owner rejected px740.4 as visually polished but still not a premium final knowledge product.

Structural remediation:
- [x] article list: ornamental metrics removed; editorial summary strip added;
- [x] workspace: grouped local product navigation;
- [x] workspace: product shell introduced;
- [x] overview: seven independent cards replaced by three domain health groups;
- [x] forms: task-oriented field surfaces and sticky save action;
- [x] Home: discovery area open by default;
- [x] Home: category icons activated;
- [x] Reader: one visual framing layer removed;
- [x] Reader: integrated GAC actions tagged and visually normalized without changing GAC renderer;
- [x] keyboard navigation supports vertical and horizontal product nav;
- [x] Search JS unchanged;
- [x] article template/content capture unchanged;
- [x] Rail state machine preserved;
- [x] structural/static checks PASS 19/19;
- [x] deterministic px740.5 package;
- [ ] Product Owner visual homologation;
- [ ] exact-package PHP lint;
- [ ] PX-740 closeout.

Contract:
- `px740-premium-product-ui-v8.md`.


### PX-740 Premium Product UI v8 candidate — px740.5

- [x] source `f9f6ef9fd2e55f24ff3c33a8711acf9fedca56e1`;
- [x] source checks PASS 19/19;
- [x] deterministic package;
- [x] artifact branch `spec007-px740-homologation-artifact`;
- [x] artifact commit `fc9faa6336af4673a92cf5862f60b6a6ffeb7e86`;
- [x] ZIP SHA-256 `95fdc74d0d5a40ee9c43498fbdcb0a4f63bfd5887d9c1febf6b79bcf796c7fd7`;
- [x] 103 distributed files;
- [x] 8 controlled replacements;
- [ ] Product Owner visual homologation;
- [ ] exact-package PHP lint;
- [ ] PX-740 closeout.


### PX-740 v8.1 Premium Designer Regression Audit — 2026-10-05

Product Owner screenshots exposed a real v8 regression.

Critical findings:
- [x] CSS ownership conflict: `visual-foundation.css` loads after `workspace.css` and re-applied horizontal tab overflow to the v8 narrow navigation column;
- [x] three simultaneous columns violated the existing design-system rule of at most two desktop columns;
- [x] homologation assets used the unchanged `0.6.0-dev` query version, allowing stale CSS/JS across px740 candidates.

High findings:
- [x] sticky table header removed because it could occlude first-row article title inside clipped rounded wrapper;
- [x] article title/meta rendering hardened as explicit block rows;
- [x] empty classification vocabularies no longer render large empty listboxes;
- [x] multi-select height now adapts to term count;
- [x] sticky submit overlay removed;
- [x] Helpful Tips initial blank rows reduced from 8 to 3 and grouped into responsive task sections.

Architecture recovery:
- [x] local navigation returns to top horizontal product navigation;
- [x] five primary domains remain directly visible;
- [x] four secondary domains move to accessible `Mais` overflow;
- [x] main + context is the maximum desktop column count;
- [x] context reflows below main at <=1180px;
- [x] final cascade ownership explicitly resides in the last-loaded visual foundation layer;
- [x] asset versions use SHA-256 file fingerprints.

Static/source validation:
- [x] 26/26 PASS;
- [x] Workspace JS syntax PASS;
- [x] Reader JS syntax PASS;
- [x] Search JS syntax PASS and byte-identical;
- [x] public article template byte-identical;
- [x] Public_Article_Content byte-identical;
- [x] Reader Rail flow/fixed/bottom preserved;
- [x] no cutover/storage mutation.

- [x] deterministic px740.6 recovery package;
- [ ] exact-package PHP lint;
- [ ] Product Owner visual regression re-test;
- [ ] PX-740 closeout.


### PX-740 v8.1 regression recovery candidate — px740.6

- [x] source commit `e2815f0400e6f899df1085935cda77ed7566df2c`;
- [x] source validation PASS 26/26;
- [x] deterministic package;
- [x] artifact branch `spec007-px740-homologation-artifact`;
- [x] artifact commit `43e977cfc45d6ebc8f04fcb3f23173533c1c3bee`;
- [x] Git blob `b17e27c22f2bba7b09b3b79307e2241c27acf95c`;
- [x] SHA-256 `247da4302e384a1442790ca9e0d4b97e5566787d5a616137346868e2fb4a4caf`;
- [x] 103 distributed files;
- [x] seven controlled replacements over px740.5;
- [ ] exact-package PHP lint;
- [ ] Product Owner visual regression re-test;
- [ ] PX-740 closeout.

Visual re-test focus:
1. list first article title visible;
2. no horizontal scrollbar/truncation in workspace nav;
3. workspace never renders three simultaneous product columns;
4. Classification empty vocabularies use compact empty state;
5. save action does not overlay the last field;
6. Helpful Tips does not render eight blank rows;
7. browser hard refresh is not required to receive changed BDC assets because URLs are fingerprinted.


### PX-740 runtime incident — px740.6

Observed by Product Owner:
- WordPress critical error after installing/testing px740.6.

Disposition:
- [x] px740.6 = RUNTIME FAIL / DO NOT USE;
- [x] runtime delta isolated;
- [x] dynamic asset hash helper removed completely;
- [x] no `hash_file()` / `filemtime()` dependency remains;
- [x] cache busting replaced by static build constant `BDC_KB_ASSET_VERSION = 0.6.0-dev-px740.6.1`;
- [x] source hotfix checks PASS 21/21;
- [x] deterministic px740.6.1 package;
- [ ] Product Owner runtime re-test;
- [ ] exact-package PHP lint;
- [ ] visual regression re-test.

px740.6.1:
- source `3d5fd8c9811d028af2cf7d3d28a06bc6ec4092e9`;
- artifact commit `7f1b840b02b26fe3add3285bdf8cb1cf485e6305`;
- SHA-256 `b9c72325c25e8bce121b521cda469b0a094d1e4958e0d1e105c27b36a0917e06`;
- 103 distributed files.


### PX-740 runtime incident escalation — px740.6.1

Product Owner result:
- [x] px740.6.1 activation = FATAL / DO NOT USE.

Diagnostic:
- [x] exact px740.6.1 ZIP extracted in GitHub Actions;
- [x] all source PHP lint PASS on PHP 8.5;
- [x] all exact-artifact PHP lint PASS on PHP 8.5;
- [ ] clean WordPress 7.1.2 / PHP 8.5 activation diagnostic queued;
- [ ] environment-specific fatal stack trace pending.

Safe recovery:
- [x] branch `spec007-px740-safe-recovery` created from known-good px740.5 source;
- [x] only `assets/css/visual-foundation.css` changed;
- [x] all 83 PHP files byte-identical to px740.5;
- [x] deterministic package `px740.5.1-safe-recovery`;
- [x] SHA-256 `9609c9f45dbd4f2f27d80a6282b35516a1cd3afc9fba7d2632ec3ba46c746d3a`;
- [ ] Product Owner activation re-test.


### PX-740 FINAL closeout — 2026-10-06

- Product Owner activation/runtime: PASS on `px740.5.1-safe-recovery`;
- Product Owner visual acceptance: PASS;
- final ZIP: `base-conhecimento-inteligencia-integrada-0.6.0-dev-px740-final.zip`;
- SHA-256: `9609c9f45dbd4f2f27d80a6282b35516a1cd3afc9fba7d2632ec3ba46c746d3a`;
- artifact commit: `cef11ca0f9d4dfef23e6e27e9093ef36c17ec8f0`;
- byte-identical to approved safe-recovery candidate;
- px740.6 / px740.6.1 remain rejected / DO NOT USE;
- exact-final-ZIP PHP lint: accepted exception, NOT PASS;
- no cutover authorized.

PX-750 Theme/Snippet Independence is now ACTIVE / DISCOVERY.


### PX-750 candidate — px750.1

- source commit: `1da2a22fac1628798d964786d03827c217b65467`;
- artifact branch: `spec007-px750-homologation-artifact`;
- artifact commit: `307fd11e19c3bb0723d04bf70bed10ad98475c9a`;
- ZIP: `base-conhecimento-inteligencia-integrada-0.6.0-dev-px750.1.zip`;
- SHA-256: `dd42eacabfa0b277d9853713b3ca61e9d0979dc74c798a4683871d75ab6be1c0`;
- 103 distributed files;
- exactly 3 runtime replacements over `px740-final`;
- deterministic build PASS;
- source static checks PASS 22/22;
- source PHP lint PASS;
- exact ZIP PHP lint PASS;
- clean WordPress 6.9.4 install PASS;
- exact ZIP activation PASS;
- plugin ACTIVE after activation;
- pending only Product Owner isolated Home/Reader smoke.

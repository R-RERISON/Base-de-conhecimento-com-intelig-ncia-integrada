# Continuidade — SPEC-007 Public Knowledge Experience

**Status:** ACTIVE / DISCOVERY  
**Branch:** `spec007-public-knowledge-experience`  
**Started:** 2026-10-01

## Inherited foundation

SPEC-006 final status:
- `CLOSED_WITH_ACCEPTED_TOOLING_EXCEPTION`.

Inherited production-candidate baseline:
- `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip`;
- SHA-256 `a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2`.

Ledger:
- PROD-003 PARTIAL — accepted PHPCS/WPCS + PHPUnit tooling debt, still a 1.0 blocker;
- PROD-004 IMPROVED_VERIFIED;
- PROD-005 IMPROVED_VERIFIED;
- PROD-006 IMPROVED_VERIFIED.

No 1.0, cutover or retirement was authorized.

## Public Experience assets already available

Do not redesign from zero.

Reuse:
- `ux/004-public-home-portal/`;
- `ux/005-public-article-reader/`;
- Search-first addendum v2;
- Header Parity + Reader Rail addendum v3;
- current BDC Public Experience Preview;
- canonical Search Service / Public Search facade;
- BDC Word Cloud;
- Helpful Tips canonical storage;
- Public Auth Bridge;
- preview Home/Article templates and scoped assets.

## Historical environmental baseline

P580 deep inventory from 2026-09-20 is REFERENCE_ONLY:
- WordPress 6.9.4;
- PHP 8.5.10;
- MariaDB 12.2.2;
- Code Snippets 3.9.6 active;
- GRE 0.8.0 active;
- GAC Acompanhamento 15.8.7-r2-b3.1.9.16 active;
- WP Unified Indexer 2.4.10 active;
- BdC Entra ID Authentication Gateway 1.1.7 active;
- Home Code Snippet ID 9 active;
- historical corpus 606 published posts.

It is not current evidence.

## Active gate

PX-700 Environmental Inventory.

Contract:
- `px700-environmental-inventory-contract-v1.md`.

PX-700 is read-only and must refresh:
- front page/theme;
- Astra Additional CSS fingerprint;
- Home Code Snippet fingerprint;
- registered Home shortcodes/AJAX;
- GRE/ASI/GAC/WPUI/Entra/Code Snippets versions and active state;
- article hook/callback pipeline;
- current published corpus/source-kind counts;
- Helpful Tips shape/coverage;
- BDC preview structural readiness;
- Ledger inputs GRE-006/GRE-007/KB2-008/ASI-018/ENV-001..004.

## Invariants

- no new ranker;
- no editorial rewrite;
- no post_content mutation;
- no _elementor_data mutation;
- no theme/customizer mutation in PX-700;
- no plugin activation/deactivation in PX-700;
- no callback removal;
- no public cutover;
- no ASI/GRE retirement by inference;
- Search Service remains canonical.

## Next action

Install/run the temporary PX-700 WordPress companion and return its JSON.

After reviewing that JSON:
1. close PX-700 discovery;
2. reconcile UX-004/UX-005 contracts against the current environment;
3. start PX-710 Information Architecture;
4. only after PX-710 open the first runtime vertical slice.


## PX-700 click-to-run companion

Prepared:
- `bdc-spec007-px700-inventory-runner-1.0.0.zip`;
- SHA-256 `1cea9cab8e846883b8b476c11166061a740b48e8851b59cf752e2c1189a87fa9`;
- PHP source snapshot SHA-256 `4322e958d80870f09a687f5d113f3a8abe65b775017da356a25bd4dde29595c0`;
- ZIP integrity PASS;
- PHP lint PASS;
- static read-only contract 12/12 PASS.

Safety verified:
- no post write;
- no option write;
- no theme change;
- no plugin activation/deactivation;
- no shortcode execution;
- no the_content execution;
- no callback removal;
- no external network;
- POST + nonce + manage_options.

Next exact action:
1. keep BDC p650.4 installed/active;
2. old SPEC-006 Final Gates Runner may be deactivated/removed;
3. install/activate PX-700 companion;
4. open Base de Conhecimento -> SPEC-007 PX-700 (fallback: Tools);
5. click Executar inventário e baixar JSON;
6. return the JSON for PX-700 review;
7. do not change Home/Astra/snippets/GRE/ASI before review.


## PX-700 environmental run — 2026-10-02

Uploaded artifact:
- `spec007-px700-environmental-inventory-20261002-175054.json`;
- SHA-256 `587ee8296515a0d62b54ae869ca676cf6a8c7b794000c45896ba801603a7717c`;
- runner status `PASS_DISCOVERY`.

Automated result:
- `TECHNICAL_PASS / HUMAN_SMOKE_PENDING`.

Stable historical elements:
- WordPress 6.9.4 unchanged;
- DB 12.2.2 unchanged;
- Home snippet ID 9 exact SHA unchanged;
- corpus remains 606;
- GRE/GAC/WPUI/Entra ownership remains present.

Material current facts:
- PHP 8.5.11;
- Astra 4.14.0;
- Additional CSS 36,804 bytes with BDC/ASI/GRE/Elementor/single-post/Astra signals;
- ASI target plugin not detected;
- `asi_search_form` not registered;
- Home remains page 41395 / Elementor Header Footer with bc_home_config + bc_ultimas + bc_populares;
- BDC preview structural PASS;
- Article pipeline still contains GAC -> GRE Tips -> WPUI anchors -> BDC anchors -> GRE Rail.

Manual smoke still required before formal PX-700 closeout:
1. Home preview visual/navigation;
2. representative Reader previews;
3. authenticated Entra profile menu.

PX-710 contract preparation is allowed.
PX-710 runtime implementation remains blocked until PX-700 human smoke is accepted.


## PX-700 FINAL — 2026-10-02

Product Owner confirmed the 3 required smoke checks:
- Home preview PASS;
- representative Article Reader PASS;
- authenticated Entra profile PASS.

PX-700 status: PASS.

Evidence:
- `evidence/spec007-px700-pass-20261002.json`;
- `evidence/spec007-px700-human-smoke-pass-20261002.json`;
- `specs/007-public-knowledge-experience/px700-closeout-20261002.md`.

## PX-710 FINAL — 2026-10-02

Information Architecture & Ownership contract closed PASS.

Frozen:
- BDC owns public candidate shell/UI/read models;
- WordPress owns request/session/canonical content/the_content;
- Entra Gateway owns auth protocol;
- GAC/WPUI remain external pipeline dependencies;
- GRE duplicate renderers may be suppressed only in candidate Reader requests;
- Search remains canonical Public_Search_Facade;
- popular compatibility semantic remains comment_count DESC;
- Helpful Tips canonical store remains _bdc_es_helpful_tips;
- environmental dependencies ENV-001..004 remain explicit;
- no cutover/retirement.

Closeout:
- `px710-closeout-20261002.md`.

## PX-720 ACTIVE — Public Shell

Contract:
- `px720-public-shell-contract-v1.md`.

Implemented first slice:
- `Public_Experience::SHELL_VERSION = public-shell-v1.0.0`;
- five quick links centralized in `Public_Experience::quick_links()`;
- Home/Reader candidate roots expose shell version;
- consistent `#bdc-public-main` landmark;
- skip-to-content link on Home/Reader;
- quick-nav ID + toggle `aria-controls`;
- shell version localized to public JS;
- shell-specific body class;
- scoped skip-link styling;
- static contract `tests/unit/spec007-px720-public-shell-contract.php`.

Not changed:
- page 41395;
- page_on_front;
- Home snippet;
- Astra Additional CSS;
- Search ranker/schema;
- editorial content/storage;
- GRE/GAC/WPUI/Entra state;
- public cutover.

Next:
1. build PX-720 candidate package;
2. run static/lint package checks;
3. install in homologation;
4. smoke Home/Reader/Entra/keyboard shell;
5. close PX-720;
6. open PX-730 Live Search.


## PX-720 package candidate — 2026-10-02

Source hardening:
- commit `11017d10ef326bfaf80fdef46a091c56446c3dcd`;
- skip-link styling constrained to `body.bdc-public-preview`;
- shell body class normalized to `bdc-public-shell-v1-0-0`;
- deterministic local builder: `tools/homologation/spec007/build-px720.py`;
- package contract: `tests/unit/spec007-px720-package-contract.php`.

Published homologation candidate:
- artifact branch: `spec007-px720-homologation-artifact`;
- artifact commit: `044f106869df0acc81b46eb454cfea7229dc88e5`;
- ZIP: `dist/base-conhecimento-inteligencia-integrada-0.6.0-dev-px720.1.zip`;
- SHA-256: `360d4d6cfaf78ffb1b78238ad34fb96b65154a400397554fd9c523006bc28fd9`;
- size: 762,408 bytes;
- 102 files distributed;
- 48 engineering files excluded by the inherited P-650 pruning contract;
- deterministic double-build PASS;
- single-root / required files / forbidden engineering paths PASS;
- PX-720 shell static-equivalent checks PASS.

Execution note:
- PHP lint of the exact published ZIP is still PENDING.
- The current local execution sandbox cannot resolve external GitHub DNS, so the exact artifact cannot be downloaded here for `php -l`.
- A remote CI pipeline was deliberately NOT introduced because the accepted packaging contract is local-only.

Evidence:
- `evidence/spec007-px720-package-ready-20261002.json`;
- artifact-side `dist/px720-package-validation.json`;
- artifact-side manifest next to the ZIP.

PX-720 status:
- `STATIC_STRUCTURAL_PASS / PHP_LINT_PENDING / WORDPRESS_SMOKE_PENDING`.

Next exact action:
1. run `python tools/homologation/spec007/build-px720.py` from a local checkout with PHP available, or lint the exact published ZIP;
2. require PHP lint PASS for the exact candidate;
3. install that same `px720.1` candidate in homologation;
4. smoke Home preview visual/navigation;
5. smoke representative Article Readers;
6. smoke authenticated Entra profile/menu;
7. smoke keyboard shell: skip link, Tab order, Ctrl/Cmd+K, Escape and mobile quick-nav;
8. if all PASS, close PX-720;
9. only then open PX-730 Live Search.

Still not authorized:
- public cutover;
- Home/Astra/snippet mutation;
- Search ranker/schema change;
- ASI/GRE/GAC/WPUI/Entra retirement or ownership change.


## PX-720 FINAL — 2026-10-02

Product Owner explicitly authorized production of the PX-720 closure ZIP and progression to the next SPEC-007 gate.

Closure status:
- `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`.

Final closure artifact:
- file: `base-conhecimento-inteligencia-integrada-0.6.0-dev-px720-final.zip`;
- artifact branch: `spec007-px720-homologation-artifact`;
- artifact commit: `3e3074825b85eaa1476744046591c17513a08162`;
- Git blob: `57c1f5511f2c542c03568670bc87750f4eb7dc95`;
- SHA-256: `360d4d6cfaf78ffb1b78238ad34fb96b65154a400397554fd9c523006bc28fd9`;
- size: 762,408 bytes;
- byte-identical to `px720.1`;
- 102 distributed files / 48 engineering files excluded.

Verified:
- deterministic double-build PASS;
- single-root PASS;
- required distribution files PASS;
- engineering pruning PASS;
- forbidden repository-only paths PASS;
- bootstrap pruning PASS;
- shell version PASS;
- five quick links contract PASS;
- skip-link scope PASS;
- Ctrl/Cmd+K PASS by static behavior contract;
- Escape quick-nav close PASS by static behavior contract.

Accepted validation exceptions:
- PX720-EX-001 — exact published ZIP PHP lint was not executed in this session and is NOT recorded as PASS;
- PX720-EX-002 — WordPress candidate Home/Reader/Entra/keyboard smoke was not executed after `px720.1` publication and is NOT recorded as PASS.

Disposition:
- exceptions do not block PX-730 discovery/runtime evolution by explicit Product Owner closure authorization;
- both remain release-readiness debt;
- they MUST be discharged before PX-790 Cutover Readiness and before any 1.0/public cutover decision.

Still frozen:
- no public cutover;
- no `page_on_front` write;
- no Home snippet mutation;
- no Astra Additional CSS mutation;
- no Search ranker/schema change;
- no editorial storage mutation;
- no GRE/GAC/WPUI/Entra retirement.

Evidence:
- `specs/007-public-knowledge-experience/px720-closeout-20261002.md`;
- `evidence/spec007-px720-closeout-20261002.json`.

## PX-730 ACTIVE — Live Search

Status:
- `ACTIVE / DISCOVERY`.

Contract:
- `px730-live-search-contract-v1.md`.

Purpose:
- formalize and harden the already-existing candidate live-search behavior;
- preserve `Public_Search_Facade` as canonical;
- do not create a new ranker;
- do not change Search schema or ranking semantics;
- do not introduce semantic/vector/AI search in PX-730;
- keep Home/Reader candidate navigation and preview gating intact.

Next:
1. reconcile existing `public-search.js` and server fallback against the PX-730 contract;
2. map live-search states and failure behavior;
3. add PX-730 static regression contract;
4. only then implement the first bounded runtime slice.


## PX-730 package candidate — 2026-10-05

Source:
- commit `231bd82e7d0f8bf02b044c7385f25a4d5f40be8d`.

Runtime scope:
- `assets/js/public-search.js`;
- `includes/class-public-experience.php`;
- `assets/css/public-foundation.css`.

Canonical Search remains frozen:
- `Public_Search_Facade` blob `def1156ff0ce4f66a1b633353a9d1d5804f4bc18` unchanged;
- no Lexical_Ranker change;
- no Search projection/schema change;
- no vector/semantic/LLM retrieval.

Candidate:
- file `base-conhecimento-inteligencia-integrada-0.6.0-dev-px730.1.zip`;
- artifact branch `spec007-px730-homologation-artifact`;
- artifact commit `a500bfc1f07c162c145cbf38890b535be27c03e5`;
- Git blob `f6185e07e86658b84a7245bdd3d790dc901ea603`;
- SHA-256 `5993f7d329ea2c161c5cc885d4bbc06771716997cedae7f3335cef23ca56beb6`;
- size 766,344 bytes;
- 102 distributed files;
- deterministic rebuild PASS;
- single root PASS;
- required distribution files PASS;
- forbidden engineering paths absent.

PX-730 UI-state changes:
- request state isolated per form;
- debounce and AbortController retained;
- request sequence guard prevents stale results;
- technical/AJAX failure no longer renders as valid zero-results;
- Reader and Home use consistent live/busy state;
- Escape cancels/dismisses the active live panel;
- clear cancels pending request before reset/navigation;
- server-side renderer differentiates invalid/technical failures from empty result.

Pending:
- exact ZIP PHP lint in an environment with the artifact materialized;
- WordPress candidate smoke for Home and Reader live search.

Still frozen:
- no cutover;
- no Home/Astra/snippet mutation;
- no editorial storage mutation;
- no legacy retirement.

Next exact action:
1. install `px730.1` over the current candidate in homologation;
2. verify Home live search success, zero-results and recoverable failure behavior;
3. verify Reader compact Search;
4. verify Ctrl/Cmd+K, Escape and clear;
5. return environmental evidence for PX-730 closeout.


## PX-730 FINAL — 2026-10-05

Product Owner confirmed all requested PX-730 WordPress candidate smoke scenarios as PASS:
- Home live search with results;
- Home zero-results state;
- Reader compact live search;
- rapid-query stale-response protection;
- Ctrl/Cmd+K;
- Escape;
- clear/reset;
- candidate Reader navigation from results.

Closure:
- status `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`;
- final ZIP `base-conhecimento-inteligencia-integrada-0.6.0-dev-px730-final.zip`;
- artifact branch `spec007-px730-homologation-artifact`;
- artifact commit `bf4410f6c107e3daeffa4a2f2faf539dc123e2d2`;
- Git blob `f6185e07e86658b84a7245bdd3d790dc901ea603`;
- SHA-256 `5993f7d329ea2c161c5cc885d4bbc06771716997cedae7f3335cef23ca56beb6`;
- byte-identical to tested `px730.1`.

Accepted exception:
- PX730-EX-001 — exact published ZIP PHP lint was not executed in this session and is NOT recorded as PASS.
- This debt must be discharged before PX-790/public cutover.

Evidence:
- `specs/007-public-knowledge-experience/px730-closeout-20261005.md`;
- `evidence/spec007-px730-human-smoke-pass-20261005.json`;
- `evidence/spec007-px730-closeout-20261005.json`.

## PX-740 ACTIVE — Reader / Tips / Rail

Status:
- `ACTIVE / DISCOVERY`.

Contract:
- `px740-reader-tips-rail-contract-v1.md`.

Purpose:
- harden Article Reader composition without replacing canonical WordPress content rendering;
- preserve `the_content` compatibility;
- preserve GAC/WPUI callbacks;
- preserve Helpful Tips canonical storage `_bdc_es_helpful_tips`;
- preserve GRE as current external owner of Helpful Tips/Summary data until explicitly migrated;
- prevent duplicate Tips/Rail rendering only inside candidate Reader;
- do not perform public cutover or plugin retirement.

Next:
1. inventory current Reader render path and model composition;
2. map Helpful Tips and Executive Summary source/renderer ownership;
3. map `the_content` callback compatibility requirements;
4. identify duplication/isolation gaps;
5. add static regression contract before runtime changes.


## PX-740 discovery inventory — 2026-10-05

Reader boundary confirmed:
- real WordPress loop;
- canonical `the_content()`;
- BDC-owned candidate shell;
- GAC/WPUI callbacks preserved;
- GRE duplicate Tips/Rail renderers suppressed only during candidate Reader request.

Ownership corrected/frozen:
- BDC `Helpful_Tips_Store` is canonical API owner for `_bdc_es_helpful_tips`;
- BDC Summary/Classification/Facts stores are canonical inputs to the BDC Reader summary projection;
- GRE remains legacy renderer compatibility, not candidate data owner.

Concrete gap:
- PX740-GAP-001: Summary Rail currently combines CSS sticky declarations, a later `position:relative` override and scroll-driven JS `translate3d`;
- this behavior currently lives inside `public-search.js`, coupling Search and Reader layout.

Risk retained:
- PX740-RISK-002: `strip_duplicate_legacy_chrome()` heuristically post-processes `the_content` output;
- no expansion of this heuristic is authorized.

Next:
1. run PX-740 static contract;
2. if PASS, implement only PX740-GAP-001 as the first bounded slice;
3. package candidate and smoke representative Reader source kinds.


## PX-740 first bounded slice — 2026-10-05

PX740-GAP-001 resolved:
- Summary Rail desktop movement is now CSS-native sticky only;
- `public-search.js` no longer owns Reader scroll/resize/translate behavior;
- mobile and print static reflow preserved.

No content/storage/hook ownership change.
Next: static verification -> `px740.1` -> representative Reader smoke.

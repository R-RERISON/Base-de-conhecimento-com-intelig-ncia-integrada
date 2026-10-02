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

# UX-005 — A-010 Public Article Reader Contract v1.0.0

**Status:** FROZEN FOR IMPLEMENTATION  
**Date:** 2026-09-20  
**Evidence:** P-580 environmental inventory #1/#2  
**Surface:** Public singular Knowledge Article

## 1. Product ownership

The BDC plugin owns the public Article Reader shell.

WordPress remains owner of:
- request/permalink;
- post identity/status;
- authentication/capabilities;
- canonical `post_content`;
- standard content filter pipeline.

The theme is not the product owner of the BDC article experience.

## 2. Rollout model

### A020-A — Preview shell
- admin-only preview/candidate renderer;
- no production route takeover;
- no post/meta/Elementor writes;
- current Astra/legacy frontend remains authoritative.

### A020-B — Shell takeover
After technical + visual acceptance:
- BDC template handles singular BDC posts;
- standard `the_content` pipeline remains executed;
- theme Additional CSS is still allowed during compatibility window.

### A050 — Theme decoupling
Only after corpus regression:
- BDC Reader must render correctly with Astra Additional CSS disabled in homologation;
- legacy compatibility rules needed by specific source kinds live inside scoped BDC assets.

## 3. Template interception guards

Reader must never hijack:
- wp-admin;
- REST requests;
- feeds;
- Elementor editor/preview;
- autosave/revision flows;
- non-post content;
- explicit compatibility/debug bypass.

Rollback switch is mandatory during homologation.

## 4. Render pipeline

Target shell:

`BDC Header → Article Hero → Main Grid(Content + Executive Summary Rail)`

Critical rule:
- Reader must call the canonical WordPress content pipeline;
- do not bypass `the_content`;
- third-party integrations remain observable unless explicitly migrated.

Known current filters include:
- GAC PostActions;
- GAC KnowledgeBridge;
- GRE Helpful Tips;
- WP Unified Indexer anchors;
- GRE Executive Summary Rail.

## 5. Progressive internalization rule

No duplicate rendering.

Phase sequence:
1. shell only — legacy GRE/GAC/WPUI filters remain active;
2. BDC Helpful Tips enabled — suppress only GRE Helpful Tips callback under controlled flag;
3. BDC Executive Summary Rail enabled — suppress only GRE Rail callback under controlled flag;
4. preserve GAC/WPUI until separate parity/dependency decisions;
5. GRE plugin decommission only after P-580B parity completion.

## 6. Source-kind compatibility

Published corpus baseline:
- legacy_html: 528;
- plain_text: 41;
- Elementor: 31;
- mixed: 3;
- Gutenberg: 3.

Priority:
1. legacy_html;
2. plain_text;
3. Elementor;
4. mixed;
5. Gutenberg/Core Blocks native path.

The Reader uses source-kind-aware CSS/compatibility, not source-kind-specific content mutation.

No frontend migration writer.

## 7. Article Hero

Must provide:
- category/classification badge where available;
- title;
- responsible/editorial identity as defined by existing product contract;
- publication date;
- updated date;
- back-to-base action.

Values derive from canonical WordPress/BDC data only.

No duplicated persisted display metadata.

## 8. Helpful Tips contract

Canonical BDC API:
`Helpful_Tips_Store` (name to be implemented under BDC namespace).

Physical storage retained for compatibility:
`_bdc_es_helpful_tips`.

Observed environmental shape:
- PHP list;
- ordered;
- 1–4 current items;
- each item:
  - `title: string`;
  - `content: string`.

V1 write contract:
- list of objects/arrays containing only `title` and `content`;
- both sanitized strings;
- empty rows removed;
- order preserved;
- no arbitrary HTML;
- per-post capability required;
- REST exposure remains closed unless separately specified.

Rendering:
- automatic at top of main article content;
- absent data => zero markup;
- semantic heading/list/card structure;
- responsive;
- printable;
- no JS required for basic display.

Search/KD integration:
- Tips become part of the canonical Knowledge Document through an explicit extractor version bump/gate;
- do not silently change frozen Search Document during initial Reader implementation.

## 9. Executive Summary Rail contract

Rail is a composed read model, not a duplicated store.

Sources:
- title: WP_Post;
- objective/escalation/important: BDC Summary;
- responsible team/catalog item/audience: BDC Classification;
- affected service/systems involved: compatibility read from GRE physical keys until canonical owners are approved.

Compatibility physical keys:
- `_bdc_es_affected_service`;
- `_bdc_es_systems_involved`.

Rendering:
- read-only;
- empty fields omitted;
- if no structured facts exist, no rail;
- desktop wide: sticky within reader grid;
- bounded max-height;
- internal scroll only when needed;
- never overlay article;
- narrow viewport: reflow into normal document flow;
- print: static flow.

## 10. External integrations

### GAC
Current callbacks are preserved by the content pipeline.
No removal or internalization in UX-005 without separate inventory/contract.

### WP Unified Indexer
Anchor injection remains preserved.
Deep-link capability is an ASI parity concern and future BDC ownership target, but UX-005 must not regress current anchors.

### Entra
Authentication remains WordPress/Entra-owned; Reader consumes current auth state only.

## 11. CSS ownership

Plugin assets:
- `public-foundation.css`;
- `public-header.css`;
- `public-article.css`;
- `public-tips.css`;
- `public-summary-rail.css`;
- `public-legacy-html.css`;
- `public-legacy-elementor.css`.

Rules:
- all selectors scoped to BDC Reader root;
- no broad `body.single-post` product rules;
- no dependency on ASI/GRE CSS for BDC-owned components;
- compatibility assets load only when needed.

## 12. Regression corpus

Minimum candidate set:
- Gutenberg: 358, 606, 44283;
- legacy_html: 359, 360, 362, 363, 364, 365, 368, 369;
- plain_text: 367, 393, 1102, 1113, 1126, 14192, 14737, 14972;
- Elementor: 385, 464, 485, 572, 574, 579, 583, 586;
- mixed: 515, 14523, 45782;
- Tips present: 36431, 36492, 36549, 41383, 45031, 45178, 45782.

A smaller blocking visual set may be selected from these, but every source kind must be represented.

## 13. Acceptance

A-010 PASS when this contract is frozen.

A-020 implementation begins behind preview/rollback controls.

A-030 requires:
- no fatal/throwable;
- no editorial writes;
- source-kind rendering;
- no duplicate Tips/Rail;
- GAC/WPUI preserved;
- tables/media/code/long words bounded;
- print CSS;
- responsive source checks.

A-040 human visual acceptance:
- compare current vs candidate;
- verify sticky rail;
- verify long summary scroll;
- verify tips;
- verify no overlap/clipping.

A-050 proves Astra Additional CSS is no longer functionally required for BDC Reader.

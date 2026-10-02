# PX-700 — Environmental Inventory Review

**Date:** 2026-10-02  
**Source artifact:** `spec007-px700-environmental-inventory-20261002-175054.json`  
**Source SHA-256:** `587ee8296515a0d62b54ae869ca676cf6a8c7b794000c45896ba801603a7717c`  
**Automated status:** PASS_DISCOVERY  
**Gate status:** TECHNICAL_PASS / HUMAN_SMOKE_PENDING

## Environment

- WordPress 6.9.4;
- PHP 8.5.11;
- MariaDB 12.2.2;
- BDC 0.6.0-dev active;
- multisite=false.

Drift from P580 historical baseline:
- WordPress unchanged;
- database unchanged;
- PHP 8.5.10 -> 8.5.11;
- published corpus remains 606;
- Home snippet ID 9 remains byte-identical.

## Home ownership

Current front page:
- post ID 41395;
- title Home;
- published page;
- template `elementor_header_footer`;
- target shortcodes in content:
  - `bc_home_config`;
  - `bc_ultimas`;
  - `bc_populares`.

Code Snippet:
- ID 9;
- `BdC Home — Shortcodes, AJAX e Renderização Segura`;
- global / priority 10 / active;
- 8,507 bytes;
- SHA-256 `a465269e50c069dd945c55c3ba5488a32664589d8bdb79755cbcbe23c3c42c5a`;
- exact SHA match with P580 historical baseline.

Behavior remains:
- WP_Query=true;
- comment_count=true;
- category=true;
- JSON response=true;
- no nonce detected;
- no capability check detected.

## Theme dependency

Theme:
- Astra 4.14.0;
- no child theme.

Additional CSS:
- present;
- 36,804 bytes;
- SHA-256 `b44b57c6a86df6b35ec1df5dfcba86c60e17555ecc2dd7d836c81c36499303a2`;
- contains BDC/ASI/GRE/Elementor/single-post/Astra signals.

Interpretation:
- ENV-002 remains a real dependency to prove removable later;
- do not disable Astra Additional CSS in PX-700.

## External plugin ownership

Detected:
- Code Snippets 3.10.2 active;
- GRE 0.8.0 active;
- GAC Acompanhamento 15.8.7-r2-b3.1.9.16 active;
- WP Unified Indexer 2.4.10 active;
- BdC Entra ID Authentication Gateway 1.1.7 active;
- Login with Microsoft Entra ID 1.0.0 installed/inactive.

ASI:
- no target plugin detected;
- `asi_search_form` is not registered.

This is homologation evidence only and does not authorize global ASI retirement.

## Public shortcodes

Registered:
- `bc_home_config` -> Code Snippets;
- `bc_ultimas` -> Code Snippets;
- `bc_populares` -> Code Snippets;
- `bdc_entra_login` -> Entra Gateway.

Not registered:
- `asi_search_form`;
- `bdc_word_cloud`.

Word Cloud service is nevertheless structurally present inside the BDC preview/runtime. Shortcode absence is not evidence of missing BDC Word Cloud capability.

## Article pipeline

Relevant `the_content` ownership remains:
- GAC PostActions priority 12;
- GAC KnowledgeBridge priority 13;
- GRE Helpful Tips priority 15;
- WP Unified Indexer anchors priority 20;
- BDC Search Anchor Manager priority 25;
- GRE Executive Summary priority 30.

BDC Public Experience owns candidate template interception at `template_include` priority 99.

Entra authentication controllers remain in `template_redirect`.

No callback removal is authorized at PX-700.

## Corpus

Published posts scanned: 606.

Current source-kind distribution:
- legacy_html: 528;
- plain_text: 41;
- elementor: 32;
- mixed: 2;
- gutenberg: 3;
- empty/error/unknown: 0.

Difference from P580:
- total unchanged at 606;
- Elementor 31 -> 32;
- mixed 3 -> 2;
- all other main counts unchanged.

This is classification drift inside the same corpus, not content-volume drift.

## Helpful Tips

- canonical key: `_bdc_es_helpful_tips`;
- 7 published posts;
- 1–4 items;
- item keys exactly `title`, `content`;
- both string;
- storage shape stable from P580.

## BDC preview

Structural PASS:
- Public Experience class;
- Public Search facade;
- Word Cloud service;
- Public Auth Bridge;
- Home template;
- Article template;
- foundation/header/home/article CSS;
- public-search JS;
- Home preview URL generation.

## Manual checks still required

1. Open Home preview and confirm layout/navigation renders.
2. Open representative Article Reader previews for available source kinds.
3. Confirm authenticated Entra profile-menu behavior visually.

Until these are confirmed:
- PX-700 = TECHNICAL_PASS / HUMAN_SMOKE_PENDING;
- PX-710 may be prepared contractually but runtime implementation remains blocked.

## Ledger inputs

- GRE-006: still PARTIAL; Reader rail/tips dependency is environmentally confirmed.
- GRE-007: current legacy Home shortcodes remain active; no retirement.
- KB2-008: no promotion from this inventory.
- ASI-018: ASI search shortcode/plugin not detected in homologation, but no global retirement conclusion.
- ENV-001: confirmed active Home Code Snippet dependency.
- ENV-002: confirmed material Astra Additional CSS dependency.
- ENV-003: confirmed active Entra Gateway dependency.
- ENV-004: confirmed active GAC + WP Unified Indexer dependencies.

## Decision

Automated PX-700 discovery is complete and stable enough to prepare PX-710.

PX-700 formal closeout waits only for the three manual smoke checks above.

No public cutover, plugin retirement, snippet deletion or CSS removal is authorized.

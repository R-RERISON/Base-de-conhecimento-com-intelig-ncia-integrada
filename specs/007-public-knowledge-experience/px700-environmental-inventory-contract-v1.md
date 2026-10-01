# PX-700 — Public Experience Environmental Inventory Contract v1

**Status:** ACTIVE / DISCOVERY  
**Date:** 2026-10-01  
**SPEC:** 007 — Public Knowledge Experience

## Objective

Refresh the real homologation environment before any Public Home/Article Reader implementation or cutover decision.

Historical P580 evidence is reused as a comparison baseline, not as current-state proof.

## Historical baseline to compare

P580 deep inventory — 2026-09-20:
- WordPress 6.9.4;
- PHP 8.5.10;
- MariaDB 12.2.2;
- Code Snippets 3.9.6 active;
- GRE 0.8.0 active;
- GAC Acompanhamento 15.8.7-r2-b3.1.9.16 active;
- WP Unified Indexer 2.4.10 active;
- BdC Entra ID Authentication Gateway 1.1.7 active;
- Home Code Snippet ID 9 active;
- 606 published posts at that inventory;
- public article pipeline included GAC/GRE/WPUI callbacks.

This historical baseline must not be silently assumed current.

## PX700-01 — WordPress/theme/front page

Collect read-only:
- WordPress version;
- PHP version;
- database version;
- active theme + parent theme;
- `page_on_front`;
- front page template;
- permalink for the front page;
- whether the current public BDC Home is rendered by content, template, shortcode or snippet composition.

## PX700-02 — external plugin ownership

Record installed/active/version for:
- GRE;
- ASI, if still present;
- GAC Acompanhamento;
- WP Unified Indexer;
- BdC Entra ID Authentication Gateway;
- Login with Microsoft Entra ID;
- Code Snippets;
- any plugin currently injecting public Home/Article behavior.

Do not infer retirement from inactive code paths.

## PX700-03 — Home ownership

Locate current ownership of:
- `[bc_home_config]`;
- `[bc_ultimas]`;
- `[bc_populares]`;
- `bdc_home_filter_v270`;
- `[bdc_entra_login]`;
- category/filter behavior;
- latest ordering;
- popular ordering;
- quick links;
- header/profile behavior.

For Code Snippets:
- record snippet ID, name, active state, scope, priority, byte count and SHA-256;
- do not export secrets or unrelated snippet bodies in evidence.

## PX700-04 — Theme/Astra dependency

Inventory:
- Astra active/customizer state relevant to BDC;
- Additional CSS byte count + SHA-256;
- selectors that target BDC/Home/single-post/Elementor/ASI/GRE where safely detectable;
- whether disabling Additional CSS would materially alter current BDC public surfaces.

Do not disable CSS during inventory.

## PX700-05 — Article pipeline

Record actual callbacks on `the_content`, `wp_footer` and relevant template hooks for BDC posts.

Specifically identify current ownership of:
- Helpful Tips;
- Executive Summary Rail;
- anchors/deep links;
- GAC actions;
- WP Unified Indexer behavior;
- legacy Elementor compatibility.

No callback is removed in PX-700.

## PX700-06 — corpus/source-kind refresh

Refresh counts for published BDC posts:
- legacy_html;
- plain_text;
- elementor;
- mixed;
- gutenberg/core blocks.

Record only counts and representative IDs already allowed for homologation; do not export full editorial content.

## PX700-07 — existing BDC preview

Validate read-only:
- Public Experience Preview admin entry exists;
- Home preview opens;
- Article Reader preview opens for representative sources;
- Search-first navigation remains candidate-to-candidate;
- Search uses canonical BDC service;
- Word Cloud is BDC-owned;
- preview remains non-cutover and restricted as designed.

## PX700-08 — parity ledger inputs

Produce evidence for current states of:
- GRE-006;
- GRE-007;
- KB2-008;
- ASI-018;
- ENV-001;
- ENV-002;
- ENV-003;
- ENV-004.

PX-700 itself does not promote these IDs unless the evidence proves the corresponding capability; its normal result is inventory/discovery.

## Safety

PX-700 is read-only:
- no post_content write;
- no _elementor_data write;
- no option mutation except temporary diagnostic state owned by a dedicated runner if required and cleaned in the same execution;
- no plugin activation/deactivation;
- no callback removal;
- no page_on_front change;
- no theme/customizer change;
- no cutover;
- no legacy retirement.

## PASS criteria

PX-700 discovery is PASS when:
- current ownership of Home/Header/Auth/Reader is identified;
- external dependencies are versioned/current;
- Home snippet/Astra dependencies are fingerprinted;
- article pipeline callbacks are enumerated;
- corpus source-kind counts are refreshed;
- existing BDC preview is proven usable;
- unknown environmental assumptions are listed explicitly.

PASS means discovery complete, not public cutover ready.

## Next gate

PX-710 — Information Architecture / Public Experience contract reconciliation.

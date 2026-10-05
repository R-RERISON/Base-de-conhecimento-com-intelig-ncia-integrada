# PX-740 — Reader / Tips / Rail Contract v1

**Status:** ACTIVE / DISCOVERY  
**Date:** 2026-10-05  
**SPEC:** 007 — Public Knowledge Experience  
**Prerequisite:** PX-730 `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`

## Objective

Harden the candidate Article Reader composition while preserving canonical WordPress content rendering and current external ownership boundaries.

PX-740 does not redesign the Reader from zero. It starts from the accepted UX-005 Reader, the current `Public_Article_Read_Model`, `Public_Article_Content`, GRE Helpful Tips/Summary behavior, GAC and WP Unified Indexer callbacks.

## Canonical content boundary

Frozen:
- WordPress loop remains canonical;
- candidate Reader continues to call the canonical `the_content` pipeline;
- raw `post_content` replacement is prohibited;
- GAC and WPUI compatible callbacks must remain effective;
- BDC may suppress duplicate external renderers only for the candidate Reader request and only after the same underlying data is represented intentionally by the BDC shell.

## Helpful Tips ownership

Frozen baseline:
- canonical storage remains `_bdc_es_helpful_tips`;
- current data ownership remains external to the BDC shell;
- BDC Reader may read and present canonical Tips data;
- PX-740 must not migrate, rewrite or normalize stored Tips values;
- no duplicate Helpful Tips renderer may appear in the candidate Reader.

## Executive Summary Rail ownership

PX-740 must inventory and freeze:
- canonical source of summary items;
- GRE renderer ownership;
- BDC Reader read-model projection;
- candidate-only duplicate suppression behavior;
- empty/missing summary behavior;
- responsive rail behavior.

No storage migration is authorized.

## Reader composition contract

Candidate Reader must preserve this order:
1. BDC public shell/header;
2. Reader breadcrumb/title/meta;
3. BDC Helpful Tips presentation when canonical data exists;
4. canonical WordPress article content via `the_content`;
5. Executive Summary Rail when canonical summary data exists.

Third-party compatible content produced through `the_content` remains inside the document region.

## Compatibility contract

At minimum preserve:
- WordPress core content filters;
- GAC post-actions/contribution bridge;
- WP Unified Indexer anchors;
- BDC Search Anchor Manager;
- shortcode/block rendering;
- source kinds currently observed in the corpus: legacy HTML, plain text, Elementor, mixed and Gutenberg.

GRE duplicate Tips/Rail renderers may be suppressed only inside candidate Reader requests.

## Empty/degraded states

Required:
- missing Tips does not affect article rendering;
- missing Summary does not affect article rendering;
- empty canonical content produces the existing explicit Reader empty state;
- one optional projection failing must not suppress canonical article content.

## Accessibility/responsive baseline

Required:
- Tips and Rail use semantic section/aside relationships;
- heading hierarchy remains deterministic;
- mobile layout must not obscure article content;
- Rail movement must not cause focus traps or content reordering in the DOM.

Full accessibility certification remains PX-760.

## Invariants

PX-740 must NOT:
- mutate `post_content`;
- mutate `_elementor_data`;
- migrate Helpful Tips/Summary storage;
- remove GAC/WPUI callbacks globally;
- deactivate GRE/GAC/WPUI/Entra;
- alter Search ranking/schema;
- alter Home/Astra/snippets;
- enable public cutover.

## First bounded slice

Before runtime changes:
1. inventory `Public_Article_Read_Model`;
2. inventory `Public_Article_Content`;
3. inventory current GRE Tips/Summary callback/data ownership;
4. inventory GAC/WPUI/BDC callbacks required through `the_content`;
5. compare current Reader output against this contract;
6. identify only concrete duplication/isolation/responsive gaps;
7. add static regression coverage.

No speculative redesign.

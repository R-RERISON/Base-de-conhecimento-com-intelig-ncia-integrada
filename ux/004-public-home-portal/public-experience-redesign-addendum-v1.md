# UX-004/UX-005 — Public Experience Redesign Addendum v1

**Status:** FROZEN FOR IMPLEMENTATION  
**Date:** 2026-09-20  
**Trigger:** human visual review of `0.5.0-ux004005.1`

## Human review result

`ux004005.1` is **FUNCTIONAL BASELINE / VISUAL REJECTED**.

The preview proved:
- plugin-owned templates can coexist without route takeover;
- Home data providers render;
- Article Reader can preserve the WordPress content pipeline;
- GRE Tips/Rail can be replaced only inside preview;
- the architecture is viable.

It must **not** become the product visual baseline.

Observed defects:
1. Home is too close to a reorganized legacy page, with weak hierarchy and excessive empty/boxed space.
2. Header replaces the real Entra experience with a static WordPress identity badge.
3. navigation discovery leaves valid product destinations disabled when title lookup fails.
4. provisional Word Cloud exposes implementation/discovery language in the user-facing surface.
5. Article Reader renders legacy article chrome inside the new shell, duplicating title/metadata.
6. Helpful Tips grid creates dead empty columns when item cardinality is low.
7. Executive Summary Rail is visually cramped and under-prioritized.
8. public surface still feels assembled from independent blocks rather than one product.

## Redesign principles

### Product, not clone
Legacy Home/Reader remain **functional parity references**, not visual templates.

### Progressive enhancement
Existing data and integrations are reused, but visual/product composition is redesigned.

### No silent capability loss
Entra, Search, Word Cloud, Categories, Latest, Popular, Helpful Tips, Executive Summary, GAC and WPUI remain tracked capabilities.

## Header v2

- use WordPress custom logo/site identity when available;
- fall back to BDC mark only when no site logo exists;
- navigation resolved through configured WordPress menus first, then page slug/title fallback;
- authentication is delegated through `Public_Auth_Bridge`;
- if `[bdc_entra_login]` exists, render the Gateway integration;
- if Gateway output is unavailable, provide WordPress account/logout fallback;
- BDC never reimplements Microsoft authentication protocol.

## Home v2

Target composition:
1. compact product header;
2. knowledge hero with dominant command search;
3. contextual quick links / featured topics;
4. category explorer with visual cues;
5. content area with Latest + Popular;
6. optional operational/context footer.

Word Cloud:
- no technical implementation copy in consumer surface;
- in preview, present as “Assuntos em destaque”;
- preview badge remains visible only in admin candidate mode;
- full ASI parity remains mandatory before cutover.

## Article Reader v2

Target composition:
1. breadcrumb/context;
2. compact article hero;
3. main reading column;
4. Helpful Tips integrated as operational callouts;
5. content;
6. wider/stickier Executive Summary Rail.

### Legacy chrome sanitizer

Reader may continue using the canonical `the_content` pipeline, but the final filtered HTML passes through a read-only `Public_Article_Content` compatibility stage.

It may remove a **duplicate legacy article header only when strong evidence exists**:
- the block contains the current post title;
- and at least two legacy chrome labels such as `Responsável`, `Publicado`, `Atualizado`, `Voltar para a base`;
- and the candidate occurs before substantive body content.

Fail-safe:
- uncertainty => preserve content;
- no mutation of post_content/_elementor_data;
- no generic title stripping;
- sanitizer affects preview/candidate output only until corpus regression passes.

GAC and WPUI remain inside the filtered content pipeline.

## Helpful Tips v2

- CSS grid uses `auto-fit/minmax`, never a fixed three-column contract;
- 1 item = full useful width;
- 2 items = balanced 2-column layout;
- 3+ items = responsive wrapping;
- visual treatment is an operational callout, not a nested mini-page.

## Summary Rail v2

- 340–380px desktop target;
- clearer typography and whitespace;
- objective can use wider text treatment;
- IMPORTANT is a distinct semantic callout;
- sticky within grid, not viewport-global fixed;
- collapse/reflow below desktop wide.

## Human acceptance

The next candidate is not judged by “does it resemble legacy?”
It is judged by:
- coherent product identity;
- functional parity;
- clarity;
- density;
- readability;
- responsive behavior;
- absence of duplicate legacy chrome;
- working auth/profile integration.

No cutover is authorized by this addendum.

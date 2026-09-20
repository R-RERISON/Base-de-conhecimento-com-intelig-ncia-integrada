# UX-004/UX-005 — Header Parity + Reader Rail Addendum v3

**Status:** FROZEN FOR IMPLEMENTATION  
**Date:** 2026-09-20  
**Trigger:** human review of `0.5.0-ux004005.3`

## 1. Human feedback incorporated

Home search-first direction remains accepted.

Required corrections:
- preserve complete Header functional contract;
- preserve all five quick links;
- preserve Entra profile shortcode behavior exactly;
- do not replace quick links with Search inside articles;
- Search remains available on articles as a second compact row;
- Article Reader needs stronger visual separation;
- Executive Summary must live in its own right-side rail and follow scroll;
- Executive Summary must not reduce the article's comfortable reading width.

## 2. Header functional parity

Quick links preserved:
1. Página Inicial;
2. Consulta Avançada -> `/consulta-avancada/`;
3. Telefones -> `/telefones-importantes/`;
4. Links Úteis -> `/links-uteis/`;
5. POSTI -> external legacy destination.

Profile integration preserved through:
`[bdc_entra_login mode="profile-menu" show_department="true" show_job_title="true" show_logout="true" show_admin_link="auto"]`.

BDC changes presentation only; Entra Gateway retains authentication/profile behavior.

Responsive header retains a mobile quick-link toggle.

## 3. Search in articles

Article header structure:
- row 1: brand + quick links + profile;
- row 2: compact global BDC Search;
- Ctrl/Cmd+K preserved.

Search must never displace the functional Header navigation.

## 4. Reader canvas

The Article Reader adopts a documentation workspace composition:
- light application canvas;
- distinct article surface;
- article heading surface;
- article content column;
- independent right context rail.

This follows documentation-product principles observed in GitHub Docs / Stripe references without copying either visual system.

## 5. Executive Summary rail

Desktop target:
- Article content up to ~980px;
- right rail ~350px;
- ~48px inter-column separation;
- Reader workspace up to ~1420px inside a wider public canvas;
- `position: sticky`;
- top offset accounts for WP admin bar + BDC Header/Search rows;
- internal scrolling only if rail height exceeds viewport.

The rail is contextual chrome, not article body.

At narrower widths:
- rail reflows below the article;
- no overlay;
- no horizontal squeeze.

## 6. Reader visual separation

Use:
- light gray page canvas;
- white article reading surface;
- subtle border and restrained shadow;
- tinted Tips callout;
- lightly tinted Executive Summary;
- clear heading hierarchy.

Avoid:
- pure-white full viewport with no boundaries;
- excessive nested cards;
- marketing hero treatment;
- gradients as primary structure.

## 7. Acceptance

`0.5.0-ux004005.4` is the first candidate implementing this addendum.

Human acceptance checks:
- Header contains all legacy components;
- profile/department/job-title/logout/admin behavior remains functional;
- article Search remains available;
- article width no longer feels compressed by Summary;
- Summary follows scroll on desktop;
- Reader has clear but restrained visual separation;
- Home search-first composition remains intact.

No cutover is authorized.

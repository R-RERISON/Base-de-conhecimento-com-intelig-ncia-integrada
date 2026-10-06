# PX-750 — Theme / Snippet Independence Contract v1

**Status:** ACTIVE / DISCOVERY  
**Date:** 2026-10-06  
**SPEC:** 007 — Public Knowledge Experience

## Objective

Prove that BDC candidate Public Home and Article Reader own their presentation and Home behavior without relying on:
- ENV-001: legacy Home Code Snippet ID 9;
- ENV-002: Astra Additional CSS / theme presentation rules.

PX-750 proves independence. It does not perform global retirement or public cutover.

## Explicitly out of retirement scope

PX-750 does not remove:
- ENV-003 Entra Gateway;
- ENV-004 GAC + WP Unified Indexer;
- GRE globally.

These remain integration dependencies until their own evidence-backed disposition.

## Current environmental baseline

From PX-700/PX-710:
- front page post 41395;
- Astra 4.14.0;
- no child theme;
- Additional CSS 36,804 bytes / SHA-256 `b44b57c6a86df6b35ec1df5dfcba86c60e17555ecc2dd7d836c81c36499303a2`;
- Code Snippet ID 9 active;
- snippet SHA-256 `a465269e50c069dd945c55c3ba5488a32664589d8bdb79755cbcbe23c3c42c5a`;
- legacy Home shortcodes: `bc_home_config`, `bc_ultimas`, `bc_populares`;
- legacy AJAX: `bdc_home_filter_v270`.

## Current candidate static audit

Candidate runtime:
- no direct Astra reference in Home/Reader templates or public CSS/JS;
- no direct reference to legacy Home shortcodes/AJAX names;
- no `do_shortcode()` in candidate templates;
- `page_on_front` is used only as preview/form URL base;
- candidate templates call `wp_head()` and `wp_footer()`.

Conclusion:
- ENV-001 dependency is structurally absent from candidate composition, but must be proven in isolated runtime;
- ENV-002 independence is NOT yet proven because WordPress/theme/custom CSS still enter through `wp_head()`.

## Controlled isolation model

Isolation must be:
- candidate-preview only;
- administrator + valid preview nonce only;
- request-scoped;
- zero option/theme/snippet mutation;
- zero plugin activation/deactivation;
- zero change to page 41395;
- zero change to public Home.

Isolation may:
- suppress WordPress Custom CSS output for the candidate request;
- dequeue styles whose source resolves to the active theme directory for the candidate request;
- temporarily remove the three legacy Home shortcodes and legacy Home AJAX callbacks from candidate request scope after registration.

Isolation must preserve:
- WordPress core styles required by admin bar/accessibility where applicable;
- BDC public CSS/JS;
- Elementor/plugin CSS needed by canonical article content;
- Entra/GAC/WPUI behavior;
- `the_content` lifecycle.

## PASS criteria

### Snippet independence
PASS when candidate Home/Search/Reader remain functional while legacy Home shortcode/AJAX registrations are suppressed in the candidate request.

### Theme/CSS independence
PASS when candidate Home and representative Reader:
- preserve structure and premium visual hierarchy with active-theme CSS and Custom CSS suppressed in candidate request;
- remain usable at desktop and responsive widths;
- preserve canonical content/plugin integrations.

## Forbidden conclusions

PX-750 PASS does not itself authorize deleting:
- Code Snippet ID 9;
- Astra Additional CSS;
- Astra theme;
- legacy Home page 41395.

Deletion/cutover belongs to PX-790 after complete evidence.

## Planned gates

- PX750-01 contract.
- PX750-02 baseline/static audit.
- PX750-03 preview-only isolation implementation.
- PX750-04 static regression contract.
- PX750-05 deterministic package.
- PX750-06 human isolation smoke.
- PX750-07 closeout.

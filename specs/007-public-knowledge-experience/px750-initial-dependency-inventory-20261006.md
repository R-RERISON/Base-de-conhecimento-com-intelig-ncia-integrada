# PX-750 — Initial Theme/Snippet Independence Inventory

**Date:** 2026-10-06  
**Status:** DISCOVERY COMPLETE FOR FIRST SLICE

## ENV-001 — Home Code Snippet

Legacy dependency:
- active globally in current site;
- owns legacy Home shortcodes and category AJAX.

Candidate findings:
- no candidate Home/Reader template or public CSS/JS directly references those shortcode/AJAX symbols;
- no candidate template calls `do_shortcode()`.

Disposition:
- direct candidate dependency: NOT DETECTED;
- runtime independence: PENDING controlled isolation proof;
- global deletion: NOT AUTHORIZED.

## ENV-002 — Astra / Additional CSS

Legacy dependency:
- Astra active;
- Additional CSS materially affects legacy BDC/ASI/GRE/Elementor/single-post surfaces.

Candidate findings:
- BDC owns scoped public foundation/header/home/article CSS;
- no direct Astra selector/name dependency detected in candidate templates/public CSS/JS;
- candidate templates still invoke `wp_head()` and `wp_footer()`.

Disposition:
- direct candidate dependency: NOT DETECTED;
- ambient theme/custom-CSS influence: PRESENT BY WORDPRESS LIFECYCLE;
- runtime independence: NOT PROVEN;
- global CSS/theme removal: NOT AUTHORIZED.

## ENV-003 / ENV-004

Remain explicit external integrations:
- Entra Gateway;
- GAC;
- WP Unified Indexer.

They are not independence targets of PX-750.

## First bounded slice

Implement an admin/nonce-gated candidate isolation mode that suppresses only legacy theme/custom-CSS/Home-snippet presentation dependencies for the current preview request.

No persistent writes.

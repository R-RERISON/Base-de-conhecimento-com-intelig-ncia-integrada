# P-580A — ASI → BDC Functional Disposition Matrix v2

**Date:** 2026-09-20  
**Purpose:** eliminate unclassified functional loss before G-585.

| Domain | Capability | Disposition | Destination / evidence |
|---|---|---|---|
| Public Search | live search/as-you-type/cancel stale/loading/zero/error | IMPROVED_CANDIDATE | UX-004 v5/v6; environmental acceptance continues |
| Public Search | public authorization facade | PLANNED_BLOCKING_CUTOVER | UX-004 H-030/H-050 |
| Public Search | item/section results | PLANNED | SPEC-005 future retrieval gate; ASI parity tracked |
| Public Search | exact anchor/deep-link | PLANNED | section/item retrieval gate |
| Public Search | progressive disclosure/full-page continuity | PARITY_CANDIDATE | Public Experience candidate |
| Home | plugin-owned portal/header | IMPROVED_CANDIDATE | UX-004 v6 human accepted |
| Home | categories/latest/popular | PARITY_CANDIDATE | Public Home read models |
| Word Cloud | public rendering/click-to-search | IMPLEMENTED_CANDIDATE | `word-cloud-v1.0.0` |
| Word Cloud | generator/quality/snapshot/lock/cron/health/admin | IMPLEMENTED_CANDIDATE | P-580WC.1 environmental acceptance pending |
| Word Cloud | search-event/interactions signals | PLANNED | telemetry/search-intelligence phase |
| Word Cloud | governed vocabulary source | PLANNED | vocabulary/governance phase |
| Retrieval | post-level lexical index/ranker | PARITY_IMPROVED | G-530..G-580 closed |
| Query | bounded normalization | PARITY_IMPROVED | search-normalizer-v1.0.0 |
| Query | aliases/vocabulary/bindings | PLANNED | governed relevance/vocabulary work |
| Ranking | mutable explicit relevance controls | PLANNED | only with simulation/guardrails |
| Analytics | Search Events/interactions/outcomes | PLANNED | SPEC-006 |
| Intelligence | frequent/emerging/zero-result/p95 product analytics | PLANNED | SPEC-006 |
| Curation | diagnostics/evidence suggestions | PLANNED | dedicated curation evolution |
| Curation | ranking simulation before apply | PLANNED | required before mutable relevance |
| Regression | Golden Queries | PARITY_IMPROVED | R-510/G-550 |
| Operations | safe rebuild/lifecycle | PARITY | G-580 |
| Operations | durable queue/retry/resume | PLANNED | SPEC-007 |
| Operations | post-install/reconciliation | PLANNED | SPEC-007/deployment lifecycle |
| Quality | structural audit | SUPERSEDED_WITH_EVIDENCE | Content Extractor/KD gates |
| Quality | diagnostic exports | PARITY_PARTIAL_PLANNED | gate/evidence consolidation |
| Executive Summary | objective | SUPERSEDED_WITH_EVIDENCE | BDC Summary domain |
| Privacy | telemetry privacy modes | PLANNED | SPEC-006; no telemetry in SPEC-005 |

## Blocking interpretation

After P-580WC.1 is environmentally accepted, the remaining ASI capabilities are no longer **unclassified MISSING**.

They are either:
- already parity/improved/superseded; or
- explicitly PLANNED in a governed future phase.

This matrix does not authorize ASI decommission by itself.
G-585 still requires:
- ASI manually inactive;
- candidate Home functional;
- Search/Golden PASS;
- rebuild/lifecycle PASS;
- dependency-zero static/runtime evidence.

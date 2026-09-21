# G-590 — Package & Environmental Homologation Runbook

**Status:** READY FOR PACKAGE PREFLIGHT / ENVIRONMENTAL GATE NOT RUN  
**Date:** 2026-09-21  
**Branch:** `spec005-section-retrieval-deeplink`

## Identity

- Product Version: `0.5.1-rc.1`
- Build label: `g590.1`
- Build ID: `g590.1-<12-char-git-sha>`
- G-590 remains OPEN until environmental evidence passes.

The engineering gate is not encoded as the public Product Version.

## Preconditions

1. checkout must be the intended G-590 commit;
2. Git working tree must be clean;
3. PHP CLI must be available;
4. Python 3 must be available;
5. no manual edit to the plugin bootstrap;
6. current homologation database/files backup or rollback point must exist.

## Build

From repository root:

```bash
python tools/homologation/spec005/build-g590.py
```

Expected artifacts under `dist/`:

- `base-conhecimento-inteligencia-integrada-0.5.1-rc.1-g590.1.zip`
- matching `.manifest.json`
- `g590-local-package-validation.json`

The builder must PASS:
- cross-SPEC unit contracts 001–005;
- T100E source regression;
- staging PHP lint;
- deterministic build 2/2;
- manifest Product Version + Build ID;
- runtime resources;
- forbidden engineering runner exclusion;
- extracted ZIP PHP lint;
- staged artifact regression.

A local package PASS is not an environmental PASS.

## Package contract

The G-590 artifact must include:
- Search engine runtime;
- Section Projector/Ranker/Service;
- Anchor Manager;
- G-590 runner;
- Golden Loader + G-550 runner required by G-590;
- both Golden runtime JSON resources;
- current product runtime capabilities enabled by baseline.

It must not include unrelated engineering runners such as:
- G-570;
- G-580;
- G-585;
- H-030 technical runner;
- P-580 inventory runner.

## Upgrade test

Install the exact generated ZIP over the current homologation plugin.

Expected lifecycle:

```text
old Search Projection/state
        ↓
plugin update
        ↓
dbDelta schema 1.1 only
        ↓
version mismatch => degraded
        ↓
honest WordPress fallback
        ↓
explicit G-590 rebuild
        ↓
pass1 + pass2 NO_CHANGE
        ↓
Projection ready
```

A plugin update must never trigger a corpus rebuild implicitly.

## Run G-590

WordPress Admin:

**Base de Conhecimento → Section Retrieval G-590**

Execute once and download the JSON.

The runner is allowed to write only:
- Search Projection derived data;
- Search Projection state Option required by lifecycle/rebuild.

It must not write editorial owners.

## Machine validation

```bash
php tools/homologation/spec005/validate-g590-evidence.php /path/to/g590-environmental.json
```

Required result:

```text
failed=0
```

The validator also requires:
- Product Version `0.5.1-rc.1`;
- Build ID `g590.1-<commit>`;
- physical schema contract PASS;
- safe version transition;
- explicit rebuild PASS;
- deterministic projection;
- full corpus analyzed;
- zero extractor errors;
- no uncontextual numbered hierarchy gap;
- all generated source kinds represented by probes;
- section query probes PASS;
- deep-link materialization PASS;
- visible text unchanged;
- p95 <= 900 ms;
- max <= 1500 ms;
- post-level Golden PASS;
- editorial fingerprint equal;
- no network / no ASI / no editorial write.

## REVIEW state

`numbered_granularity_review_required=true` is not automatically a failure.

It requires specialist review of whether numbered child structures need their own navigable Search identity. No heuristic is added merely to suppress the REVIEW.

If the corpus demonstrates material navigability loss, reopen the appropriate SPEC-004 extraction/hierarchy contract by addendum.

## Immediate NO-GO

Stop G-590 and do not continue to G-585 if any occurs:
- activation/update performs implicit rebuild;
- Projection cannot reach ready after explicit rebuild;
- pass2 is not deterministic;
- Golden blocking or technical failure;
- editorial fingerprint changes;
- section query expected target missing;
- generated anchor cannot materialize;
- visible article text changes;
- uncontextual strong numbered hierarchy gap;
- performance budget exceeded;
- missing source-kind probe coverage;
- unexpected network/ASI/editorial write;
- package identity does not match manifest/evidence.

## Rollback

If package activation/update itself causes operational regression:

1. deactivate the G-590 candidate if necessary;
2. reinstall the previously accepted plugin ZIP;
3. Search Projection 1.1 data is derived and may remain;
4. no editorial rollback should be necessary because G-590 has zero editorial write;
5. previous runtime must degrade/fallback honestly if it rejects the newer Projection state;
6. if operationally required, rebuild Search Projection using the previously accepted runtime/gate procedure.

Do not manually delete editorial meta, taxonomies, comments, Elementor data, or post content as a G-590 rollback action.

## Promotion

Only after accepted environmental evidence:
1. version evidence in repository;
2. close T590-14..T590-19 with references;
3. update Master Functional Parity Ledger;
4. promote ASI-003/004/005 only to the evidence-supported state;
5. resume G-585 as **engine-independence proof**, not ASI retirement;
6. execute G-595 Boundary Closeout;
7. only after G-595 may SPEC-006 begin.

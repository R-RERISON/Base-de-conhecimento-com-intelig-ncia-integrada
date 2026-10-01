# SPEC-006 — Accepted Tooling Exception

**Status:** ACCEPTED / PRODUCT OWNER DECISION  
**Date:** 2026-10-01  
**Scope:** PHPCS/WPCS + PHPUnit local execution only

## Decision

The Product Owner explicitly chose to continue without executing the remaining local PHPCS/WPCS and PHPUnit gates because the required local tooling is not available/known in the current workstation workflow.

This is an **accepted residual tooling debt**, not a fabricated PASS.

## What is already proven on p650.4

Artifact:
- `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip`;
- SHA-256 `a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2`.

Proven:
- package identity 102/102;
- PHP lint 83/83;
- deterministic package contract;
- engineering/lab files absent;
- native modular runtime PASS;
- Search/Public Experience/Word Cloud runtime PASS;
- official Plugin Check 2.1.0 static checks 29/29 completed;
- residual Plugin Check findings explicitly dispositioned;
- unresolved blocking Plugin Check errors after disposition = 0;
- unresolved high/critical after disposition = 0;
- rollback p650.4 -> p650.3 -> p650.4 PASS;
- post_content preserved;
- BDC editorial metadata preserved;
- _elementor_data preserved;
- Search Projection rows/state preserved;
- final p650.4 identity PASS.

## What is NOT proven

Not executed on the current source/plugin tree:
- PHPCS/WPCS;
- PHPUnit.

These two items must remain represented as:
- `NOT_RUN_ACCEPTED_TOOLING_EXCEPTION`;
- never `PASS`.

## Ledger effect

- PROD-003 remains `PARTIAL`.
- PROD-004 may be promoted based on official Plugin Check + explicit disposition.
- PROD-005 may be promoted based on modular runtime/package evidence.
- PROD-006 may be promoted based on package pruning/integrity/environmental/rollback evidence.

## Release/cutover effect

This exception does NOT authorize:
- version 1.0.0;
- release-quality claim that all Premium Product Gate items passed;
- public cutover;
- ASI/GRE/KB2 retirement;
- bulk migration;
- deletion of legacy storage.

The debt remains visible and must be revisited before any gate that explicitly requires PROD-003 to be fully verified.

## Closeout rule

SPEC-006 may close operationally as:

`CLOSED_WITH_ACCEPTED_TOOLING_EXCEPTION`

It must NOT be labeled `Premium Done` or `all quality gates PASS`.

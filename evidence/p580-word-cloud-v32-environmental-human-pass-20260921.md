# P-580WC.3.2 — Environmental idempotency confirmation

**Date:** 2026-09-21  
**Candidate:** `0.5.0-p580wc.3.2`  
**Evidence type:** human environmental confirmation in homologation  
**Result:** PASS

## Sequence

Previous environmental iterations:
- p580wc.3: one Windows 10 interaction could increment twice;
- p580wc.3.1: duplicate remained after explicit counter reset;
- p580wc.3.2 replaced transient check/set with an atomic `add_option()` event claim.

Product Owner confirmation after installing p580wc.3.2:
- explicit homologation reset performed;
- consultation counting worked correctly;
- duplicate increment was no longer reproduced.

## Decision

H-023 Word Cloud consultation idempotency is accepted.

This closes the Word Cloud blocking implementation slice for H-030:
- semantic quality profile v2 accepted as current baseline;
- BDC-owned snapshot/health/cron/admin available;
- highlighted topics + aggregate consultation counts available;
- one confirmed gesture produces one aggregate increment.

This does not authorize:
- production cutover;
- anonymous public consultation telemetry;
- ASI physical removal;
- G-585 closure.

The next gate is UX-004 H-030 Technical Acceptance.

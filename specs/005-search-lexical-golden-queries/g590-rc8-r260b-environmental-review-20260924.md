# G-590 RC8 / R-260B — Environmental Review

**Evidence:** `bdc-kb-spec005-g590-section-20260924-182654.json`  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.8`  
**Build:** `g590.8-5456389bc863`

## Gate result

- T590-14 PASS
- T590-15 FAIL
- T590-16 PASS
- T590-17 PASS
- T590-18 PASS
- T590-19 FAIL / OPEN

T590-15 remains the only blocker.

## R-260B result

Shadow state partition over 548 deterministic candidates:

- toc_suppressed: 77;
- existing_heading_redundant: 0;
- duplicate_candidate_ambiguous: 78;
- promotable_shadow: 211;
- uncertain: 182.

Capacity:
- overflow_post_count: 0;
- overflow_total: 0;
- existing_at_limit_post_count: 0.

Anchor:
- paragraph_anchor_contract_blocked_count: 211.

## Interpretation

R-260B proves that:
1. capacity is not a blocker in the current corpus;
2. no promotable candidate collides with an existing heading;
3. duplicate labels can be excluded fail-closed;
4. 211 candidates are structurally promotable in shadow;
5. direct navigation remains the only unresolved architectural dimension.

## Search baseline

Current heading-based Section runtime remains healthy:
- 12 eligible probes;
- section query failures: 0;
- deep-link failures: 0;
- visible-text changes: 0;
- performance PASS;
- editorial fingerprint unchanged.

## Disposition

**R-260B = PASS / SHADOW DISCOVERY CLOSED.**

Option C — dedicated structural projection shared by consumers — is accepted as the preferred structural ownership model, still without production activation.

R-260C opens to measure anchor feasibility against rendered HTML before any Deep-Link Contract v2 or runtime promotion.

# R-260 — Legacy Numbered Hierarchy Fidelity

**Status:** OPEN / DISCOVERY / READ-ONLY  
**Trigger:** G-590 RC5 environmental evidence  
**Owner:** SPEC-004 + SPEC-005 joint review

## Problem statement

Legacy HTML may encode semantic headings as ordinary paragraphs containing hierarchical numbering.

The current Content Extractor correctly preserves source DOM semantics and does not invent headings, but downstream Search evidence found strong numbered structures that are not represented as `kind=heading`.

The problem is not whether numbering exists. The problem is whether a deterministic structural recovery can distinguish:
- real semantic section titles;
- table-of-contents/index entries;
- ordinary numbered prose;
- lists already represented by explicit DOM;
- duplicate labels.

## Baseline

SPEC-004 G-250 remains the production baseline.

No previous PASS is revoked.

## Discovery metrics required

RC6 must report:
- strong signals by source kind;
- strong signals without heading context by source kind;
- strong signals by confidence;
- affected post count;
- top affected posts by signal count;
- deterministic paragraph candidates;
- candidate post count;
- source-kind/status matrix for Search probe eligibility.

## Decision gate R-260A

No implementation proposal until the evidence answers:
1. Is the gap concentrated in legacy_html?
2. What fraction is deterministic vs ambiguous?
3. How many posts are affected?
4. Do high-volume posts indicate duplicated TOC patterns?
5. Would derived virtual sections exceed the existing 64 sections/post bound?
6. Can recovery remain consumer-derived without changing KD hashes?

## Candidate implementation options

These are hypotheses, not decisions:

### Option A — Content Extractor normalization
Promote strong numbered paragraphs into headings.

Risk: changes KD hashes and every downstream consumer.

### Option B — Derived structural projection
Keep Content Extractor immutable; Search consumes Numbered Hierarchy Resolver output to materialize virtual sections.

Risk: Search-specific structural semantics and deep-link limitations.

### Option C — Dedicated structural projection shared by consumers
Create a versioned read-only structural view between extractor and downstream consumers.

Risk: new abstraction and lifecycle/versioning cost.

## Acceptance principle

No solution is accepted merely because it makes G-590 green.

The selected solution must improve structural fidelity without fabricating hierarchy, duplicating TOC entries or regressing closed SPEC-004 contracts.

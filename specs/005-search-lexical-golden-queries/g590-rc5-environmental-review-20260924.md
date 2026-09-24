# G-590 RC5 — Environmental Evidence Review

**Evidence:** `bdc-kb-spec005-g590-section-20260924-151947.json`  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.5`  
**Build:** `g590.5-fea648a891f7`

## Gate result

- T590-14: PASS
- T590-15: FAIL
- T590-16: FAIL
- T590-17: PASS
- T590-18: PASS
- T590-19: FAIL / OPEN

## T590-15 — real structural gap

RC5 refined the hierarchy metric and still found:

- strong numbered non-heading nodes: 1,677;
- with heading context: 383;
- without heading context: 1,294.

Samples include legacy HTML paragraphs such as:
- `5.1 INTRODUÇÃO`;
- `5.2 POSTURA NO ATENDIMENTO`;
- `6.1 AVALIAÇÃO DA MONITORIA DE QUALIDADE - 1º NÍVEL`.

This is no longer classified as an evidence false-positive.

Disposition:
- SPEC-004 baseline G-250 remains valid;
- SPEC-004 is reopened in **R-260 DISCOVERY/read-only**;
- no runtime structural promotion is authorized yet.

## T590-16 — runner authorization mismatch

RC5 still showed:
- Gutenberg source posts: 5;
- Gutenberg generated anchors: 17;
- Gutenberg probe candidates: 0;
- `unprobed_source_kinds=['gutenberg']`.

Review found the runner accepted probe candidates only for `post_status=publish`, while canonical Search authorizes `publish|draft|pending|private|future` with `edit_post` capability.

Disposition:
- evidence runner corrected to canonical authorization semantics;
- production Search runtime unchanged.

## Stable passes

- corpus 623/623;
- extractor errors 0;
- explicit rebuild PASS, 0 writes, 0 mismatches;
- executed probes: 12, query/deep-link/visible-text failures 0;
- p95 323.7031 ms;
- Golden PASS;
- editorial fingerprint unchanged;
- no ASI/network/editorial write.

## RC6 purpose

RC6 is a **discovery/evidence build**, not a workaround to make G-590 green.

It must:
1. validate probe source-kind eligibility under canonical authorization;
2. report strong hierarchy by source kind/confidence/post;
3. report deterministic paragraph recovery candidates;
4. keep T590-15 blocking until structural ownership is decided.

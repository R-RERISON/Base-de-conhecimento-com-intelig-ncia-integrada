# PX-730 — Live Search Contract v1

**Status:** ACTIVE / DISCOVERY  
**Date:** 2026-10-02  
**SPEC:** 007 — Public Knowledge Experience  
**Prerequisite:** PX-720 `CLOSED_WITH_ACCEPTED_VALIDATION_EXCEPTION`

## Objective

Harden the existing candidate live-search experience without creating a new retrieval or ranking system.

PX-730 starts from the current `public-search.js`, `Public_Search_Facade`, `Public_Home_Read_Model` and candidate Home/Reader surfaces. It does not redesign Search from zero.

## Canonical ownership

Frozen:
- `Public_Search_Facade` remains the canonical public Search facade;
- existing lexical Search Service/ranker remains canonical;
- candidate UI may orchestrate requests/results but may not own ranking;
- result links remain candidate Reader links while public cutover is disabled.

## Invariants

PX-730 must NOT:
- create a new ranker;
- change ranking semantics;
- change Search index/projection schema;
- introduce vector, semantic, embedding, RAG or LLM retrieval;
- mutate editorial content;
- mutate `post_content` or `_elementor_data`;
- change Home/Astra/snippets;
- enable public cutover;
- retire ASI/GRE/GAC/WPUI/Entra.

## Interaction contract

Live Search must define and validate:
- idle;
- below-minimum-character state;
- debounce;
- request in-flight;
- request cancellation/AbortController;
- ready results;
- empty results;
- recoverable request failure;
- clear/reset;
- keyboard focus entry via Ctrl/Cmd+K;
- Escape behavior where applicable.

No state may leave stale results presented as current.

## Home contract

Home Search remains the primary action.

Required:
- candidate request stays nonce/capability constrained;
- canonical facade supplies result ordering;
- category/search state remains candidate-to-candidate;
- server-rendered fallback remains available;
- live results must not alter ranking semantics.

## Reader contract

Reader keeps the compact global Search row.

Required:
- Search must not interfere with canonical WordPress loop/the_content;
- result navigation targets candidate Reader;
- Reader search panel must have deterministic open/close/clear states;
- Search must not alter Tips/Summary/Rail ownership.

## Accessibility baseline

Required:
- focus-visible preserved;
- live result container exposes appropriate live/busy state;
- keyboard-only search is usable;
- no focus trap;
- request/loading state is understandable without pointer interaction.

Full accessibility certification remains PX-760.

## Failure contract

AJAX/network/JSON failure:
- must degrade to a clear recoverable UI state;
- must not report an empty corpus as if Search succeeded;
- must not navigate away automatically;
- must preserve server-submit fallback.

## Telemetry boundary

Existing BDC consultation/word-cloud tracking may remain if:
- it does not affect ranking;
- it is non-blocking for result navigation;
- failure is silent from the navigation perspective;
- no new analytics storage contract is introduced in PX-730.

## Regression minimum

PX-730 evidence must prove:
- canonical facade/action remains unchanged;
- min-query/debounce/cancel behavior;
- Home live search;
- Reader live search;
- server fallback;
- candidate result URLs;
- no ranker/schema mutation;
- no public cutover mutation.

## First bounded slice

Before runtime change:
1. inventory current `public-search.js` states;
2. inventory server fallback and AJAX contract;
3. identify contract gaps only;
4. add static regression coverage;
5. implement only the gaps required by this contract.

No speculative redesign.

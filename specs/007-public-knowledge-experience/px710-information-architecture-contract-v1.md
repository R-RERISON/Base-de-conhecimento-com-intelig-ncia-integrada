# PX-710 — Public Experience Information Architecture & Ownership Contract v1

**Status:** PREPARED / BLOCKED_BY_PX700_HUMAN_SMOKE  
**Date:** 2026-10-02  
**SPEC:** 007 — Public Knowledge Experience

## Objective

Freeze the final ownership and composition of the BDC Public Home + Article Reader before changing public runtime.

PX-710 does not implement cutover.

## Inputs

- SPEC-006 p650.4 foundation;
- PX-700 environmental inventory 2026-10-02;
- UX-004 Public Home contracts/addenda;
- UX-005 Article Reader contracts/addenda;
- current BDC Public Experience Preview;
- canonical SPEC-005 Search Service.

## PX710-01 — Product shape

The public BDC is a retrieval/documentation product.

Primary journey:
`Home -> Search -> Results -> Article Reader -> Official action/context`.

Home:
- Search is dominant;
- exploration is secondary/progressive;
- no dashboard-first or marketing-portal treatment.

Article Reader:
- documentation workspace;
- strong reading width;
- independent right context rail;
- compact global Search;
- no duplicated legacy chrome.

## PX710-02 — Ownership map

### BDC-owned

BDC owns:
- Public Home shell;
- Article Reader shell;
- public header presentation;
- Search UI;
- Search result presentation;
- categories/latest/popular read models;
- Word Cloud presentation/read model;
- Helpful Tips presentation;
- Executive Summary read model + rail presentation;
- theme-independent scoped CSS/JS;
- preview/cutover routing contract.

### WordPress-owned

WordPress remains owner of:
- request/permalink;
- post identity/status;
- authentication session;
- capabilities;
- canonical post content;
- category taxonomy;
- `the_content` lifecycle.

### Entra Gateway-owned

Entra Gateway remains owner of:
- authentication protocol;
- SSO;
- profile-menu shortcode behavior;
- logout/auth redirects.

BDC owns only the presentation bridge.

### GAC-owned

GAC remains external owner of:
- PostActions panel;
- KnowledgeBridge contribution panel;
- observation/access behavior.

BDC Reader must preserve the canonical `the_content` pipeline so GAC can continue to operate until a future explicit integration/cutover decision.

### WP Unified Indexer-owned

WPUI remains external owner of its current anchor injection until a dedicated parity/retirement decision.

BDC Search Anchor Manager may coexist, but duplicate anchor behavior must be regression-tested before any public cutover.

### GRE

GRE no longer owns canonical BDC metadata/Helpful Tips storage, but remains an active public renderer in the current legacy surface.

During BDC Reader preview/candidate:
- GRE Helpful Tips renderer is removed only inside the controlled preview request because BDC renders canonical Tips itself;
- GRE Executive Summary renderer is removed only inside the controlled preview request because BDC renders its own composed rail.

This does not retire GRE globally.

## PX710-03 — Search contract

No second ranker.

Public Search must continue through `Public_Search_Facade`:
- canonical normalizer;
- canonical projection;
- canonical lexical ranker;
- authorization revalidation before result return;
- publish/private handling per WordPress capability;
- password-protected content excluded;
- bounded query length;
- rate limiting;
- WordPress native fallback when projection/search is degraded.

Home and Reader share the same Search contract.

## PX710-04 — Home data semantics

### Categories

Use WordPress categories through the BDC curated Home read model.

No separate taxonomy or duplicate category store.

### Latest

Contract:
- published posts only;
- category filter optional;
- order by date DESC, ID DESC;
- bounded result count.

### Popular

Compatibility contract for first public cutover:
- published posts only;
- category filter optional;
- order by `comment_count DESC, date DESC, ID DESC`.

Reason:
- PX-700/P580 confirm the current Home snippet uses comment_count;
- keeping this semantic avoids an unnecessary product behavior change during shell migration.

Any future popularity metric belongs to a separate evidence-backed gate.

### Word Cloud

Use BDC `Word_Cloud_Service` snapshot.

Public request must never trigger regeneration.

No shortcode registration is required for BDC ownership.

## PX710-05 — Header/Auth contract

Header row 1:
- BDC brand;
- five quick links:
  1. Página Inicial;
  2. Consulta Avançada;
  3. Telefones;
  4. Links Úteis;
  5. POSTI;
- Entra profile area.

Article row 2:
- compact global BDC Search.

Auth:
- prefer installed `bdc_entra_login` Gateway profile-menu integration;
- fallback to WordPress account/login only when Gateway is unavailable;
- BDC must not implement Entra/OIDC protocol itself.

## PX710-06 — Article content pipeline

Reader must use the canonical WordPress content pipeline.

Current candidate path:
- run inside the main loop;
- call `the_content()`;
- preserve WordPress/GAC/WPUI/other compatible filters;
- strip only duplicated legacy chrome where the compatibility heuristic safely identifies it.

Do not replace `the_content` with raw `post_content`.

Do not bypass third-party callbacks merely to simplify the template.

## PX710-07 — Helpful Tips

Canonical owner:
- `BDC\KnowledgeBase\Helpful_Tips_Store`;
- physical key `_bdc_es_helpful_tips`.

Shape:
- ordered list;
- `title:string`;
- `content:string`.

Reader:
- renders Tips once;
- legacy GRE Tips renderer may be removed only for the candidate Reader request to prevent duplicate rendering;
- no storage migration is required.

## PX710-08 — Executive Summary Rail

BDC read model composes:
- Summary store;
- Classification store;
- Knowledge Facts store.

Display fields may include:
- Objetivo;
- Equipe responsável;
- Item de Catálogo;
- Serviço Afetado;
- Sistemas envolvidos;
- Público Alvo;
- Escalonamento;
- IMPORTANTE.

No metadata duplication.

Desktop:
- independent right rail;
- sticky when layout permits;
- must not reduce article reading width below comfortable target.

Responsive:
- rail reflows below article;
- never overlays content.

## PX710-09 — Theme/Snippet transition model

Current dependencies confirmed by PX-700:
- Home page 41395 still uses Code Snippet shortcodes;
- Astra Additional CSS is material and contains BDC/ASI/GRE/Elementor/single-post signals.

Migration model:
1. keep current public Home untouched;
2. evolve BDC candidate surfaces independently;
3. prove candidate parity;
4. prove BDC surfaces with legacy Home snippet/Astra BDC CSS dependency disabled in a controlled homologation window;
5. only then authorize cutover/removal under PX-750/PX-790.

No destructive edit to page 41395 in PX-710.

## PX710-10 — Preview to cutover boundary

Preview remains:
- nonce-gated;
- administrator-only;
- non-disruptive;
- candidate-to-candidate navigation.

Public cutover later must:
- remove admin-only preview restriction only through an explicit public routing gate;
- preserve authorization-safe Search;
- preserve Entra integration;
- preserve external content pipeline dependencies until individually dispositioned.

## PX710-11 — Environmental dependency disposition

PX-700 confirms:
- ENV-001 Home Code Snippet = active dependency;
- ENV-002 Astra Additional CSS = active dependency;
- ENV-003 Entra Gateway = active dependency;
- ENV-004 GAC + WPUI = active dependencies.

These remain blockers for theme/snippet independence and cutover, not blockers for preview/candidate implementation.

ASI target plugin/search shortcode were not detected in homologation:
- do not add new ASI dependency;
- do not claim global ASI retirement from this single environment.

## PX710-12 — First implementation slice

After PX-700 human smoke PASS, first runtime slice is PX-720 Public Shell.

PX-720 may:
- harden the existing candidate Home/Header/Reader shell;
- improve route/component composition;
- improve theme independence in candidate mode;
- add bounded regressions.

PX-720 may NOT:
- change lexical ranking;
- change canonical storage;
- write editorial content;
- change page_on_front;
- delete Home snippet;
- remove Astra CSS globally;
- deactivate GRE/GAC/WPUI/Entra;
- enable public cutover.

## Acceptance

PX-710 is PASS when:
- PX-700 human smoke is PASS;
- ownership map is accepted;
- Home data semantics are frozen;
- Search/Auth/Reader/Tips/Rail contracts are frozen;
- external dependencies and later retirement gates are explicit;
- first PX-720 slice is bounded and non-disruptive.

Next:
- PX-720 Public Shell.

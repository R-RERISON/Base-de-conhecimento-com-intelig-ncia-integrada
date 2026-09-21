# UX-004 — H-010 Public Home Contract v1.0.0

**Status:** FROZEN FOR IMPLEMENTATION  
**Date:** 2026-09-20  
**Evidence:** P-580 environmental inventory #1/#2  
**Surface:** Public Home / Portal de Entrada

## 1. Product ownership

The Base de Conhecimento plugin owns the public Home experience.

WordPress may retain a front-page object as route identity, but the application shell, components, assets and data orchestration belong to the BDC plugin.

The current page 41395 is a migration/rollback anchor, not the target implementation container.

## 2. Rollout model

No abrupt replacement.

### Phase H020-A — Preview
- BDC Home renderer available only to administrators through an explicit preview path/flag;
- current Home remains untouched;
- no page content write;
- no Elementor write;
- no change to `page_on_front`.

### Phase H020-B — Controlled takeover
After technical + human acceptance:
- plugin may intercept the configured front page on frontend requests;
- route/permalink remains stable;
- current page content remains retained for rollback;
- Elementor editor/preview, REST, admin and feeds are never intercepted.

### Phase H050 — Legacy retirement
Only after Home independence PASS:
- remove runtime dependency on Code Snippet #9 and ASI public shortcodes;
- preserve rollback evidence;
- physical deletion of legacy content/snippets is a separate explicit decision.

## 3. Template/shell contract

Target:
- plugin-owned template/renderer;
- shared Public Experience foundation;
- BDC Header;
- BDC Home main;
- WordPress footer integration only where required.

Do not store the BDC application markup in `post_content`.

## 4. Header contract

Must preserve/improve:
- BDC brand;
- Página Inicial;
- Consulta Avançada;
- Telefones;
- Links Úteis;
- POSTI;
- authenticated user identity/profile affordance.

Authentication ownership remains WordPress/Entra integration. The BDC Home must not require `[bdc_entra_login]` to render its chrome.

The Header reads WordPress authentication state through canonical APIs and exposes integration points rather than owning SSO protocol logic.

## 5. Home component contract

Required components:
1. Hero/title/description;
2. Public Search;
3. Word Cloud;
4. curated categories;
5. Últimas Atualizações;
6. Instruções Populares;
7. loading/empty/error/degraded states.

Each component:
- has a server-side service/read model;
- has an independent renderer/component;
- does not directly query legacy ASI/GRE storage;
- shares BDC design tokens;
- must fail locally without collapsing the full Home.

## 6. Latest contract

Semantic label:
`Últimas Atualizações`.

V1 deterministic rule:
- post type: `post`;
- status: `publish`;
- visibility: WordPress-authorized;
- explicit order: `post_date DESC, ID DESC` for compatibility with the current public list;
- bounded result count;
- category filter may constrain the same query.

Future change to `post_modified` requires an explicit product decision because it changes user-visible ordering.

## 7. Popular contract

Current legacy evidence:
- hosted in Code Snippet #9;
- uses WP_Query;
- `comment_count` appears in the implementation;
- no meta-based popularity signal was observed.

Compatibility rule for initial BDC implementation:
- provide a legacy-compatible popularity provider using `comment_count DESC, post_date DESC, ID DESC`;
- mark the provider as `legacy_popularity_v1`;
- keep the provider behind an interface.

Target improvement:
- when BDC Search/Consumption telemetry is available, introduce a measured popularity provider based on actual consumption/interactions;
- compare legacy vs measured results;
- switch only through a separately accepted contract.

Do not silently redefine “popular”.

## 8. Categories/filter contract

Current Home categories are a curated navigation surface, not an unrestricted taxonomy dump.

V1:
- use WordPress category taxonomy;
- configuration is a BDC-owned allowlist/order;
- preserve the current visible category set during first acceptance;
- category click updates Latest and Popular result regions consistently;
- `Todos` removes category constraint;
- URL/state synchronization is desirable but not required for first slice.

AJAX/REST implementation must:
- validate input;
- resolve terms through WordPress APIs;
- never trust arbitrary query fragments;
- use bounded result counts;
- support authenticated and applicable anonymous/internal frontend requests according to site access policy.

## 9. Public Search contract

No duplicate ranker.

Public Search uses the frozen BDC Search engine through a public facade.

Authorization:
- Projection is retrieval-only, never authorization authority;
- every result is revalidated against WordPress visibility/capability;
- publish/public content is eligible;
- private/restricted content only when current user is authorized;
- draft/pending/future never leak to unauthorized users.

UX capabilities inherited from ASI baseline:
- search-as-you-type;
- submit/Enter;
- stale-request cancellation;
- minimum query length;
- loading;
- zero results;
- technical error;
- degraded/fallback;
- retry;
- progressive result disclosure where useful;
- keyboard/focus/ARIA.

Item/section result parity remains a tracked ASI capability and is not silently dropped.

## 10. Word Cloud contract

Word Cloud remains mandatory.

BDC module must ultimately own:
- generator;
- quality/canonicalization;
- content/title/heading/taxonomy signals;
- search/interaction signals when telemetry exists;
- vocabulary;
- allow/blocklist;
- snapshot;
- lock;
- scheduling;
- health/history;
- public rendering;
- click-to-search.

Initial implementation may stage sources:
- source availability must be reported;
- missing future signals are explicitly `pending`;
- no signal can disappear silently.

## 11. Asset contract

Split public assets:
- `public-foundation.css`;
- `public-header.css`;
- `public-home.css`;
- `public-search.css`;
- `public-word-cloud.css`;
- public JS modules/components.

No dependency on Astra Additional CSS for correct BDC Home behavior.

Assets load only on BDC public surfaces.

## 12. Accessibility/responsive

Required:
- semantic landmarks;
- visible focus;
- keyboard operation;
- ARIA live feedback for async search/filter states;
- no state represented by color only;
- desktop plus 782px/520px source rules;
- human desktop acceptance remains mandatory;
- narrower visual acceptance follows release requirement decision.

## 13. Compatibility/decommission

Home implementation must not depend on:
- `[asi_search_form]`;
- `[bdc_word_cloud]` from ASI;
- Code Snippet #9 after H050;
- Elementor markup for the application shell.

During transition these may coexist only as rollback/reference paths.

## 14. Acceptance

H-010 PASS requires this contract frozen.

H-020 implementation starts behind preview/non-disruptive controls.

H-030 must prove:
- no editorial writes;
- no ASI runtime for candidate Home;
- public authorization;
- Search regression/Golden unchanged;
- Latest/Popular/Category bounded behavior;
- Word Cloud module state explicit;
- no raw legacy shortcode.

H-040 requires Product Owner visual acceptance.

H-050 authorizes Home cutover only, not ASI/GRE global decommission.

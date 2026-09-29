# Public Experience Visual Research — Search-first v1

**Date:** 2026-09-20  
**Status:** RESEARCH COMPLETE / INPUT FOR UX-004 + UX-005  
**Trigger:** human review of `0.5.0-ux004005.2`.

## Product direction

The BDC public experience is not a marketing website.

It is a knowledge retrieval product.

Primary mental model:
- Google-like search-first Home;
- documentation-style clean article reading;
- persistent access to Search while reading;
- progressive disclosure for secondary discovery features.

## External references researched

### Material Design — Search pattern
Key pattern:
- persistent search is appropriate when Search is the primary app task;
- search field should be immediately available;
- suggestions/autocomplete are progressive enhancements;
- search remains visible around results.

Applied to BDC:
- Home search dominates the first viewport;
- secondary content must not compete with the query task.

### Zendesk Help Center
Observed guidance:
- search bar is available at the top of every Help Center page;
- instant search can route directly to matching articles;
- native search searches titles/content and ranks relevant results.

Applied to BDC:
- Search must be available inside Article Reader;
- returning to Home is not a prerequisite for a new query.

### Intercom Modern Help Center
Observed patterns:
- cleaner docs-style reading layout;
- quick search via Ctrl/Cmd+K;
- persistent navigation/context;
- breadcrumbs;
- content and branding retained while public experience is modernized.

Applied to BDC:
- keyboard shortcut `Ctrl/Cmd+K`;
- clean article chrome;
- preserve source content/integrations while replacing presentation;
- BDC Summary Rail supplies persistent context without adding a second heavy navigation sidebar in v1.

### GitBook
Observed patterns:
- global Ask/Search palette can be opened with Ctrl/Cmd+K;
- results may navigate to pages or sections;
- keyboard-oriented documentation navigation;
- search available throughout documentation.

Applied to BDC:
- global search contract belongs to Public Experience foundation;
- article Search should return BDC preview/reader links, not legacy frontend links;
- future section/deep-link results remain part of ASI parity.

### Algolia DocSearch
Observed pattern:
- documentation search is a dedicated frontend experience, separate from indexing;
- first-keystroke discovery and keyboard navigation are core expectations.

Applied to BDC:
- Search UI is a product component independent from Search storage/indexing;
- future instant results can be layered over the frozen lexical engine/public facade.

## UX decision

### Home
First viewport:
1. compact BDC header;
2. centered title;
3. dominant search field;
4. a short row of frequent topics;
5. optional “Explorar a Base” disclosure.

Not shown by default:
- dashboard metrics;
- large category cards;
- Latest/Popular panels;
- large Word Cloud container;
- marketing-like hero illustration.

Capabilities are preserved through progressive disclosure, not deleted.

### Article Reader
- sticky compact header;
- persistent Search field;
- Ctrl/Cmd+K focuses Search;
- breadcrumb;
- title + minimal metadata;
- reading column;
- Helpful Tips as compact operational callout;
- Executive Summary Rail on desktop;
- no duplicated legacy header;
- no return-to-Home requirement to search.

### Search navigation
Within preview:
- Search result links always open the new Article Reader preview;
- Home Latest/Popular links also open the new Reader;
- this allows end-to-end validation of the candidate public experience.

## Non-goals

This research does not authorize:
- production cutover;
- ASI/GRE removal;
- semantic/AI ranking;
- public authorization facade bypass;
- silent loss of Word Cloud quality/telemetry features.

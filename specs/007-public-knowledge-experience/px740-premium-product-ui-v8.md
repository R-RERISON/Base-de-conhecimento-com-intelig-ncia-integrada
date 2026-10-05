# PX-740 — Premium Product UI v8

**Status:** SOURCE PASS / PACKAGE PENDING  
**Date:** 2026-10-05

## Why v8 exists

v7 improved polish but did not resolve the deeper product-design problem visible in Product Owner screenshots. The interface still exposed the grammar of a WordPress backoffice:
- large horizontal tab strip;
- cards for every status;
- implementation-oriented metrics;
- long stacked forms;
- hidden Home discovery;
- redundant Reader framing.

v8 changes composition, not domain behavior.

## Product principles

1. **Task first** — one dominant work surface, not dashboard decoration.
2. **Domain grouping** — Content, Knowledge Structure and Governance are visually distinct.
3. **Progressive density** — navigation and context support the task without competing with it.
4. **Document first** — public Reader prioritizes article reading over containers.
5. **Discovery visible** — public Home exposes useful existing content below Search.
6. **WordPress is host, not visual identity** — BDC pages keep WordPress capabilities while presenting a coherent product shell.

## Admin architecture

### Article list
- search remains canonical;
- total/status data uses existing WordPress counts;
- ornamental Summary/Classificação implementation counters are removed from the primary view;
- table remains accessible and server-rendered.

### Article workspace
Registry is unchanged:
- overview;
- content;
- summary;
- classification;
- details;
- intelligence;
- core_blocks;
- review;
- history.

Presentation groups:
- **Artigo:** overview, content, summary, classification, details;
- **Governança:** intelligence, core_blocks, review, history.

Desktop:
- local product navigation;
- active work area;
- contextual read-only panel where applicable.

<=1100px:
- navigation returns to horizontal scrolling;
- content/context reflow vertically.

### Overview
Seven independent status cards are replaced by:
- Content editorial;
- Knowledge structure;
- Governance.

No new metric or score is invented.

### Forms
- existing handlers/writers remain canonical;
- visual grouping only;
- save action remains explicit;
- no autosave contract introduced.

## Public architecture

### Home
Existing canonical read models only:
- categories;
- latest;
- popular;
- word cloud;
- Search facade.

Discovery opens by default but Search remains dominant.

### Reader
- article template and `the_content` capture unchanged;
- one redundant visual shell removed;
- GAC action surface is identified only for candidate styling;
- Rail state machine remains flow/fixed/bottom.

## Blocked changes

v8 does not authorize:
- Search ranking/schema changes;
- new writers;
- post/meta/options mutation;
- GAC/WPUI/GRE ownership changes;
- content migration;
- theme/snippet mutation;
- public cutover;
- 1.0 release authorization.

## Acceptance

Product Owner should evaluate:
- article list;
- workspace overview;
- workspace content/read-only areas;
- long writer form;
- Home default state;
- Home Search results;
- Reader with GAC + Summary Rail.

PASS requires the interface to read as a coherent product workspace rather than decorated WordPress admin.

# PX-740 — Premium Visual Refinement v7

**Status:** IMPLEMENTED / HOMOLOGATION REQUIRED  
**Date:** 2026-10-05

## Diagnosis

Screenshots confirmed functional maturity but visible development scaffolding:
- excessive card nesting in Reader;
- weak editorial hierarchy between shell, GAC-integrated content and article body;
- Home hero leaves neutral space without visual intent;
- navigation pills read as generic primitives;
- search results still resemble a technical list;
- Summary Rail is functional but visually utilitarian;
- Preview admin page exposes homologation structure rather than product framing.

## Direction

1. reduce cardification;
2. strengthen typography and reading rhythm;
3. use depth through planes rather than borders;
4. quiet navigation chrome;
5. keep Search as the dominant Home action;
6. make Reader an editorial canvas;
7. normalize legacy content visually without mutating storage;
8. present Summary Rail as contextual intelligence;
9. keep internal Preview clearly internal but product-quality.

## Runtime boundaries

v7 changes only:
- CSS presentation;
- internal Preview admin markup;
- preview sample IDs;
- UI version label.

No changes to:
- Search facade/ranker/schema;
- Home data semantics;
- article content capture;
- `the_content` pipeline;
- GAC/WPUI callbacks;
- Helpful Tips or Summary data;
- Reader Rail state machine;
- public cutover;
- theme/snippet/editorial storage.

## Acceptance

- Home no longer reads as an empty prototype canvas;
- Header/navigation is quieter and more product-like;
- Search command bar has clear hierarchy;
- Reader has editorial rhythm and fewer visible boxes;
- Summary Rail integrates with the Reader;
- internal Preview does not look like raw development tooling.

Functional regression remains governed by PX-730/PX-740 contracts.

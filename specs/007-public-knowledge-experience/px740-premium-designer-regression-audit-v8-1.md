# PX-740 — Premium Designer Regression Audit v8.1

**Date:** 2026-10-05  
**Trigger:** Product Owner screenshots from px740.5  
**Disposition:** REGRESSION CONFIRMED / RECOVERY IMPLEMENTED / VISUAL RE-TEST REQUIRED

## Executive result

px740.5 must not be accepted as premium. The screenshots show a hard navigation regression and a broader layout regression.

The issue is not subjective styling. It is a conflict between layout ownership layers plus an architecture that exceeded the width budget available inside wp-admin.

## Findings

| Severity | Finding | Evidence / consequence | Recovery |
| --- | --- | --- | --- |
| Critical | CSS ownership conflict | `visual-foundation.css` loads after `workspace.css` and reintroduced `.bdc-kb-tabs { display:flex; overflow-x:auto }` | final v8.1 cascade moved to/owned by last-loaded visual layer |
| Critical | Three-column architecture | local nav + writer + context reduced usable writer width and violated Design System v1 | top local nav; workspace max = main + context |
| Critical | Asset cache collision | all candidates used `?ver=0.6.0-dev` | SHA-256 file fingerprint appended to product version |
| High | Navigation clipping | labels truncated and horizontal scrollbar visible | vertical nav removed; five direct routes + accessible overflow |
| High | First row title loss | screenshot shows post #14523 metadata but not primary title | sticky thead removed; title/meta block rendering hardened |
| High | Empty vocabulary listboxes | large blank controls communicate unfinished UI | explicit empty state replaces empty select |
| High | Fixed 6-row listboxes | one-term vocabulary rendered oversized | multi-select size follows available term count |
| High | Sticky submit overlay | save bar competes with form and can overlay last controls | submit returns to document flow |
| High | Helpful Tips form inflation | 8 blank rows rendered regardless of current content | default slots reduced to 3; existing items + one still preserved |
| Medium | Excess pill language | vocabulary actions looked like tags rather than actions | compact rounded-rectangle action treatment |
| Medium | Surface nesting | backoffice felt component-heavy | fewer layout containers; hierarchy via spacing and sections |

## Modern design validation

The recovered direction follows established enterprise UI principles:
- navigation must be brief, scannable and goal-oriented;
- spacing/proximity should establish relationships before borders;
- pills are reserved primarily for tags/selections rather than generic navigation/actions;
- content surfaces should avoid excessive nesting;
- application layout must adapt without compressing the main task below a comfortable width.

## v8.1 information architecture

### Workspace navigation
Primary:
1. Visão geral
2. Conteúdo
3. Sumário
4. Classificação
5. Detalhes

Overflow `Mais`:
- Inteligência
- Blocos do WordPress
- Revisão e governança
- Histórico

No registry key, route, handler or writer changes.

### Workspace body
Desktop wide:
- main work area;
- optional read-only context panel.

<=1180px:
- context reflows below main.

There is no third simultaneous product column.

## Form behavior

### Classification
- terms available: canonical select remains;
- empty vocabulary: explicit empty state;
- multiple terms: listbox height derives from term count, capped at 6 rows.

### Knowledge details / Helpful Tips
- operational facts and Helpful Tips become separate task sections;
- default empty Tip capacity = 3 rows instead of 8;
- when existing Tips exceed 3, every existing item plus one blank row remains visible up to the store limit.

## Asset cache contract

`bdc_kb_asset_version( relative_path )` returns:
- `BDC_KB_VERSION + first 12 chars of SHA-256(file)` when file exists;
- `BDC_KB_VERSION` fallback otherwise.

This preserves product version `0.6.0-dev` while making changed CSS/JS URLs unique between homologation candidates.

## Invariants

Unchanged:
- Search JS / ranking / schema;
- public article template;
- Public_Article_Content;
- Post_Activity_Registry keys;
- Summary/Classificação/Review stores and handlers;
- GAC/WPUI/GRE ownership;
- Reader Rail flow/fixed/bottom;
- editorial storage;
- public cutover.

## Source gate

26/26 static/source checks PASS.

px740.5 remains rejected for visual regression.
v8.1 requires a new packaged candidate and Product Owner visual re-test.

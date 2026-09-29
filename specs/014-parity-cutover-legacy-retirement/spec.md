# SPEC-014 — Parity, Cutover & Legacy Retirement

**Status:** PLANEJADA / ÚLTIMA SPEC PRÉ-1.0

## Problema

Aposentar legado é decisão operacional, não inferência arquitetural.

## Entrada

Master Functional Parity Ledger madura e sem gaps desconhecidos aplicáveis.

## Escopo

- freeze de versões legadas;
- production consumer preflight;
- data preservation map;
- shortcodes/hooks/filters inventory;
- coexistence;
- single-writer enforcement;
- deactivation drills;
- rollback/reactivation;
- data migration/adoption;
- rebuild de projections;
- cleanup;
- uninstall/retention;
- release 1.0.0.

## Retirement separado

### GRE
Summary/Tips/Coverage/Public Rail e consumidores resolvidos.

### ASI
Search post/item, anchors, Golden, governed relevance, telemetry/intelligence e operations aplicáveis resolvidos.

### KB2Ops
Studio/Review/Classificação/pre-analysis/reporting/public consumers resolvidos ou formalmente aposentados.

### Ambiente
Code Snippets/Astra CSS/GAC/WPUI/adapters somente após preflight.

## Gates

CUT-1400 Reference Freeze  
CUT-1410 Ledger Zero-Blocker  
CUT-1420 Consumer Preflight  
CUT-1430 Data Preservation  
CUT-1440 GRE Drill  
CUT-1450 ASI Drill  
CUT-1460 KB2Ops Drill  
CUT-1470 Environmental Dependency Drill  
CUT-1480 Rollback/Reactivation  
CUT-1490 1.0.0 Release Gate

PARTIAL, GAP ou UNKNOWN_ENVIRONMENTAL aplicável impede retirement. 1.0.0 só nasce após CUT-1490 PASS.

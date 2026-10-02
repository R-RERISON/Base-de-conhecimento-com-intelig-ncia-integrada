# SPEC-007 — Public Knowledge Experience

**Status:** ATIVA / IMPLEMENTATION

Os candidatos UX-004/UX-005 existentes serão reutilizados; não serão reescritos por princípio.

## Problema

A experiência pública depende de tema, snippets, ASI, GRE e integrações. O BDC precisa possuir a jornada pública premium sem acoplamento visual frágil.

## Jornada

Home -> pesquisa -> resultados -> artigo -> dicas/resumo -> fonte oficial.

## Escopo

- Public Home plugin-owned;
- Search-first;
- Header/navigation;
- Entra/Auth bridge;
- categories/latest/popular;
- live search;
- Article Reader;
- filters necessários preservados;
- Helpful Tips;
- Executive Summary Rail composto;
- print;
- responsive;
- teclado/screen reader;
- theme independence;
- preflight Astra Additional CSS/Code Snippets;
- GAC/WP Unified Indexer somente conforme evidência.

## Regras

- nenhum segundo ranker;
- zero rewrite editorial;
- legacy chrome removido somente com evidência;
- fallback preserva conteúdo;
- Search Service canônico é reutilizado.

## Gates

PX-700 Environmental Inventory  
PX-710 Information Architecture  
PX-720 Public Shell  
PX-730 Live Search  
PX-740 Reader/Tips/Rail  
PX-750 Theme/Snippet Independence  
PX-760 Accessibility/Responsive  
PX-770 Performance  
PX-780 Human Product Acceptance  
PX-790 Cutover Readiness


## Activation baseline — 2026-10-01

SPEC-007 was activated after the formal closeout of SPEC-006.

Inherited foundation:
- artifact: `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip`;
- SHA-256: `a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2`;
- SPEC-006 status: `CLOSED_WITH_ACCEPTED_TOOLING_EXCEPTION`;
- PROD-003 remains PARTIAL and must not be represented as solved;
- PROD-004/005/006 are IMPROVED_VERIFIED.

Existing product assets to reuse:
- current BDC Public Experience Preview;
- UX-004 Search-first Home contracts/addenda;
- UX-005 Article Reader contracts/addenda;
- Public Search facade;
- Word Cloud;
- Helpful Tips canonical storage;
- modular Search/Public Experience/Word Cloud foundation.

Historical environmental baseline:
- P580 deep inventory from 2026-09-20 is REFERENCE_ONLY for PX-700;
- environmental dependency state must be refreshed before implementation/cutover conclusions.

First active gate:
- `PX-700 Environmental Inventory`.

No public cutover is authorized by SPEC-007 activation.


## Gate state — 2026-10-02

- PX-700 Environmental Inventory: PASS.
- PX-710 Information Architecture & Ownership: PASS.
- PX-720 Public Shell: ACTIVE.

PX-720 remains candidate/preview only. No public cutover is authorized.

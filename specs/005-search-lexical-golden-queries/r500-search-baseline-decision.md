# R-500 — Search Baseline Decision

**Status:** PASS / CLOSED  
**Data:** 2026-09-18

## Decisões

1. Superfície inicial: **administrativa / Knowledge List**.
2. Busca pública: postergada.
3. `WP_Query s + modified DESC`: rejeitado como ranking de Search.
4. `WP_Query` nativo: preservado como baseline/fallback.
5. Search Document semântico post-level: **necessário conceitualmente**.
6. Search Document deve consumir Content Extractor/owners aprovados, não reimplementar parser editorial.
7. Persistência/FULLTEXT: **a decidir em G-520**.
8. Golden Queries: obrigatórias antes do engine/ranker.
9. ASI permanece baseline funcional de qualidade; nova arquitetura deve preservar/superar seus bons contratos.

## Evidência

`evidence/r500-t502-environmental-20260918T200421Z.json`.

## DoR

R-500 fechado não significa runtime autorizado.

Próximo gate: **R-510 Golden Dataset v1**.

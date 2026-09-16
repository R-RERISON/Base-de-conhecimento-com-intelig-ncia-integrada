# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- R-200: PASS.
- R-210: PASS.
- G-220: PASS ambiental.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240/v1: FAIL CONTROLADO — perda estrutural.
- G-240/v2/KD 2.0.1: full-corpus técnico PASS, porém aceite humano A/B **FAIL CONTROLADO — HIERARCHY FIDELITY**.
- evidência humana: `evidence/g240-v2-acceptance-20260916T153610Z.json`.
- análise: `g240-v2-hierarchy-gap-analysis-20260916.md`.
- próximo contrato: `knowledge-document-contract-v2.1.0-draft.md`.
- PR #3 permanece DRAFT/NÃO MERGEAR.
- G-245 BLOCKED; writer/migration Elementor proibidos.

## Resultado humano A/B

- 8/8 revisados;
- cobertura 8/8;
- ordem 8/8;
- ausência de invenção 8/8;
- estrutura preservada 5/8;
- estrutura perdida 3/8: 1290, 370, 1307;
- stale/repeatability/sample mismatch = 0;
- gate=false.

## Causa

O KD 2.0.1 prova cardinalidade estrutural, mas ainda não prova fidelidade de relações pai/filho. `children` é reconstruído por `parent_item_id`; não existe inferência textual de numeração hierárquica.

O acceptance runner também trata `review_required` como falha automática, apesar de essa condição representar limitação explícita e não necessariamente `not_ready`.

## Próximo estado

KD `2.1.0` deve adicionar relationship fidelity, numbered hierarchy resolver conservador, proveniência/confiança de hierarquia e acceptance gate separado entre human pass, system status e gate pass.

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
- contrato KD 2.1.0 congelado: `knowledge-document-contract-v2.1.0.md`.
- implementação KD 2.1.0 preparada no build `0.4.0-acceptance.12`.
- validação ambiental do build `.12`: **PENDENTE**.
- PR #3 permanece DRAFT/NÃO MERGEAR.
- G-245 BLOCKED; writer/migration Elementor proibidos.

## Baseline humana que motivou o 2.1.0

- 8/8 revisados;
- cobertura 8/8;
- ordem 8/8;
- ausência de invenção 8/8;
- estrutura preservada 5/8;
- estrutura perdida 3/8: 1290, 370, 1307;
- stale/repeatability/sample mismatch = 0;
- gate=false.

## Implementado no KD 2.1.0 / `0.4.0-acceptance.12`

1. `Hierarchy_Relationships` read-only:
   - expected/actual para topologia explícita;
   - parent edges;
   - root count;
   - max depth;
   - sibling/tree signatures;
   - heading parent/path signatures quando a boundary é inequivocamente verificável.
2. `Numbered_Hierarchy_Resolver` conservador:
   - gramática `1`, `1.1`, `1.2`, `1.2.1`;
   - não infere token isolado;
   - exige parent prefix anterior no mesmo source + heading_path;
   - DOM explícito vence;
   - conflito/ambiguidade ficam auditáveis.
3. Proveniência/confiança:
   - `explicit_dom|numbering_inferred|heading_inferred|flat`;
   - `authoritative|deterministic|ambiguous`.
4. KD schema `2.1.0`:
   - adiciona `hierarchy.relationship_fidelity`;
   - adiciona `hierarchy.numbered_hierarchy`;
   - hierarchy entra em `source_hash` e `document_hash`.
5. AI readiness:
   - relationship mismatch explícito → `not_ready`;
   - `HIERARCHY_AMBIGUOUS`/`HIERARCHY_NUMBERING_CONFLICT` → `review_required`;
   - expõe `cardinality_complete`, `relationship_complete`, `structure_complete`.
6. Acceptance runner:
   - separa `human_pass`, `system_status`, `gate_pass`;
   - `review_required` deixa de falhar automaticamente;
   - `not_ready` permanece bloqueante.

## Próximo passo obrigatório

Não avançar para G-245.

Executar no ambiente de homologação, nesta ordem:

1. testes/smoke do build `0.4.0-acceptance.12`;
2. full-corpus KD 2.1.0 em duas passagens;
3. verificar zero errors/throwables/hash mismatch/canonical JSON mismatch e zero mutação editorial;
4. analisar qualquer `HIERARCHY_*` produzido pelo full-corpus;
5. somente com full-corpus tecnicamente aceitável, executar novamente os mesmos oito A/B;
6. versionar as novas evidências;
7. fechar G-240 apenas com 8/8 de estrutura humana preservada e `gate_pass=true`.

## Bloqueios permanecem

- PR #3: DRAFT / NÃO MERGEAR.
- G-245: BLOCKED.
- writer Elementor: PROIBIDO.
- migration Elementor: PROIBIDA.
- qualquer persistência de KD/resultado: PROIBIDA nesta fase.

# Roadmap SpecKit — Base de Conhecimento com Inteligência Integrada

> **Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Estado canônico — 2026-09-18

1. **SPEC-000 — Inventário Profundo e Contratos** — CONCLUÍDA.
2. **SPEC-001 — Core mínimo + Summary narrativo** — CONCLUÍDA.
3. **SPEC-002 — Classificação de Conhecimento** — CONCLUÍDA.
4. **UX-001/UX-002/UX-003** — CONCLUÍDAS / baseline visual vigente.
5. **SPEC-003 — Review & Governança** — CONCLUÍDA.
6. **SPEC-004 — Content Extractor + Knowledge Document + Canonical Block Normalization** — **CLOSED/main**.
   - G-240 PASS/CLOSED;
   - G-245 PASS/CLOSED;
   - G-250 PASS/CLOSED;
   - RC final `0.4.0-spec004-rc2`.
7. **SPEC-005 — Search Lexical + Golden Queries** — **ATIVA / DISCOVERY** em `spec005-search-lexical-golden-queries`.
8. **SPEC-006 — Telemetria / Inteligência de Busca** — planejada.
9. **SPEC-007 — Operações / Jobs / Indexação** — planejada.
10. **SPEC-008 — Semantic Search / Vetores / Hybrid Retrieval** — futura, condicionada a Golden Queries/métricas.
11. **SPEC-009+ — IA / Foundry / RAG** — futura, assistiva e governada.

## Fronteira da SPEC-005

A SPEC-005 começa sem engine própria.

Gate atual: **R-500 — Search Baseline / Definition of Ready**.

Antes de runtime:
- medir `WP_Query s`;
- comparar cobertura com Content Extractor;
- recontar corpus;
- selecionar consultas reais;
- montar Golden Dataset v1;
- decidir superfície inicial;
- decidir WordPress-first storage;
- fechar contratos G-520.

Runtime lexical permanece bloqueado até **R-500 + R-510 + G-520**.

## Dívidas que não entram na SPEC-005

- AUTH-UX-001;
- retirada Elementor;
- telemetria detalhada;
- durable queue;
- item/deep-link sem evidência;
- semantic/vector;
- Foundry/IA.

## Invariantes

- sem big-bang;
- WordPress-first;
- baseline antes de mudança;
- lexical antes de semantic;
- Golden Query blocking fail = NO-GO;
- projection nunca é autoridade;
- nenhuma capacidade postergada implementada silenciosamente;
- mesmo artefato testado deve ser o candidato de release;
- GO de homologação != GO de produção.

# Premium Rebaseline — Revisão de todas as SPECs

**Data:** 2026-09-21  
**Baseline:** spec005-search-lexical-golden-queries @ 76d2617c43a5347002ef846c32b8ea00ad556953

## Regras

1. SPECs executadas permanecem como história comprovada.
2. Evidência nova pode gerar addendum, nunca apagar o passado.
3. Gap residual é explícito.
4. Futuras SPECs são reestruturadas.
5. Public Experience não é subproduto da engine Search.
6. Paridade é incremental.
7. PLANNED = GAP para cutover.
8. 1.0.0 somente após cutover.

## Referências revalidadas

- KB2Ops 0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94.
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1.
- GRE 0.8.0 @ c58a403a29f275789fde7e65452358f307adc927.
- ambiente homologação/produção como quarta referência funcional.

## SPEC-000

CLOSED histórico + REABERTURA DOCUMENTAL v2.
Atualizar GRE 0.8, referências ambientais, Master Ledger e padrão Premium. Zero runtime.

## SPEC-001

CLOSED / PRESERVADA. Summary narrativo WordPress-first é sólido. Residual de paridade GRE fecha em SPEC-006/014; não reabrir writer original.

## SPEC-002

CLOSED / PRESERVADA. Taxonomias canônicas continuam. Residual: affected_service, systems_involved e disposition de service/technologies/keywords/versions -> SPEC-006.

## SPEC-003

CLOSED / PRESERVADA. Review/Governança é superior em auditabilidade. AI READY/include_ai não será copiado por inércia; será redesenhado como policy na SPEC-012.

## SPEC-004

CLOSED / PRESERVADA. Content pipeline é fundação estratégica. KD continua projeção; Migration Fidelity Source continua lossless/efêmera; adapters legados permanecem enquanto o corpus exigir.

## SPEC-005

ACTIVE / SCOPE REFINED.

Permanece:
- Query Normalizer;
- Search Document/Projection;
- post lexical;
- Golden;
- performance/security;
- lifecycle;
- item/section retrieval;
- item identity;
- anchors/deep links;
- fallback/explicabilidade.

Move para SPEC-007:
- Home;
- Article Reader;
- Header/Auth;
- Summary Rail/Tips;
- Astra/Code Snippets independence.

Move para SPEC-008:
- events/interactions/outcomes;
- vocabulary/aliases/bindings;
- relevance rules;
- diagnostics/suggestions;
- simulation/apply;
- Search Intelligence;
- Word Cloud convergida.

G-585 passa a provar independência técnica da engine, não aposentadoria ASI.

## Futuro

- SPEC-006 — Premium Product Foundation, Domain Consolidation & Distribution.
- SPEC-007 — Public Knowledge Experience.
- SPEC-008 — Search Intelligence, Telemetry, Privacy & Governed Relevance.
- SPEC-009 — Operations, Indexing & Reliability.
- SPEC-010 — Semantic Search & Vectors.
- SPEC-011 — AI Platform, WordPress AI & Foundry.
- SPEC-012 — AI-Assisted Knowledge Governance.
- SPEC-013 — Evidence Resolution & RAG.
- SPEC-014 — Parity, Cutover & Legacy Retirement.

## Ordem

Fechar fronteira técnica SPEC-005 -> 006 -> 007 -> 008 -> 009 -> decidir 010 por evidência -> 011 -> 012 -> 013 -> 014.

Discovery pode ser paralelo. Runtime continua vertical e gateado.

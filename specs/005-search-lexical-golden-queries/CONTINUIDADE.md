# Continuidade — SPEC-005 Search Lexical e Golden Queries

## Prompt pronto para novo chat

```text
Você é o Orquestrador Principal do projeto Base de Conhecimento com Inteligência Integrada.
Idioma: português do Brasil.
Mantra: Quem não sabe onde está, não sabe para onde quer ir.

ANTES DE ALTERAR
1. Leia AGENTS.md, .specify/PROJECT_MANIFEST.md e .specify/memory/constitution.md.
2. Leia SPEC-005, ADR-005-001, ADR-005-002, ADR-005-003 e contratos G-520.
3. Leia docs/DEFINITION-OF-DONE.md, current-state.md e este CONTINUIDADE.md.
4. Confirme branch/HEAD e PR #7.
5. Repositório/Constituição prevalecem sobre memória de chat.

REPO
- R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- branch: spec005-search-lexical-golden-queries
- base: main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634
- PR #7: DRAFT / NÃO MERGEAR

ESTADO
- SPEC-004 CLOSED/main
- R-500 PASS/CLOSED
- R-510 PASS/CLOSED
- G-520 PASS/CLOSED
- G-530 OPEN / próximo gate
- G-585 obrigatório antes do RC

R-510 FREEZE
Golden:
- version golden-relevance-v1.0.0
- hash e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4
- 5 blocking + 1 quarantine

Challenge:
- version technical-challenge-v1.0.0
- hash 2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807
- 7 cases

G-520
- closeout: g520-closeout-20260919.md
- evidence: evidence/g520-contract-validation-20260919.json
- 25/25 PASS
- zero runtime changes during gate

CONTRACT VERSIONS
- search-normalizer-v1.0.0
- search-document-v1.0.0
- lexical-ranker-v1.0.0
- search-result-v1.0.0
- golden-runner-v1.0.0

STORAGE DECISION — ADR-005-003
- one table: {$wpdb->prefix}bdc_kb_search_documents
- one option: bdc_kb_search_projection_state
- SQL LIKE bounded
- ranking PHP
- WP_Query native fallback
- no FULLTEXT v1
- no Golden table
- no query logging
- no ASI storage/runtime dependency

G-530 OBJETIVO
Implementar engine lexical local mínima conforme contratos, sem UI nova:
1. Query Normalizer;
2. Search Document Builder;
3. Projection schema/repository mínimo;
4. candidate retrieval strict+relaxed;
5. WordPress authorization revalidation;
6. Lexical Ranker;
7. Search Result/Service;
8. WP_Query fallback;
9. testes unitários/integration locais;
10. zero editorial write.

LIMITES
- não implementar Golden runner ainda: G-550;
- não implementar UI material: G-560;
- não criar FULLTEXT;
- não criar queue/telemetry;
- não criar semantic/vector/AI;
- não usar ASI;
- schema/rebuild deve seguir ADR-005-003 e rollback contract.

CRITÉRIO G-530
- normalizer determinístico;
- projection/document deterministic;
- ranker versioned;
- result contract;
- fallback honesto;
- unit/integration PASS;
- zero write editorial;
- nenhum gate posterior marcado por inferência.
```

## Estado humano

G-520 está **PASS/CLOSED**. Não há ZIP ou instalação necessária para esse gate.

Próximo trabalho: **G-530 — implementação local da engine lexical**.

# Continuidade — SPEC-005 Search Lexical e Golden Queries

## Prompt pronto para novo chat

```text
Você é o Orquestrador Principal do projeto Base de Conhecimento com Inteligência Integrada.
Idioma: português do Brasil.
Mantra: Quem não sabe onde está, não sabe para onde quer ir.

ANTES DE ALTERAR
1. Leia AGENTS.md, .specify/PROJECT_MANIFEST.md e .specify/memory/constitution.md.
2. Leia a SPEC-005, ADR-005-001, ADR-005-002 e contratos vigentes.
3. Leia docs/DEFINITION-OF-DONE.md, current-state.md e este CONTINUIDADE.md.
4. Confirme branch/HEAD no GitHub.
5. Repositório/Constituição prevalecem sobre memória de chat.

REPOSITÓRIO
- Repo: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada.
- Branch: spec005-search-lexical-golden-queries.
- Base: main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634.
- PR #7 permanece DRAFT / NÃO MERGEAR.

ESTADO
- SPEC-004 CLOSED/main.
- R-500 PASS/CLOSED.
- R-510 PASS/CLOSED.
- G-520 OPEN / próximo gate.
- Engine lexical bloqueada até G-520 PASS.
- G-585 continua obrigatório antes do RC.

R-510 FINAL
- evidence: evidence/r510-t5142-environmental-20260918T235252Z.json.
- source SHA-256: b461ff671177128958f8729208d1dcdb6a9635f93307879869b0908b8f252d89.
- T513: 5 AUTO_PASS / 1 AMBIGUOUS_QUARANTINED / 0 AUTO_FAIL.
- human review required=false.
- Estrutura/36620 preservado em quarantine; concorrente 516 não substituiu expected.
- T514 Challenge Discovery: 7 casos, complete=true.
- diversity PASS.
- synthetic robustness 16/16 PASS.
- real-world enrichment=PENDING_TELEMETRY.
- r510_ready=true.

T515 FREEZE
Golden Relevance:
- version golden-relevance-v1.0.0
- set_hash e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4
- 6 total / 5 active / 1 quarantined
- fixture fixtures/golden-relevance-v1.0.0.json

Technical Challenge:
- version technical-challenge-v1.0.0
- set_hash 2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807
- 7 cases
- fixture fixtures/technical-challenge-v1.0.0.json

Hash contract:
- r510-suite-hash-contract-v1.md
Closeout:
- r510-closeout-20260918.md

INVARIANTES
- WordPress é autoridade editorial e de acesso.
- Content Extractor/KD é origem semântica dos derivados.
- Golden real/histórica é separada de Technical Challenge.
- Challenge corpus-derived/synthetic não é consulta real de usuário.
- typo/alias reais aguardam Telemetria; não fabricar dados.
- ASI é referência, nunca dependência.
- nenhuma tabela/índice/ranking/embedding/vector store do ASI pode ser reutilizado.
- nenhuma engine/schema/FULLTEXT antes de G-520.
- semantic/vector/IA continuam fora de escopo desta etapa.

PRÓXIMO PASSO EXATO — G-520
1. T520 congelar Query Normalization Contract.
2. T521 congelar Search Document Contract baseado no Content Extractor.
3. T522 congelar Ranking Contract lexical explicável.
4. T523 congelar Search Result Contract.
5. T524 congelar Golden Runner Contract usando suite_version/set_hash.
6. T525 decidir storage WordPress-first com base no R-500/R-510.
7. T526 fechar Security Matrix.
8. T527 fechar Rollback/Rebuild Contract.
9. T528 somente então marcar G-520 PASS e liberar G-530.

Não criar runtime Search por inferência durante G-520.
```

## Estado humano

R-510 está **PASS/CLOSED**. Não há nova instalação necessária para fechar esse gate.

Próximo trabalho: **G-520 — contratos e decisão de storage**.

# Estado atual — SPEC-005

**ATIVA — R-500 PASS/CLOSED; R-510 PASS/CLOSED; G-520 OPEN.**

**Branch:** `spec005-search-lexical-golden-queries`  
**Base:** `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`

## Gate atual

**G-520 — Search Contract v1.**

A engine lexical continua **bloqueada** até G-520 PASS. R-500 e R-510 já estão satisfeitos.

## R-500 — PASS/CLOSED

- corpus total: 623;
- publish: 606;
- superfície inicial: ADMIN-FIRST / Knowledge List;
- `modified DESC` rejeitado como ranking;
- Content Extractor/KD é origem semântica dos derivados;
- Search Document post-level é requisito conceitual;
- schema/FULLTEXT continuam condicionados à decisão G-520.

## R-510 — PASS/CLOSED

Closeout: `r510-closeout-20260918.md`.

Evidência ambiental final:
`evidence/r510-t5142-environmental-20260918T235252Z.json`

SHA-256 do upload:
`b461ff671177128958f8729208d1dcdb6a9635f93307879869b0908b8f252d89`

Resultado T513:
- AUTO_PASS: 5;
- AMBIGUOUS_QUARANTINED: 1;
- AUTO_FAIL: 0;
- human review required: false.

Caso quarentenado:
- `GQ-LEGACY-005 / Estrutura`;
- expected 36620 preservado;
- concorrente 516 rank 1;
- expected rank 2;
- nenhum expected foi trocado;
- fora do blocking set.

Resultado T514:
- Technical Challenge Discovery: 7 casos;
- natural_language: 1;
- summary_dependent: 3;
- elementor_semantic_gap: 3;
- diversity: PASS;
- synthetic robustness: 16/16 PASS;
- real-world enrichment: `PENDING_TELEMETRY`;
- `r510_ready=true`.

## T515 — datasets congelados

Golden Relevance:
- fixture: `fixtures/golden-relevance-v1.0.0.json`;
- version: `golden-relevance-v1.0.0`;
- set_hash: `e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4`;
- 6 total / 5 active blocking / 1 quarantined.

Technical Challenge:
- fixture: `fixtures/technical-challenge-v1.0.0.json`;
- version: `technical-challenge-v1.0.0`;
- set_hash: `2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807`;
- 7 cases;
- never Golden blocking;
- never claimed as real user queries.

Hash contract:
`r510-suite-hash-contract-v1.md`.

## Independência ASI

ASI permanece referência histórica, não dependência.

- nenhuma leitura deliberada do storage ASI após T510;
- Golden/Challenge são fixtures próprias;
- nenhum futuro schema/ranking/vector/embedding pode depender do ASI;
- G-585 continua obrigatório antes do RC com ASI ausente/desativado.

## Próximo passo

Executar G-520, na ordem:
1. T520 Query Normalization Contract;
2. T521 Search Document Contract;
3. T522 Ranking Contract;
4. T523 Search Result Contract;
5. T524 Golden Runner Contract;
6. T525 WordPress-first storage decision;
7. T526 Security Matrix;
8. T527 Rollback/Rebuild Contract;
9. T528 G-520 PASS.

Nenhuma tabela, FULLTEXT, Search Projection ou engine é criada antes da decisão T525/T528.

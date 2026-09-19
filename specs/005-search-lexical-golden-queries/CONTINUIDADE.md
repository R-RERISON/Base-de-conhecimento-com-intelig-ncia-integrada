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
- G-530 PASS/CLOSED
- G-540 PASS/CLOSED
- G-550 PASS/CLOSED
- G-560 OPEN / próximo gate
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

G-530 FINAL
- closeout: g530-closeout-20260919.md
- evidence: evidence/g530-local-validation-20260919.json
- build local: 0.5.0-g530.1
- 17/17 tests PASS; 7/7 lint; zero editorial write/ASI/FULLTEXT/network

G-540 OBJETIVO
Executar full-corpus/projection rebuild automatizado em homologação:
1. ensure schema explícito;
2. full-corpus pass 1;
3. stale cleanup somente após pass 1 completa;
4. full-corpus pass 2;
5. hashes determinísticos;
6. pass 2 = NO_CHANGE;
7. coverage/source kinds/states;
8. fingerprint editorial before/after;
9. JSON machine-readable.

LIMITES
- não implementar Golden runner ainda: G-550;
- não implementar UI material: G-560;
- não criar FULLTEXT;
- não criar queue/telemetry;
- não criar semantic/vector/AI;
- não usar ASI;
- schema/rebuild deve seguir ADR-005-003 e rollback contract.

CRITÉRIO G-540
- corpus completo processado;
- duas passagens sem fatal/throwable;
- mesmos source/document hashes;
- segunda passagem sem rewrite;
- row count compatível com corpus;
- fingerprint editorial inalterado;
- somente então G-540 PASS.
```

## Estado humano

G-520 está **PASS/CLOSED**. Não há ZIP ou instalação necessária para esse gate.

Próximo trabalho: **G-530 — implementação local da engine lexical**.

## Continuidade G-540 — 2026-09-19

Candidato de homologação:
- versão: `0.5.0-g540.1`;
- evidência local: `evidence/g540-local-package-validation-20260919.json`;
- source/package parity 55/55;
- PHP lint 50/50;
- active requires 44/44;
- deterministic build 2/2;
- SHA-256 `b08a7abdef9c6287571ec137c2623618077a8ea6426d964bfc9e858659270a7f`.

Correção obrigatória realizada antes do build:
- `class-migration-fidelity-source.php`;
- missing closing brace detectado pelo lint full-package;
- commit `3b1f1631c8b004ba0b6923310359389d1721c01b`;
- blob corrigido `d884b22fc2f8c755c45667e703351b03a86494f4`.

Estado:
- G-530 CLOSED;
- G-540 PASS/CLOSED;
- G-550 OPEN.

G-540 AMBIENTAL
- evidence: evidence/g540-environmental-20260919T115727Z.json
- upload SHA-256: c2f1195c35917bc1863f262a7c0a930d622e93733aaccbfba3325becf1b789de
- corpus 623/623;
- pass1 623 WRITTEN;
- pass2 623 NO_CHANGE / 0 WRITTEN;
- determinism mismatch=0;
- DB snapshot mismatch=0;
- Projection ready;
- editorial fingerprint equal;
- errors/throwables=0;
- G-540 CLOSED.

PRÓXIMO PASSO — G-550
1. empacotar Golden Relevance v1.0.0 e Technical Challenge v1.0.0 como recursos próprios;
2. validar set_hash/version em runtime;
3. executar somente com Projection ready;
4. Golden blocking failure=0;
5. Technical Challenge failure=0;
6. quarantine permanece warning;
7. JSON machine-readable;
8. zero ASI/query logging.


## Continuidade G-550 — 2026-09-19

Build:
- `0.5.0-g550.1`;
- evidence local: `evidence/g550-local-package-validation-20260919.json`;
- SHA-256: `5c0fb99476aab84149341c1f069f64bfb9d8bdb57daaeca2636fe518164739b5`;
- 59 arquivos / 52 PHP;
- 52/52 lint;
- 45/45 active requires;
- deterministic build 2/2;
- Golden hash current;
- Challenge hash current;
- stale runtime guard ativo;
- G-540 runner OFF;
- G-550 runner ON.

Estado:
- G-540 CLOSED;
- G-550 package/local PASS;
- G-550 environmental NOT_RUN;
- G-550 ainda OPEN.

Próxima ação humana:
1. instalar o ZIP `0.5.0-g550.1` sobre a versão atual;
2. acessar **Base de Conhecimento → Golden Gate G-550**;
3. executar **Executar G-550 e baixar JSON**;
4. anexar o JSON para fechamento T550–T555.


## G-550 ambiental — PASS/CLOSED

- evidence: `evidence/g550-environmental-20260919T121422Z.json`;
- upload SHA-256: `e6f83104d330129cd0bd1835f4c1fd21f8d7117b461dbb646527f408d56cd07b`;
- Projection ready;
- Golden hash current;
- Challenge hash current;
- 13/13 result expectations PASS;
- blocking_failed=0;
- warning_failed=0;
- technical_failed=0;
- technical_error_count=0;
- status PASS;
- next gate G-560;
- zero ASI/network/query log/identity export.

G-560 agora é o gate ativo. Antes de implementar:
1. ler Visual Contract v2;
2. ler Design System/UI as Code canônicos;
3. preservar WordPress como shell sem voltar a wp-admin genérico;
4. Search deve distinguir success/zero_results/degraded/technical_error;
5. acessibilidade e responsividade são critérios executáveis;
6. nenhuma evolução de ranking durante G-560.

# Estado atual — SPEC-005

**ATIVA — R-500 PASS/CLOSED; R-510 PASS/CLOSED; G-520 PASS/CLOSED; G-530 PASS/CLOSED; G-540 PASS/CLOSED; G-550 OPEN.**

**Branch:** `spec005-search-lexical-golden-queries`  
**Base:** `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`

## Gate atual

**G-550 — Golden Gate.**

G-520 fechou os contratos e autorizou implementação local da engine. Isso **não** autoriza produção nem merge.

## Baselines fechadas

R-500:
- corpus 623 / publish 606;
- superfície ADMIN-FIRST;
- WP_Query nativo preservado como fallback;
- gap semântico 91/610;
- Summary gap 14/18.

R-510:
- Golden Relevance `golden-relevance-v1.0.0`;
- set_hash `e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4`;
- 5 blocking ativos + 1 quarantine;
- Technical Challenge `technical-challenge-v1.0.0`;
- set_hash `2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807`;
- 7 casos;
- synthetic 16/16 PASS;
- real-world enrichment `PENDING_TELEMETRY`.

## G-520 — PASS/CLOSED

Closeout:
`g520-closeout-20260919.md`

Evidence:
`evidence/g520-contract-validation-20260919.json`

Validação:
- 25/25 checks PASS;
- zero runtime file alterado durante G-520;
- SHA-256 da evidência R-510 confirmado: `b461ff671177128958f8729208d1dcdb6a9635f93307879869b0908b8f252d89`.

Versões congeladas:
- normalizer `search-normalizer-v1.0.0`;
- Search Document `search-document-v1.0.0`;
- ranker `lexical-ranker-v1.0.0`;
- result `search-result-v1.0.0`;
- Golden Runner `golden-runner-v1.0.0`.

T525 / ADR-005-003:
- uma tabela BDC: `{$wpdb->prefix}bdc_kb_search_documents`;
- uma Option: `bdc_kb_search_projection_state`, autoload=false;
- retrieval v1: SQL LIKE bounded + ranking PHP;
- FULLTEXT: NÃO AUTORIZADO no v1;
- Golden table: NÃO AUTORIZADA;
- ASI: zero runtime/storage dependency.

## Próximo passo — G-530

Implementar o menor vertical slice local:
1. T530 Query Normalizer;
2. T531 Search Document Builder + Projection Repository mínimo;
3. T532 Lexical Ranker;
4. T533 Search Result/Service explicável;
5. T534 WP_Query fallback/degraded;
6. T535 testes locais determinísticos;
7. T536 prova de zero write editorial;
8. T537 G-530 PASS.

Ainda não criar UI nova, Golden runtime, FULLTEXT, queue, telemetria, vetor ou IA.

G-585 continua obrigatório antes do RC com ASI ausente/desativado.


## G-530 — PASS/CLOSED

Closeout: `g530-closeout-20260919.md`.  
Evidence: `evidence/g530-local-validation-20260919.json`.

Build local: `0.5.0-g530.1`.

Implementado:
- Query Normalizer;
- Search Document Builder;
- Projection Repository;
- Lexical Ranker;
- Search Service;
- WP_Query degraded fallback.

Validação:
- 17/17 unit/integration PASS;
- 7/7 PHP lint;
- local/GitHub blob parity 7/7;
- zero write editorial;
- zero network;
- zero ASI runtime identifier;
- zero FULLTEXT;
- zero schema/rebuild implícito no bootstrap.

Regressões capturadas antes do closeout:
- substring LIKE sem token lexical real;
- rewrite de Projection sem mudança.

## Próximo passo — G-540

Executar ambiente real de homologação de forma automatizada:
1. criação explícita do schema derivado;
2. full-corpus build;
3. segunda passagem;
4. prova de hash determinístico;
5. prova de NO_CHANGE/idempotência;
6. coverage e estados;
7. fingerprint editorial antes/depois;
8. zero fatal/throwable.

Nenhuma validação manual artigo-a-artigo será exigida.

## G-540 — candidato de homologação 0.5.0-g540.1

Pacote local validado em 2026-09-19.

Evidência: `evidence/g540-local-package-validation-20260919.json`.

Validação do artefato:
- source/package parity: 55/55;
- PHP lint: 50/50 PASS;
- active requires: 44/44;
- zero write editorial no runtime Search;
- writes autorizados apenas em Search Projection derivada + option de estado;
- ZIP íntegro;
- rebuild determinístico 2/2;
- SHA-256: `b08a7abdef9c6287571ec137c2623618077a8ea6426d964bfc9e858659270a7f`.

Durante o fechamento do pacote, o lint integral detectou um erro sintático preexistente em `class-migration-fidelity-source.php` (fechamento ausente no scan de warnings). O source do branch foi corrigido antes da geração do ZIP no commit `3b1f1631c8b004ba0b6923310359389d1721c01b`.

Estado do gate:
- pacote/local: PASS;
- execução ambiental G-540: NOT_RUN;
- G-540 global: OPEN.

Próximo passo: instalar `0.5.0-g540.1` sobre o plugin atual em homologação, abrir **Base de Conhecimento → Search Corpus G-540**, executar o runner e anexar o JSON baixado. Não remover/desinstalar o plugin antes da atualização.


## G-540 — PASS/CLOSED

Evidência ambiental:
`evidence/g540-environmental-20260919T115727Z.json`

SHA-256 do upload:
`c2f1195c35917bc1863f262a7c0a930d622e93733aaccbfba3325becf1b789de`

Ambiente:
- WordPress 6.9.4;
- PHP 8.5.10;
- plugin 0.5.0-g540.1;
- MariaDB 12.2.2.

Resultado:
- corpus 623 -> 623;
- Projection schema exists=true;
- row_count=623;
- DB snapshot mismatches=0;
- state_after=ready;
- pass1: 623 written;
- pass2: 0 written / 623 NO_CHANGE;
- determinism: 623 compared / 0 mismatch;
- 623/623 document_state=ready;
- errors=[];
- throwables=[];
- editorial fingerprint before/after idêntico;
- changed_posts=0;
- T540/T541/T542/T543/T544/T545=true;
- next_gate=G-550.

Coverage:
- title 623;
- summary 18;
- headings 136;
- taxonomy 0;
- body 610;
- extractor_warning_total 426, sem extractor_error_code;
- source kinds permanecem coerentes com o corpus conhecido.

Performance observada do rebuild, não SLA:
- pass1 p50 4.0901 ms / p95 10.8771 ms;
- pass2 p50 1.8780 ms / p95 7.7550 ms;
- total 7958.915 ms.

## Próximo passo — G-550

Implementar Golden Runner runtime próprio:
- fixtures Golden/Challenge empacotadas no plugin;
- verificação de suite_version/set_hash em runtime;
- Projection obrigatoriamente ready;
- execução explícita POST + nonce + manage_options;
- blocking_failed=0 e technical_failed=0 para PASS;
- quarantine apenas warning;
- nenhum query log;
- nenhuma dependência ASI.

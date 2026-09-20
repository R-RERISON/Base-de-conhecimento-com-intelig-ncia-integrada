# Estado atual — SPEC-005

**ATIVA — R-500 PASS/CLOSED; R-510 PASS/CLOSED; G-520 PASS/CLOSED; G-530 PASS/CLOSED; G-540 PASS/CLOSED; G-550 PASS/CLOSED; G-560 PASS/CLOSED; G-570 PASS/CLOSED; G-580 OPEN.**

**Branch:** `spec005-search-lexical-golden-queries`  
**Base:** `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`

## Gate atual

**G-580 — Lifecycle.**

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


## G-550 — candidato de homologação 0.5.0-g550.1

Evidência local:
`evidence/g550-local-package-validation-20260919.json`

Validação:
- runtime resources próprios: Golden + Technical Challenge;
- set_hash recalculado em runtime;
- stale guard de normalizer/document/ranker/result;
- Projection obrigatoriamente ready;
- wordpress_fallback não pode produzir PASS;
- 6/6 local harness PASS;
- 52/52 PHP lint PASS;
- 45/45 active requires;
- source/package parity preservada;
- ZIP íntegro;
- rebuild determinístico 2/2;
- SHA-256 `5c0fb99476aab84149341c1f069f64bfb9d8bdb57daaeca2636fe518164739b5`.

Estado:
- G-550 package/local: PASS;
- G-550 ambiental: NOT_RUN;
- G-550 global: OPEN.

Próxima ação: instalar `0.5.0-g550.1`, acessar **Base de Conhecimento → Golden Gate G-550**, executar o runner e anexar o JSON.


## G-550 — PASS/CLOSED

Evidência ambiental:
`evidence/g550-environmental-20260919T121422Z.json`

SHA-256 do upload:
`e6f83104d330129cd0bd1835f4c1fd21f8d7117b461dbb646527f408d56cd07b`

Resultado:
- Projection ready=true;
- Golden set_hash declarado = calculado;
- Challenge set_hash declarado = calculado;
- 13 resultados executados;
- blocking_failed=0;
- warning_failed=0;
- technical_failed=0;
- technical_error_count=0;
- status=PASS;
- T550/T551/T552/T553/T554/T555=true;
- next_gate=G-560.

Golden:
- pendrive -> post 527 rank 1;
- MSTeams -> 579 rank 1;
- Windows 11 -> 583 rank 1;
- Termo de assinatura -> 45855 rank 1;
- Estrutura quarantine -> 36620 rank 2, PASS warning;
- SCCM -> 412 rank 2.

Technical Challenge:
- 7/7 PASS;
- Elementor semantic gaps 3/3;
- natural language 1/1;
- Summary-dependent 3/3.

Privacidade/independência:
- query log=false;
- identity/IP/session export=false;
- network=false;
- depends_on_asi=false.

## Próximo passo — G-560

Ler Visual Contract v2 e UX baselines canônicas antes de alterar UI. O gate cobre:
- interface Search conforme identidade BDC;
- desktop/782/520;
- teclado/focus/ARIA;
- zero-result distinto de erro técnico;
- human relevance acceptance assistida por evidência, sem regressão para teste artigo-a-artigo.


## G-560 — candidato técnico de homologação 0.5.0-g560.1

Evidência local:
`evidence/g560-local-package-validation-20260919.json`

Integração:
- Knowledge List mantém a arquitetura visual UX-002.3;
- consulta vazia continua usando WP_Query com modified DESC;
- consulta não vazia usa Search_Service canônico;
- resultados Search são apresentados em rank lexical, com metadado discreto `Relevância #N`;
- nenhum novo shell/tela de resolvedor foi criado;
- paginação permanece na listagem normal; Search v1 é bounded pelo contrato.

Estados:
- success;
- zero_results;
- degraded / WordPress compatibility fallback;
- invalid_query;
- technical_error.

Acessibilidade/Visual Contract:
- role=search;
- label visível;
- helper associado via aria-describedby;
- feedback aria-live;
- foco perceptível herdado da fundação;
- breakpoints 782px/520px;
- estado não depende apenas de cor;
- nenhuma biblioteca visual externa.

Segurança:
- fingerprint editorial cobre post fields, Summary, `_elementor_data` e taxonomias canônicas;
- zero write editorial;
- zero network;
- zero ASI runtime;
- zero FULLTEXT;
- zero query logging.

Validação local:
- 60 arquivos;
- 53 PHP;
- 53/53 lint PASS;
- 44/44 active requires;
- 26/26 checks de contrato PASS;
- 4/4 blobs alterados = GitHub;
- deterministic build 2/2;
- SHA-256 `9f4f14c775aaad4e7d2360ed11dd3b036587556b1497e210eccaf787c89b8297`.

Estado do gate:
- implementação/package local: PASS;
- G-560 ambiental: NOT_RUN;
- T564 human visual acceptance: NOT_RUN;
- G-560 global: OPEN.

O Visual Contract v2 exige revisão humana para mudança material de UI. O runner ambiental automatiza o restante; não haverá teste manual artigo-a-artigo.


## G-560 — automated/environmental PASS

Evidência:
`evidence/g560-environmental-review-20260919T134816Z.json`

SHA-256 do upload original:
`9906bbecbd1d2c056a2a1e87d2b50d8d4885adc904621a8b1120ff49c40b1527`

Resultado:
- 21/21 checks PASS;
- Projection ready;
- Search_Service integration PASS;
- Visual Contract source markers PASS;
- responsive markers 782/520 PASS;
- accessibility markers PASS;
- Windows 11 -> success/projection_like/rank 1;
- Summary semantic case -> success/projection_like/rank 1;
- zero-results -> zero_results/projection_like;
- invalid query -> invalid_query/none;
- editorial fingerprint before/after idêntico;
- query log=false;
- external network=false;
- ASI dependency=false;
- errors=[];
- g560_technical_ready=true.

Gate residual:
- T564 human visual acceptance = NOT_RUN;
- T565 G-560 PASS = false;
- next_gate = G-560-HUMAN.

Nenhum outro subgate técnico do G-560 permanece aberto.


## G-560 — PASS/CLOSED

Evidência humana:
`evidence/g560-human-visual-acceptance-20260919.md`

Resultado visual:
- desktop Search: PASS humano;
- superfície observada: Knowledge List / query `windows 11`;
- Search toolbar, ações, feedback e hierarquia: PASS;
- nenhum clipping/overlap/overflow visível;
- identidade BDC/UX-002.3 preservada.

Divergência registrada:
- revisão visual humana 782/520: DEFERRED/NON-BLOCKING;
- decisão explícita do Product Owner: mobile não é requisito bloqueante neste momento;
- checks técnicos 782/520 continuam PASS;
- Visual Contract v2 global permanece inalterado;
- dívida visual mobile deve ser revisitada quando suporte mobile for requisito de release.

Gate:
- T560 PASS;
- T561 PASS técnico com mobile visual deferred;
- T562 PASS;
- T563 PASS;
- T564 PASS humano desktop;
- T565 PASS;
- G-560 CLOSED.

Próximo gate: G-570 — Segurança/Performance.


## G-570 — candidato de homologação 0.5.0-g570.1

Contrato:
`g570-security-performance-contract-v1.md`

Evidência local:
`evidence/g570-local-package-validation-20260919.json`

Escopo:
- nenhum peso/ranking/retrieval core foi alterado desde G-560;
- runner é read-only para posts e Search Projection;
- negative capability checks via filtros temporários WordPress;
- SQL/bounds/abuse automatizados;
- benchmark real será executado somente em homologação.

Bounds congelados:
- 256 caracteres;
- 1024 bytes;
- 16 tokens;
- 128 caracteres/token;
- candidate cap 200;
- result default 20 / hard cap 50.

Benchmark do gate:
- 6 consultas curadas;
- 1 warm-up por consulta;
- 5 repetições medidas;
- 30 amostras;
- p95 <= 750 ms;
- max <= 1500 ms;
- zero fallback/technical_error/throwable;
- budget é guardrail de homologação, não SLA de produção.

Validação local:
- 61 arquivos;
- 54 PHP;
- 54/54 lint PASS;
- ZIP extraído 54/54 lint PASS;
- 44/44 active requires;
- 37/37 contract checks PASS;
- zero write editorial;
- zero write Projection;
- zero network;
- zero ASI;
- zero FULLTEXT;
- build determinístico 2/2;
- SHA-256 `4cd915fc3be58166a354434bf9688f0a1509ca36d570697540dff9eace82ab62`.

Estado:
- G-570 package/local: PASS;
- G-570 ambiental: NOT_RUN;
- T570–T574: OPEN;
- G-570 global: OPEN.

Próxima ação: instalar `0.5.0-g570.1`, executar **Base de Conhecimento → Security & Performance G-570** e anexar o JSON.


## G-570 — PASS/CLOSED

Evidência ambiental:
`evidence/g570-environmental-review-20260920T164828Z.json`

SHA-256 do upload:
`38d399cab1f466c1b919513b8cf3650dd3ae7b6a55d94b715efe5597c3b89ffd`

Resultado:
- 45/45 checks PASS;
- Projection ready=true / corpus 623;
- edit_posts denial fail-closed;
- edit_post(583) denial ocultou o documento;
- live results todos revalidados por capability;
- candidate cap 200 comprovado;
- result hard cap 50 comprovado;
- min limit clamp 1 comprovado;
- SQL-like payload bounded;
- abuse cases 7/7 PASS;
- benchmark 30 amostras;
- mean 165.3279 ms;
- p50 178.4739 ms;
- p95 207.7448 ms;
- max 212.9128 ms;
- budget p95 750 ms / max 1500 ms;
- fallback_count=0;
- technical_error_count=0;
- fingerprint editorial idêntico;
- network=false;
- query logging=false;
- ASI=false;
- FULLTEXT=false;
- runner writes Projection=false;
- runner writes editorial=false;
- errors=[];
- throwables=[];
- T570/T571/T572/T573/T574=true;
- next_gate=G-580.

Próximo gate:
- G-580 Lifecycle — activation/update, rebuild/fallback, disable module e uninstall retention.

## G-580 — candidato de homologação 0.5.0-g580.1

Contrato:
`g580-lifecycle-rebuild-contract-v1.md`

Evidência local:
`evidence/g580-local-package-validation-20260920.json`

Implementação:
- activation/update preparam somente schema e estado; não executam rebuild implícito;
- rebuild do corpus é operação explícita e separada do bootstrap;
- Projection não-ready usa `wordpress_fallback` em estado degraded;
- kill switch `BDC_KB_SEARCH_ENABLED` + filtro `bdc_kb_search_enabled`, sem nova Option persistente;
- deactivation e uninstall v1 preservam tabela/Option por default;
- erro de corpus vazio marca Projection como failed;
- Search ranker e pesos permanecem congelados.

Validação local/package:
- base G-570 retida revalidada por SHA-256 `4cd915fc3be58166a354434bf9688f0a1509ca36d570697540dff9eace82ab62`;
- 65 arquivos / 58 PHP;
- 58/58 PHP lint PASS;
- 46/46 active requires;
- suíte G-580 33/33 PASS;
- delta exato: 4 arquivos adicionados + 2 modificados / 0 removidos;
- ranker SHA-256 antes/depois `47787ee0fcf6845c264fcc77b8ae0d5d5657e1e3f66e0d0939fe1940fecaad78`;
- build determinístico 2/2;
- ZIP íntegro;
- SHA-256 `b2a2d55793a3835ff06151d2c1cc4e301292df51bc35c5b5bdf166546a4f166e`.

Estado:
- G-580 package/local: PASS;
- G-580 ambiental: NOT_RUN;
- T580–T584: permanecem OPEN até evidência ambiental;
- G-580 global: OPEN;
- G-585 continua obrigatório antes do RC.

Próxima ação: instalar `0.5.0-g580.1` sobre a versão atual em homologação, acessar **Base de Conhecimento → Lifecycle G-580**, executar **Executar G-580 e baixar JSON** e anexar o JSON gerado.


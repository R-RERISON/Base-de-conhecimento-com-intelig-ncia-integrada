# Estado atual — SPEC-005

**ATIVA — PREMIUM CONSOLIDATION / G-590 SECTION RETRIEVAL & DEEP-LINK.**

**Branch:** `spec005-section-retrieval-deeplink`

## Implementação G-590 — estado em 2026-09-21

Implementado no runtime candidato:
- Section Projector determinístico;
- Search Document `v1.1.0`;
- Projection schema `1.1.0` na tabela única;
- schema contract físico de colunas + índices;
- lifecycle degrade-safe em schema incompleto/version mismatch;
- Section Ranker/Service;
- fachada canônica `Search_Service::search_sections()`;
- Anchor Manager efêmero/fail-closed;
- G-550 addendum para document v1.1 sem mudar ranker/result;
- runner ambiental G-590;
- validador machine-readable de evidência;
- budget G-590: p95 <= 900 ms / max <= 1500 ms.

Validação estática sobre blobs GitHub: **18/18 PASS** antes do último hardening de schema/performance; novo gate estático foi ampliado e precisa ser executado no package de homologação.

Master Parity Ledger:
- ASI-003/004/005 = PARTIAL;
- continuam blockers;
- nenhuma paridade/cutover foi declarada.

## Próximo passo exato

Criar build de homologação G-590 com:
- versão própria;
- `BDC_KB_SPEC005_G590_SECTION_BUILD=true`;
- demais runners de engenharia desativados salvo dependências necessárias;
- lint/package parity;
- instalar sobre ambiente que possua Projection anterior;
- executar **Base de Conhecimento → Section Retrieval G-590**;
- validar o JSON com `tools/homologation/spec005/validate-g590-evidence.php`.

G-590 somente pode fechar após evidência ambiental. G-585 permanece PAUSED até então.

---

# Estado atual — SPEC-005

**ATIVA — PREMIUM CONSOLIDATION / G-590 SECTION RETRIEVAL & DEEP-LINK.**

**Branch:** `spec005-section-retrieval-deeplink`  
**Base de consolidação:** Premium Rebaseline @ `01508379f91a336b26b17268fb119458bd077f7e`.

## Decisão atual

R-500/R-510/G-520/G-530/G-540/G-550/G-560/G-570/G-580 permanecem fechados e são contratos de regressão.

A nova implementação não pode alterar `lexical-ranker-v1.0.0`.

G-590 foi aberto para fechar ASI-003/004/005:
- item/section retrieval;
- identidade estável;
- deep-link/anchor.

Storage aprovado: mesma Search Projection, sem segunda tabela.

Public Home/Reader seguem para SPEC-007. Telemetry/vocabulary/relevance seguem para SPEC-008.

G-585 permanece PAUSED enquanto G-590 estiver aberto. Após G-590, será retomado como prova técnica de independência da engine, não como autorização de aposentadoria do ASI.

## Próximo passo exato

Implementar T590-10..T590-14, preservando post-level ranking e sem mutação editorial. Em seguida produzir package/runner para coverage ambiental antes de qualquer claim de paridade.

---

# Estado atual — SPEC-005

**ATIVA — R-500/R-510/G-520/G-530/G-540/G-550/G-560/G-570/G-580 PASS/CLOSED; P-580A/P-580B/UX-004/UX-005 OPEN; G-585 PAUSED.**

**Branch:** `spec005-search-lexical-golden-queries`  
**Base:** `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`

## Gate atual

**UX-004/H-020 + UX-005/A-020 — Public Experience preview. G-585 PAUSED.**

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

## G-580 — primeira execução ambiental / FAIL CONTROLADO

Artefato:
`bdc-kb-spec005-g580-lifecycle-20260920-172010.json`

Revisão:
`evidence/g580-environmental-false-negative-review-20260920.json`

Resultados funcionais:
- T580 PASS;
- T581 PASS;
- T582 PASS;
- T583 PASS;
- Projection final `ready`;
- corpus/row_count 623/623;
- pass1 623 NO_CHANGE / 0 written;
- pass2 623 NO_CHANGE / 0 written;
- determinism mismatch=0;
- stale_rows_deleted=0;
- fingerprint editorial igual;
- errors=[] / throwables=[].

Bloqueio:
- T584=false exclusivamente porque `runtime_source.no_query_logging=false`;
- o mesmo relatório informa `persists_query_log=false`;
- root cause confirmado no runner: autocolisão do literal `bdc_kb_search_query_log` com a própria asserção de source scan;
- classificação: VALIDATOR_FALSE_NEGATIVE;
- não é defeito funcional da Search;
- G-580 permanece OPEN porque o artefato machine-readable original não marcou T584 PASS.

## G-580 — patch de validador 0.5.0-g580.2

Evidência local:
`evidence/g580-validator-patch-local-validation-20260920.json`

Escopo:
- somente bootstrap/version + runner G-580;
- lifecycle/rebuild/Search Service/ranker inalterados;
- regression check adicionada ao teste unitário.

Validação:
- 65 arquivos / 58 PHP;
- 58/58 lint;
- 46/46 active requires;
- delta vs g580.1: 2 modified / 0 added / 0 deleted;
- build determinístico 2/2;
- ZIP SHA-256 `47f3d2c641863eca5048bcb4ed469ebe243c1262ef4aaada582f60ceec84cf29`;
- ranker SHA-256 permanece `47787ee0fcf6845c264fcc77b8ae0d5d5657e1e3f66e0d0939fe1940fecaad78`.

Estado:
- package/local patch: PASS;
- environmental recheck: REQUIRED;
- G-580: OPEN.

Próxima ação: instalar `0.5.0-g580.2` sobre `g580.1`, executar novamente **Base de Conhecimento → Lifecycle G-580 → Executar G-580 e baixar JSON** e anexar o novo JSON.

## G-580 — PASS/CLOSED — 2026-09-20

Evidência canônica:
- `evidence/g580-environmental-review-20260920T173016Z.json`;
- closeout: `g580-closeout-20260920.md`;
- upload SHA-256 `f778789c8067d4ced3272fce02054a1a696d321c5b6b1bdd5cfea0f6b16b53f6`.

Resultado:
- T580/T581/T582/T583/T584 = PASS;
- activation/update sem rebuild implícito;
- rebuild 623/623;
- pass1/pass2 623 NO_CHANGE / 0 WRITTEN;
- determinism mismatch=0;
- fallback Projection-not-ready PASS;
- disable module fallback PASS;
- uninstall/deactivation retention PASS;
- fingerprint editorial equal;
- no ASI/network/query logging;
- errors=[] / throwables=[];
- next_gate=G-585.

A execução g580.1 com falso negativo ficou preservada como evidência diagnóstica. O patch g580.2 corrigiu somente o validador; Search core/ranker não mudaram.

## G-585 — candidato de homologação 0.5.0-g585.1

Contrato:
`g585-asi-independence-contract-v1.md`

Evidência local:
`evidence/g585-local-package-validation-20260920.json`

Objetivo:
- provar runtime BDC sem dependência do Advanced Search Intelligence;
- ASI deve ser manualmente desativado em homologação;
- runner não chama `deactivate_plugins()` e não remove storage legado;
- presença física de tabelas/options ASI não bloqueia; dependência runtime bloqueia.

Runner prova:
- T585 static scan dos PHP BDC carregados + símbolos/hooks runtime;
- T586 ASI ausente de active_plugins/sitewide;
- T587 Search probes + Golden Suite PASS sem ASI;
- T588 rebuild próprio PASS sem ASI;
- T589 lifecycle + kill switch + deactivation retention sem ASI;
- T589.1 evidence dependency-zero;
- T589.2 G-585 PASS.

Validação local:
- 66 arquivos / 59 PHP;
- 59/59 lint PASS;
- 48/48 active requires;
- 29/29 contract checks;
- source/package parity bootstrap + runner;
- delta vs g580.2: 1 added / 1 modified / 0 deleted;
- Search Service, rebuild, lifecycle e ranker byte-identical ao g580.2;
- ranker SHA-256 `47787ee0fcf6845c264fcc77b8ae0d5d5657e1e3f66e0d0939fe1940fecaad78`;
- deterministic build 2/2;
- ZIP SHA-256 `cae86ef93572e9320efe89d8fed891e90be032e5d46e4aaccf6dca647bf62880`.

Estado:
- G-580 CLOSED;
- G-585 package/local PASS;
- G-585 environmental NOT_RUN;
- G-585 global OPEN;
- G-590 bloqueado.

Próxima ação:
1. desativar manualmente **Advanced Search Intelligence** em homologação;
2. atualizar o BDC para `0.5.0-g585.1`;
3. acessar **Base de Conhecimento → Independência G-585**;
4. executar **Executar G-585 e baixar JSON**;
5. anexar o JSON para decisão T585–T589.2.

Não desinstalar/remover tabelas ASI neste gate.

## G-585 — primeira execução ambiental / FAIL CONTROLADO

Evidência:
- `evidence/g585-environmental-fail-dependency-20260920T174342Z.json`;
- revisão: `evidence/g585-dependency-blocker-review-20260920.json`;
- upload SHA-256 `4e7ebdef00dd90d7141d34496549e6f41033dc9f97de91b69869039417b1ca42`.

Resultado:
- static scan dos 48 arquivos BDC carregados: `matches=[]`;
- `dependency_zero=true` para runtime source BDC;
- ASI ativo: não;
- `active_plugins=[]` para legacy detector;
- loaded hooks legacy: nenhum;
- loaded symbol legacy: `class:BDC_KX_ASI_Adapter`;
- T585=false;
- T586=true;
- Search/Golden/rebuild/lifecycle não executados por fail-fast;
- status `FAIL_DEPENDENCY_FOUND`;
- G-585 permanece OPEN;
- G-590 permanece BLOCKED.

Interpretação:
- não há evidência de regressão da Search;
- não há referência ASI encontrada no runtime source BDC;
- o blocker é um símbolo já carregado no ambiente e seu arquivo de origem não era registrado pelo g585.1;
- não relaxar o gate sem atribuir a origem.

## G-585 — patch diagnóstico 0.5.0-g585.2

Objetivo único:
- atribuir origem de classes/functions legacy via Reflection;
- registrar `source_scope` e `source_path` relativos;
- não exportar caminho absoluto do servidor;
- manter exatamente o mesmo comportamento bloqueante do T585.

Validação local:
- 66 arquivos / 59 PHP;
- 59/59 lint;
- 48/48 active requires;
- 31/31 checks;
- deterministic build 2/2;
- ZIP SHA-256 `0db339e50e997bbc4211972173809ea71517fc242696b17345bf8d095146c915`;
- delta vs g585.1: 0 added / 2 modified / 0 deleted;
- Search Service/rebuild/lifecycle/ranker inalterados;
- ranker SHA-256 `47787ee0fcf6845c264fcc77b8ae0d5d5657e1e3f66e0d0939fe1940fecaad78`.

Próxima ação:
1. manter o Advanced Search Intelligence desativado;
2. atualizar para `0.5.0-g585.2`;
3. executar novamente **Base de Conhecimento → Independência G-585**;
4. anexar o JSON;
5. usar `loaded_symbols[].source_scope/source_path` para decidir a remoção/desativação do componente residual ou corrigir o ownership, sem whitelist prematura.

## Rebaseline de produto — ASI Functional Parity / UX-004

Decisão do Product Owner em 2026-09-20:

- zero dependência do ASI NÃO autoriza perda de funcionalidades;
- ASI 4.6.8 passa a ser baseline funcional completa, não somente referência de Search;
- toda capacidade relevante deve ser PARITY, IMPROVED, SUPERSEDED_WITH_EVIDENCE ou RETIRED_BY_PO;
- Word Cloud é funcionalidade obrigatória;
- Home pública é superfície canônica obrigatória do BDC;
- Header/Body/scripts isolados devem convergir para uma implementação mantida pelo plugin BDC;
- g585.2 permanece válido como diagnóstico, mas G-585 está PAUSED até rebaseline funcional.

Artefatos:
- `asi-functional-parity-rebaseline-v1.md`;
- `asi-functional-inventory-v1.md`;
- `ux/004-public-home-portal/spec.md`;
- `ux/004-public-home-portal/tasks.md`.

Estado revisado:
- G-580 CLOSED;
- P-580A Functional Inventory OPEN;
- UX-004 DISCOVERY/CONTRACT;
- G-585 PAUSED / BLOCKED BY FUNCTIONAL PARITY REBASELINE;
- G-590 BLOCKED.

Próxima ação:
1. concluir inventário funcional ASI;
2. localizar ownership dos artefatos atuais da Home;
3. fechar contrato Public Search + Word Cloud + Home;
4. somente então implementar novo pacote de Home/decommission.

## Rebaseline pública — UX-005 Article Reader / P-580B GRE

Nova evidência do ambiente:
- Home atual é conteúdo editorial + CSS/shortcodes, não superfície plugin-owned;
- Astra Custom CSS v3 preview mistura Home, ASI, Single Post, Elementor hardening e legacy normalizer;
- GRE 0.6.0 possui frontend rail automático e coverage/admin capabilities;
- Article Reader público precisa ser BDC-owned.

Novos artefatos:
- `specs/005-search-lexical-golden-queries/gre-functional-inventory-v1.md`;
- `ux/005-public-article-reader/spec.md`;
- `ux/005-public-article-reader/tasks.md`.

Owners atuais confirmados:
- Summary BDC: Objetivo, Escalonamento, Importante;
- Classification BDC: Audiência, Equipes responsáveis, Itens de catálogo;
- GRE sem owner BDC ainda: Serviço Afetado, Sistemas Envolvidos.

Structured Tips / “Dicas úteis”:
- capacidade obrigatória do Article Reader;
- lista ordenada estruturada;
- edição via Workspace;
- renderização automática no topo;
- sem HTML manual/shortcode como arquitetura final;
- storage ainda não congelado.

CSS/theme direction:
- Astra Custom CSS deixa de ser owner;
- regras úteis de Elementor legacy serão absorvidas em compatibility layer BDC escopada;
- assets públicos serão separados por surface/component;
- target gate A-050 prova Article Reader com Astra Custom CSS desativado.

Estado:
- G-580 CLOSED;
- P-580A OPEN;
- P-580B OPEN;
- UX-004 OPEN;
- UX-005 OPEN;
- G-585 PAUSED;
- G-590 BLOCKED.

## P-580A/H-001/A-001 — Public Experience Inventory package ready

Build:
- `0.5.0-p580a.1`;
- ZIP SHA-256 `39a0c8ba819d46713d1584fbd36c0efc6ab635c0929b63e21e1683d145109277`;
- 67 files / 60 PHP;
- 60/60 PHP lint;
- 46/46 active requires;
- 21/21 contract checks;
- deterministic build 2/2;
- delta vs g585.2: 1 added / 1 modified / 0 deleted.

Runner:
- Home/front-page configuration;
- theme/child theme;
- active Additional CSS bytes + SHA only;
- target shortcode registration and callback origin;
- public hook callback origin;
- `bdc_home_filter_v270` AJAX ownership;
- Home candidate posts/pages without exporting content;
- published-post source-kind distribution;
- warning families;
- “Dicas úteis” discovery by meta-key/content/Elementor presence without values;
- GRE 8-field coverage counts without values.

Safety:
- read-only;
- no shortcode execution;
- no `the_content` filter execution;
- no editorial/meta/Elementor writes;
- no network;
- no CSS/content/GRE value export.

Build flags:
- Search engine ON;
- G-585 runner OFF;
- P-580 inventory runner ON.

Evidence:
- `evidence/p580-public-experience-inventory-local-validation-20260920.json`.

Status:
- package/local PASS;
- environmental NOT_RUN;
- H-001/A-001 remain OPEN pending JSON from homologation.

Next:
1. install `0.5.0-p580a.1`;
2. open **Base de Conhecimento → Inventário Público**;
3. run **Executar inventário e baixar JSON**;
4. attach JSON;
5. use evidence to close ownership/corpus discovery before implementation.

## P-580 environmental inventory #1 — PASS / deep inventory pending

Evidence:
- `evidence/p580-public-experience-inventory-20260920T184051Z.json`;
- review: `evidence/p580-public-experience-inventory-review-20260920.md`;
- upload SHA-256 `ea4b050f9d1f8cc52954498acb5ed363a7273d34b4528719088db0f7d4b5c65a`.

Findings:
- Home canônica atual = page 41395 / publish / Elementor Header Footer;
- Astra 4.13.3 / no child theme / Additional CSS 36,804 bytes;
- Home v2.7.0 behaviors are hosted in Code Snippets runtime;
- ASI Search/Word Cloud absent when ASI off;
- Entra login belongs to BDC Entra Gateway;
- 606 published posts: 528 legacy_html, 41 plain_text, 31 Elementor, 3 mixed, 3 Gutenberg;
- article runtime also includes GAC panels/bridge, WP Unified Indexer anchors, GRE Tips and GRE Summary Rail;
- Helpful Tips = `_bdc_es_helpful_tips`, 7 published posts, not embedded in post_content/Elementor;
- GRE historical fields have real environmental coverage and cannot be silently retired;
- no errors/throwables; safety contract PASS.

Important correction:
- Article Reader compatibility priority is legacy_html first, not Elementor-first.
- Environment GRE contains Helpful Tips runtime not present in the consulted GRE main/0.6.0 repository; environmental code has diverged and must be profiled before migration.

Deep discovery build:
- `0.5.0-p580a.2`;
- 67 files / 60 PHP;
- lint 60/60;
- active requires 46/46;
- 25/25 contract checks;
- deterministic build 2/2;
- ZIP SHA-256 `319b97c4f5ee0a6f9f0679cb478d113b91413660bf19a00b83ec56eca2b6d0b4`;
- evidence local: `evidence/p580-deep-inventory-local-validation-20260920.json`.

p580a.2 adds diagnostics only:
- target plugin versions + selected file hashes;
- Code Snippets ID/name/scope/hash + behavioral signals, no source export;
- Helpful Tips type/list keys/item counts, no values.

Status:
- H-001: PARTIAL PASS;
- A-001: PARTIAL PASS;
- P-580A/P-580B OPEN;
- G-585 PAUSED;
- G-590 BLOCKED.

## UX-004/H-020 + UX-005/A-020 — preview candidate 0.5.0-ux004005.1

Contracts frozen:
- `ux/004-public-home-portal/h010-public-home-contract-v1.md`;
- `ux/005-public-article-reader/a010-article-reader-contract-v1.md`.

Implementation mode:
- ADMIN PREVIEW ONLY;
- manage_options + nonce;
- no public route takeover;
- no page_on_front change;
- no post/meta/Elementor write;
- current Home/articles remain authoritative outside preview.

Home preview:
- plugin-owned Header/shell;
- Search uses frozen Search Service only because preview is admin-only;
- public consumer authorization facade remains pending;
- curated categories;
- Últimas provider date DESC / ID DESC;
- Populares provider legacy-compatible comment_count DESC;
- Word Cloud content-only preview explicitly NON-CUTOVER; ASI quality/telemetry/vocabulary/health parity remains required.

Article preview:
- plugin-owned shell/hero;
- canonical the_content pipeline preserved;
- GAC + WP Unified Indexer preserved;
- GRE Tips/Rail suppressed only inside preview to avoid duplication;
- BDC Helpful Tips reads existing `_bdc_es_helpful_tips`;
- composed Executive Summary Rail;
- sticky desktop / reflow narrow / print flow.

Local/package validation:
- 77 files / 66 PHP;
- PHP lint 66/66;
- extracted ZIP lint 66/66;
- active requires 49/49;
- 26/26 contract checks;
- source/package parity for all changed/new source;
- delta vs p580a.2: 10 added + bootstrap modified / 0 deleted;
- deterministic build 2/2;
- ZIP SHA-256 `d61acada9230f454efc7ecb2c4580cf4c612d4a7724edaa84d7e8fd1314e8fe7`;
- Search/rebuild/lifecycle/ranker unchanged.

Evidence:
- `evidence/ux004005-public-preview-local-validation-20260920.json`.

Status:
- H-001 CLOSED;
- H-010 CLOSED/FROZEN;
- H-020 candidate local PASS / ENV+HUMAN pending;
- A-001 CLOSED;
- A-010 CLOSED/FROZEN;
- A-020 candidate local PASS / ENV+HUMAN pending;
- P-580A/P-580B remain open for broader parity;
- G-585 PAUSED;
- G-590 BLOCKED.

Next:
1. install `0.5.0-ux004005.1`;
2. open **Base de Conhecimento → Prévia Pública**;
3. inspect Home preview;
4. inspect representative Article Reader previews, especially one with Helpful Tips/Summary;
5. send screenshots for visual iteration;
6. do not disable ASI/GRE/Code Snippets/Astra for this preview.

## UX-004/UX-005 — human visual review + redesign candidate 0.5.0-ux004005.2

Human result for `0.5.0-ux004005.1`:
- FUNCTIONAL BASELINE only;
- VISUAL/PRODUCT DIRECTION REJECTED;
- no cutover authorized.

Observed:
- Home too close to legacy composition;
- static identity did not preserve Entra Gateway experience;
- some navigation targets unresolved;
- Word Cloud preview leaked technical copy;
- Article Reader duplicated legacy article chrome;
- Tips fixed grid produced dead space;
- Summary Rail too cramped.

Redesign addendum:
- `ux/004-public-home-portal/public-experience-redesign-addendum-v1.md`.

Candidate `0.5.0-ux004005.2`:
- Public Auth Bridge delegates to `[bdc_entra_login]` when registered, WordPress fallback otherwise;
- WordPress custom logo first;
- navigation menu-first + page fallback;
- redesigned Home composition;
- technical Word Cloud copy removed from consumer surface;
- `Public_Article_Content` compatibility stage preserves `the_content` pipeline and strips duplicate legacy chrome only with strong evidence;
- Tips auto-fit;
- Summary Rail widened to 360px desktop;
- GAC/WPUI not removed;
- GRE Tips/Rail suppressed in preview only.

Local validation:
- 80 files / 69 PHP;
- 69/69 lint;
- 69/69 extracted ZIP lint;
- 52/52 active requires;
- 33/33 contract checks;
- deterministic build 2/2;
- exact source/package parity;
- ZIP SHA-256 `24e96812189a7fdd1252714030a4b0341f9357170721c2aa94dbcc2d18c4181b`;
- Search/rebuild/lifecycle/ranker unchanged.

Evidence:
- `evidence/ux004005-public-redesign-local-validation-20260920.json`.

Status:
- H-020 redesign candidate LOCAL PASS / ENV+HUMAN pending;
- A-020 redesign candidate LOCAL PASS / ENV+HUMAN pending;
- no cutover;
- G-585 PAUSED;
- G-590 BLOCKED.

Next:
1. install `0.5.0-ux004005.2` over current preview;
2. review Home;
3. review Article Reader #36431 or equivalent with Tips/Summary;
4. verify Gateway Entra UI/action;
5. verify no duplicate legacy hero;
6. iterate before public facade/Word Cloud parity work.

## UX-004/UX-005 — search-first candidate 0.5.0-ux004005.3

Human direction:
- Home must behave like a knowledge Search product, not a marketing/portal site;
- Search must remain available inside article reading;
- candidate links must stay inside the new Reader during homologation;
- Visual Contract BDC remains the design authority.

Web research captured in:
- `ux/004-public-home-portal/visual-research-search-first-v1.md`;
- `ux/004-public-home-portal/public-experience-search-first-addendum-v2.md`.

Patterns adopted:
- persistent/search-first pattern;
- Ctrl/Cmd+K;
- clean documentation reading;
- progressive disclosure of secondary discovery;
- global Search inside articles.

Candidate `0.5.0-ux004005.3`:
- removes metric dashboard / large marketing hero from Home;
- centered dominant search;
- compact frequent topics;
- Categories/Latest/Popular preserved inside progressive “Explorar a Base” disclosure;
- Article Reader header contains persistent Search;
- Article Search stays on current page and renders candidate result panel;
- Search/Home/Latest/Popular result links route to Article Reader preview, not legacy article;
- Ctrl/Cmd+K focuses primary/global Search;
- Article Reader simplified to breadcrumb + title/meta + content + Tips + Summary Rail;
- previous auth bridge, legacy chrome sanitizer, GAC/WPUI preservation remain.

Validation:
- 81 files / 69 PHP / 2 JS;
- 69/69 PHP lint;
- 69/69 extracted ZIP lint;
- 52/52 active requires;
- 40/40 contract checks;
- deterministic build 2/2;
- ZIP SHA-256 `19bebb7b55015dac8b356f66390915c8a21659b362df2315f64a9f69905a2cea`;
- Search/rebuild/lifecycle/ranker unchanged.

Evidence:
- `evidence/ux004005-search-first-v3-local-validation-20260920.json`.

Status:
- H-020 search-first candidate LOCAL PASS / ENV+HUMAN pending;
- A-020 clean Reader candidate LOCAL PASS / ENV+HUMAN pending;
- public authorization facade / instant suggestions remain pending;
- full Word Cloud parity remains pending;
- no cutover;
- G-585 PAUSED;
- G-590 BLOCKED.

## UX-004/UX-005 — v4 Header parity + Reader context rail

Human review of `0.5.0-ux004005.3`:
- Home search-first direction accepted/improving;
- Header lost legacy functional components;
- Article Reader still too white / weakly separated;
- Executive Summary must live in an independent right rail and follow scroll.

Header contract source reintroduced:
- Página Inicial;
- Consulta Avançada;
- Telefones;
- Links Úteis;
- POSTI external;
- Entra profile menu with department/job title/logout/admin attributes;
- responsive quick-links toggle.

Article Search:
- no longer replaces quick links;
- appears as a second compact row under the functional Header;
- Ctrl/Cmd+K retained.

Reader v4:
- light application canvas;
- distinct article surface;
- content target ~980px;
- Summary rail ~350px;
- gap ~48px;
- Reader workspace ~1420px;
- sticky Summary with header-aware top offset;
- rail reflows below article <=1040px;
- print becomes static flow.

Package:
- version `0.5.0-ux004005.4`;
- SHA-256 `a07a518708b9ad92afbffacb6cd6527e944247e7084112851bc1e84c2c6ea300`;
- 81 files / 69 PHP / 2 JS;
- 69/69 PHP lint;
- 69/69 extracted ZIP lint;
- 19/19 v4 contract checks;
- deterministic build 2/2;
- delta vs v3: 8 modified / 0 added / 0 deleted;
- Search/rebuild/lifecycle/ranker unchanged.

Artifacts:
- `ux/005-public-article-reader/header-parity-reader-rail-addendum-v3.md`;
- `evidence/ux004005-header-reader-v4-local-validation-20260920.json`.

Status:
- H-020 v4 LOCAL PASS / ENV+HUMAN pending;
- A-020 v4 LOCAL PASS / ENV+HUMAN pending;
- no cutover;
- G-585 PAUSED;
- G-590 BLOCKED.

## UX-004/UX-005 — v5 live search + GAC parity + follow-scroll rail

Human findings from v4:
- Summary rail did not actually follow viewport scroll;
- title card remained too large;
- GAC content actions disappeared;
- Article Search worked but lacked emphasis;
- ASI production Search confirms input-driven incremental results without Enter.

v5 implementation:
- authenticated preview AJAX Search;
- 2-char minimum;
- 180ms debounce;
- stale-request cancellation through AbortController;
- Home + Reader update results while typing;
- candidate result links stay in new Reader;
- section/trecho result parity remains future ASI capability work;
- GAC regression fixed by executing canonical the_content() inside the true WordPress loop before read-only legacy chrome sanitization;
- Summary rail moved to a full-height slot and follows scroll through clamped JS transform, stopping at article end;
- rail width reduced to ~300px;
- content target increased to ~1060px;
- posts without Summary switch to centered single-column Reader;
- title surface reduced to 30px heading / 16x20 padding;
- Article Search receives stronger visual treatment.

Package:
- version `0.5.0-ux004005.5`;
- SHA-256 `9b011563b24345139350fb0c7477050e7803ea51ef7aa912bf983b98e2b89cd9`;
- 81 files / 69 PHP / 2 JS;
- 69/69 PHP lint;
- 69/69 extracted ZIP lint;
- 25/25 v5 contract checks;
- active requires unchanged 52/52;
- deterministic build 2/2;
- delta vs v4: 10 modified / 0 added / 0 deleted;
- exact source/package blob parity for all changed files;
- Search/rebuild/lifecycle/ranker unchanged.

Evidence:
- `evidence/ux004005-live-search-gac-reader-v5-local-validation-20260920.json`.

Status:
- H-020 v5 LOCAL PASS / ENV+HUMAN pending;
- A-020 v5 LOCAL PASS / ENV+HUMAN pending;
- GAC environmental confirmation REQUIRED;
- ASI section/trecho parity still tracked, not claimed complete;
- no cutover;
- G-585 PAUSED;
- G-590 BLOCKED.

## UX-004/UX-005 — premium polish candidate 0.5.0-ux004005.6

v5 functional baseline confirmed by human review:
- live Search works without Enter;
- GAC content actions restored;
- Summary follow-scroll works;
- Home direction accepted.

v6 scope:
- visual/product polish only;
- no Search/ranking redesign;
- no GAC integration change;
- no Summary follow algorithm change;
- no Header behavior change.

Premium changes:
- Search results use document-search hierarchy: compact rank, category metadata, title, two-line excerpt, restrained arrow;
- live loading state + reduced-motion fallback;
- result panel has bounded internal scroll;
- server/live Search visual hierarchy aligned;
- Home search-first composition preserved with tighter rhythm;
- Header chrome visually quieter;
- Article heading de-cardified into document context;
- Helpful Tips becomes integrated callout rather than nested card;
- article typography/readability refined; prose/list target around 82ch where compatible;
- Executive Summary visual weight reduced while follow-scroll remains unchanged.

Validation:
- 81 files / 69 PHP / 2 JS;
- 69/69 PHP lint;
- 69/69 extracted ZIP lint;
- JS syntax PASS;
- CSS parse 4/4 PASS;
- 24/24 static checks;
- 52/52 active requires unchanged;
- repository/package source parity 9/9;
- deterministic build 2/2;
- delta vs v5: 9 modified / 0 added / 0 deleted;
- ZIP SHA-256 `68b916b6eaf1281bc3b6208694dbaf7733831970bbcc970ae69de58fce5a1ad4`.

Artifacts:
- `ux/005-public-article-reader/premium-polish-addendum-v4.md`;
- `evidence/ux004005-premium-polish-v6-local-validation-20260920.json`.

Status:
- H-020 v6 LOCAL PASS / ENV+HUMAN pending;
- A-020 v6 LOCAL PASS / ENV+HUMAN pending;
- no cutover;
- G-585 PAUSED;
- G-590 BLOCKED.

## Visual baseline accepted + P-580WC.1 Word Cloud

Human decision:
- Public Experience `0.5.0-ux004005.6` approved as CURRENT VISUAL BASELINE;
- visual work pauses and may resume as product maturity increases;
- this does not authorize public cutover or close technical parity gates.

Word Cloud v1:
- contract: `word-cloud-v1.0.0`;
- BDC-owned Options/transient only; zero new table;
- title/heading/content/taxonomy/allowlist/blocklist available;
- search events/interactions/vocabulary explicitly pending;
- snapshot + quality maturity + lock + hourly cron + health/history + admin operations + click-to-search;
- public requests never generate snapshot;
- zero ASI runtime/storage dependency;
- deactivation clears cron only and retains state.

Package:
- `0.5.0-p580wc.1`;
- SHA-256 `0b68e3eefa19bb68dce74de208e37bb128e6de1190162fbfc326ac67670f3a8d`;
- 85 files / 73 PHP;
- 73/73 lint + extracted lint;
- 56/56 active requires;
- 19/19 contract checks;
- source/package/repository parity 7/7;
- deterministic 2/2.

Evidence:
- `evidence/p580-word-cloud-v1-local-validation-20260920.json`;
- `specs/005-search-lexical-golden-queries/word-cloud-contract-v1.md`;
- `specs/005-search-lexical-golden-queries/asi-functional-disposition-matrix-v2.md`.

Status:
- H-040/A-040 current human visual PASS;
- H-023 LOCAL PASS / ENV pending;
- P-580A classification advanced; no capability may be silently dropped;
- G-585 remains PAUSED until Word Cloud/Home technical acceptance and ASI-off proof;
- G-590 BLOCKED.

## P-580WC — environmental quality gap / p580wc.2

Environmental execution of `0.5.0-p580wc.1`:
- runtime status READY;
- 300 public terms;
- freshness OK;
- source availability correctly reported;
- however public top terms included generic/noisy tokens such as `usuario`, `nao`, `clicar`, `ser`, `banco`, `objetivo`, `sistema`, `regras`, `mail`, `ncia`.

Interpretation:
- generation/runtime pipeline PASS;
- semantic public quality FAIL CONTROLADO;
- not an architecture/runtime failure;
- H-023 remains OPEN.

Reference comparison:
- current ASI surface shows recognizable concepts/phrases such as MSTeams, Windows 11, pendrive, Termo de assinatura, BitLocker and mensageria;
- ASI consultation counts cannot be reproduced honestly until BDC telemetry/interactions exist.

Remediation `0.5.0-p580wc.2`:
- contract `word-cloud-v1.1.0`;
- quality profile `semantic-balanced-v2`;
- body terms OFF by default;
- expanded PT-BR stopwords + governed noise blocklist;
- observed governed allowlist;
- title/heading phrase preservation;
- document/structural evidence for maturity;
- frequency alone cannot publish a content-only term;
- stale v1 snapshot contract guard + one-time profile migration;
- Home wording = `Assuntos em destaque` until consultation telemetry exists.

Local validation:
- 85 files / 73 PHP / 2 JS;
- 73/73 PHP lint;
- 73/73 extracted ZIP lint;
- 56/56 active requires;
- 20/20 static quality/contract checks;
- 7/7 quality harness;
- source/package/repository parity 6/6;
- deterministic build 2/2;
- delta vs p580wc.1: 6 modified / 0 added / 0 deleted;
- SHA-256 `dd65c56adb150c3799036f7bf166dc5f63ebe1ed94e8b8c22c9e5faf90167bb1`.

Evidence:
- `evidence/p580-word-cloud-quality-gap-v1-v2-20260920.json`.

Status:
- H-023 = v1 ENV QUALITY FAIL CONTROLADO / v2 LOCAL PASS / ENV RECHECK REQUIRED;
- P-580A ACTIVE;
- G-585 PAUSED;
- G-590 BLOCKED.

## P-580WC.3 — highlighted topics + aggregate consultation signal

Human feedback after p580wc.2:
- semantic quality materially improved;
- Home should expose more highlighted topics;
- each topic should display a real consultation count;
- consultation usage should add relevance weight.

Decision:
- do not reuse semantic occurrence count as “consultations”;
- do not count every live-search keystroke;
- record only confirmed interactions: topic click and Search result click;
- no raw event log, user_id, IP or session storage;
- only current public Word Cloud terms can receive aggregate counts;
- current collector is preview/admin-only;
- future public anonymous collector/anti-abuse remains telemetry work.

Implementation:
- contract `word-cloud-v1.2.0`;
- aggregate `consultation-aggregate-v1.0.0`;
- Option `bdc_kb_word_cloud_consultations`, autoload=false;
- bounded 500 canonical terms;
- display_score = semantic_score + logarithmic consultation boost;
- Home candidate pool 32;
- visible highlighted topics 6 -> 12;
- visible count badge on every topic;
- live and server Search result clicks can confirm a consultation;
- no Search ranker change;
- no snapshot regeneration required from p580wc.2.

Package:
- `0.5.0-p580wc.3`;
- SHA-256 `33cef92d7667cbf3acd9ad1abb9b6e20684ee4dd16c95816e8db44e5cffd7ae9`;
- 86 files / 74 PHP / 2 JS;
- 74/74 PHP lint;
- 74/74 extracted ZIP lint;
- JS syntax PASS;
- Home CSS parse PASS / 0 errors;
- active requires 57/57;
- 26/26 static checks;
- 6/6 consultation harness;
- source/package/repository parity 10/10;
- deterministic build 2/2;
- delta vs p580wc.2: 1 added + 9 modified / 0 deleted.

Evidence:
- `evidence/p580-word-cloud-consultation-v3-local-validation-20260920.json`.

Status:
- H-023 p580wc.3 LOCAL PASS / ENV count+ranking recheck required;
- P-580A ACTIVE;
- G-585 PAUSED;
- G-590 BLOCKED.

## P-580WC.3.1 — consultation idempotency patch

Environmental p580wc.3 finding:
- E-mail count incremented correctly;
- Windows 10 duplicated a single cloud interaction;
- consultation aggregate itself remained functional;
- classification: FAIL CONTROLADO — EVENT IDEMPOTENCY.

Root-cause boundary:
- v3 endpoint had no idempotency key;
- therefore duplicate valid delivery could increment twice;
- exact browser-level duplicate mechanism is not assumed.

Patch:
- version `0.5.0-p580wc.3.1`;
- consultation aggregate `v1.1.0`;
- per-gesture event_id;
- event_id persisted on the clicked DOM node so duplicated listeners reuse the same id;
- WeakSet local repeat guard;
- server transient dedupe for 5 minutes;
- duplicate response = `recorded=false, duplicate=true`;
- explicit admin action **Resetar consultas de homologação**;
- counters are never reset automatically.

Validation:
- 86 files / 74 PHP / 2 JS;
- 74/74 PHP lint;
- 74/74 extracted ZIP lint;
- JS syntax PASS;
- 18/18 idempotency checks;
- exact source/package/repository parity 4/4;
- deterministic build 2/2;
- delta vs p580wc.3: 4 modified / 0 added / 0 deleted;
- SHA-256 `43a6ed6ac2bb6eac8d4ee1f9515d53e39b65acc19ca2f62c17b28da334224d64`.

Evidence:
- `evidence/p580-word-cloud-consultation-idempotency-v31-20260921.json`.

Status:
- H-023 remains OPEN pending environmental +1 verification;
- P-580A ACTIVE;
- G-585 PAUSED;
- G-590 BLOCKED.

## P-580WC.3.2 — atomic consultation idempotency

Environmental p580wc.3.1 finding:
- after explicit reset, Windows 10 still incremented twice from one interaction;
- frontend gesture-id reuse alone was insufficient;
- transient check/set remained race-prone.

Root cause:
- `get_transient()` + `set_transient()` is not an atomic claim;
- concurrent requests can both observe the key as absent before either writes it;
- classification: FAIL CONTROLADO — ATOMICITY.

Patch:
- version `0.5.0-p580wc.3.2`;
- consultation aggregate `v1.2.0`;
- atomic event claim via `add_option()`;
- unique `option_name` prevents two concurrent requests from claiming the same event;
- 5-minute expiry stored as option value;
- expired claim retry;
- hourly opportunistic GC, max 500 expired keys/run;
- reset action clears both consultation counts and event-claim keys;
- existing client event_id/WeakSet/keepalive guards preserved.

Validation:
- 86 files / 74 PHP / 2 JS;
- 74/74 PHP lint;
- 74/74 extracted ZIP lint;
- 57/57 active requires unchanged;
- 16/16 atomicity checks;
- exact source/package/repository parity 2/2;
- deterministic build 2/2;
- delta vs p580wc.3.1: 2 modified / 0 added / 0 deleted;
- SHA-256 `06b0a03fd6c969b48eade9dfad20eac1882817adf33660b6a41df48287cef321`.

Evidence:
- `evidence/p580-word-cloud-consultation-atomicity-v32-20260921.json`.

Status:
- H-023 remains OPEN pending environmental +1 verification;
- P-580A ACTIVE;
- G-585 PAUSED;
- G-590 BLOCKED.

## H-023 CLOSED / H-030 candidate 0.5.0-h030.1

Word Cloud:
- p580wc.3.2 environmental human confirmation PASS;
- one confirmed interaction = one aggregate increment;
- H-023 PASS/CLOSED for current maturity.

Public Search Facade:
- contract `public-search-facade-v1.0.0`;
- same normalizer/projection/ranker/result contracts;
- no second ranker;
- hard public status policy: publish/private only;
- private requires `read_post`;
- draft/pending/future excluded;
- password-protected excluded;
- AJAX auth+nopriv;
- nonce + bounded query/result size;
- approximate 60/minute rate bound with HMAC fingerprint; no raw IP persistence;
- no query log.

H-030 runner:
- H030 functional parity;
- H031 security/capability including anonymous Search probes;
- H032 candidate Home smoke with ASI manually inactive;
- H033 raw legacy shortcode scan;
- H034 Search/Golden regression;
- editorial fingerprint before/after;
- never deactivates ASI automatically.

Package:
- `0.5.0-h030.1`;
- SHA-256 `00e5032ea500515b3dae04e9695f2e5e234c53c94905525509e1b3da5a741f03`;
- 88 files / 76 PHP / 2 JS;
- 76/76 PHP lint + extracted ZIP lint;
- JS syntax PASS;
- 61/61 active requires;
- 24/24 H-030 static checks;
- source/package/repository parity 6/6;
- deterministic 2/2;
- Search Service/rebuild/lifecycle/ranker byte-identical.

Status:
- H-023 CLOSED;
- H-030 LOCAL PASS / ENV pending;
- H-050 BLOCKED until H-030;
- G-585 PAUSED until H-030 PASS;
- G-590 BLOCKED.


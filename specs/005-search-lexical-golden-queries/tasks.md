# Tasks — SPEC-005

## R-500 — Baseline / DoR
- [x] T500 congelar baseline `main`, versão/plugin e corpus conhecido.
- [x] T501 inventariar comportamento atual `WP_Query s`.
- [x] T502 criar diagnóstico read-only de busca nativa — PASS AMBIENTAL.
- [x] T503 medir corpus/scope/status/permissions — PASS ambiental para baseline admin.
- [x] T504 comparar conteúdo pesquisável nativo vs Content Extractor — PASS; gap semântico comprovado.
- [x] T505 classificar gaps — PASS; ranking/coverage/Summary comprovados; normalization real segue para Golden.
- [x] T506 decidir superfície inicial — ADMIN-FIRST; pública postergada.
- [x] T507 registrar benchmark p50/p95 e limites — PASS ambiental.
- [x] T508 fechar R-500 — PASS/CLOSED.

## R-510 — Golden Dataset
- [x] T510 recuperar expectativas úteis do legado sem copiar runtime — PASS AMBIENTAL; 6 candidates limpos.
- [x] T511 preservar seed histórico e implementar baseline runner independente — v1 SUPERSEDED; v2 PASS LOCAL + PASS AMBIENTAL (2026-09-18 21:45:45 UTC); ver análise/evidência T511.2.
- [x] T512 definir schema Golden v1 — contrato/fixture candidate versionados; freeze final depende T513/T514.
- [x] T513 Automated Golden Validator — PASS AMBIENTAL: 5 AUTO_PASS / 1 AMBIGUOUS_QUARANTINED / 0 AUTO_FAIL; human review required=false.
- [x] T514 Diversity/Robustness Validator — PASS AMBIENTAL: Challenge Discovery 7 casos, diversity PASS, synthetic 16/16 PASS; real-world enrichment=PENDING_TELEMETRY.
- [x] T515 gerar set_hash/version — Golden `golden-relevance-v1.0.0` / `e449364d...`; Challenge `technical-challenge-v1.0.0` / `2928dcd8...`.
- [x] T516 fechar R-510 — PASS/CLOSED; próximo gate G-520.

## G-520 — Contratos
- [x] T520 Query Normalization Contract — FROZEN `search-normalizer-v1.0.0`.
- [x] T521 Search Document Contract — FROZEN `search-document-v1.0.0`.
- [x] T522 Ranking Contract — FROZEN `lexical-ranker-v1.0.0`.
- [x] T523 Search Result Contract — FROZEN `search-result-v1.0.0`.
- [x] T524 Golden Runner Contract — FROZEN `golden-runner-v1.0.0`.
- [x] T525 WordPress-first storage decision — ADR-005-003: Projection BDC própria, uma tabela, sem FULLTEXT v1.
- [x] T526 Security matrix — FROZEN.
- [x] T527 rollback/rebuild contract — FROZEN.
- [x] T528 G-520 PASS — 25/25 contract checks; CLOSED em 2026-09-19.

## G-530 — Engine lexical
- [x] T530 implementar normalizer mínimo — `search-normalizer-v1.0.0`.
- [x] T531 implementar retrieval mínimo — Search Document + Projection Repository próprios.
- [x] T532 implementar ranker versionado — `lexical-ranker-v1.0.0`.
- [x] T533 implementar resultado explicável — Search Service + matched_signals.
- [x] T534 fallback/degraded — WP_Query native relevance, score=null.
- [x] T535 testes locais — 17/17 PASS + 7/7 PHP lint.
- [x] T536 zero write editorial — scan PASS; writes somente Projection/Option derivadas.
- [x] T537 G-530 PASS — CLOSED em 2026-09-19; próximo gate G-540.

## G-540 — Corpus
- [x] T540 executar full-corpus — PASS AMBIENTAL: 623/623.
- [x] T541 duas passagens/determinismo — 623 comparados / 0 mismatch.
- [x] T542 coverage — 623 ready; source kinds e campos registrados.
- [x] T543 rebuild/idempotência — pass2 0 written / 623 NO_CHANGE.
- [x] T544 zero fatal/throwable — errors=[] / throwables=[].
- [x] T545 G-540 PASS — CLOSED em 2026-09-19; próximo gate G-550.

## G-550 — Golden Gate
- [x] T550 runner explícito — PASS AMBIENTAL.
- [x] T551 relatório JSON — PASS; 13 resultados.
- [x] T552 blocking failures = 0 — PASS.
- [x] T553 warnings documentados — PASS; quarantine executada sem warning failure.
- [x] T554 evidence current vs algorithm_version/set_hash — PASS; hashes recalculados e versões current.
- [x] T555 G-550 PASS — CLOSED em 2026-09-19; próximo gate G-560.

## G-560 — UX/Humano
- [x] T560 UI conforme Visual Contract v2 — source/runtime contract PASS.
- [x] T561 desktop/782/520 — source contract PASS; visual human review ainda pendente.
- [x] T562 teclado/focus/ARIA — PASS ambiental.
- [x] T563 zero-result != erro técnico — PASS live.
- [x] T564 human visual acceptance — PASS desktop; mobile visual review DEFERRED/NON-BLOCKING por decisão explícita do Product Owner.
- [x] T565 G-560 PASS — CLOSED em 2026-09-19; próximo gate G-570.

## G-570 — Segurança/Performance
- [x] T570 scope/capability — PASS ambiental.
- [x] T571 SQL/bounds — PASS ambiental.
- [x] T572 abuse/long query — PASS ambiental.
- [x] T573 p50/p95 — PASS: p95 207.7448 ms / max 212.9128 ms.
- [x] T574 G-570 PASS — CLOSED em 2026-09-20; próximo gate G-580.

## G-580 — Lifecycle
- [x] T580 activation/update — PASS ambiental; schema-only, implicit_rebuild=false, Projection snapshot unchanged.
- [x] T581 rebuild/fallback — PASS ambiental; fallback honesto + rebuild 623/623, pass2 NO_CHANGE, mismatch=0.
- [x] T582 disable module — PASS ambiental; wordpress_fallback / search_module_disabled.
- [x] T583 uninstall retention — PASS ambiental; deactivation/uninstall não destrutivos por default.
- [x] T584 G-580 PASS — CLOSED em 2026-09-20; evidence g580-environmental-review-20260920T173016Z.json; próximo gate G-585.

## G-595 — Boundary Closeout / RC técnico
- [ ] T595-01 deterministic build do artefato que fechou G-590/G-585.
- [ ] T595-02 manifest/checksum.
- [ ] T595-03 regressão SPEC-001–005 consolidada.
- [ ] T595-04 environmental boundary smoke.
- [ ] T595-05 relatório final da fronteira Search.
- [ ] T595-06 merge somente após aprovação humana.


## P-580A — ASI Functional Parity Rebaseline
- [x] P580A-01 inventário funcional completo ASI 4.6.8 — baseline funcional versionada.
- [x] P580A-02 matriz de disposição v2 criada — parity/improved/planned/superseded; nenhum descarte silencioso.
- [x] P580A-03 Public Search/Home mapeados; UX-004 candidata v6 human accepted.
- [x] P580A-04 Word Cloud inventariada; `word-cloud-v1.0.0` candidata BDC local PASS.
- [x] P580A-05 Telemetry/Search Intelligence mapeados → PLANNED SPEC-006.
- [x] P580A-06 item-level/deep-link/anchors mapeados → PLANNED gate próprio.
- [x] P580A-07 Operations/Queue/Migrations/Post-Install mapeados → PLANNED SPEC-007.
- [ ] P580A-08 fechar rebaseline antes de Decommission Readiness.

## P-580B — GRE Functional Parity
- [ ] P580B-01 inventário GRE 0.6.0.
- [ ] P580B-02 mapear oito fields para owners BDC.
- [ ] P580B-03 fechar gaps Serviço Afetado/Sistemas Envolvidos.
- [ ] P580B-04 absorver coverage/curation management.
- [ ] P580B-05 homologar Executive Summary Rail BDC.
- [ ] P580B-06 provar zero dependência GRE antes de retirada.

## UX-005 — Public Article Reader
- [ ] UX005-A001 inventory de template/CSS/source kinds.
- [ ] UX005-A010 contract do reader/rail/tips/compat.
- [ ] UX005-A020 implementação plugin-owned.
- [ ] UX005-A030 corpus regression.
- [ ] UX005-A040 human acceptance.
- [ ] UX005-A050 Astra Custom CSS não requerido.

## G-585 — ASI Independence / Decommission Readiness
> PAUSED: G-585 não pode fechar antes de P-580A/UX-004 eliminarem perda funcional bloqueante.
- [ ] T585 static scan runtime: zero `asi_*`, `asi4_*`, classes/functions/hooks ASI.
- [ ] T586 desativar ASI em homologação.
- [ ] T587 executar Search + Golden Suite com ASI ausente.
- [ ] T588 rebuild do índice/projection própria com ASI ausente.
- [ ] T589 comprovar rollback/lifecycle sem ASI.
- [ ] T589.1 registrar evidência de dependency-zero.
- [ ] T589.2 G-585 PASS antes de RC.
- [x] G585-PKG-01 contrato `g585-asi-independence-contract-v1.md` congelado.
- [x] G585-PKG-02 runner nunca desativa/remove ASI automaticamente; bloqueia com `BLOCKED_LEGACY_ACTIVE`.
- [x] G585-PKG-03 static runtime scan + loaded symbols/hooks + active plugin probe implementados.
- [x] G585-PKG-04 Search + Golden + rebuild + lifecycle/rollback integrados ao runner.
- [x] G585-PKG-05 29/29 checks locais; 59/59 PHP lint; 48/48 active requires.
- [x] G585-PKG-06 build determinístico 2/2 — SHA-256 `cae86ef93572e9320efe89d8fed891e90be032e5d46e4aaccf6dca647bf62880`.
- [ ] G585-ENV executar com ASI manualmente desativado e anexar JSON.
  - g585.1: FAIL CONTROLADO — ASI inativo, source BDC dependency-zero, porém símbolo carregado `BDC_KX_ASI_Adapter`; origem não atribuída.
  - g585.2: patch diagnóstico pronto; Reflection reporta source_scope/source_path; gate permanece bloqueante; reexecução requerida.

### G-540 — pacote de homologação
- [x] G540-PKG-01 source/package parity 55/55.
- [x] G540-PKG-02 PHP lint 50/50 PASS.
- [x] G540-PKG-03 active requires 44/44 resolvidos.
- [x] G540-PKG-04 zero write editorial no runtime Search; writes permitidos apenas na Projection derivada.
- [x] G540-PKG-05 build determinístico 2/2 — SHA-256 `b08a7abdef9c6287571ec137c2623618077a8ea6426d964bfc9e858659270a7f`.
- [x] G540-PKG-06 ZIP `0.5.0-g540.1` pronto para homologação.
- [x] G540-ENV PASS — evidence `g540-environmental-20260919T115727Z.json`; SHA-256 `c2f1195c35917bc1863f262a7c0a930d622e93733aaccbfba3325becf1b789de`.


### G-550 — pacote de homologação
- [x] G550-PKG-01 runtime resources próprios Golden/Challenge empacotados.
- [x] G550-PKG-02 set_hash/version validados em runtime.
- [x] G550-PKG-03 stale guard de normalizer/document/ranker/result.
- [x] G550-PKG-04 fallback WordPress não pode aprovar Golden.
- [x] G550-PKG-05 local harness 6/6 PASS.
- [x] G550-PKG-06 PHP lint 52/52 PASS.
- [x] G550-PKG-07 active requires 45/45.
- [x] G550-PKG-08 build determinístico 2/2 — SHA-256 `5c0fb99476aab84149341c1f069f64bfb9d8bdb57daaeca2636fe518164739b5`.
- [x] G550-ENV PASS — evidence `g550-environmental-20260919T121422Z.json`; upload SHA-256 `e6f83104d330129cd0bd1835f4c1fd21f8d7117b461dbb646527f408d56cd07b`.


### G-560 — pacote técnico de homologação
- [x] G560-PKG-01 Knowledge List usa Search_Service para consulta não vazia; listagem sem consulta preserva modified DESC.
- [x] G560-PKG-02 estados success/zero_results/degraded/invalid_query/technical_error possuem feedback distinto.
- [x] G560-PKG-03 label visível + helper + aria-describedby + aria-live + focus-visible.
- [x] G560-PKG-04 breakpoints 782/520 e ações mobile validados estaticamente.
- [x] G560-PKG-05 fingerprint editorial cobre post_content, Summary, _elementor_data e taxonomias canônicas.
- [x] G560-PKG-06 26/26 checks locais PASS; PHP lint 53/53; active requires 44/44.
- [x] G560-PKG-07 build determinístico 2/2 — SHA-256 `9f4f14c775aaad4e7d2360ed11dd3b036587556b1497e210eccaf787c89b8297`.
- [x] G560-ENV PASS — 21/21 checks; evidence `g560-environmental-review-20260919T134816Z.json`; upload SHA-256 `9906bbecbd1d2c056a2a1e87d2b50d8d4885adc904621a8b1120ff49c40b1527`.
- [x] G560-HUMAN PASS — desktop aprovado; evidência `g560-human-visual-acceptance-20260919.md`; revisão visual mobile deferred.


### G-570 — pacote de homologação
- [x] G570-PKG-01 contrato Security/Performance v1 congelado.
- [x] G570-PKG-02 runner ambiental read-only implementado.
- [x] G570-PKG-03 capability negative tests usam filtros temporários e removem em finally.
- [x] G570-PKG-04 SQL/bounds/abuse checks automatizados.
- [x] G570-PKG-05 benchmark: 6 queries × 5 medições + warm-up; 30 amostras esperadas.
- [x] G570-PKG-06 budget de homologação: p95 <=750 ms / max <=1500 ms; não é SLA de produção.
- [x] G570-PKG-07 37/37 checks locais PASS; PHP lint 54/54; active requires 44/44.
- [x] G570-PKG-08 zero write editorial/Projection no runner; zero rede/ASI/FULLTEXT/query logging.
- [x] G570-PKG-09 build determinístico 2/2 — SHA-256 `4cd915fc3be58166a354434bf9688f0a1509ca36d570697540dff9eace82ab62`.
- [x] G570-ENV PASS — 45/45 checks; evidence `g570-environmental-review-20260920T164828Z.json`; upload SHA-256 `38d399cab1f466c1b919513b8cf3650dd3ae7b6a55d94b715efe5597c3b89ffd`.


## G-590 — Section Retrieval & Deep-Link — Premium consolidation
- [x] T590-01 rebaseline/ownership: ASI-003/004/005 confirmados como blockers.
- [x] T590-02 ADR-005-004: uma Search Projection; sem segunda tabela.
- [x] T590-03 congelar Section Retrieval Contract v1.
- [x] T590-04 congelar Deep-Link Contract v1.
- [x] T590-05 congelar Cross-SPEC Regression Contract.
- [x] T590-10 implementar Section Projector determinístico.
- [x] T590-11 evoluir Search Document/Projection schema para 1.1.0.
- [x] T590-12 implementar Section Ranker/Service + fachada canônica `Search_Service::search_sections()`.
- [x] T590-13 implementar Anchor Manager read-only/fail-closed.
- [x] T590-13A adicionar schema contract físico (colunas+índices) e lifecycle degraded-safe.
- [x] T590-14 unit/regression Search post-level + SPEC-001–004 — PASS ambiental preservado até RC9.
- [ ] T590-15 coverage diagnostic ambiental — FAIL/OPEN; 1.294 strong gaps originaram R-260; R-260D1 contextual discovery em RC10.
- [x] T590-16 technical challenge/Golden section-level — PASS ambiental; source kinds cobertos, 0 section/deep-link failures.
- [x] T590-17 lifecycle migration 1.0→1.1 + explicit rebuild — PASS ambiental; prepare sem reindex, rebuild determinístico.
- [x] T590-18 performance/security — PASS ambiental; RC9 p95 305.8531 ms / max 341.8281 ms; safety íntegra.
- [ ] T590-19 G-590 PASS/CLOSED.

### G-590 — execução ambiental resumível
- [x] G590-ENV-01 RC1 `0.5.1-rc.1/g590.1` executado — FAIL CONTROLADO HTTP 504; nenhum JSON; funcionalidade não avaliada.
- [x] G590-ENV-02 causa raiz — runner monolítico síncrono.
- [x] G590-ENV-03 arquitetura resumível implementada — AJAX + nonce + capability + Option autoload=false + lock.
- [x] G590-ENV-04 fingerprint editorial chunked 50/posts.
- [x] G590-ENV-05 coverage chunked 25/posts sem alterar semântica de probe.
- [x] G590-ENV-06 rebuild core preservado; SPEC/G-580 não reaberto.
- [x] G590-ENV-07 RC2 `0.5.1-rc.2/g590.2` gerado; execução ambiental encontrou FAIL CONTROLADO de UI bootstrap — botões sem JS por late enqueue em `render_page()`.
- [x] G590-ENV-07A corrigir lifecycle de assets via `admin_enqueue_scripts` + asset dedicado + `wp_localize_script`.
- [x] G590-ENV-07B gerar RC3 `0.5.1-rc.3/g590.3` — LOCAL PACKAGE PASS; SHA-256 `22f9caae295f30347676eb835dd879c78c8c7468915cfde272020a8b7911560d`.
- [x] G590-ENV-08 RC3 executado até `Concluído — JSON disponível`; download bloqueado por dupla codificação `&amp;`/nonce — FAIL CONTROLADO de download, gate não reiniciado.
- [x] G590-ENV-08A corrigir URL de download: `add_query_arg()` + `wp_create_nonce()` + escaping somente no render boundary.
- [x] G590-ENV-08B gerar RC4 `0.5.1-rc.4/g590.4` — LOCAL PACKAGE PASS; SHA-256 `c05211f1db3ee568403b5f3b74abd8f8d3335d11865e9b77197afaf20e33b3ee`.
- [ ] G590-ENV-08C instalar RC4 e baixar JSON do job já concluído, sem reiniciar evidência.
- [x] G590-ENV-09 RC4 evidence analisada — T590-14 PASS, T590-15 FAIL, T590-16 FAIL, T590-17 PASS, T590-18 PASS; G-590 OPEN.
- [x] G590-ENV-10 classificar T590-15 — evidence contract defect: contador incluía `explicit_dom` como strong hierarchy signal.
- [x] G590-ENV-11 classificar T590-16 — probe selection gap: Gutenberg 17 generated anchors / 0 probe formulado no v1.2.
- [x] G590-ENV-12 congelar Evidence Contract Addendum v1.3.
- [x] G590-ENV-13 implementar strong hierarchy metric + samples + repeated-title runtime probe fallback.
- [x] G590-ENV-14 gerar pacote RC5 `0.5.1-rc.5/g590.5` — LOCAL PACKAGE PASS; source `fea648a891f715fca2a081597d0529018f83b94f`; SHA-256 `eb0958461509817c1dfb5ebdead895d1e7b090891711927e3e9103752bf2a695`.
- [ ] G590-ENV-15 executar RC5 e baixar JSON.
- [ ] G590-ENV-16 validar evidence machine `failed=0` e decidir T590-15/T590-16.

## Pós G-590
- [ ] retomar G-585 apenas como engine independence proof.
- [ ] fechar boundary da SPEC-005 antes da SPEC-006.


### G-590 — RC5 review / R-260 discovery
- [x] G590-ENV-17 analisar RC5 — strong hierarchy gap confirmado: 1294 sem heading context.
- [x] G590-ENV-18 reabrir SPEC-004 em R-260 DISCOVERY/read-only sem invalidar G-250.
- [x] G590-ENV-19 identificar divergência de probe authorization: runner publish-only vs Search canonical allowed statuses + edit_post.
- [x] G590-ENV-20 implementar diagnostics por source kind/confidence/post + structural recovery candidates.
- [x] G590-ENV-21 gerar RC6 `0.5.1-rc.6/g590.6` — LOCAL PACKAGE PASS; source `27223c6ce89e7fb25453086d0fe6a60c0cdeec36`; SHA-256 `cd4cd53eea5ba140c60accfaeaaf58c2f99b049c542fc36d3d244588afd82842`.
- [ ] G590-ENV-22 executar RC6 e baixar JSON.
- [ ] G590-ENV-23 decidir owner R-260 com base na distribuição completa.


### RC6 → R-260A / RC7
- [x] G590-ENV-24 analisar RC6 — T590-16 PASS; T590-15 único blocker.
- [x] G590-ENV-25 confirmar 1.294 strong gaps / 189 posts; 548 deterministic candidates / 94 posts.
- [x] G590-ENV-26 congelar R-260A read-only profiler; sem promoção runtime.
- [x] G590-ENV-27 implementar `R260_Hierarchy_Profiler` puro e isolado.
- [x] G590-ENV-28 gerar RC7 `0.5.1-rc.7/g590.7` — LOCAL PACKAGE PASS; SHA-256 `b826005d8ad777bfeac47e8b9a0743d42ef033b535ba59ae08f1bb1a4d8e8fbc`.
- [ ] G590-ENV-29 executar RC7 e baixar JSON.
- [ ] G590-ENV-30 decidir R-260B architecture owner a partir de TOC/body/uncertain distribution.


### RC7 → R-260B / RC8
- [x] G590-ENV-31 analisar RC7 — T590-16 PASS; T590-15 único blocker.
- [x] G590-ENV-32 fechar R-260A discovery: 548 candidates = 77 TOC + 289 body + 182 uncertain.
- [x] G590-ENV-33 congelar R-260B Structural Shadow Projection Contract v1.
- [x] G590-ENV-34 implementar shadow projector read-only: heading collision / duplicate label / MAX_SECTIONS / anchor blocker.
- [x] G590-ENV-35 manter Option C como arquitetura preferida, ainda não congelada.
- [x] G590-ENV-36 gerar RC8 `0.5.1-rc.8/g590.8` — LOCAL PACKAGE PASS; source `5456389bc8632cb21cedfa5d758380fa7ca5e242`; SHA-256 `f237f2be921acc5bcdf5798a664f020d5b1ba0d24d1435f78cd638e0af61ad32`.
- [ ] G590-ENV-37 executar RC8 e baixar JSON.
- [ ] G590-ENV-38 decidir R-260C runtime architecture a partir de capacity/collision/anchor evidence.


### RC8 → R-260C / RC9
- [x] G590-ENV-39 analisar RC8 — T590-16 PASS; T590-15 único blocker.
- [x] G590-ENV-40 fechar R-260B: 211 promotable / 78 duplicate / 77 TOC / 182 uncertain / 0 overflow / 0 heading collision.
- [x] G590-ENV-41 congelar R-260C Anchor Feasibility Contract v1.
- [x] G590-ENV-42 implementar profiler read-only de targets renderizados exatos.
- [x] G590-ENV-43 preservar Anchor Manager/Search Section runtime sem alteração.
- [x] G590-ENV-44 gerar RC9 `0.5.1-rc.9/g590.9` — LOCAL PACKAGE PASS; source `ea7203b6c2ab2e34438eb7b5d53c05ee877cac0a`; SHA-256 `8b56270a74b675d5601e93dc99553c11f0910017b3d2bf3a2c6aad3fe65c12f9`.
- [x] G590-ENV-45 executar RC9 e baixar JSON — evidence `bdc-kb-spec005-g590-section-20260925-180631.json`.
- [x] G590-ENV-46 analisar RC9 e fechar R-260C — 211 promotable = 131 paragraph_unique + 80 paragraph_ambiguous; 0 no-match; Deep-Link v2 viável fail-closed.
- [x] G590-ENV-47 dividir R-260D em D1 diagnóstico contextual e D2 runtime promotion evidence-gated.
- [x] G590-ENV-48 congelar `r260d-contextual-anchor-feasibility-contract-v1.md` e implementar profiler + machine validator read-only.
- [x] G590-ENV-49 gerar RC10 `0.5.1-rc.10/g590.10` — source `cf1374fd85de56c8d31192948a15d7e274eee1eb`; SHA-256 `456922cf831ef171a9d1ad8970bf3506294e1ac297e739bd690bc100ab603d5c`; 86 files; PHP 73/73; JS 3/3; JSON 2/2; active requires 69/69; D1 unit 15/15; deterministic 2/2.
- [ ] G590-ENV-50 executar RC10 e baixar JSON.
- [ ] G590-ENV-51 validar `validate-r260d-evidence.php` e decidir R-260D2 runtime promotion.

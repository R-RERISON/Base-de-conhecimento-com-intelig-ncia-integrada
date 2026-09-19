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
- [ ] T560 UI conforme Visual Contract v2.
- [ ] T561 desktop/782/520.
- [ ] T562 teclado/focus/ARIA.
- [ ] T563 zero-result != erro técnico.
- [ ] T564 human relevance acceptance.
- [ ] T565 G-560 PASS.

## G-570 — Segurança/Performance
- [ ] T570 scope/capability.
- [ ] T571 SQL/bounds.
- [ ] T572 abuse/long query.
- [ ] T573 p50/p95.
- [ ] T574 G-570 PASS.

## G-580 — Lifecycle
- [ ] T580 activation/update.
- [ ] T581 rebuild/fallback.
- [ ] T582 disable module.
- [ ] T583 uninstall retention.
- [ ] T584 G-580 PASS.

## G-590 — RC
- [ ] T590 deterministic build.
- [ ] T591 manifest/checksum.
- [ ] T592 regression SPEC-001–004.
- [ ] T593 environmental RC smoke.
- [ ] T594 final report.
- [ ] T595 merge only after human approval.


## G-585 — ASI Independence / Decommission Readiness
- [ ] T585 static scan runtime: zero `asi_*`, `asi4_*`, classes/functions/hooks ASI.
- [ ] T586 desativar ASI em homologação.
- [ ] T587 executar Search + Golden Suite com ASI ausente.
- [ ] T588 rebuild do índice/projection própria com ASI ausente.
- [ ] T589 comprovar rollback/lifecycle sem ASI.
- [ ] T589.1 registrar evidência de dependency-zero.
- [ ] T589.2 G-585 PASS antes de RC.

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

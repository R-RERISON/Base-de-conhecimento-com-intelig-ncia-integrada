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
- [ ] T540 executar full-corpus — próximo passo ambiental automatizado.
- [ ] T541 duas passagens/determinismo.
- [ ] T542 coverage.
- [ ] T543 rebuild/idempotência se aplicável.
- [ ] T544 zero fatal/throwable.
- [ ] T545 G-540 PASS.

## G-550 — Golden Gate
- [ ] T550 runner explícito.
- [ ] T551 relatório JSON.
- [ ] T552 blocking failures = 0.
- [ ] T553 warnings documentados.
- [ ] T554 evidence current vs algorithm_version/set_hash.
- [ ] T555 G-550 PASS.

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

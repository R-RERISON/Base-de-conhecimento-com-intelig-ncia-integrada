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
- [x] T511 preservar conjunto inicial de consultas reais do ASI e implementar baseline runner — PASS LOCAL; homologação do runner pendente.
- [x] T512 definir schema Golden v1 — contrato/fixture candidate versionados; freeze final depende T513/T514.
- [ ] T513 revisão humana expected_post/max_rank/severity.
- [ ] T514 garantir suíte não vazia e diversidade.
- [ ] T515 gerar set_hash/version.
- [ ] T516 fechar R-510.

## G-520 — Contratos
- [ ] T520 Query Normalization Contract.
- [ ] T521 Search Document Contract.
- [ ] T522 Ranking Contract.
- [ ] T523 Search Result Contract.
- [ ] T524 Golden Runner Contract.
- [ ] T525 WordPress-first storage decision.
- [ ] T526 Security matrix.
- [ ] T527 rollback/rebuild contract.
- [ ] T528 G-520 PASS.

## G-530 — Engine lexical
- [ ] T530 implementar normalizer mínimo.
- [ ] T531 implementar retrieval mínimo.
- [ ] T532 implementar ranker versionado.
- [ ] T533 implementar resultado explicável.
- [ ] T534 fallback/degraded.
- [ ] T535 testes locais.
- [ ] T536 zero write editorial.
- [ ] T537 G-530 PASS.

## G-540 — Corpus
- [ ] T540 executar full-corpus.
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

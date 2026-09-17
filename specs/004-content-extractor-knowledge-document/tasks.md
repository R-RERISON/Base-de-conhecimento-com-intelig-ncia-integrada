# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: PASS/CLOSED/main com KD 2.1.0.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — WordPress Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- T087C writer Elementor: CANCELADO / SUPERSEDED antes de implementação.
- T090: PASS LOCAL / READ-ONLY.
- T091: PASS AMBIENTAL.
- T092: PASS LOCAL / READ-ONLY.
- T093: PASS AMBIENTAL.
- T094: PASS AMBIENTAL.
- T095 Migration Fidelity Source v1: PASS LOCAL / READ-ONLY.
- T096 Lossless Core Block Serialization: PASS AMBIENTAL.
- T097 Static Editorial Parity + stale-source: PASS AMBIENTAL.
- T098.1 Block Migration Readiness: FAIL CONTROLADO / SEM MUTAÇÃO.
- T098.2 Block Migration Readiness: **PASS AMBIENTAL**.
- T099A Journal Store + Lock Smoke: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Baseline visual
UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório.

## T098.2 — PASS AMBIENTAL
Evidência: `evidence/g245-t098-readiness-pass-20260917T191436Z.json`.
SHA-256 bruto: `f7bf589858db6c6629cb937ba28dad4ef658d1e265225185dad77e55dd342ff0`.

Resultado:
- 623/623 em duas passagens;
- errors/throwables/safety violations 0;
- dry-run hash mismatches 0;
- journal hash mismatches 0;
- dry-run: ready 611, noop 7, review_required 5;
- 611 journals preparados apenas em memória;
- 25 batches cobrem os 611 elegíveis, duplicidade 0, cursor failures 0;
- fingerprint editorial unchanged;
- `t098_block_migration_readiness_pass=true`.

## T099A — Journal Store + Lock ambiental
Runtime: `includes/class-block-migration-storage-lock-smoke.php`.

Objetivo: provar a trilha durável real antes de qualquer canário editorial.

O smoke pode mutar somente:
- `_bdc_kb_block_migration_journal`;
- `_bdc_kb_block_migration_lock`.

Critérios PASS:
- candidato automático `legacy_html`/`plain_text` com dry-run ready;
- journal 0 antes;
- journal persistido + readback íntegro;
- lock adquirido + readback íntegro;
- lock liberado;
- journal temporário removido;
- journal 0 depois;
- lock ausente depois;
- `post_content` SHA-256 before/after igual;
- `_elementor_data` SHA-256 before/after igual;
- nenhum `wp_update_post`, writer, shortcode render, block render ou network.

## Próximos subgates

- [ ] executar T099A e versionar evidência.
- [ ] T099B: selecionar 1 `legacy_html` de baixo risco e gerar Authorization Pack.
- [ ] T099C: canário real de 1 artigo + rollback, somente com autorização específica.
- [ ] T100: batches homologados.
- [ ] T101: dependência residual Elementor / gate de retirada.
- [ ] G-250 Lifecycle/RC.

## Regras
1. Core Blocks são o destino canônico.
2. Plugin Gutenberg não é requisito.
3. Elementor permanece até dependência zero.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Write em `post_content` exige gates e autorização explícitos.
6. KD não é representação editorial lossless.
7. Raw payload não sai em runners de corpus.
8. Mixed não é decidido automaticamente.
9. UX-002 não pode regredir.
10. Trabalho incompleto permanece fora de `main`.

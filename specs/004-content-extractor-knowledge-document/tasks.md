# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: **PASS / CLOSED / PROMOVIDO PARA `main`** com KD 2.1.0.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS ambiental**.
- T082: **PASS LOCAL / CONTRATUAL**.
- T083: **PASS LOCAL / CONTRATUAL**.
- T084: **PASS LOCAL / CONTRATUAL**.
- T085: **PASS LOCAL / CONTRATUAL**.
- T086 Batches retomáveis: **PASS LOCAL / CONTRATUAL** em `0.4.0-g245-batch.1`.
- T087 Canário controlado + rollback: **NEXT / PREPARAÇÃO READ-ONLY**.
- G-250: NOT_RUN.

## Baseline visual obrigatória

A branch G-245 foi sincronizada com `main@6d0fc8e33f826ee957038483d22fa1b804bae056` no commit `145e16bf31f7afe2d3f08d087b79b69f3f40b885`, preservando integralmente a UX-002 homologada.

Arquivos visuais canônicos devem permanecer fora do diff G-245 vs `main`: `class-admin-page.php`, `class-classification-admin.php`, `visual-foundation.css` e `class-visual-foundation.php`.

## S006 — G-245 / Elementor Normalization & Production Readiness

**Regra:** nenhuma escrita editorial está autorizada por T080–T086.

### T080 — Production Preflight — PASS WITH REVIEW ITEMS

- [x] Preflight read-only; blockers 0; writer/migration false.
- [x] Evidência: `evidence/g245-preflight-summary-20260916T215612Z.json`.

### T081 — Projection Plan — PASS AMBIENTAL

- [x] Contrato determinístico congelado.
- [x] 58 assertions locais PASS.
- [x] Duas passagens 622/622.
- [x] Zero errors/throwables/mismatches/violations; fingerprint preservado; `gate.t081_pass=true`.
- [x] Evidência: `evidence/g245-projection-summary-20260917T111009Z.json`.

### T082 — Gateway — PASS LOCAL / CONTRATUAL

- [x] `elementor-gateway-contract-v1.md`.
- [x] Elementor `4.1.0` homologado; ausente blocking; diferente review_required.
- [x] feature flag default false + capability + hard phase gate.
- [x] writer/migration false.
- [x] 45 assertions PASS.

### T083 — Journal / rollback — PASS LOCAL / CONTRATUAL

- [x] `journal-rollback-contract-v1.md`.
- [x] write-ahead/capsule/hashes/transições/rollback/idempotência contratados.
- [x] `journal_persisted=false`; storage durável ainda pendente antes de qualquer write real.

### T084 — Stale-source guard — PASS LOCAL / CONTRATUAL

- [x] `stale-source-guard-contract-v1.md`.
- [x] fresh/stale/blocking e fail-closed.
- [x] T083+T084: 38 assertions PASS.

### T085 — Dry-run zero-write — PASS LOCAL / CONTRATUAL

- [x] `migration-dry-run-contract-v1.md`.
- [x] ready/review_required/noop/blocked.
- [x] review_required não simula journal/apply.
- [x] stale/gateway blocking/unsafe plan fail-closed.
- [x] hash/JSON determinísticos.
- [x] 38 assertions PASS + lint PASS.

### T086 — Batches retomáveis — PASS LOCAL / CONTRATUAL

- [x] `migration-batch-plan-contract-v1.md`.
- [x] somente dry-runs `ready` elegíveis.
- [x] ordenação canônica e dedupe por `post_id`.
- [x] duplicata conflitante fail-closed.
- [x] batch size 1–100; default 25.
- [x] cursor versionado com `cohort_hash`, `next_offset` e `cursor_hash`.
- [x] cursor adulterado e cursor stale fail-closed.
- [x] resume sem repetir itens concluídos.
- [x] zero duplicidade entre batches; cobertura exata do cohort.
- [x] `cohort_hash` e `batch_hash` determinísticos.
- [x] `persists_checkpoint=false`; não existe executor.
- [x] writer/migration/execution false.
- [x] **37 assertions PASS + lint PASS**.

### Próximos subgates

- [ ] T087 Canário controlado e rollback comprovado.
- [ ] T088 Runbook de produção.
- [ ] T089 Autorização explícita posterior para qualquer writer real.

## Regra para T087

T087 pode ter sua preparação/readiness implementada sem writer. **A execução real do canário não está autorizada por este avanço.** Antes de qualquer mutação são obrigatórios journal durável, stale recheck imediatamente antes do write, escopo canário mínimo, rollback comprovável e autorização humana explícita.

## Regras constitucionais

1. PASS de T081–T086 não autoriza persistência editorial.
2. Journal durável é obrigatório antes do primeiro caminho mutável.
3. Stale-source deve ser revalidado imediatamente antes de qualquer write futuro.
4. Writer/migration permanecem disabled-by-default até canário, rollback e autorização explícita.
5. UX-002 é contrato visual obrigatório e não pode regredir.
6. Trabalho incompleto permanece fora da `main` até gates e revisão.

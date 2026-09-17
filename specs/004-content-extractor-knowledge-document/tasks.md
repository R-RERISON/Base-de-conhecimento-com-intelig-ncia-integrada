# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: **PASS / CLOSED / PROMOVIDO PARA `main`** com KD 2.1.0.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS AMBIENTAL**.
- T082–T086: **PASS LOCAL / CONTRATUAL**, com T081 também comprovado ambientalmente.
- T083B Journal Durable Storage: **PASS LOCAL / SMOKE AMBIENTAL PENDENTE** em `0.4.0-g245-canary-prep.1`.
- T087A Canary Readiness: **PASS LOCAL / READ-ONLY**.
- T087B-prep Exclusive Migration Lock: **PASS LOCAL**.
- T087 canário mutável + rollback real: **BLOCKED / NÃO EXECUTADO**.
- T088 Runbook: **FROZEN PROCEDURE / EXECUTION BLOCKED**.
- T089 autorização/release de writer: NOT_RUN.
- G-250: NOT_RUN.

## Baseline visual obrigatória

A branch G-245 foi sincronizada com `main@6d0fc8e33f826ee957038483d22fa1b804bae056` no commit `145e16bf31f7afe2d3f08d087b79b69f3f40b885`, preservando integralmente a UX-002 homologada.

Arquivos visuais canônicos devem permanecer fora do diff G-245 vs `main`: `class-admin-page.php`, `class-classification-admin.php`, `visual-foundation.css` e `class-visual-foundation.php`.

## S006 — G-245 / Elementor Normalization & Production Readiness

**Regra:** nenhum PASS abaixo autoriza escrita editorial por si só.

### T080 — Production Preflight — PASS WITH REVIEW ITEMS

- [x] Preflight read-only; blockers 0; writer/migration false.
- [x] `faq_wd` classificado como legacy orphan e `wpt` como dependência legada desconhecida.
- [x] Evidência: `evidence/g245-preflight-summary-20260916T215612Z.json`.

### T081 — Projection Plan — PASS AMBIENTAL

- [x] Contrato determinístico congelado.
- [x] 58 assertions locais PASS.
- [x] Duas passagens 622/622.
- [x] Zero errors/throwables/mismatches/violations; fingerprint preservado; `gate.t081_pass=true`.
- [x] Evidência: `evidence/g245-projection-summary-20260917T111009Z.json`.

### T082 — Gateway — PASS LOCAL / CONTRATUAL

- [x] Elementor `4.1.0` homologado; ausente => blocking; versão divergente => review_required.
- [x] feature flag default false + capability + hard phase gate.
- [x] 45 assertions PASS; writer/migration false.

### T083/T084 — Journal contract + Stale-source — PASS LOCAL / CONTRATUAL

- [x] write-ahead/capsule/hashes/transições/rollback/idempotência.
- [x] fresh/stale/blocking fail-closed.
- [x] 38 assertions combinadas PASS.

### T085 — Dry-run — PASS LOCAL / CONTRATUAL

- [x] ready/review_required/noop/blocked.
- [x] review_required não simula journal/apply.
- [x] determinismo/hash e safety invariants.
- [x] 38 assertions PASS + lint PASS.

### T086 — Batches retomáveis — PASS LOCAL / CONTRATUAL

- [x] cohort determinístico, dedupe, cursor versionado, resume e stale/tamper fail-closed.
- [x] zero duplicidade e cobertura exata do cohort.
- [x] não existe executor; execution/writer/migration false.
- [x] 37 assertions PASS + lint PASS.

### T083B — Journal Durable Storage — PASS LOCAL / SMOKE PENDENTE

- [x] storage WordPress-first escolhido: postmeta privado append-only `_bdc_kb_migration_journal`.
- [x] custom table/options/comments/file storage rejeitados por princípio de negação.
- [x] rollback capsule em Base64 para round-trip byte-exato, mantendo hashes sobre payload original.
- [x] cadeia linear por `parent_event_id`; fork/retry stale bloqueados.
- [x] `manage_options`, post existente, limite de payload e readback obrigatório.
- [x] falha de readback tenta remover imediatamente o evento recém-criado.
- [x] **22/22 assertions locais PASS + lint PASS**.
- [x] smoke ambiental implementado e **desabilitado por padrão**.
- [ ] executar smoke em homologação e obter `gate.t083b_storage_pass=true`.

Artefatos: `journal-storage-contract-v1.md`, `class-elementor-migration-journal-store.php`, `class-elementor-migration-journal-smoke.php`, `tests/unit/spec004-journal-durable-store.php`.

### T087A — Canary Readiness — PASS LOCAL / READ-ONLY

- [x] escopo 1, journal durável, capsule íntegro, dry-run ready, source fresh, versão homologada e identidade dos hashes.
- [x] mesmo com autorização simulada, `execution_allowed=false` e executor separado obrigatório.
- [x] 25 assertions PASS.

### T087B-prep — Exclusive Migration Lock — PASS LOCAL

- [x] postmeta privado único `_bdc_kb_migration_lock`.
- [x] aquisição exclusiva com `add_post_meta(..., true)`.
- [x] token obrigatório para release; release repetido idempotente.
- [x] TTL 30–900s; lock expirado não sofre takeover automático.
- [x] **18/18 assertions PASS + lint PASS**.
- [x] lock não concede writer/migration.

Artefatos: `migration-lock-contract-v1.md`, `class-elementor-migration-lock.php`, `tests/unit/spec004-migration-lock.php`.

### T088 — Runbook — FROZEN PROCEDURE / EXECUTION BLOCKED

- [x] sequência freeze → journal → stale recheck → write → verificação → rollback definida.
- [x] abort conditions, Authorization Pack, batches e produção definidos.
- [x] primeiro canário exige rollback real comprovado.
- [x] `t088-production-runbook-v1.md` congelado.

### Próximos subgates

- [ ] validar T083B ambientalmente em homologação.
- [ ] T087C: menor executor mutável version-gated, **disabled-by-default**, sem habilitá-lo.
- [ ] selecionar candidato canário de baixo risco e gerar Authorization Pack.
- [ ] obter autorização específica para 1 canário.
- [ ] executar canário + rollback real e fechar T087.
- [ ] T089: somente após evidências, decidir autorização de writer/release e eventual escalada para batches.

## Regras constitucionais

1. Journal durável deve existir antes de qualquer byte editorial alterado.
2. Stale-source deve ser revalidado imediatamente antes do write.
3. Lock exclusivo é obrigatório do freeze até commit/rollback operacional.
4. Writer/migration permanecem disabled-by-default.
5. Nenhuma autorização genérica de continuidade equivale à autorização do canário específico.
6. UX-002 não pode regredir.
7. Trabalho incompleto permanece fora da `main` até gates e revisão.

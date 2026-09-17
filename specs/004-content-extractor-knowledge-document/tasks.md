# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: **PASS / CLOSED / PROMOVIDO PARA `main`** com KD 2.1.0.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT.
- T080 Production Preflight: **PASS WITH REVIEW ITEMS**.
- T081 Projection Plan read-only: **PASS ambiental** em `0.4.0-g245-projection.2`.
- T082 Elementor Gateway version-gated: **PASS LOCAL / CONTRATUAL**.
- T083 Journal/rollback: **PASS LOCAL / CONTRATUAL**.
- T084 Stale-source guard: **PASS LOCAL / CONTRATUAL**.
- T085 Dry-run zero-write: **PASS LOCAL / CONTRATUAL** em `0.4.0-g245-dryrun.1`.
- T086 Batches retomáveis: **NEXT / NOT_STARTED**.
- G-250: NOT_RUN.

## Baseline visual obrigatória

A branch G-245 foi sincronizada com `main@6d0fc8e33f826ee957038483d22fa1b804bae056` no commit `145e16bf31f7afe2d3f08d087b79b69f3f40b885`, preservando integralmente a UX-002 homologada.

Os arquivos visuais canônicos permanecem fora do diff G-245 vs `main`, incluindo `class-admin-page.php`, `class-classification-admin.php`, `visual-foundation.css` e `class-visual-foundation.php`.

## S005 — G-240 / Real Content Acceptance

- [x] Content Extractor/KD v2 read-only.
- [x] KD 2.1.0 com relationship fidelity e inferência conservadora.
- [x] Full-corpus em duas passagens sem mutação editorial.
- [x] A/B humano 8/8 PASS.
- [x] Promovido para `main` pelo merge `32a696386bf2ab5574d4d7725db78636fa51f36c`.

Evidências finais:

- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`;
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## S006 — G-245 / Elementor Normalization & Production Readiness

**Regra:** nenhuma escrita editorial está autorizada por T080–T085.

### T080 — Production Preflight — PASS WITH REVIEW ITEMS

- [x] Preflight read-only.
- [x] Inventário de ambiente/shortcodes/providers.
- [x] `writer_allowed=false` e `migration_execution_allowed=false`.
- [x] Evidência ambiental versionada.

Evidência: `evidence/g245-preflight-summary-20260916T215612Z.json`.

### T081 — Projection Plan read-only — PASS

- [x] Contrato `elementor-projection-plan-contract-v1.md` congelado.
- [x] Projeção determinística por `source_kind` sem persistência.
- [x] `source_hash_before`, projection hash, warnings e `requires_review`.
- [x] Shortcodes opacos; nunca `do_shortcode()`.
- [x] 58 assertions locais PASS.
- [x] Duas passagens ambientais 622/622.
- [x] Zero errors/throwables/mismatches/violations; fingerprint preservado; `gate.t081_pass=true`.

Evidência: `evidence/g245-projection-summary-20260917T111009Z.json`.

### T082 — Elementor Gateway version-gated — PASS LOCAL / CONTRATUAL

- [x] Contrato `elementor-gateway-contract-v1.md` congelado.
- [x] versão homologada exata `4.1.0`.
- [x] Elementor ausente => blocking; versão diferente => review_required.
- [x] feature flag default `false` + capability futura `manage_options` + hard phase gate.
- [x] mesmo com versão/flag/capability válidas, `writer_allowed=false`.
- [x] 45 assertions locais PASS.
- [x] zero alteração nos arquivos visuais homologados.

### T083 — Journal / rollback — PASS LOCAL / CONTRATUAL

- [x] Contrato `journal-rollback-contract-v1.md` congelado.
- [x] estados `prepared`, `applied`, `partial_failure`, `rolled_back`.
- [x] capsule de rollback com integridade por hash.
- [x] rollback bloqueia target alterado ou capsule adulterado.
- [x] rollback repetido é idempotent noop.
- [x] `journal_persisted=false`; storage durável ainda não escolhido.
- [x] writer/migration false.

### T084 — Stale-source guard — PASS LOCAL / CONTRATUAL

- [x] Contrato `stale-source-guard-contract-v1.md` congelado.
- [x] compara `source_hash_before` vs Knowledge Document atual.
- [x] estados fresh/stale/blocking.
- [x] mismatch/hash inválido falham fechado antes de qualquer write.
- [x] combinado com T083: 38 assertions locais PASS.

### T085 — Dry-run zero-write — PASS LOCAL / CONTRATUAL

- [x] Contrato `migration-dry-run-contract-v1.md` congelado.
- [x] consome Projection Plan + Gateway + Stale Guard.
- [x] estados ready/review_required/noop/blocked.
- [x] review_required não prepara journal nem simula apply.
- [x] stale/gateway blocking/plan unsafe falham fechado.
- [x] hash/JSON canônicos determinísticos.
- [x] `execution_allowed=false`, writer/migration false.
- [x] 38 assertions locais PASS + lint PASS.

### Próximos subgates

- [ ] T086 Batches retomáveis.
- [ ] T087 Canário controlado e rollback comprovado.
- [ ] T088 Runbook de produção.
- [ ] T089 Autorização explícita posterior para qualquer writer real.

## Regras constitucionais

1. Content Extractor/KD/Projection Plan permanecem read-only.
2. Determinismo sem fidelidade estrutural/hierárquica não é aceite.
3. `review_required` é limitação explícita, não erro silencioso.
4. PASS de T081–T085 não autoriza persistência editorial.
5. Journal durável é obrigatório antes do primeiro caminho mutável.
6. Stale-source deve ser revalidado imediatamente antes de qualquer write futuro.
7. Writer/migration Elementor permanecem disabled-by-default até subgates, rollback e autorização explícita.
8. UX-002 é contrato visual obrigatório; nenhum subgate G-245 pode regredir a baseline homologada.
9. Trabalho incompleto permanece fora da `main` até gates e revisão.

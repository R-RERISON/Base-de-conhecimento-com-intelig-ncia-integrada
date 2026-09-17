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
- T082 Elementor Gateway version-gated: **NEXT / NOT_STARTED**.
- G-250: NOT_RUN.

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

**Regra:** nenhuma escrita editorial está autorizada por T080/T081.

### T080 — Production Preflight — PASS WITH REVIEW ITEMS

- [x] Preflight read-only.
- [x] Inventário de ambiente/shortcodes/providers.
- [x] `writer_allowed=false` e `migration_execution_allowed=false`.
- [x] Evidência ambiental versionada.

Evidência: `evidence/g245-preflight-summary-20260916T215612Z.json`.

### T081 — Projection Plan read-only — PASS

- [x] T081A Contrato `elementor-projection-plan-contract-v1.md` congelado.
- [x] T081B Projeção determinística por `source_kind` sem persistência.
- [x] T081C Schema/version, `source_hash_before`, strategy, projection hash, warnings e `requires_review`.
- [x] T081D Shortcodes como dependências opacas; nunca `do_shortcode()`.
- [x] T081E Review policy integral para casos críticos.
- [x] T081F Canonicalização/hash determinístico.
- [x] T081G 58 assertions locais PASS + lint PASS.
- [x] T081H Runner ambiental full-corpus endurecido.
- [x] T081I Executado em homologação: 622/622 + 622/622.
- [x] T081J Gate fechado: zero errors/throwables/mismatches/violations, fingerprint preservado e `gate.t081_pass=true`.

Evidência:

- `evidence/g245-projection-summary-20260917T111009Z.json`;
- raw recebido SHA-256 `b342490b15999e0b64e48fa7f18f38f442efc924f8bab6b96569027be7106d57`;
- plan status: 503 projectable, 84 review_required, 33 native_noop, 2 not_applicable;
- changed posts: 0;
- writer/migration: false.

### Próximos subgates

- [ ] T082 Elementor Gateway version-gated com writer **disabled-by-default**.
- [ ] T083 Journal/rollback.
- [ ] T084 Stale-source guard.
- [ ] T085 Dry-run.
- [ ] T086 Batches retomáveis.
- [ ] T087 Canário controlado e rollback comprovado.
- [ ] T088 Runbook de produção.
- [ ] T089 Autorização explícita posterior para qualquer writer real.

## Regras constitucionais

1. Content Extractor/KD/Projection Plan permanecem read-only.
2. Determinismo sem fidelidade estrutural/hierárquica não é aceite.
3. DOM explícito vence inferência.
4. Inferência textual deve ser conservadora e auditável.
5. `review_required` é limitação explícita, não erro silencioso.
6. PASS de T081 não autoriza persistência editorial.
7. Writer/migration Elementor permanecem disabled-by-default até subgates, rollback e autorização explícita.
8. Trabalho incompleto permanece fora da `main` até gates e revisão.

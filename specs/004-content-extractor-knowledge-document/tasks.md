# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240/v1: FAIL CONTROLADO — perda estrutural.
- G-240/v2/KD 2.0.1: full-corpus técnico PASS, porém aceite humano A/B FAIL CONTROLADO — HIERARCHY FIDELITY.
- KD 2.1.0 / build `0.4.0-acceptance.12`: full-corpus técnico PASS + aceite humano 8/8 PASS.
- **G-240: PASS / CLOSED / PROMOVIDO PARA `main`.**
- merge G-240: `32a696386bf2ab5574d4d7725db78636fa51f36c`.
- **G-245: IN PROGRESS somente em `spec004-g245-production-readiness`; PR #4 DRAFT.**
- G-250: NOT_RUN.

## S005 — G-240 / Real Content Acceptance

### Concluído

- [x] Implementar e validar Content Extractor/KD v2 read-only.
- [x] Corrigir colisões de IDs estruturais.
- [x] Preservar structural anchors de listas.
- [x] Introduzir expectativa semântica DOM independente.
- [x] Corrigir materialização de listas/tabelas no Legacy adapter.
- [x] Atingir full-corpus PASS no KD `2.0.1`.
- [x] Executar A/B humano nos mesmos oito posts do G-240 v1.
- [x] Registrar análise de hierarchy fidelity.
- [x] Congelar contrato `knowledge-document-contract-v2.1.0.md`.
- [x] Implementar relationship fidelity independente de contagens.
- [x] Implementar `Numbered_Hierarchy_Resolver` conservador.
- [x] Adicionar proveniência/confiança de hierarquia.
- [x] Fazer DOM explícito vencer inferência textual.
- [x] Tratar ambiguidade/conflito como `review_required`.
- [x] Corrigir acceptance gate para não confundir `review_required` com `not_ready`.
- [x] Executar testes sintéticos de árvore, numeração, conflito e ambiguidade.
- [x] Reexecutar full-corpus KD 2.1.0 / `0.4.0-acceptance.12`.
- [x] Reexecutar os mesmos oito casos A/B.
- [x] Fechar G-240 com zero mutação editorial.
- [x] Promover PR #3 para `main`.

### Evidências finais KD 2.1

- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`.
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`.
- full-corpus: 622→622, duas passagens 622/622, zero errors/throwables/hash/canonical mismatch, zero structure_incomplete, zero `not_ready`.
- A/B: 8/8 coverage, 8/8 order, 8/8 no invented text, 8/8 structure preserved, 8/8 gate_pass.

## S006 — G-245 / Elementor Normalization & Production Readiness

**Status: IN PROGRESS — somente subgates governados; nenhuma escrita editorial autorizada.**

Branch:

`spec004-g245-production-readiness`

PR:

`#4` — DRAFT.

### T080 — Production Preflight — PASS WITH REVIEW ITEMS

- [x] Criar branch dedicada baseada no head aprovado de G-240.
- [x] Implementar `Production_Preflight` read-only.
- [x] Avaliar WordPress/PHP/DOM/Elementor/backup/shortcodes/cron/loopback.
- [x] Inventariar plugins ativos e dependências observáveis de shortcodes sem exportar corpo editorial.
- [x] Forçar `writer_allowed=false` e `migration_execution_allowed=false`.
- [x] Validar localmente política/lint/package.
- [x] Executar preflight em homologação.
- [x] Registrar evidência e matriz inicial de compatibilidade.

Evidência na branch G-245:

- `evidence/g245-preflight-summary-20260916T215612Z.json`;
- blockers: 0;
- review items: `faq_wd`, `wpt` e loopback não testado;
- corpus 622→622;
- fingerprint editorial preservado.

### T081 — Projection Plan read-only

**Status: implementação iniciada na branch G-245; aceite/gate ainda não fechado.**

- [ ] Congelar contrato final do Projection Plan.
- [ ] Confirmar projeção determinística por `source_kind` sem persistência.
- [ ] Confirmar `source_hash_before`, schema, strategy, projection hash, warnings e `requires_review`.
- [ ] Preservar shortcodes como dependências opacas; nunca executar `do_shortcode()` genericamente.
- [ ] Forçar revisão para dependências/compatibilidade não resolvidas.
- [ ] Comprovar canonicalização e repetibilidade.
- [ ] Fechar testes locais de estratégias e zero-write.
- [ ] Executar full-corpus ambiental em duas passagens e versionar evidência.

### Próximos subgates

- [ ] Elementor Gateway version-gated com writer disabled-by-default.
- [ ] Journal/rollback.
- [ ] Stale-source guard.
- [ ] Dry-run.
- [ ] Batches retomáveis.
- [ ] Canário controlado e rollback comprovado.
- [ ] Runbook de produção.
- [ ] Autorização explícita posterior para qualquer writer real.

## Regras constitucionais

1. Content Extractor/KD são read-only.
2. Determinismo sem fidelidade estrutural/hierárquica não é aceite.
3. DOM explícito vence inferência.
4. Inferência textual deve ser conservadora e auditável.
5. `review_required` é limitação explícita, não `not_ready` automático.
6. G-240 PASS não autoriza persistência editorial.
7. Preflight e Projection Plan nunca autorizam escrita por efeito colateral.
8. Writer/migration Elementor permanecem disabled-by-default até subgates, rollback e autorização explícita.
9. `main` deve permanecer um baseline conhecido; trabalho incompleto fica em branch dedicada.

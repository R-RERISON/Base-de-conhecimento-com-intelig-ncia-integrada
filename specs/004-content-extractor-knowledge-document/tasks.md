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
- baseline institucional `main`: `422de89f5e341204b7549116cc2022fbc978f3ab`.
- **G-245: IN PROGRESS somente em `spec004-g245-production-readiness`; PR #4 DRAFT.**
- G-250: NOT_RUN.

## S005 — G-240 / Real Content Acceptance

### Concluído

- [x] Implementar e validar Content Extractor/KD v2 read-only.
- [x] Corrigir colisões de IDs estruturais.
- [x] Preservar structural anchors de listas.
- [x] Introduzir expectativa semântica DOM independente.
- [x] Corrigir materialização de listas/tabelas no Legacy adapter.
- [x] Fechar KD 2.1.0 com relationship fidelity, proveniência/confiança e inferência conservadora.
- [x] Executar full-corpus em duas passagens sem mutação editorial.
- [x] Executar A/B humano nos mesmos oito posts.
- [x] Atingir 8/8 structure preserved e 8/8 gate_pass.
- [x] Promover PR #3 para `main`.

Evidências finais:

- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`;
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## S006 — G-245 / Elementor Normalization & Production Readiness

**Status: IN PROGRESS — somente subgates governados; nenhuma escrita editorial autorizada.**

Branch: `spec004-g245-production-readiness`  
PR: `#4` — DRAFT / NÃO MERGEAR enquanto gates aplicáveis estiverem abertos.

### T080 — Production Preflight — PASS WITH REVIEW ITEMS

- [x] T080A Criar branch dedicada.
- [x] T080B Implementar `Production_Preflight` read-only.
- [x] T080C Avaliar WordPress/PHP/DOM/Elementor/backup/shortcodes/cron/loopback.
- [x] T080D Homologar inicialmente Elementor `4.1.0` e tratar versões desconhecidas como review.
- [x] T080E Inventariar plugins/shortcodes sem exportar corpo editorial.
- [x] T080F Forçar `writer_allowed=false` e `migration_execution_allowed=false`.
- [x] T080G Fechar testes locais.
- [x] T080H Gerar package e validar lint/integridade/paridade.
- [x] T080I Executar preflight em homologação.
- [x] T080J Versionar evidência e matriz de compatibilidade.

Evidência:

- `evidence/g245-preflight-summary-20260916T215612Z.json`;
- blockers: 0;
- review items: `faq_wd`, `wpt` e loopback não testado;
- corpus 622→622;
- fingerprint editorial preservado.

### T081 — Projection Plan read-only

**Status: IN PROGRESS — implementação/runner já existem; gate ambiental ainda não fechado.**

- [x] T081A Congelar contrato `elementor-projection-plan-contract-v1.md`.
- [x] T081B Implementar projeção determinística por `source_kind` sem persistência.
- [x] T081C Emitir schema/version, `source_hash_before`, strategy, projection hash, warnings e `requires_review`.
- [x] T081D Preservar shortcodes como dependências opacas; nunca executar `do_shortcode()`.
- [ ] T081E Fechar integralmente as regras de `requires_review` do contrato para dependências, warnings dinâmicos/unsupported e compatibilidade não resolvida.
- [x] T081F Implementar canonicalização e hash determinístico.
- [ ] T081G Reexecutar e ampliar testes locais de estratégias, repetibilidade e zero-write após correções finais.
- [x] T081H Implementar runner ambiental temporário full-corpus em duas passagens, sem exportar conteúdo editorial bruto.
- [ ] T081I Executar runner em homologação e versionar evidência full-corpus.
- [ ] T081J Fechar gate T081 somente com 622/622 nas duas passagens, zero errors/throwables/hash mismatch/canonical mismatch, zero writer violations, zero legacy shortcode review violations e fingerprint editorial preservado.

### Próximos subgates após T081

- [ ] T082 Elementor Gateway version-gated com writer disabled-by-default.
- [ ] T083 Journal/rollback.
- [ ] T084 Stale-source guard.
- [ ] T085 Dry-run.
- [ ] T086 Batches retomáveis.
- [ ] T087 Canário controlado e rollback comprovado.
- [ ] T088 Runbook de produção.
- [ ] T089 Autorização explícita posterior para qualquer writer real.

## Regras constitucionais

1. Content Extractor/KD são read-only.
2. Determinismo sem fidelidade estrutural/hierárquica não é aceite.
3. DOM explícito vence inferência.
4. Inferência textual deve ser conservadora e auditável.
5. `review_required` é limitação explícita, não `not_ready` automático.
6. G-240 PASS não autoriza persistência editorial.
7. Preflight e Projection Plan nunca autorizam escrita por efeito colateral.
8. Writer/migration Elementor permanecem disabled-by-default até subgates, rollback e autorização explícita.
9. `main` deve permanecer baseline conhecido; trabalho incompleto fica em branch dedicada.

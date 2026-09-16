# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240/v1: FAIL CONTROLADO — perda estrutural.
- G-240/v2/KD 2.0.1: full-corpus técnico PASS, porém aceite humano A/B **FAIL CONTROLADO — HIERARCHY FIDELITY**.
- KD 2.1.0 / build `0.4.0-acceptance.12`: full-corpus técnico PASS + aceite humano 8/8 PASS.
- **G-240: PASS / CLOSED.**
- **G-245: IN PROGRESS — T080 preflight concluído; Projection Plan read-only aberto.**
- G-250: NOT_RUN.

## S005 — G-240 / Real Content Acceptance

### Concluído

- [x] Implementar e validar Content Extractor/KD v2 read-only.
- [x] Corrigir colisões de IDs estruturais.
- [x] Preservar structural anchors de listas.
- [x] Introduzir expectativa semântica DOM independente.
- [x] Corrigir materialização de listas/tabelas no Legacy adapter.
- [x] Atingir full-corpus PASS no `0.4.0-acceptance.11` / KD `2.0.1`.
- [x] Executar A/B humano nos mesmos oito posts do G-240 v1.
- [x] Versionar `evidence/g240-v2-acceptance-20260916T153610Z.json`.
- [x] Registrar `g240-v2-hierarchy-gap-analysis-20260916.md`.

### Baseline humana que motivou KD 2.1

- 8/8 revisados;
- cobertura completa 8/8;
- ordem preservada 8/8;
- nenhum texto inventado 8/8;
- estrutura preservada 5/8;
- estrutura perdida 3/8: 1290, 370, 1307;
- stale 0;
- repeatability failure 0;
- sample mismatch 0;
- gate=false.

### Evolução — KD `2.1.0`

- [x] T079F Congelar contrato `knowledge-document-contract-v2.1.0.md` para hierarchy fidelity.
- [x] T079G Implementar expected/actual relationship fidelity independente de contagens: parent edges, max depth, sibling order e tree signature.
- [x] T079H Implementar `Numbered_Hierarchy_Resolver` conservador para `1`, `1.1`, `1.2`, `1.2.1`, sem cruzar source + `heading_path`.
- [x] T079I Adicionar proveniência/confiança de hierarquia: `explicit_dom|numbering_inferred|heading_inferred|flat` e `authoritative|deterministic|ambiguous`.
- [x] T079J Fazer DOM explícito vencer numeração; conflito → `HIERARCHY_NUMBERING_CONFLICT` + `review_required`.
- [x] T079K Sinal hierárquico forte não resolvido → `HIERARCHY_AMBIGUOUS`; nunca `candidate_ready`.
- [x] T079L Corrigir acceptance gate: `review_required` humanamente aprovado não falha automaticamente; `not_ready` continua bloqueante.
- [x] T079M Testes sintéticos de árvore explícita, numeração resolvível, conflito DOM×numeração, ambiguidade, token isolado e IPv4 não confundido com outline.
- [x] T079N Reexecutar full-corpus com KD 2.1.0 / `0.4.0-acceptance.12`; 622/622 em duas passagens, zero errors/throwables/hash/canonical mismatch, zero structure_incomplete, zero not_ready e gate técnico PASS.
- [x] T079O Reexecutar os mesmos oito casos A/B; 8/8 human_pass e 8/8 gate_pass.
- [x] T079P Fechar G-240 com 8/8 estrutura humana preservada, `gate_pass=true`, zero stale/repeatability/sample mismatch, zero `not_ready` e zero mutação editorial.

### Evidências finais KD 2.1

- `evidence/kd-v21-smoke-summary-20260916T172538Z.json` — resumo verificável do full-corpus; SHA-256 do artefato bruto registrado no arquivo.
- `evidence/g240-kd21-acceptance-20260916T193359Z.json` — aceite humano final 8/8 PASS.
- full-corpus: corpus 622→622, duas passagens 622/622, zero erros, zero throwables, zero hash mismatch, zero canonical JSON mismatch, zero structure_incomplete, zero `not_ready`.
- A/B: 8/8 coverage, 8/8 order, 8/8 no invented text, 8/8 structure preserved, 8/8 gate_pass.
- posts 1290, 370 e 1307, que motivaram KD 2.1, passaram `structure_preserved=true`.
- `review_required` permanece explícito em casos conservadores; não é tratado como `not_ready`.

## S006 — G-245 / Elementor Normalization & Production Readiness

**Status: IN PROGRESS — somente subgates read-only autorizados.**

### T080 — Production Preflight — PASS WITH REVIEW ITEMS

- [x] T080A Criar branch dedicada `spec004-g245-production-readiness` baseada no head aprovado de G-240.
- [x] T080B Implementar `Production_Preflight` sem persistência, sem execução de shortcodes e sem rede externa.
- [x] T080C Implementar avaliação determinística `compatible|review_required|blocking` para WordPress/PHP/DOM/Elementor/backup/shortcodes/cron/loopback.
- [x] T080D Homologar inicialmente apenas Elementor `4.1.0`; versão desconhecida => `review_required`; Elementor ausente => `blocking` para migration.
- [x] T080E Adicionar inventário read-only de plugins ativos e dependências observáveis de shortcodes, sem exportar corpo editorial.
- [x] T080F Garantir `writer_allowed=false` e `migration_execution_allowed=false` independentemente do resultado do preflight.
- [x] T080G Adicionar testes locais de política; lint PASS e 7/7 cenários PASS.
- [x] T080H Gerar package `0.4.0-g245-preflight.1`; 29/29 PHP lint PASS, ZIP integrity PASS e paridade Git PASS.
- [x] T080I Executar preflight em homologação e versionar evidência: zero blockers, dois review items, corpus 622→622 e fingerprint editorial idêntico.
- [x] T080J Classificar gaps e congelar `g245-compatibility-matrix-v1.md`.

#### Evidência T080

- `evidence/g245-preflight-summary-20260916T215612Z.json`.
- raw artifact SHA-256: `5b9e561b0d0053d6a4fe8fbcbc16cf45107620b1bd72d48517b17e54371c80da`.
- runtime: WordPress `6.9.4`, PHP `8.5.10`, Elementor `4.1.0`, MariaDB `12.2.2`, DOMDocument ativo, WP-Cron habilitado.
- blockers: 0.
- review items: shortcodes legados sem handler (`faq_wd`, `wpt`) e loopback não testado.
- `faq_wd`: classificado como legacy orphan; origem histórica externa compatível com 10WebFAQ/FAQ WD, ausente do runtime ativo.
- `wpt`: origem não comprovada; permanece unknown legacy dependency.
- esses gaps não bloqueiam Projection Plan read-only, mas continuam impedindo execução dinâmica/writer.

### T081 — Projection Plan read-only

- [ ] T081A Congelar contrato `elementor-projection-plan-contract-v1.md`.
- [ ] T081B Implementar projeção determinística por `source_kind` sem persistência.
- [ ] T081C Emitir `source_hash_before`, `projection_schema_version`, `projection_strategy`, `projection_hash`, warnings e `requires_review`.
- [ ] T081D Preservar shortcodes como dependências opacas; nunca executar `do_shortcode()`.
- [ ] T081E Forçar `requires_review=true` quando houver `faq_wd`, `wpt`, shortcode sem handler, conteúdo dinâmico ou compatibilidade `review_required`.
- [ ] T081F Garantir plano canônico e repetível para o mesmo source hash.
- [ ] T081G Adicionar testes de estratégias native/projectable/review_required e zero-write.
- [ ] T081H Criar runner ambiental temporário para full-corpus de Projection Plan em duas passagens.

### Próximos subgates após T081

- [ ] Elementor_Gateway version-gated com writer disabled-by-default.
- [ ] Journal/rollback.
- [ ] Dry-run.
- [ ] Stale-source guard.
- [ ] Batches retomáveis.
- [ ] Canário e rollback.
- [ ] Runbook produção.

A abertura de G-245 autoriza apenas os subgates previstos. **Nenhum writer/migration deve ser habilitado antes de preflight, projection plan, dry-run, stale-source guard e critérios de rollback estarem aprovados.**

## Regras constitucionais

1. Content Extractor/KD são read-only.
2. Determinismo sem fidelidade hierárquica não é aceite.
3. Contagem estrutural não substitui validação de relações pai/filho.
4. Inferência textual só pode ser determinística, conservadora e auditável.
5. DOM explícito vence inferência.
6. `review_required` é limitação explícita, não sinônimo automático de `not_ready`.
7. G-240 PASS habilita a preparação de G-245, mas não autoriza persistência editorial automaticamente.
8. Writer/migration Elementor permanecem disabled-by-default até aprovação explícita dos subgates de G-245.
9. Preflight nunca é autorização de escrita; sua função é produzir fatos e bloqueios auditáveis.
10. Projection Plan é documento derivado read-only; sua existência nunca autoriza write por efeito colateral.

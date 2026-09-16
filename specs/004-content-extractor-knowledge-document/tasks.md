# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240/v1: FAIL CONTROLADO — perda estrutural.
- G-240/v2/KD 2.0.1: full-corpus técnico PASS, porém aceite humano A/B **FAIL CONTROLADO — HIERARCHY FIDELITY**.
- KD 2.1.0 / build `0.4.0-acceptance.12`: implementação pronta para validação ambiental.
- G-245: BLOCKED.
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

### Baseline humana

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
- [ ] T079N Reexecutar full-corpus com KD 2.1.0 / `0.4.0-acceptance.12`; analisar todos os `HIERARCHY_*` e atingir gate técnico aceitável.
- [ ] T079O Reexecutar os mesmos oito casos A/B.
- [ ] T079P Fechar G-240 somente após 8/8 estrutura humana preservada, `gate_pass=true` e limitações refletidas no readiness.

## S006 — G-245 / Elementor Normalization & Production Readiness

- [x] Contratos de Elementor/produção congelados.
- [ ] Production Preflight read-only.
- [ ] Matriz de compatibilidade.
- [ ] Projection Plan read-only.
- [ ] Elementor_Gateway version-gated com writer disabled-by-default.
- [ ] Journal/rollback.
- [ ] Dry-run.
- [ ] Stale-source guard.
- [ ] Batches retomáveis.
- [ ] Canário e rollback.
- [ ] Runbook produção.

**G-245 permanece BLOCKED por G-240.**

## Regras constitucionais

1. Content Extractor/KD são read-only.
2. Determinismo sem fidelidade hierárquica não é aceite.
3. Contagem estrutural não substitui validação de relações pai/filho.
4. Inferência textual só pode ser determinística, conservadora e auditável.
5. DOM explícito vence inferência.
6. `review_required` é limitação explícita, não sinônimo automático de `not_ready`.
7. Writer/migration Elementor continua proibido até G-240 PASS.

# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-001/002/003: concluídas.
- UX-002 `0.4.0-ux002.3`: **PASS / CLOSED / promovida para main** e contrato visual obrigatório.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- T080 Production Preflight: **PASS WITH REVIEW ITEMS**.
- T081 Projection Plan: **PASS AMBIENTAL**.
- T082 Gateway: **PASS LOCAL / CONTRATUAL**.
- T083 Journal/rollback: **PASS LOCAL / CONTRATUAL**.
- T084 Stale-source guard: **PASS LOCAL / CONTRATUAL**.
- T085 Dry-run: **PASS LOCAL / CONTRATUAL**.
- T086 Batches retomáveis: **PASS LOCAL / CONTRATUAL** em `0.4.0-g245-batch.1`.
- T087A Canary Readiness: **PASS LOCAL / READ-ONLY**.
- T087 canário mutável + rollback real: **BLOCKED / NÃO AUTORIZADO**.
- T088 Runbook: **NEXT**.
- writer/migration Elementor permanecem não autorizados.

## Baseline comprovada

`main`: `6d0fc8e33f826ee957038483d22fa1b804bae056`.
Sincronização G-245 + UX-002: `145e16bf31f7afe2d3f08d087b79b69f3f40b885`.

Os arquivos visuais homologados devem permanecer fora do diff: `class-admin-page.php`, `class-classification-admin.php`, `visual-foundation.css`, `class-visual-foundation.php`.

## Gates fechados

- T081: 622/622 em duas passagens, zero mutação, `gate.t081_pass=true`.
- T082: gateway version-gated, 45 assertions PASS.
- T083/T084: journal contract + stale guard, 38 assertions PASS.
- T085: dry-run zero-write, 38 assertions PASS.
- T086: batches determinísticos/retomáveis, 37 assertions PASS.
- T087A: readiness de canário read-only, 25 assertions PASS.

Todos mantêm `execution_allowed=false`, `writer_allowed=false` e `migration_execution_allowed=false` conforme aplicável.

## T087A — Canary Readiness

Pré-condições explícitas: escopo 1, journal storage durável, capsule íntegro, dry-run ready, source fresh, Elementor homologado, identidade/hashes coerentes e dry-run zero-write.

Sem autorização explícita: `awaiting_explicit_authorization`. Com autorização simulada: `ready_for_controlled_canary`, mas ainda `execution_allowed=false` e `requires_separate_mutable_executor=true`.

**O T087 completo NÃO PASSOU.** Faltam storage durável comprovado, executor mutável mínimo, escolha do artigo canário, autorização humana explícita, evidência before/write/after/rollback e restauração integral.

## Próximo passo exato

1. congelar T088 Runbook de homologação/produção;
2. decidir/implementar storage durável WordPress-first para journal e smoke próprio;
3. preparar pacote de homologação para readiness/storage, ainda sem writer;
4. somente depois solicitar autorização específica para executar **um** canário mutável e seu rollback.

## Guardrails

- UX-002 não pode regredir;
- journal write-ahead durável é obrigatório antes de write;
- stale-source deve ser revalidado imediatamente antes de write;
- canário inicial = 1 item;
- produção não é ambiente experimental;
- nenhuma autorização de writer será inferida de “continue”, “vamos em frente” ou equivalente;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

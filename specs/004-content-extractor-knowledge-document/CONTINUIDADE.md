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
- T085 Dry-run: **PASS LOCAL / CONTRATUAL** em `0.4.0-g245-dryrun.1`.
- T086 Batches retomáveis: **NEXT / NOT_STARTED**.
- writer/migration Elementor permanecem não autorizados.

## Baseline comprovada

`main`: `6d0fc8e33f826ee957038483d22fa1b804bae056`.

Sincronização G-245 + UX-002: `145e16bf31f7afe2d3f08d087b79b69f3f40b885`.

Após T085, compare com `main`: **48 commits à frente, 0 atrás**, sem qualquer um dos arquivos visuais homologados no diff.

Os seguintes arquivos visuais não podem aparecer no diff durante T08x:

- `class-admin-page.php`;
- `class-classification-admin.php`;
- `visual-foundation.css`;
- `class-visual-foundation.php`.

## T080 / T081

T080: PASS WITH REVIEW ITEMS, blockers 0, corpus/fingerprint preservados, writer/migration false.

T081: 622/622 em duas passagens, zero errors/throwables/mismatches/violations, fingerprint editorial idêntico, changed posts=0, `gate.t081_pass=true`, 44/44 checks independentes PASS.

Evidência: `evidence/g245-projection-summary-20260917T111009Z.json`.

## T082 — Gateway

- Elementor `4.1.0` homologado;
- ausente => blocking; diferente => review_required;
- feature flag default false;
- capability futura `manage_options`;
- hard phase gate;
- writer/migration sempre false;
- 45 assertions PASS.

## T083 — Journal / rollback

- write-ahead journal durável deve existir antes do primeiro write futuro;
- capsule before com `post_content` e `_elementor_data`;
- hashes de integridade;
- rollback stale/tampered bloqueado;
- rollback repetido idempotent noop;
- `journal_persisted=false` nesta fase.

## T084 — Stale-source guard

- `source_hash_before` vs Knowledge Document atual;
- fresh/stale/blocking;
- mismatch/hash inválido bloqueiam;
- T083+T084: 38 assertions PASS.

## T085 — Dry-run

- deterministic zero-write;
- ready/review_required/noop/blocked;
- review_required não simula journal/apply;
- stale e gateway blocking falham fechado;
- versão não homologada => review;
- unsafe plan => blocked;
- `execution_allowed=false`, writer/migration false;
- 38 assertions PASS + lint PASS.

Artefatos: `elementor-gateway-contract-v1.md`, `journal-rollback-contract-v1.md`, `stale-source-guard-contract-v1.md`, `migration-dry-run-contract-v1.md` e testes `spec004-*` correspondentes.

## Próximo passo exato — T086

Implementar **batches retomáveis ainda read-only**:

1. ordenação/deduplicação determinística dos candidatos elegíveis;
2. batch size explícito e limitado;
3. cursor/checkpoint versionado e validado;
4. resume determinístico sem repetir concluídos;
5. zero duplicidade entre batches;
6. batch hash canônico;
7. cursor inválido/stale falha fechado;
8. nenhum writer/executor editorial;
9. zero persistência em `post_content`/`_elementor_data`;
10. testes locais e contrato congelado antes do canário.

## Guardrails preservados

- WordPress/Elementor são a fonte editorial;
- Knowledge Document/Projection Plan são reconstruíveis;
- UX-002 não pode regredir;
- journal durável ainda é pré-condição para qualquer write;
- stale-source deverá ser revalidado imediatamente antes de qualquer write futuro;
- writer/migration permanecem proibidos;
- produção não é ambiente experimental;
- PR #4 permanece DRAFT enquanto G-245 não fechar integralmente.

## Reentrada obrigatória

Ler AGENTS, Manifesto, Constituição, SPEC-004, Visual Contract v2 e DoD; confirmar branch/commit/PR e diff visual limpo antes de modificar runtime.

> Quem não sabe onde está, não sabe para onde quer ir.

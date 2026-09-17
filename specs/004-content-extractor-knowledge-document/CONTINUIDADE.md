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

A branch deve permanecer `behind_by=0` e os seguintes arquivos visuais não podem aparecer no diff contra `main` durante T08x:

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
- ausente => blocking;
- diferente => review_required;
- feature flag default false;
- capability futura `manage_options`;
- hard phase gate;
- writer/migration sempre false em T082;
- 45 assertions PASS.

## T083 — Journal / rollback

Contrato storage-neutral fechado:

- write-ahead journal deve ser durável antes do primeiro write futuro;
- capsule before com `post_content` e `_elementor_data`;
- hashes de integridade;
- rollback stale/tampered bloqueado;
- rollback repetido idempotent noop;
- `journal_persisted=false` nesta fase.

## T084 — Stale-source guard

- `source_hash_before` vs Knowledge Document atual;
- fresh/stale/blocking;
- mismatch/hash inválido bloqueiam;
- zero-write;
- T083+T084: 38 assertions PASS.

## T085 — Dry-run

- deterministic zero-write;
- ready/review_required/noop/blocked;
- review_required não simula journal/apply;
- stale e gateway blocking falham fechado;
- versão Elementor não homologada => review;
- unsafe plan => blocked;
- `execution_allowed=false`, writer/migration false;
- 38 assertions PASS + lint PASS.

Artefatos:

- `elementor-gateway-contract-v1.md`;
- `journal-rollback-contract-v1.md`;
- `stale-source-guard-contract-v1.md`;
- `migration-dry-run-contract-v1.md`;
- `tests/unit/spec004-elementor-gateway.php`;
- `tests/unit/spec004-journal-stale-guards.php`;
- `tests/unit/spec004-migration-dry-run.php`.

## Próximo passo exato — T086

Implementar **batches retomáveis ainda read-only**.

T086 deve demonstrar no mínimo:

1. ordenação/deduplicação determinística dos candidatos elegíveis;
2. batch size explícito e limitado;
3. cursor/checkpoint versionado e validado;
4. resume determinístico sem repetir itens já concluídos;
5. zero duplicidade entre batches;
6. batch hash canônico;
7. cursor inválido/stale falha fechado;
8. nenhum writer/executor editorial;
9. zero persistência em `post_content`/`_elementor_data`;
10. testes locais e documentação antes de avançar ao canário.

## Guardrails preservados

- WordPress/Elementor são a fonte editorial;
- Knowledge Document e Projection Plan são reconstruíveis;
- UX-002 não pode regredir;
- journal durável ainda é pendência obrigatória antes de qualquer write;
- stale-source deverá ser revalidado imediatamente antes de qualquer write futuro;
- writer/migration permanecem proibidos;
- produção não é ambiente experimental;
- PR #4 permanece DRAFT enquanto G-245 não fechar integralmente.

## Reentrada obrigatória em novo chat

1. ler `AGENTS.md`;
2. ler `.specify/PROJECT_MANIFEST.md`;
3. ler `.specify/memory/constitution.md`;
4. ler `specs/004-content-extractor-knowledge-document/`;
5. ler `ux/002-mockup-visual-foundation/visual-contract-v2.md`;
6. ler `docs/DEFINITION-OF-DONE.md`;
7. confirmar branch/commit/PR no GitHub;
8. confirmar diff visual limpo contra main;
9. somente então avançar T086.

> Quem não sabe onde está, não sabe para onde quer ir.

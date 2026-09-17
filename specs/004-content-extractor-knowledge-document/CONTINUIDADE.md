# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-001/002/003: concluídas.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- T080 Production Preflight: **PASS WITH REVIEW ITEMS**.
- T081 Projection Plan: **PASS AMBIENTAL** em `0.4.0-g245-projection.2`.
- T082 Elementor Gateway version-gated: **NEXT / NOT_STARTED**.
- writer/migration Elementor permanecem não autorizados.

## Baseline comprovada

A `main` está em `72f26121373b12fa08f08ea8d38b4c8d73f8637c` e inclui a pasta `scr/` com referências visuais. A branch G-245 deve permanecer sincronizada sem descartar seus artefatos.

G-240 continua comprovado pelas evidências KD 2.1.0 e aceite humano 8/8.

## T080

Production Preflight: PASS WITH REVIEW ITEMS, blockers 0, corpus/fingerprint preservados, `writer_allowed=false`, `migration_execution_allowed=false`.

Evidência: `evidence/g245-preflight-summary-20260916T215612Z.json`.

## T081 — PASS fechado

Evidência ambiental recebida em 2026-09-17:

- WordPress 6.9.4;
- PHP 8.5.10;
- Elementor 4.1.0;
- corpus 622;
- duas passagens 622/622;
- zero errors/throwables;
- zero projection hash/canonical JSON mismatches;
- zero projection-hash/writer/safety/review-policy violations;
- fingerprint editorial idêntico before/after;
- changed posts = 0;
- `gate.t081_pass=true`;
- 44/44 checks independentes PASS.

Evidência versionada: `evidence/g245-projection-summary-20260917T111009Z.json`.
Raw recebido SHA-256: `b342490b15999e0b64e48fa7f18f38f442efc924f8bab6b96569027be7106d57`.

## Próximo passo exato — T082

Implementar **Elementor Gateway version-gated**, mas **sem writer real**.

T082 deve demonstrar no mínimo:

1. versão Elementor explicitamente suportada antes de qualquer caminho mutável;
2. writer desabilitado por default/feature flag;
3. capability e nonce definidos para qualquer futura ação administrativa;
4. nenhuma persistência durante os testes de T082;
5. falha fechada para versão desconhecida/incompatível;
6. contrato explícito para `source_hash_before` a ser consumido pelo stale-source guard de T084;
7. testes locais cobrindo allow/deny e zero-write;
8. documentação do que ainda falta para T083 journal/rollback.

T082 PASS **não** autoriza `_elementor_data` ou `post_content` write. A primeira capacidade realmente mutável só pode existir após gates subsequentes e autorização explícita.

## Guardrails preservados

- WordPress/Elementor são a fonte editorial;
- Knowledge Document e Projection Plan são projeções reconstruíveis;
- writer/migration permanecem proibidos;
- produção não é ambiente experimental;
- GO de homologação != GO de produção;
- PR #4 permanece DRAFT enquanto G-245 não fechar integralmente.

## Reentrada obrigatória em novo chat

1. ler `AGENTS.md`;
2. ler `.specify/PROJECT_MANIFEST.md`;
3. ler `.specify/memory/constitution.md`;
4. ler `specs/004-content-extractor-knowledge-document/`;
5. ler `docs/DEFINITION-OF-DONE.md`;
6. confirmar branch/commit/PR no GitHub;
7. somente então avançar T082.

> Quem não sabe onde está, não sabe para onde quer ir.

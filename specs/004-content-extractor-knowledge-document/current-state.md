# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- R-200/R-210/G-220: PASS.
- G-230/v1: PASS de determinismo / superseded for AI.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0 / `0.4.0-acceptance.12`: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em branch dedicada; PR #4 DRAFT.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS AMBIENTAL**.
- T082: **NEXT / NOT_STARTED**.
- G-250: NOT_RUN.

## Baseline `main`

A `main` avançou para `72f26121373b12fa08f08ea8d38b4c8d73f8637c` com referências visuais em `scr/`. A branch G-245 incorpora essa referência sem alterar seus guardrails.

## Ambiente homologado

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- MariaDB `12.2.2`;
- corpus: 622 posts;
- DOMDocument ativo;
- WP-Cron habilitado.

## G-245 — T080 Production Preflight

**PASS WITH REVIEW ITEMS.** Blockers: 0. `faq_wd` permanece legacy orphan; `wpt` permanece dependência legada desconhecida; loopback não foi testado no preflight v1. Writer/migration continuam false.

Evidência: `evidence/g245-preflight-summary-20260916T215612Z.json`.

## G-245 — T081 Projection Plan read-only

**PASS AMBIENTAL em `0.4.0-g245-projection.2`.**

Evidência: `evidence/g245-projection-summary-20260917T111009Z.json`.

Resultado comprovado:

- 622 posts;
- primeira passagem: 622/622;
- segunda passagem: 622/622;
- errors/throwables: 0;
- projection hash mismatches: 0;
- canonical JSON mismatches: 0;
- projection-hash violations: 0;
- writer violations: 0;
- safety violations: 0;
- legacy-shortcode review violations: 0;
- migration-warning review violations: 0;
- fingerprint editorial before/after: idêntico;
- changed posts: 0;
- `gate.t081_pass=true`;
- validação independente: 44/44 checks PASS.

Distribuição:

- projectable: 503;
- review_required: 84;
- native_noop: 33;
- not_applicable: 2.

O raw recebido possui SHA-256 `b342490b15999e0b64e48fa7f18f38f442efc924f8bab6b96569027be7106d57`.

## Próximo passo — T082

Projetar e implementar o **Elementor Gateway version-gated**, ainda com writer desabilitado por padrão. T082 não recebe autorização de escrita real. O gateway deve tornar explícitos contrato de versão, capability, feature flag, caminhos de negação e falhas seguras, preparando T083/T084 sem persistir conteúdo editorial.

## Guardrails

- WordPress/Elementor continuam fonte editorial;
- Content Extractor/KD/Projection Plan são derivados read-only;
- nenhum writer Elementor está autorizado;
- nenhuma migration Elementor está autorizada;
- produção não é ambiente experimental;
- GO de homologação != GO de produção;
- qualquer futura persistência exige version gate, journal/rollback, stale-source guard, dry-run, batches retomáveis, canário e autorização explícita.

> Quem não sabe onde está, não sabe para onde quer ir.

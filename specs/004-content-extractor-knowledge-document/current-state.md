# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 é contrato visual obrigatório.
- R-200/R-210/G-220: PASS.
- G-230/v1: PASS de determinismo / superseded for AI.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0 / `0.4.0-acceptance.12`: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em branch dedicada; PR #4 DRAFT.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS AMBIENTAL**.
- T082: **PASS LOCAL / CONTRATUAL**.
- T083: **NEXT / NOT_STARTED**.
- G-250: NOT_RUN.

## Baseline `main` e preservação visual

A `main` atual está em `6d0fc8e33f826ee957038483d22fa1b804bae056`, merge da UX-002 homologada (`0.4.0-ux002.3`).

A branch `spec004-g245-production-readiness` foi sincronizada manualmente com essa baseline no commit `145e16bf31f7afe2d3f08d087b79b69f3f40b885`, priorizando os arquivos da UX-002 como autoridade e conciliando apenas o bootstrap necessário ao G-245.

Validação pós-sync:

- branch G-245: ahead 31 / behind 0 imediatamente após o merge de baseline;
- `class-admin-page.php`, `class-classification-admin.php`, `visual-foundation.css`, `class-visual-foundation.php`, UX-002 e governança visual ficaram alinhados à `main`;
- o diff restante contra `main` contém apenas artefatos G-245;
- T082 não alterou nenhum arquivo visual.

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

## G-245 — T082 Elementor Gateway version-gated

**PASS LOCAL / CONTRATUAL em `0.4.0-g245-gateway.1`.**

Implementado:

- `Elementor_Gateway` isolado;
- contrato `elementor-gateway-contract-v1.md`;
- versão Elementor homologada exata `4.1.0`;
- ausência de Elementor => `blocking`;
- versão diferente => `review_required`;
- feature flag `BDC_KB_ELEMENTOR_WRITER_ENABLED` default `false`;
- capability futura `manage_options`;
- contrato explícito de action/nonce para futura mutação, sem handler writer registrado;
- `source_hash_before` como requisito do stale-source guard T084;
- hard gate de fase T082 em código impede qualquer writer mesmo com versão/flag/capability válidas;
- `writer_allowed=false` e `migration_execution_allowed=false` sempre;
- zero persistência, zero escrita em `post_content`, zero `_elementor_data`, zero rede e zero execução de shortcode.

Validação local:

- Gateway lint PASS;
- teste T082 lint PASS;
- bootstrap lint PASS;
- **45 assertions PASS**.

Artefatos: `elementor-gateway-contract-v1.md`, `package-g245-gateway1.md`, `tests/unit/spec004-elementor-gateway.php`.

## Próximo passo — T083

Projetar e implementar o **Journal/Rollback Contract** ainda sem ativar writer real.

T083 deve definir antes/depois, atomicidade possível, formato de journal, rollback idempotente, retenção/privacidade, correlação por execução e comportamento em falha parcial. A implementação deve permanecer sem mutação editorial até que o dry-run e os gates posteriores autorizem o caminho correto.

## Guardrails

- WordPress/Elementor continuam fonte editorial;
- Content Extractor/KD/Projection Plan são derivados read-only;
- nenhum writer Elementor está autorizado;
- nenhuma migration Elementor está autorizada;
- UX-002 permanece baseline visual obrigatória;
- produção não é ambiente experimental;
- GO de homologação != GO de produção;
- qualquer futura persistência exige version gate, journal/rollback, stale-source guard, dry-run, batches retomáveis, canário e autorização explícita.

> Quem não sabe onde está, não sabe para onde quer ir.

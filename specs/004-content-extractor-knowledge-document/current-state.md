# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS ambiental**.
- G-230/v1: **PASS de determinismo / superseded for AI**.
- G-240/v1: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2/KD 2.0.1: **PASS técnico / FAIL humano — hierarchy fidelity**.
- KD 2.1.0 / `0.4.0-acceptance.12`: **PASS técnico full-corpus + PASS humano 8/8**.
- **G-240: PASS / CLOSED / promovido para `main`.**
- **G-245: IN PROGRESS em branch dedicada; PR #4 permanece DRAFT.**
- G-250: NOT_RUN.

## Baseline `main`

- merge G-240: `32a696386bf2ab5574d4d7725db78636fa51f36c`;
- baseline institucional sincronizada: `422de89f5e341204b7549116cc2022fbc978f3ab`.

A branch `spec004-g245-production-readiness` foi sincronizada com essa baseline sem descarte do trabalho G-245. O trabalho incompleto continua isolado do runtime da `main`.

## Ambiente de homologação validado

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- MariaDB `12.2.2`;
- multisite: não;
- corpus: 622 posts;
- DOMDocument: ativo;
- WP-Cron: habilitado.

## Fechamento G-240 / KD 2.1

Full-corpus:

- corpus 622 → 622;
- duas passagens 622/622;
- errors: 0;
- throwables: 0;
- hash mismatches: 0;
- canonical JSON mismatches: 0;
- `structure_incomplete`: 0;
- `not_ready`: 0;
- fingerprint editorial before/after idêntico;
- changed posts: 0;
- gate técnico: PASS.

Readiness observada:

- candidate_ready: 387;
- review_required: 233;
- not_applicable: 2;
- not_ready: 0.

Aceite humano:

- 8/8 coverage;
- 8/8 order;
- 8/8 no invented text;
- 8/8 structure preserved;
- 8/8 human_pass;
- 8/8 gate_pass;
- stale/repeatability/sample mismatch: 0.

Evidências:

- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`;
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## G-245 — T080 Production Preflight

**PASS WITH REVIEW ITEMS.**

Evidência:

- `evidence/g245-preflight-summary-20260916T215612Z.json`;
- raw SHA-256: `5b9e561b0d0053d6a4fe8fbcbc16cf45107620b1bd72d48517b17e54371c80da`.

Resultado:

- blockers: 0;
- review items: 2;
- `faq_wd`: legacy orphan;
- `wpt`: unknown legacy dependency;
- loopback/network: não testado no preflight v1;
- corpus 622 → 622;
- fingerprint editorial preservado;
- zero persistência;
- zero execução de shortcode;
- zero escrita em `post_content`/`_elementor_data`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`.

Esses pontos não bloqueiam Projection Plan read-only, mas continuam bloqueando writer/migration até tratamento explícito.

## G-245 — T081 Projection Plan read-only

**LOCAL READY / ENVIRONMENTAL PENDING.**

Build de trabalho: `0.4.0-g245-projection.2`.

Estado técnico:

- contrato `elementor-projection-plan-contract-v1.md` congelado;
- `Elementor_Projection_Plan` read-only e determinístico;
- warnings estruturais/dinâmicos de migração agora forçam `requires_review=true` conforme contrato;
- `SHORTCODE_NOT_EXPANDED` de handler registrado permanece warning opaco sem overblocking automático;
- `faq_wd`, `wpt` e handlers ausentes continuam obrigatoriamente em review;
- `projection_hash` é SHA-256 canônico e depende de `source_hash_before`;
- todos os safety flags permanecem estritamente `false`;
- runner full-corpus foi endurecido para falhar em hash inválido, safety violation ou warning crítico sem review;
- teste local ampliado: **58 assertions PASS**;
- lint PHP dos artefatos alterados: PASS no ambiente local de validação.

Ainda não é permitido declarar T081 PASS porque falta evidência ambiental no WordPress de homologação.

Para fechar T081 ainda é obrigatório:

1. instalar/executar a build `0.4.0-g245-projection.2` em homologação;
2. executar o runner full-corpus em duas passagens;
3. comprovar 622/622 em ambas as passagens;
4. obter zero errors/throwables/hash mismatch/canonical mismatch;
5. obter zero projection-hash/safety/writer/review-policy violations;
6. comprovar corpus e fingerprint editorial inalterados;
7. versionar a evidência ambiental e as distribuições de status/strategy/warnings;
8. manter o PR #4 DRAFT até esse gate fechar.

## Guardrails

- Content Extractor/KD permanecem read-only;
- Projection Plan é derivado read-only e não autoriza escrita;
- nenhum writer Elementor está autorizado;
- nenhuma migration Elementor está autorizada;
- nenhum Knowledge Document/Projection Plan é persistido;
- produção não será ambiente experimental;
- GO de homologação não equivale a GO de produção;
- próximos writer-related subgates exigem version gate, journal/rollback, stale-source guard, dry-run, batches retomáveis e canário controlado.

> Quem não sabe onde está, não sabe para onde quer ir.

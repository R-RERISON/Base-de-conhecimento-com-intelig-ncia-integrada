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
- **G-240: PASS / CLOSED e promovido para `main`.**
- **G-245: IN PROGRESS em branch dedicada; ainda não promovido.**
- G-250: NOT_RUN.

## Baseline `main`

Merge de G-240:

`32a696386bf2ab5574d4d7725db78636fa51f36c`

A baseline `main` contém o knowledge plane read-only aceito em G-240 e não contém o trabalho posterior de G-245.

## Ambiente de homologação validado

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- multisite: não;
- corpus: 622 posts;
- DOMDocument: ativo.

## Fechamento técnico G-240 / KD 2.1

Full-corpus:

- corpus 622 → 622;
- primeira passagem: 622/622;
- segunda passagem: 622/622;
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

Evidência: `evidence/kd-v21-smoke-summary-20260916T172538Z.json`.

## Fechamento humano G-240

Mesmo conjunto fixo de oito slots:

- expected/reviewed: 8/8;
- human_passed: 8/8;
- gate_passed: 8/8;
- coverage: 8/8;
- order: 8/8;
- no invented text: 8/8;
- structure preserved: 8/8;
- stale: 0;
- repeatability failures: 0;
- sample ID mismatches: 0;
- system not_ready: 0;
- gate global: true.

Os posts 1290, 370 e 1307, que haviam exposto perda de hierarchy fidelity, fecharam com `structure_preserved=true` em KD 2.1.0.

Evidência: `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## Runtime consolidado no knowledge plane

Componentes principais:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`;
- `Canonical_JSON`;
- `Semantic_Structure`;
- `Hierarchy_Relationships`;
- `Numbered_Hierarchy_Resolver`;
- `Knowledge_Document` schema `2.1.0`.

O knowledge plane permanece estritamente read-only.

## G-245 — estado externo à baseline `main`

Branch ativa:

`spec004-g245-production-readiness`

PR:

`#4` — DRAFT / NÃO MERGEAR.

T080 Production Preflight já foi executado em homologação com:

- blockers: 0;
- review items: 2;
- corpus 622 → 622;
- fingerprint editorial idêntico;
- zero persistência;
- zero execução de shortcode;
- zero escrita em `post_content`/`_elementor_data`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`.

Itens `review_required` conhecidos:

- `faq_wd` e `wpt` sem handler runtime registrado;
- loopback ainda não testado pelo preflight v1.

Esses gaps não bloqueiam planejamento/projeção read-only, mas bloqueiam writer até tratamento explícito.

## Próximo passo

Continuar G-245 apenas na branch dedicada:

1. fechar Projection Plan read-only com contrato, testes e evidência ambiental;
2. manter qualquer gateway version-gated e writer disabled-by-default;
3. definir journal/rollback, stale-source guard e dry-run;
4. só depois planejar canário controlado;
5. writer real exige autorização explícita posterior.

## Guardrails

- `main` representa G-240 fechado, não G-245 concluído;
- Content Extractor/KD continuam read-only;
- nenhum writer Elementor está autorizado;
- nenhuma migration Elementor está autorizada;
- nenhuma persistência de KD/resultado foi autorizada;
- produção não será ambiente experimental;
- GO de homologação não equivale a GO de produção.

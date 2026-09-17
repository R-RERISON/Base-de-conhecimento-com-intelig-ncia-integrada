# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-001/002/003: concluídas.
- R-200: PASS.
- R-210: PASS.
- G-220: PASS ambiental.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240/v1: FAIL CONTROLADO — perda estrutural.
- G-240/v2/KD 2.0.1: full-corpus técnico PASS, porém aceite humano A/B FAIL CONTROLADO — HIERARCHY FIDELITY.
- KD 2.1.0 / build `0.4.0-acceptance.12`: PASS técnico full-corpus + PASS humano 8/8.
- **G-240: CLOSED / PASS e promovido para `main`.**
- merge de referência G-240: `32a696386bf2ab5574d4d7725db78636fa51f36c`.
- evidência técnica: `evidence/kd-v21-smoke-summary-20260916T172538Z.json`.
- evidência humana: `evidence/g240-kd21-acceptance-20260916T193359Z.json`.
- contrato KD 2.1.0 congelado: `knowledge-document-contract-v2.1.0.md`.
- **G-245: IN PROGRESS somente em `spec004-g245-production-readiness`; PR #4 DRAFT.**
- writer/migration Elementor permanecem não autorizados.

## Baseline comprovada em `main`

A `main` contém o fechamento G-240 e permanece separada do trabalho G-245.

Knowledge Document:

- schema `2.1.0`;
- Content Extractor/KD read-only;
- relações hierárquicas conservadoras e auditáveis;
- `review_required` preserva limitações conhecidas sem ser confundido com `not_ready`.

## Evidência técnica que fechou G-240

Ambiente de homologação:

- WordPress 6.9.4;
- PHP 8.5.10;
- Elementor 4.1.0;
- plugin `0.4.0-acceptance.12`;
- DOMDocument ativo.

Full-corpus:

- corpus 622 → 622;
- primeira passagem: 622/622;
- segunda passagem: 622/622;
- errors: 0;
- throwables: 0;
- hash mismatches: 0;
- canonical JSON mismatches: 0;
- structure_incomplete: 0;
- `not_ready`: 0;
- fingerprint editorial before/after idêntico;
- changed posts: 0;
- gate técnico: PASS.

Readiness:

- candidate_ready: 387;
- review_required: 233;
- not_applicable: 2;
- not_ready: 0.

## Evidência humana que fechou G-240

Mesmo conjunto de oito slots:

- reviewed: 8/8;
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

Os casos 1290, 370 e 1307, que motivaram KD 2.1, fecharam com estrutura humana preservada.

## Estado de G-245

Branch canônica de trabalho:

`spec004-g245-production-readiness`

PR:

`#4` — DRAFT / NÃO MERGEAR até fechamento dos subgates aplicáveis.

T080 Production Preflight foi executado read-only em homologação e registrou:

- blockers: 0;
- review items: shortcodes legados `faq_wd`/`wpt` sem handler e loopback não testado;
- corpus 622 → 622;
- fingerprint editorial preservado;
- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- zero execução de shortcodes;
- zero rede externa;
- zero persistência;
- zero escrita editorial.

Evidência G-245/T080 permanece na branch G-245 e não compõe a baseline runtime da `main`.

## Próximo passo exato

Continuar a partir do PR #4, revalidando o estado real do branch antes de qualquer mudança:

1. conferir `AGENTS.md`;
2. conferir `.specify/PROJECT_MANIFEST.md`;
3. conferir `.specify/memory/constitution.md`;
4. conferir esta SPEC e `docs/DEFINITION-OF-DONE.md`;
5. confirmar `main`, branch G-245, head do PR #4 e divergências;
6. fechar Projection Plan read-only de forma determinística e ambientalmente validada;
7. manter writer disabled-by-default;
8. somente depois avançar para journal/rollback, stale-source guard, dry-run e canário.

## Critério objetivo do próximo passo

Projection Plan só fecha quando houver contrato congelado, testes locais PASS, repetibilidade, zero-write comprovado e evidência ambiental full-corpus sem mutação editorial. A existência do plano não autoriza writer.

## Guardrails preservados

- WordPress/Elementor continuam fonte editorial;
- Knowledge Document é derivado reconstruível;
- nenhum writer Elementor está autorizado;
- nenhuma migration Elementor está autorizada;
- nenhuma persistência de KD foi autorizada;
- produção não é ambiente experimental;
- GO de homologação != GO de produção.

> Quem não sabe onde está, não sabe para onde quer ir.

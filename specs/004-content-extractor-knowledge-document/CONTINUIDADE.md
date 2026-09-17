# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

Antes de qualquer alteração, reler `AGENTS.md`, `.specify/PROJECT_MANIFEST.md`, `.specify/memory/constitution.md`, SPEC-004, ADRs vigentes e `docs/DEFINITION-OF-DONE.md`.

## Estado atual

- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- KD 2.1.0: PASS técnico + humano 8/8.
- ADR-004-001: ACEITA — Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- T091: PASS AMBIENTAL.
- T092 Block Projection 1.1: PASS LOCAL, 25/25.
- T093: **PASS AMBIENTAL**.
- T094 Editorial Fidelity: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**, 11/11 local.
- Nenhum writer/migration está autorizado.

## T093 comprovado

Evidência resumida:
`evidence/g245-block-projection-t093-summary-20260917T174835Z.json`.

SHA-256 do JSON bruto recebido:
`db04ba6ac564c9471e00491987ae2a8d8125a2c9b7462aca9dc55e6259e8da43`.

Resultado:

- corpus 623;
- duas passagens 623/623;
- errors 0;
- throwables 0;
- projection hash mismatches 0;
- canonical mismatches 0;
- safety violations 0;
- fingerprint editorial before/after igual;
- `gate_result.t093_block_projection_pass=true`.

Plan status:

- projectable 347;
- review_required 269;
- native_noop 4;
- not_applicable 3.

KD readiness:

- candidate_ready 387;
- review_required 233;
- not_applicable 3.

Razões agregadas dos reviews:

- HIERARCHY_AMBIGUOUS 964 ocorrências;
- SHORTCODE_NOT_EXPANDED 55;
- HTML_LOCAL_HEADING_FLATTENED 19;
- HIERARCHY_NUMBERING_CONFLICT 6;
- HTML_NESTED_LIST_IN_TABLE_FLATTENED 2.

## Descoberta que bloqueia serializer direto

O KD é semântico, não editorial lossless.

No legado, o adapter materializa muitos blocos como texto visível. Portanto não preserva no KD toda a informação necessária para migração sem perda, especialmente:

- href de links;
- src/attachment de imagens;
- rich inline formatting;
- alguns detalhes estruturais/editoriais de HTML e Elementor.

**Não implementar `KD -> serialize_blocks()` como writer.**

Arquitetura correta:

`fonte original -> Migration Fidelity Source -> Core Block Serializer`

com KD em paralelo como guardrail semântico/estrutural:

`fonte original -> Content Extractor -> Knowledge Document`.

## T094 — Editorial Fidelity

Contrato: `editorial-fidelity-contract-v1.md`.
Runner: `includes/class-editorial-fidelity-inventory-smoke.php`.

O runner conta/agrega sem exportar conteúdo, URLs ou IDs:

- links;
- imagens e resolução de attachment por contagem;
- inline formatting;
- styled spans;
- figures/captions/br;
- tables/spans;
- shortcodes;
- Elementor widgets/media refs;
- source × fidelity class.

Classes:

- native_core_blocks;
- not_applicable;
- elementor_source_adapter_required;
- shortcode_resolution_required;
- rich_html_source_required;
- complex_table_source_required;
- kd_structure_sufficient_candidate.

Validação local: 11/11 PASS + lint.

Pacote de homologação:

- `0.4.0-g245-editorial-fidelity-t094.1`;
- SHA-256 `e25494a8c7be9bc2103e421ab7698d4a2f1114aea85fa2efdb0811a5a48f2caf`;
- 38 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- T093 smoke OFF;
- T094 smoke ON;
- writer OFF.

## Próximo passo exato

1. instalar pacote T094 em homologação;
2. abrir `Base de Conhecimento > Editorial Fidelity G-245`;
3. executar `Executar T094 e baixar JSON`;
4. devolver o JSON;
5. versionar a evidência;
6. definir `Migration Fidelity Source v1` estritamente conforme a distribuição real;
7. somente depois iniciar serializer Core Blocks in-memory.

## Guardrails absolutos

- não mexer na UX-002 sem UX-SPEC;
- não instalar/declarar plugin Gutenberg como requisito;
- não remover Elementor agora;
- não escrever `_elementor_data`;
- não escrever `post_content`;
- não tratar KD como representação editorial lossless;
- não executar shortcodes para migrar;
- não baixar mídia remota;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — Core Blocks como destino editorial canônico.
- T091: PASS AMBIENTAL.
- T092 Block Projection 1.1: PASS LOCAL.
- T093: **PASS AMBIENTAL**.
- T094 Editorial Fidelity: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Arquitetura editorial vigente

Canônica futura: `WP_Post.post_content` + WordPress Core Blocks.

- plugin Gutenberg não é dependência;
- Elementor é source adapter legado/read-only durante transição;
- `_elementor_data` é preservado enquanto houver dependência;
- nenhum novo writer usa `_elementor_data` como destino.

## Baseline T093

Ambiente: WordPress 6.9.4 / PHP 8.5.10 / Elementor 4.1.0 / Block Projection 1.1.0.

Corpus: 623 posts.

- legacy_html 536;
- plain_text 41;
- elementor 34;
- mixed 5;
- gutenberg 4;
- empty 3.

Plan status:

- projectable 347;
- review_required 269;
- native_noop 4;
- not_applicable 3.

T093: 623/623 em duas passagens, errors/throwables/hash mismatches/canonical mismatches/safety violations = 0, fingerprint editorial idêntico e `t093_block_projection_pass=true`.

Evidência resumida: `evidence/g245-block-projection-t093-summary-20260917T174835Z.json`.

## Diagnóstico dos 233 KD reviews

Ocorrências agregadas por família:

- HIERARCHY_AMBIGUOUS 964;
- SHORTCODE_NOT_EXPANDED 55;
- HTML_LOCAL_HEADING_FLATTENED 19;
- HIERARCHY_NUMBERING_CONFLICT 6;
- HTML_NESTED_LIST_IN_TABLE_FLATTENED 2.

Isso mostra que o principal volume é ambiguidade conservadora de numeração/hierarquia, não falha de parser.

## Descoberta crítica pré-serializer

O KD 2.1 preserva semântica e estrutura para IA/busca, mas não é editorialmente lossless.

No adapter legado:

- links são reduzidos a texto visível, sem href;
- rich inline (`strong`, `em` etc.) é achatado no texto;
- imagens preservam alt + ID sintético, sem src/attachment reference canônica;
- células/list items/quotes também priorizam conteúdo textual sem rich markup completo.

Consequência: `KD -> Core Blocks` direto pode causar regressão editorial apesar de passar cobertura textual.

## T094 — Editorial Fidelity Gate

Contrato: `editorial-fidelity-contract-v1.md`.
Runner: `includes/class-editorial-fidelity-inventory-smoke.php`.

Objetivo: medir no corpus, sem exportar conteúdo/URLs/post IDs, quais fontes exigem rich source para migração.

Classificações:

- native_core_blocks;
- not_applicable;
- elementor_source_adapter_required;
- shortcode_resolution_required;
- rich_html_source_required;
- complex_table_source_required;
- kd_structure_sufficient_candidate.

Validação local: 11/11 assertions PASS + PHP lint PASS.

Pacote: `0.4.0-g245-editorial-fidelity-t094.1`  
SHA-256: `e25494a8c7be9bc2103e421ab7698d4a2f1114aea85fa2efdb0811a5a48f2caf`.

## Próximo passo

Executar T094 em homologação. A distribuição real definirá o menor `Migration Fidelity Source v1` necessário antes do serializer.

Nenhum writer está autorizado.

## Guardrails

- UX-002 não pode regredir;
- plugin Gutenberg não pode virar dependência silenciosa;
- Elementor não pode ser removido antes de dependência zero;
- KD não pode ser usado como fonte editorial lossless;
- nenhuma migração automática em activation/update;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

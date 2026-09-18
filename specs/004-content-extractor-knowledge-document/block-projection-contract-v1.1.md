# T092 — Block Projection Contract v1.1

**Status:** FROZEN — PASS LOCAL / READ-ONLY  
**SPEC:** 004 — G-245 Rebaseline  
**Target:** WordPress Core Blocks  
**Base:** `block-projection-contract-v1.md` + evidência T091

## 1. Motivação baseada em evidência

T091 executou duas passagens sobre 623 posts com `gate_result.t091_block_projection_pass=true`, zero errors/throwables/hash mismatches/safety violations e fingerprint editorial preservado.

Gaps observados no corpus:

- `BLOCK_PROJECTION_UNSUPPORTED_KIND:image`: 40;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:quote`: 8;
- `BLOCK_PROJECTION_TABLE_SPAN_REVIEW`: 32;
- `KNOWLEDGE_DOCUMENT_REVIEW_REQUIRED`: 233.

T092 só amplia a allowlist onde a entrada atual já contém dados suficientes para uma projeção semanticamente segura.

## 2. Alteração v1.1

### `quote` → `core/quote`

`kind=quote` passa a ser projetado para `core/quote`, preservando o texto em ordem. Não há inferência de citação/autor ausente.

### `image` permanece `review_required`

O Knowledge Document 2.1.0 preserva o texto alternativo do fragmento de imagem, mas não preserva uma referência canônica de mídia (`src`, attachment ID ou equivalente) suficiente para produzir `core/image` sem inventar dados.

Portanto, v1.1 **não** projeta imagem e mantém:

`BLOCK_PROJECTION_UNSUPPORTED_KIND:image`

Qualquer futura liberação de `core/image` exige contrato de proveniência de mídia e nova evidência full-corpus.

### Tabelas com spans permanecem `review_required`

`rowspan`/`colspan` diferente de 1 continua emitindo:

`BLOCK_PROJECTION_TABLE_SPAN_REVIEW`

Nenhuma flattening silenciosa é permitida.

## 3. Diagnóstico T093

O runner full-corpus passa a exportar, além das métricas T091:

- `knowledge_document_readiness`;
- `knowledge_document_reasons` agregados para `review_required|not_ready`;
- `source_plan_matrix` por source kind × plan status.

Esses dados não incluem conteúdo editorial nem post IDs.

## 4. Safety invariants

Continuam obrigatoriamente falsos/nulos:

- `serialized_post_content`;
- `writer_allowed`;
- `migration_execution_allowed`;
- `persists_state`;
- `writes_post_content`;
- `writes_elementor_data`;
- `calls_external_network`;
- `executes_shortcodes`;
- `renders_dynamic_blocks`;
- `depends_on_gutenberg_plugin`.

## 5. Versionamento

Block Projection schema: `1.1.0`.

A mudança de schema é necessária porque a mesma entrada `kind=quote` deixa de ser unsupported e passa a produzir `core/quote`, alterando o plano/hash de forma deliberada e versionada.

## 6. Aceite local

O teste unitário deve comprovar:

- v1.1.0 exposto no plano;
- `quote` → `core/quote`;
- imagem sem proveniência continua review;
- table spans continuam review;
- determinismo preservado;
- writer/plugin dependency continuam false.

**Resultado local:** 25/25 assertions PASS + PHP lint PASS.

## 7. Próximo gate

T093: full-corpus read-only com Block Projection 1.1.0, duas passagens, determinismo, fingerprint preservado e diagnóstico dos motivos KD.

T093 deve orientar o contrato de serialização; nenhum writer é autorizado por T092/T093.

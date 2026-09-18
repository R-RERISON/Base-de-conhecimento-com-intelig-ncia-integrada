# Package — G-245 / T093 Block Projection v1.1

**Build:** `0.4.0-g245-block-projection-t093.1`  
**SHA-256:** `9e77a0496d323b979c690f10ade6699e9346432b9cf2a2feb4e06b1f5876b6e1`

## Objetivo

Executar T093 em homologação sobre o corpus completo, read-only, com Block Projection schema `1.1.0` e diagnóstico agregado dos motivos do Knowledge Document.

## Conteúdo

- `quote → core/quote` habilitado;
- imagem continua `review_required` por falta de proveniência de mídia suficiente;
- table spans continuam `review_required`;
- T093 Block Projection smoke habilitado;
- Elementor Projection smoke desabilitado;
- Journal smoke desabilitado;
- writer Elementor desabilitado;
- nenhum writer `post_content` implementado.

## Validação de pacote

- T092 unit assertions: 25/25 PASS;
- PHP lint pré-ZIP: 37/37 PASS;
- PHP lint pós-reextração: 37/37 PASS;
- single plugin root: PASS;
- UX-002 byte parity: PASS nos quatro arquivos visuais canônicos;
- Gutenberg plugin dependency: false.

## Saída esperada

JSON `bdc-kb-spec004-t093-block-projection-*.json` com:

- duas passagens full-corpus;
- hashes/determinismo;
- `knowledge_document_readiness`;
- `knowledge_document_reasons`;
- `source_plan_matrix`;
- warnings;
- projected block names;
- fingerprint editorial;
- `gate_result.t093_block_projection_pass=true`.

T093 não autoriza serialização persistente nem writer.

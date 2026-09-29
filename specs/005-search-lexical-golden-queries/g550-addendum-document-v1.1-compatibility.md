# G-550 Addendum — Search Document v1.1 compatibility

**Status:** ACCEPTED / regression guard  
**Data:** 2026-09-21  
**Relaciona:** G-550 fechado + G-590 em consolidação

## Motivo da reabertura documental

G-590 adiciona Section Projection ao Search Document, elevando:
- `search-document-v1.0.0` → `search-document-v1.1.0`.

O Golden G-550 congelava a versão do documento e, corretamente, passaria a sinalizar STALE.

## Evidência de compatibilidade

Diff contra Premium Rebaseline `01508379f91a336b26b17268fb119458bd077f7e`:

### Lexical_Ranker
- blob SHA permanece `a17cd7bad11e5940cc0a5c2dd95201ac26e66f7f`;
- versão permanece `lexical-ranker-v1.0.0`;
- nenhum peso/tie-break/sinal alterado.

### Search_Document_Builder
A mudança é aditiva:
- adiciona `sections`;
- adiciona `section_projection_version`;
- adiciona `diagnostics.section_count`;
- inclui os campos novos no `document_hash`.

Não altera a construção de:
- `title_norm`;
- `summary_norm`;
- `headings_norm`;
- `taxonomy_norm`;
- `body_norm`;
- `document_state`;
- `source_kind`.

### Search_Service::search()
O método post-level existente não é alterado. G-590 adiciona `search_sections()` como nova fachada, sem mudar `search-result-v1.0.0`.

## Decisão

`Golden_Suite_Loader::EXPECTED_DOCUMENT_VERSION` passa a aceitar exatamente `search-document-v1.1.0`.

Isso **não** rebaselineia expectativas, ranks, queries ou tolerâncias Golden.

Qualquer alteração futura em campos consumidos pelo `Lexical_Ranker`, pesos, tie-break, normalizer ou `search()` exige novo addendum/gate e execução integral do Golden.

## Critério de validade

Este addendum só é considerado confirmado quando:
1. unit/regression da SPEC-005 passa;
2. Golden G-550 ambiental passa sem blocking/technical failure;
3. blob SHA do Lexical_Ranker continua igual ao baseline;
4. candidate retrieval continua sem carregar `sections_json`.

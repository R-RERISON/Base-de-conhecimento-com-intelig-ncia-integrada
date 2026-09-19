# G-520 / T521 — Search Document Contract v1

**Status:** FROZEN  
**Document version:** `search-document-v1.0.0`  
**Normalizer:** `search-normalizer-v1.0.0`

## Natureza

Search Document é projeção post-level reconstruível. Nunca é autoridade editorial, de status ou de acesso.

Um documento corresponde a um `WP_Post(ID)`.

## Campos lógicos

```text
post_id
document_state
source_kind
title_norm
summary_norm
headings_norm
taxonomy_norm
body_norm
source_hash
document_hash
document_version
normalizer_version
post_modified_gmt
indexed_at_gmt
```

`document_state = ready|degraded`.

## Mapeamento de fonte

### title_norm
Fonte: `WP_Post.post_title`.

### summary_norm
Concatenação determinística, nesta ordem:
1. `_bdc_es_objective`;
2. `_bdc_es_escalation`;
3. `_bdc_es_important`.

Fonte: `Meta_Contract::fields()`.

### headings_norm
Textos dos fragments do `Content_Extractor` cujo `kind=heading`, preservando ordinal.

### body_norm
Textos dos demais fragments materializados pelo `Content_Extractor`, preservando ordinal:
- paragraph;
- list_item;
- table_caption;
- table_row;
- quote;
- code;
- image alt quando materializado;
- demais kinds textuais futuros somente após revisão do contrato.

Heading não é duplicado no body.

### taxonomy_norm
Somente taxonomias canônicas de `Classification_Contract::fields()`:
- `bdc_kb_audience`;
- `bdc_kb_responsible_team`;
- `bdc_kb_knowledge_type`;
- `bdc_kb_catalog_item`.

Ordenação determinística por ordem do contrato e, dentro da taxonomia, `term_id ASC`. Materializar `name` e `slug`. Legacy keys não entram.

## Normalização

Cada campo textual usa o perfil lexical do T520:
- strip/control cleanup;
- accent folding;
- lowercase;
- separadores para espaço;
- whitespace collapse.

Não aplicar limites de query aos documentos.

## Estado degraded

O documento permanece indexável por title/Summary/taxonomy quando o Content Extractor falhar ou não materializar body/headings.

Nesse caso:
- `document_state=degraded`;
- headings/body ficam vazios quando indisponíveis;
- o erro/warning não vira conteúdo pesquisável;
- Search Response deve propagar estado degraded quando o resultado depender desse caminho.

## Hashes

### source_hash
SHA-256 de entrada canônica contendo:
- post_id;
- post_title;
- três metas Summary;
- termos canônicos (term_id/name/slug);
- `post_content_sha256`;
- `elementor_data_sha256`;
- document_version;
- normalizer_version.

### document_hash
SHA-256 da representação normalizada de saída, em ordem fixa:
`title|summary|headings|taxonomy|body|document_state|source_kind|document_version|normalizer_version`.

Finalidade:
- stale detection;
- idempotência;
- rebuild;
- evitar rewrite sem mudança.

## Status e permissão

Não armazenar autorização como verdade.

No read path:
1. projection recupera candidatos;
2. WordPress carrega o post;
3. post type/status/capability são revalidados;
4. somente então o documento pode ser ranqueado/retornado.

## Proibições

- chunks/item index;
- embeddings/vetores;
- HTML bruto como índice;
- execução de shortcode;
- renderização Elementor;
- renderização dinâmica de blocks;
- conteúdo de warnings como termo;
- query logs;
- dependência ASI.

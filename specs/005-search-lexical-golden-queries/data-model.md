# Data Model — SPEC-005

**Estado:** PROVISÓRIO / schema BDC próprio permitido mediante G-520.

## Entidades lógicas

### SearchQuery
Efêmera:
- original;
- normalized;
- tokens;
- detected_type;
- normalizer_version.

### SearchDocument
Projeção reconstruível, post-level:
- post_id;
- title/title_norm;
- objective/summary norm quando aplicável;
- headings_norm;
- body_norm;
- taxonomy_norm quando aprovada;
- source/content hash;
- modified timestamp;
- index_version.

Persistência: **a decidir em G-520**.

### SearchResult
Efêmera:
- post_id;
- title;
- official_url;
- score;
- rank;
- matched_signals;
- retrieval_state;
- algorithm_version.

### GoldenQuery
Expectativa governada:
- id;
- query/query_norm;
- expected_post_id;
- max_rank;
- severity;
- rationale;
- source;
- active;
- contract_version.

Persistência: **a decidir em G-520**.

### GoldenRun
Evidência:
- suite_version;
- set_hash;
- algorithm_version;
- normalizer_version;
- status;
- counts;
- results;
- timestamp.

## Proibições

Não criar nesta SPEC-005 v1:
- item table;
- queue table;
- analytics events;
- interactions;
- quality_daily;
- embeddings;
- vectors;
- migrations registry genérico.


## Ownership de storage

Qualquer persistência aprovada deve pertencer ao novo plugin.

Candidatos de namespace:
- `{prefix}bdc_kb_search_documents`;
- `{prefix}bdc_kb_golden_queries`;
- demais tabelas somente quando uma SPEC/gate provar necessidade.

Os nomes finais serão congelados em G-520.

É proibido usar `asi_*` como storage de produção, fallback ou dependência transitiva.

Embeddings/vetores são de SPEC futura, mas já ficam sob a mesma regra de ownership e lifecycle próprios.

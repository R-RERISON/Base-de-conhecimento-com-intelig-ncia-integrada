# Data Model — SPEC-005 / G-520

**Status:** FROZEN PARA G-530  
**Storage ADR:** ADR-005-003

## SearchQuery — efêmera

- original;
- normalized;
- tokens[];
- detected_type;
- normalizer_version.

Contrato: `g520-query-normalization-contract-v1.md`.

## SearchDocument — projeção persistida post-level

Tabela aprovada:
`{$wpdb->prefix}bdc_kb_search_documents`.

Campos:
- post_id;
- document_state;
- source_kind;
- title_norm;
- summary_norm;
- headings_norm;
- taxonomy_norm;
- body_norm;
- source_hash;
- document_hash;
- document_version;
- normalizer_version;
- post_modified_gmt;
- indexed_at_gmt.

Contrato: `g520-search-document-contract-v1.md`.

Não é fonte da verdade.

## ProjectionState — Option

Option:
`bdc_kb_search_projection_state`, autoload=false.

- schema_version;
- status;
- document_version;
- normalizer_version;
- corpus_count;
- source_fingerprint;
- last_success_at_gmt;
- last_error_code.

Sem query/content/user telemetry.

## SearchResult — efêmera

- post_id;
- title canônico;
- official_url;
- rank;
- score;
- matched_signals[];
- source_kind;
- document_state;
- visibility_revalidated.

Contrato: `g520-search-result-contract-v1.md`.

## Golden Relevance — fixture versionada

Não criar tabela Golden v1.

Fixture:
`fixtures/golden-relevance-v1.0.0.json`.

- 6 total;
- 5 active blocking;
- 1 quarantined;
- set_hash `e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4`.

## Technical Challenge — fixture versionada

Fixture:
`fixtures/technical-challenge-v1.0.0.json`.

- 7 cases;
- non-Golden-blocking;
- set_hash `2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807`.

## GoldenRun — efêmero/report

- suite versions/hashes;
- runtime versions;
- status;
- blocking_failed;
- warning_failed;
- technical_failed;
- results;
- timestamp.

Persistência de histórico de runs não é necessária no v1. JSON baixado/evidence de engenharia cobre o gate.

## Explicitamente não criar

- Golden Query table;
- item table;
- queue table;
- analytics events;
- interactions;
- daily quality;
- embeddings;
- vectors;
- vocabulary table;
- rules table;
- migration registry genérico;
- qualquer `asi_*`.

## Ownership

A única tabela aprovada em G-520 pertence ao BDC e possui lifecycle/rebuild próprios.

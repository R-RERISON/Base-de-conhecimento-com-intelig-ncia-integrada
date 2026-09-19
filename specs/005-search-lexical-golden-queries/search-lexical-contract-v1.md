# Search Lexical Contract v1

**Status:** FROZEN EM G-520  
**Runtime autorizado somente após T528/G-520 PASS.**

## Versões

- normalizer: `search-normalizer-v1.0.0`;
- document: `search-document-v1.0.0`;
- ranking: `lexical-ranker-v1.0.0`;
- result: `search-result-v1.0.0`;
- Golden runner: `golden-runner-v1.0.0`.

## Contratos canônicos

- T520: `g520-query-normalization-contract-v1.md`;
- T521: `g520-search-document-contract-v1.md`;
- T522: `g520-ranking-contract-v1.md`;
- T523: `g520-search-result-contract-v1.md`;
- T524: `g520-golden-runner-contract-v1.md`;
- T525: `adr-005-003-search-storage-wordpress-first.md`;
- T526: `security-matrix.md`;
- T527: `g520-rollback-rebuild-contract-v1.md`.

## Pipeline v1

`query -> normalize -> projection candidate retrieval -> WordPress authorization revalidation -> lexical rank -> SearchResponse`

Fallback:
`query -> normalize -> bounded WP_Query native relevance -> authorization -> SearchResponse(degraded)`

## Storage

- uma tabela: `{$wpdb->prefix}bdc_kb_search_documents`;
- uma Option de estado: `bdc_kb_search_projection_state`;
- sem Golden table;
- sem FULLTEXT;
- sem query log.

## Search Document

Fontes:
- title: WordPress;
- Summary: Meta_Contract;
- headings/body: Content Extractor;
- taxonomy: Classification_Contract.

Projection é descartável/reconstruível.

## Resultado

Estados:
`success|zero_results|invalid_query|degraded|technical_error`.

Retrieval:
`projection_like|wordpress_fallback`.

Zero-result != erro técnico.

## Ranking

Determinístico, explicável, sem recência, usuário, telemetria, IA ou vetor.

Qualquer mudança material exige nova algorithm_version e invalida Golden evidence anterior.

## Golden

Golden e Technical Challenge possuem versions/hashes separados.

- Golden blocking failure => NO-GO;
- Technical Challenge failure => gate técnico FAIL;
- quarantine não escolhe vencedor;
- STALE se versões/hashes/runtime divergem.

## Segurança

WordPress é autoridade de status/capability. Projection nunca concede acesso.

## Proibições v1

- FULLTEXT;
- semantic/vector;
- embeddings;
- IA/model rerank;
- hardcoded equivalences;
- analytics/query logging;
- item/deep-link;
- queue;
- ASI runtime/storage.

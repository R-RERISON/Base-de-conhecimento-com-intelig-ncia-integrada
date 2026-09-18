# Search Lexical Contract v1 — DRAFT

**Estado:** DRAFT / não autoriza implementação antes de G-520.

## Input

- query string;
- caller context;
- limit bounded;
- opcionalmente filtros explicitamente autorizados.

## Normalized Query

Campos mínimos:
- `original`;
- `normalized`;
- `tokens[]`;
- `detected_type`;
- `normalizer_version`.

Invariantes:
- determinístico;
- bounded;
- sem rede;
- sem IA;
- sem persistência;
- original não é perdido.

## Search Document post-level

Campos candidatos mínimos:
- post_id;
- title;
- title_norm;
- summary/objective_norm quando disponível;
- headings_norm derivados do Content Extractor;
- body_norm derivado do Content Extractor;
- taxonomy_norm quando autorizada;
- source_hash/content_hash;
- post_modified_gmt;
- index_version.

É projeção reconstruível.

## Search Result

- post_id;
- title;
- official_url;
- rank;
- score;
- matched_signals[];
- retrieval_state: `ready|degraded|fallback`;
- algorithm_version.

WordPress revalida status/visibilidade antes da entrega.

## Ranking

Pesos não estão congelados neste draft. Ordem qualitativa inicial:
`exact_title > partial_title > objective/summary > heading > taxonomy > body`.

Cobertura de tokens é sinal explícito. Empate deve ser determinístico.

## Erros/estados

- `success`;
- `zero_results`;
- `invalid_query`;
- `degraded`;
- `technical_error`.

Zero-result nunca é technical error.

## Proibições

- writer editorial;
- semantic/vector;
- model rerank;
- hardcode de equivalências do domínio;
- retornar conteúdo privado sem autorização;
- projection como autoridade de permissão.

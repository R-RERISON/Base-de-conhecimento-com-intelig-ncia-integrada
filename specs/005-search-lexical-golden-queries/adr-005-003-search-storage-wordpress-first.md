# ADR-005-003 — Search Storage WordPress-first

**Status:** ACCEPTED  
**Data:** 2026-09-19  
**Gate:** G-520/T525

## Contexto

R-500 provou:
- corpus total 623 / publish 606;
- WP_Query nativo: 85% Top-1, 100% Top-20 em self-retrieval;
- 91/610 posts com texto semântico não integralmente representado nos campos nativos (14,92%);
- 14/18 posts com Summary possuem sinal não integralmente pesquisável nativamente (77,78%);
- Content Extractor p95 7,4911 ms/post;
- MariaDB 12.2.2.

R-510 adicionou Technical Challenges reais do corpus para Summary e Elementor/Content Extractor.

## Opções

### A — WP_Query nativo
Rejeitada como mecanismo canônico.

É fallback válido, mas não representa todo o Search Document semântico.

### B — WP_Query + hooks mínimos
Rejeitada como mecanismo canônico.

Meta JOIN poderia ampliar Summary, mas não resolve de forma limpa:
- fragments semânticos do Content Extractor;
- ranking explicável por campo;
- rebuild/versionamento;
- Elementor/mixed sem reprocessar conteúdo no read path.

Hooks globais continuam proibidos.

### C — Search Retrieval Projection própria
**ACEITA.**

Menor infraestrutura que cobre o contrato sem ler ASI.

### D — Projection + FULLTEXT
**NÃO AUTORIZADA no v1.**

MariaDB suporta a evolução, mas não existe benchmark da Projection que demonstre necessidade. Com 623 documentos, adicionar FULLTEXT agora compraria complexidade sem evidência.

Se G-570 provar p95/escala insuficiente, nova evidência pode promover FULLTEXT sem alterar a fonte editorial.

## Storage aprovado

Uma tabela própria:

`{$wpdb->prefix}bdc_kb_search_documents`

Schema lógico:

```sql
post_id BIGINT UNSIGNED PRIMARY KEY
document_state VARCHAR(16) NOT NULL
source_kind VARCHAR(32) NOT NULL
title_norm TEXT NOT NULL
summary_norm LONGTEXT NOT NULL
headings_norm LONGTEXT NOT NULL
taxonomy_norm LONGTEXT NOT NULL
body_norm LONGTEXT NOT NULL
source_hash CHAR(64) NOT NULL
document_hash CHAR(64) NOT NULL
document_version VARCHAR(32) NOT NULL
normalizer_version VARCHAR(32) NOT NULL
post_modified_gmt DATETIME NULL
indexed_at_gmt DATETIME NOT NULL
```

Índices B-tree mínimos:
- PRIMARY(post_id);
- KEY(document_state);
- KEY(document_version);
- KEY(source_hash).

Sem FULLTEXT no schema v1.

Sem foreign key.

## Projection state

Uma única Option BDC, autoload=false:

`bdc_kb_search_projection_state`

Campos:
- schema_version;
- status: `not_built|building|ready|degraded|failed`;
- document_version;
- normalizer_version;
- corpus_count;
- source_fingerprint;
- last_success_at_gmt;
- last_error_code.

Não contém queries nem conteúdo editorial.

## Retrieval v1

SQL LIKE bounded sobre campos normalizados:
- strict all-token pass;
- relaxed any-token fallback;
- prepared statements;
- `esc_like()`;
- candidate cap 200.

Ranking final acontece em PHP pelo T522 depois da revalidação WordPress.

## Golden storage

Não criar tabela Golden no v1.

As suites são recursos versionados do projeto/plugin e identificadas por version/hash. Persistência administrável só nasce se um caso real de edição operacional justificar.

## Lifecycle

- tabela é projeção descartável;
- criação de schema não dispara rebuild;
- activation/update não executa reindexação em massa;
- rebuild é explícito;
- enquanto `building|failed|not_built`, Search usa fallback WordPress;
- stale rows são removidas somente após uma passagem completa bem-sucedida;
- deactivation não remove tabela;
- uninstall permanece não destrutivo por default.

## Consequências

Autorizado para G-530:
- implementação da Projection;
- SQL LIKE bounded;
- ranker PHP determinístico;
- fallback WP_Query.

Não autorizado:
- FULLTEXT;
- segunda tabela Search;
- item index;
- queue;
- analytics/query log;
- embeddings/vector;
- reutilização de storage ASI.

# ADR-005-004 — Section Retrieval na Search Projection única

**Status:** ACCEPTED
**Data:** 2026-09-21
**Gate:** G-590

## Contexto

O Premium Rebaseline promoveu item/section retrieval, identidade de seção e deep-links a gaps bloqueantes da fronteira técnica da SPEC-005.

O ASI 4.6.8 resolvia isso com índice/tabela própria de items. O BDC já possui Content Extractor canônico, Search Projection própria, ranking post-level comprovado e lifecycle/rebuild seguro.

Criar uma segunda tabela sem medir necessidade violaria WordPress-first e o princípio de negação.

## Opções

### A — copiar o item index do ASI
Rejeitada. Reproduz schema e complexidade histórica sem necessidade comprovada.

### B — reextrair posts em cada consulta
Rejeitada. Coloca parsing editorial no read path, aumenta latência e mistura retrieval com extração.

### C — persistir seções bounded dentro da Search Document Projection
**ACEITA.**

Adicionar uma projeção JSON reconstruível à mesma row post-level.

## Decisão

Evoluir `{$wpdb->prefix}bdc_kb_search_documents` para schema 1.1.0 com:
- `sections_json LONGTEXT NOT NULL`;
- `section_projection_version VARCHAR(32) NOT NULL`.

Não criar segunda tabela, FK, FULLTEXT, queue, telemetry ou bindings.

A ProjectionState passa a registrar `section_projection_version`.

## Read path

1. recuperar/rankear posts exatamente pelo ranker v1;
2. limitar parents;
3. carregar `sections_json` somente dos posts ranqueados;
4. rankear seções em PHP;
5. retornar somente seção navegável;
6. post continua sendo a unidade primária do Search.

## Consequência

A solução adiciona capacidade sem criar novo owner de dados ou segundo índice. Caso benchmark posterior prove insuficiência, uma tabela de item só poderá ser reconsiderada por nova ADR com evidência.

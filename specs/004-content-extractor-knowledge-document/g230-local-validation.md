# G-230 — Validação local do Knowledge Document

## Estado

- implementação: concluída;
- schema: `Knowledge Document v1.0.0`;
- teste unitário: **10/10 PASS**;
- smoke ambiental: pendente.

## Componentes

- `class-canonical-json.php`;
- `class-knowledge-document.php`;
- `tests/unit/spec004-knowledge-document.php`.

## Casos validados

1. JSON canônico ordena mapas e preserva listas;
2. input lógico idêntico produz mesmos hashes e mesmos bytes;
3. alteração semântica altera `source_hash` e `document_hash`;
4. alteração de título altera os hashes;
5. URL/data operacional não altera hashes semânticos;
6. ruído bruto/layout não utilizado não contamina hashes;
7. ordem de seções é semântica e altera hash;
8. schema/heading/ordinal/proveniência são projetados deterministicamente;
9. `Knowledge_Document::build()` é read-only;
10. post inválido falha fechado.

## Regressão do extractor

No mesmo package de validação:

- G-220 unit tests: **14/14 PASS**;
- G-230 unit tests: **10/10 PASS**.

## Decisão de hash

`source_hash` cobre a representação semântica extraída, sem hashes/tamanhos brutos.

`document_hash` cobre o documento canônico exceto:

- o próprio `document_hash`;
- `canonical_url`;
- `modified_gmt`.

Esses dois campos permanecem como proveniência operacional sem provocar reindexação semântica por mudança de URL/timestamp.

## Gate

G-230 permanece **IMPLEMENTED / LOCAL PASS / ENV SMOKE PENDING** até duas construções ambientais do corpus real produzirem zero mismatch e zero mutação editorial.

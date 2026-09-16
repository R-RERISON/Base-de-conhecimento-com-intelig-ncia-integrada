# Package — 0.4.0-acceptance.9

## Objetivo

Build temporário de homologação para diagnóstico agregado dos cinco documentos ainda `not_ready` após `acceptance.8`.

Este package **não altera** Knowledge Document `2.0.1`, parser Legacy, `Semantic_DOM_Expectation`, hashes, `ai_readiness`, `structure_complete` ou o gate G-240.

## Mudanças runtime

- versão `0.4.0-acceptance.9`;
- novo runner temporário `Final_Structure_Diagnostic`;
- novo menu: **Base de Conhecimento → Diagnóstico Estrutural Final**;
- o runner reconstrói os KDs read-only, filtra apenas `not_ready` e exporta somente agregados;
- cruza cada `STRUCTURE_COUNT_MISMATCH:*` com `source_kind` e contexto DOM;
- mede listas/tabelas dentro de `pre/code`, headings, listas-pai, parser-unreachable e tabelas image-only/sem texto;
- não exporta IDs, títulos, URLs ou conteúdo editorial.

## Artefato

`base-conhecimento-inteligencia-integrada-0.4.0-acceptance.9.zip`

SHA-256:

`f20ced6d9f076ddd0408bfa48486f4b4ede8c4e06186a6bb5adea738ab193bd8`

## Validação local

- 29 arquivos runtime;
- 25 arquivos PHP;
- PHP lint 25/25 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- somente bootstrap + novo runner diferem do `acceptance.8`;
- parser/KD/smoke permanecem herdados do `acceptance.8`.

## Git ↔ package parity

- bootstrap: `572ef09522575217020fd246840c88092f1e3cbb`;
- `class-final-structure-diagnostic.php`: `45c8f6d0ec8c423ebd7ee29bd130f6c76413c932`.

## Execução

1. instalar/substituir por `0.4.0-acceptance.9` em homologação;
2. **não executar** `Aceitação G-240 v2`;
3. não é necessário repetir `Validação KD v2` nesta rodada;
4. executar apenas **Base de Conhecimento → Diagnóstico Estrutural Final**;
5. retornar `bdc-kb-spec004-final-structure-diag-*.json`.

O diagnóstico é somente read-only e não libera G-245.
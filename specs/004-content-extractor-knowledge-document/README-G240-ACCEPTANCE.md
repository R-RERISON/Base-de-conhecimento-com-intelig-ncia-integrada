# G-240 — Real Content Acceptance

## Estado

**G-240 v1: FAIL CONTROLADO — STRUCTURE LOSS.**  
**G-240 v2: STRUCTURAL REMEDIATION ACTIVE / `acceptance.11` ENV SMOKE PENDING.**

Não reutilizar builds anteriores para novo aceite humano.

## O que já foi comprovado

O G-240 v1 mostrou cobertura textual, ordem e ausência de invenção preservadas, mas perda estrutural em 7/8 casos.

A remediação v2 reduziu `structure_incomplete` de 82 para 5 sem relaxar o gate. Os diagnósticos `.8`, `.9` e `.10` isolaram a última classe de falha.

### Diagnóstico pipeline `.10`

Evidência: `evidence/pipeline-diag-20260916T143948Z.json`.

Para os cinco documentos restantes:

- `raw_expected_lists == unwrapped_expected_lists`;
- `raw_expected_tables == unwrapped_expected_tables`;
- `fragment_list_containers == block_lists`;
- `fragment_table_containers == block_tables`.

Conclusão: shortcode unwrap e `Semantic_Structure` não perdem containers. A perda ocorre exclusivamente no `Legacy_HTML_Adapter`, entre DOM e fragments.

## Remediação `acceptance.11`

O parser passa a:

1. atravessar recursivamente wrappers internos dentro de `li`, `p` e `blockquote` até encontrar a primeira fronteira estrutural `ul|ol|table`;
2. ao encontrar a fronteira, delegar a subárvore ao adapter correspondente sem descer novamente nela, evitando duplicidade;
3. preservar `alt` de imagens dentro de células de tabela como conteúdo semântico da célula.

Não foram alterados:

- schema KD `2.0.1`;
- `Semantic_DOM_Expectation`;
- `Semantic_Structure`;
- hashes;
- `ai_readiness`;
- critério `structure_incomplete=0`;
- critério `not_ready=0`.

Package: `package-acceptance11.md`.

## Sequência

1. instalar `0.4.0-acceptance.11`;
2. executar somente **Validação KD v2**;
3. exigir full-corpus PASS;
4. somente então repetir os mesmos oito posts A/B humanos;
5. G-245 só é liberado após G-240 v2 PASS.

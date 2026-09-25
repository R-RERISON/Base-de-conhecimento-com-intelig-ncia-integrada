# G-590 — Section Retrieval Contract v1

**Status:** FROZEN PARA IMPLEMENTAÇÃO
**Section projection:** `search-section-projection-v1.0.0`
**Section ranker:** `lexical-section-ranker-v1.1.0`

## Objetivo

Encontrar uma seção relevante dentro de posts já aprovados pelo ranking post-level, sem alterar `lexical-ranker-v1.0.0`.

## Fonte

Somente fragments do `Content_Extractor`. Um item navegável nasce de `kind=heading`.

A SPEC-004 não é alterada: Search consome fragments e cria projeção própria.

## Modelo

Cada seção contém:
- section_key SHA-256;
- ordinal;
- source_ordinal;
- level;
- title;
- title_norm;
- path_norm;
- text_norm;
- anchor_id;
- anchor_state = generated|unresolved;
- projection_version.

## Identidade

A identidade estrutural usa post_id, level, heading path normalizado e ocorrência determinística do mesmo path.

Duplicidade estrutural recebe keys distintas, porém fica `anchor_state=unresolved` quando não houver alvo renderizado inequívoco.

`anchor_state` controla **navegabilidade**, não elegibilidade de retrieval. Uma seção unresolved continua pesquisável e identificável por `section_key`.

## Section text

Texto após o heading até o próximo heading.
- heading não é duplicado em text_norm;
- limite: 4.000 caracteres normalizados por seção;
- máximo: 64 seções/post;
- ordem editorial preservada.

## Ranking

Pré-condição: parent post já foi ranqueado/autorizado.

Sinais: exact section title, title token coverage, section text coverage, exact phrase em title/text e bounded parent-rank boost.

Um parent match isolado não transforma toda seção em resultado.

O ranker não exige deep-link disponível. `generated|unresolved` podem ser resultados; somente a camada de navegação decide se existe `deep_link_url`.

Para query com >=2 tokens, seção precisa cobrir pelo menos 50% dos tokens no title/text.

Tie-break:
1. score DESC;
2. title coverage DESC;
3. parent rank ASC;
4. section ordinal ASC;
5. section_key ASC.

## Bounds

- parents: máximo 20;
- seções/post: máximo 64;
- retorno/post: default 3, máximo 5;
- nenhuma busca SQL dentro do JSON;
- uma consulta bounded por IDs para carregar seções.

## Proibições

Segundo ranker de post, segunda tabela, mutação editorial, aliases/vocabulary/relevance rules, telemetry, IA ou vetor.


## Addendum v1.1

A separação formal entre retrieval e deep-link está congelada em:
`g590-section-retrieval-addendum-v1.1.md`.

Result contract atual: `search-section-result-v1.1.0`.

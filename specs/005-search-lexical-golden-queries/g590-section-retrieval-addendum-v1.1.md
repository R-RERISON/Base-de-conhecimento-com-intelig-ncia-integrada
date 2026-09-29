# G-590 — Section Retrieval Addendum v1.1

**Status:** FROZEN PARA HOMOLOGAÇÃO  
**Data:** 2026-09-21  
**Supersede parcial:** elegibilidade do ranker/result v1.0.0

## Problema encontrado em review pré-homologação

A implementação inicial fazia o `Search_Section_Ranker` ignorar qualquer seção com `anchor_state != generated`.

Isso acoplava indevidamente duas capabilities distintas:

- **ASI-003/ASI-004:** localizar e identificar a seção relevante;
- **ASI-005:** navegar diretamente até a seção por deep-link.

Uma seção estruturalmente válida pode ser relevante e possuir `section_key` estável mesmo quando o destino HTML é ambíguo ou não pode receber anchor de forma inequívoca.

## Decisão

### Retrieval

O ranker considera seções `generated` **e** `unresolved`.

Relevância continua dependendo exclusivamente de:
- query normalizada;
- título;
- texto da seção;
- coverage;
- parent rank bounded.

`anchor_state` não adiciona score e não remove uma seção relevante.

### Navigation

O result contract separa:

- `parent_url` — permalink do artigo;
- `deep_link_url` — preenchido somente quando `anchor_state=generated`;
- `deep_link_available` — booleano explícito;
- `url` — deep-link quando disponível; fallback para `parent_url` quando não.

Nenhum `#fragment` é inventado para seção unresolved.

## Versões

- Section Projection: `search-section-projection-v1.0.0` — inalterada;
- Section Ranker: `lexical-section-ranker-v1.1.0`;
- Section Result: `search-section-result-v1.1.0`.

## Regra de produto

**Encontrabilidade não depende de navegabilidade.**

Uma limitação de anchor nunca pode apagar silenciosamente uma seção relevante do retrieval.

## Regressão obrigatória

- seção unresolved pode ser ranqueada;
- `section_key` permanece presente;
- `deep_link_available=false`;
- `deep_link_url=''`;
- `url=parent_url`;
- seção generated continua retornando `url` com fragment;
- ranker post-level permanece `lexical-ranker-v1.0.0`.

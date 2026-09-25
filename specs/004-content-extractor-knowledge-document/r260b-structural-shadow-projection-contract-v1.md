# R-260B — Structural Shadow Projection Contract v1

**Status:** FROZEN PARA DIAGNÓSTICO AMBIENTAL  
**Data:** 2026-09-24  
**Owner:** SPEC-004 + SPEC-005 joint review  
**Runtime effect:** ZERO

## Origem

R-260A/RC7 classificou os 548 candidatos determinísticos:

- TOC-like: 77;
- body-bearing: 289;
- uncertain: 182.

T590-16 passou; T590-15 permaneceu como único blocker.

## Objetivo

Antes de criar qualquer estrutura derivada produtiva, medir em shadow mode:

1. colisão de body candidates com headings reais;
2. duplicidade de label entre body candidates;
3. impacto no limite `Search_Section_Projector::MAX_SECTIONS=64`;
4. quantidade de candidates que exigiria extensão do anchor contract atual.

## Estados shadow

Cada candidate determinístico é classificado exatamente em um estado:

- `toc_suppressed`: R-260A marcou TOC-like;
- `existing_heading_redundant`: body-bearing, mas label já existe como heading real no post;
- `duplicate_candidate_ambiguous`: body-bearing sem heading real, porém label aparece em mais de um candidate;
- `promotable_shadow`: body-bearing, sem heading real e label único entre candidates;
- `uncertain`: sem body signal e não TOC-like.

A soma dos estados deve ser igual ao candidate_count.

## Capacity

Para cada post:

```
shadow_section_count = existing_section_count + promotable_shadow_count
```

Registrar:
- `existing_at_limit`;
- `max_sections_exceeded`;
- `overflow_by`.

Nenhuma Section real é criada.

## Anchorability

O Deep-Link Contract v1 materializa anchors somente em headings `h1..h6`.

Todo `promotable_shadow` originado de paragraph deve ser reportado como:

`paragraph_target_not_supported_by_heading_only_anchor_contract`

Isso é blocker arquitetural para deep-link direto, não autorização para mudar o Anchor Manager.

## Guardrails

R-260B não pode:
- alterar Content Extractor;
- alterar KD;
- alterar Search Section Projector;
- gravar Projection;
- alterar post_content;
- criar anchor real;
- mudar ranking;
- chamar rede externa.

## Decision Gate R-260B

A implementação produtiva continua bloqueada até sabermos:

- quantos candidates são realmente `promotable_shadow`;
- quantos são redundantes/duplicados;
- se existe overflow >64;
- se a extensão de anchor para targets não-heading é necessária e segura.

## Arquitetura candidata

Option C — **Dedicated Structural Projection shared by consumers** permanece a hipótese preferida.

Ela só pode ser congelada após RC8 se os números de capacity/collision forem compatíveis com uma projeção versionada e se o contrato de deep-link tiver uma solução fail-closed sem write editorial.

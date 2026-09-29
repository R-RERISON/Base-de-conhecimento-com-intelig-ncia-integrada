# R-260C — Anchor Feasibility Contract v1

**Status:** FROZEN PARA DIAGNÓSTICO AMBIENTAL  
**Data:** 2026-09-24  
**Owner:** SPEC-004 + SPEC-005 joint review  
**Runtime effect:** ZERO

## Origem

R-260B/RC8 classificou os 548 deterministic candidates em:

- toc_suppressed: 77;
- existing_heading_redundant: 0;
- duplicate_candidate_ambiguous: 78;
- promotable_shadow: 211;
- uncertain: 182.

Capacity:
- overflow_post_count: 0;
- overflow_total: 0;
- existing_at_limit_post_count: 0.

Todos os 211 `promotable_shadow` permanecem bloqueados apenas porque o Deep-Link Contract v1 materializa anchors exclusivamente em headings.

## Objetivo

Provar, sem injetar anchors, se o HTML renderizado possui um alvo inequívoco para cada `promotable_shadow`.

## Escopo de matching

Para cada candidate:
1. procurar match exato normalizado em `<p>`;
2. se não houver, procurar match exato normalizado em blocos `<p>|<li>|<div>`;
3. nunca aceitar substring;
4. nunca aceitar mais de um target como navegável.

## Estados

Cada promotable candidate recebe exatamente um:

- `paragraph_unique`;
- `paragraph_ambiguous`;
- `block_unique_nonparagraph`;
- `block_ambiguous`;
- `not_rendered_exact`.

A soma dos estados deve ser igual ao total de promotable candidates.

## Guardrails

R-260C não pode:
- alterar Search Anchor Manager;
- injetar anchor;
- alterar Search Section Projector;
- gravar Search Projection;
- alterar KD/Extractor;
- escrever `post_content`;
- chamar rede externa.

## Decision Gate R-260C

A extensão de deep-link só pode ser proposta se a evidência ambiental mostrar cobertura suficiente de targets inequívocos.

Preferência de segurança:
- `paragraph_unique` é o target candidato primário;
- `block_unique_nonparagraph` exige revisão adicional antes de qualquer implementação;
- estados ambiguous/no-match continuam fail-closed.

R-260C não fecha G-590. Ele decide se existe base segura para um Deep-Link Contract v2.

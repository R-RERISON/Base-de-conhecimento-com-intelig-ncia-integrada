# R-260D1 — Contextual Anchor Feasibility Contract v1

**Status:** FROZEN PARA DIAGNÓSTICO AMBIENTAL  
**Data:** 2026-09-25  
**Owner:** SPEC-004 + SPEC-005 joint review  
**Runtime effect:** ZERO

## Origem

RC9 / R-260C comprovou que os 211 `promotable_shadow` possuem representação exata no HTML renderizado:

- `paragraph_unique`: 131;
- `paragraph_ambiguous`: 80;
- `block_unique_nonparagraph`: 0;
- `block_ambiguous`: 0;
- `not_rendered_exact`: 0.

Conclusão R-260C:
- o Deep-Link Contract v2 é tecnicamente viável em modo fail-closed;
- 131 targets já são inequívocos por título;
- 80 targets não podem usar seleção por primeira ocorrência, índice ou substring;
- nenhuma promoção runtime é autorizada antes de medir desambiguação contextual.

## Objetivo

Medir se os 80 títulos renderizados mais de uma vez podem ser resolvidos deterministicamente usando contexto corporal adjacente derivado da fonte.

R-260D1 continua diagnóstico. Ele não cria Sections, não muda `anchor_state` e não injeta anchors.

## Algoritmo

Para cada `promotable_shadow`:

1. localizar somente parágrafos `<p>` cujo texto normalizado seja exatamente igual ao `title_norm`;
2. se houver exatamente um, classificar `title_unique`;
3. se houver mais de um, derivar da sequência de fragments os dois primeiros blocos textuais não vazios após o candidate e antes da próxima boundary estrutural;
4. a boundary usa a mesma semântica de R-260A: heading real ou node `numbering_inferred` com `depth > 1`;
5. comparar os dois blocos fonte, em ordem, com os dois blocos renderizados imediatamente posteriores a cada ocorrência do título;
6. somente uma ocorrência com ambos os blocos exatos => `context_unique`;
7. zero ou múltiplas ocorrências continuam fail-closed.

Não é permitido:
- primeira ocorrência;
- nth occurrence persistida;
- substring;
- fuzzy matching;
- score heurístico;
- mutação editorial.

## Estados

Cada candidate recebe exatamente um:

- `title_unique`;
- `context_unique`;
- `context_ambiguous`;
- `context_insufficient`;
- `context_not_matched`;
- `title_not_rendered`.

Invariantes:
- soma dos estados = `promotable_count`;
- `effective_unique_count = title_unique + context_unique`;
- `unresolved_remaining_count = promotable_count - effective_unique_count`.

## Guardrails

R-260D1 não pode:
- alterar Content Extractor;
- alterar Knowledge Document;
- alterar Search Section Projector;
- gravar Search Projection;
- alterar Anchor Manager;
- escrever `post_content`, Elementor ou metadata;
- chamar rede externa;
- redefinir T590-15 para obter PASS.

## Decision Gate D1 → D2

R-260D2 runtime promotion somente pode ser proposto após evidência ambiental RC10.

A evidência deve:
- preservar integralmente R-260C;
- demonstrar `title_not_rendered=0` no mesmo corpus;
- informar ganho real de `context_unique`;
- manter casos ambíguos/insuficientes/no-match como unresolved;
- manter T590-14/T590-16/T590-17/T590-18 PASS;
- manter fingerprint editorial idêntico;
- manter G-590 OPEN durante D1.

Não existe threshold percentual artificial para aprovar D2. O critério é ausência de falso alvo: somente identidade contextual inequívoca pode virar candidata a deep-link.

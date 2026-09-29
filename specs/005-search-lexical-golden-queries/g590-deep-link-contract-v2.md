# G-590 — Deep-Link / Anchor Contract v2

**Status:** FROZEN PARA IMPLEMENTAÇÃO / HOMOLOGAÇÃO  
**Data:** 2026-09-28  
**Supersede:** v1 para resolução de targets não-heading  
**Anchor prefix:** `bdc-kb-section-`

## Princípio

Deep-link só é emitido quando o destino renderizado pode ser comprovado deterministicamente.

Encontrabilidade continua independente de navegabilidade.

## Escrita editorial

**ZERO.**

O BDC nunca persiste id em:
- post_content;
- _elementor_data;
- metadata editorial.

Anchor continua efêmero em `the_content`.

## Heading targets

Comportamento v1 preservado:
- normalizar heading h1..h6;
- exigir exatamente um target;
- inserir span vazio;
- duplicidade => fail-closed.

## Numbered paragraph targets

Uma Section estrutural derivada de paragraph pode receber anchor somente por uma destas estratégias:

### title_unique

- localizar `<p>` por texto normalizado exatamente igual ao title_norm;
- exigir exatamente uma ocorrência.

### context_unique

Quando o título aparece mais de uma vez:
- usar exatamente dois blocos corporais normalizados derivados da fonte;
- comparar os blocos em ordem imediatamente após cada ocorrência renderizada;
- exigir exatamente uma ocorrência contextual.

Não permitido:
- primeira ocorrência;
- nth occurrence persistida;
- substring;
- fuzzy matching;
- similaridade;
- score heurístico;
- posição percentual.

## Persistência derivada

A Section pode persistir apenas dados reconstruíveis:
- anchor_strategy;
- anchor_context_parts normalizados e bounded;
- target_state;
- anchor_id derivado de section_key.

Offset DOM nunca é persistido.

## Revalidação runtime

Mesmo quando Projection marcou `anchor_state=generated`, o Anchor Manager reexecuta a estratégia sobre o HTML atual.

Se o target não continuar inequívoco:
- não injeta anchor;
- não altera texto;
- falha fechado.

## URL

Sem alteração:

`get_permalink(post_id) . '#' . rawurlencode(anchor_id)`

Somente `anchor_state=generated` produz `deep_link_url`.

`unresolved`:
- permanece pesquisável;
- deep_link_available=false;
- deep_link_url='';
- url=parent_url.

## Idempotência

Se o anchor BDC já existir no HTML:
- não inserir novamente.

## Regressão obrigatória

- texto visível antes/depois idêntico;
- heading behavior v1 preservado;
- title_unique paragraph materializa;
- context_unique paragraph materializa;
- duplicate/context mismatch não materializa;
- unresolved continua no retrieval;
- zero write editorial;
- zero network.

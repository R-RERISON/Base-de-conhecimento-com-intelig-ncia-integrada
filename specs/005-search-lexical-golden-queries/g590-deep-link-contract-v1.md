# G-590 — Deep-Link / Anchor Contract v1

**Status:** FROZEN PARA IMPLEMENTAÇÃO
**Anchor prefix:** `bdc-kb-section-`

## Princípio

Deep-link só é emitido quando o destino estrutural pode ser comprovado.

## Escrita editorial

**ZERO.**

O BDC não grava id em `post_content`, `_elementor_data` ou metadata.

## Anchor renderizado

Em singular post, no main loop/main query, o BDC pode enriquecer o HTML retornado por `the_content` com anchor efêmero.

O anchor:
- deriva de `section_key`;
- não substitui id/name existente;
- é inserido como span vazio dentro do heading alvo;
- não altera texto visível;
- não altera fonte editorial.

Formato: `bdc-kb-section-{20 hex}`.

## Resolução

O Anchor Manager:
1. lê sections projetadas;
2. considera somente `anchor_state=generated`;
3. normaliza headings renderizados;
4. encontra alvo pelo título;
5. exige exatamente uma correspondência inequívoca;
6. em 0 ou >1 correspondências, não injeta.

A Section Projection marca títulos duplicados como unresolved por default. Runtime também falha fechado.

## URL

`get_permalink(post_id) . '#' . rawurlencode(anchor_id)`.

## Regressão

Obrigatório provar:
- texto visível idêntico;
- nenhum post_content alterado;
- idempotência;
- headings duplicados sem deep-link;
- anchor somente para post/section atuais.


## Retrieval independente

Este contrato não controla se uma seção pode aparecer na Search.

- `anchor_state=generated`: Section Result pode emitir `deep_link_url`;
- `anchor_state=unresolved`: Section Result continua válido, mas `deep_link_url=''` e `url` cai para o permalink do artigo.

Portanto, falha de anchor é fail-closed para **navegação direta**, não para **encontrabilidade da seção**.

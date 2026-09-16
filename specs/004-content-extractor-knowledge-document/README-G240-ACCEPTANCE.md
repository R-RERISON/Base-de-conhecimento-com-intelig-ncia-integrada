# G-240 — Real Content Acceptance

## Estado

**G-240 v1: FAIL CONTROLADO — STRUCTURE LOSS.**  
**G-240 v2: STRUCTURAL REMEDIATION ACTIVE.**

Não reutilizar `0.4.0-acceptance.1` para novo aceite.

## O que o v1 provou

A evidência `evidence/g240-acceptance-20260916T085721Z.json` mostrou:

- 8/8 slots revisados;
- cobertura completa nos 8;
- ordem preservada nos 8;
- nenhum texto inventado nos 8;
- 7/8 com perda estrutural;
- zero stale;
- zero repeatability failure;
- zero write.

Portanto o defeito está localizado no modelo estrutural, não na segurança/read-only nem na cobertura textual básica.

## Remediação v2

Knowledge Document `2.0.0` adiciona:

- `heading_path`;
- `blocks[]`;
- árvore de listas;
- tabela estruturada;
- `ai_readiness` objetivo.

O checkbox humano “aceitável para IA” foi removido.

## Próximo package

O próximo build será `0.4.0-acceptance.2`, mas ele só deve ser liberado após:

- testes/lint locais;
- package parity;
- smoke full-corpus v2 pronto.

Quando liberado, haverá duas ferramentas temporárias:

1. **Base de Conhecimento → Validação KD v2** — rodar primeiro;
2. **Base de Conhecimento → Aceitação G-240 v2** — rodar somente se a validação full-corpus passar.

## Aceitação v2

A amostra A/B usa os mesmos oito posts do v1.

Marcar somente:

- cobertura completa;
- ordem semântica preservada;
- nenhum texto inventado;
- estrutura semântica preservada.

O sistema exibirá `ai_readiness`; não é necessário o operador decidir subjetivamente se a estrutura é “boa para IA”.

## Bloqueios

- PR #3 não deve ser mergeado enquanto G-240 v2 não passar;
- G-245 permanece bloqueado;
- nenhum writer Elementor está autorizado.

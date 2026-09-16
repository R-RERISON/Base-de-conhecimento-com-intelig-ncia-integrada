# G-240 — Real Content Acceptance

## Estado

**G-240 v1: FAIL CONTROLADO — STRUCTURE LOSS.**  
**G-240 v2: STRUCTURAL REMEDIATION ACTIVE.**

Não executar o aceite humano enquanto o full-corpus KD v2 estiver com `gate.pass=false`.

## O que o v1 provou

A evidência `evidence/g240-acceptance-20260916T085721Z.json` mostrou:

- 8/8 slots revisados;
- cobertura completa nos 8;
- ordem preservada nos 8;
- nenhum texto inventado nos 8;
- 7/8 com perda estrutural;
- zero stale;
- zero repeatability failure.

O problema foi estrutural, não de determinismo ou cobertura textual.

## Gate técnico antes do A/B humano

A ferramenta **Base de Conhecimento → Validação KD v2** deve produzir simultaneamente:

- corpus invariável;
- fingerprint editorial idêntico;
- zero posts alterados;
- zero errors/throwables;
- zero hash/canonical JSON mismatch;
- `structure_incomplete=0` nas duas passagens;
- `ai_readiness.not_ready=0` nas duas passagens;
- `DOMDocument=true`.

## Último resultado — `acceptance.7`

Evidência: `evidence/kd-v2-smoke-20260916T124150Z.json`.

- 622/622 documentos nas duas passagens;
- segurança/determinismo PASS;
- `structure_incomplete=5`;
- `not_ready=5`;
- headings mismatch zerado;
- 3 documentos residuais por listas (`105 expected / 90 actual`);
- 2 documentos residuais por tabelas (`6 / 4`).

## Build atual — `0.4.0-acceptance.8`

Build **diagnóstico-only**. Não altera parser, KD schema `2.0.1`, readiness ou gate. Adiciona somente:

- `HTML_DIAG_LIST_PARSER_UNREACHABLE:<n>`;
- `HTML_DIAG_LIST_NO_MATERIALIZABLE_CONTENT:<n>`;
- `HTML_DIAG_LIST_IMAGE_ONLY:<n>`;
- `HTML_DIAG_TABLE_PARSER_UNREACHABLE:<n>`;
- `HTML_DIAG_TABLE_NO_MATERIALIZABLE_TEXT:<n>`;
- `HTML_DIAG_TABLE_IMAGE_ONLY:<n>`.

Objetivo: distinguir perda real de alcance do parser, markup estrutural sem conteúdo materializável e estruturas image-only antes da última correção.

## Execução atual

1. instalar `0.4.0-acceptance.8`;
2. executar somente **Validação KD v2**;
3. baixar e retornar `bdc-kb-spec004-kd-v2-smoke-*.json`;
4. **não executar Aceitação G-240 v2 ainda**.

## A/B humano posterior

Somente após full-corpus PASS serão reutilizados exatamente os mesmos 8 posts do G-240 v1. O humano avaliará apenas:

- cobertura;
- ordem;
- ausência de invenção;
- estrutura preservada.

`ai_readiness` é calculado pelo sistema, não por checkbox humano.

Qualquer alteração editorial posterior ao baseline torna o slot `stale`.

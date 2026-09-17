# Package `0.4.0-acceptance.7`

## Objetivo

Build temporário de homologação para validar Knowledge Document `2.0.1` e `Semantic_DOM_Expectation` em full-corpus.

## Artefato

- arquivo: `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.7.zip`;
- SHA-256: `635016ef4a5acb4d94d04f7b0e9e05ce8d86af637c5654f96b0d58e3904cc91e`.

## Resultado ambiental

Evidência: `evidence/kd-v2-smoke-20260916T124150Z.json`.

- 622/622 documentos em duas passagens;
- zero errors/throwables;
- zero source/document hash mismatch;
- zero canonical JSON mismatch;
- fingerprint editorial idêntico;
- zero posts alterados;
- `structure_incomplete`: **50 → 5**;
- headings mismatch: **47 docs → 0**;
- residual: 3 docs de listas (`105 expected / 90 actual`) e 2 docs de tabelas (`6 / 4`);
- `ai_readiness`: 546 candidate_ready, 69 review_required, 5 not_ready, 2 not_applicable;
- gate FAIL porque exige `structure_incomplete=0` e `not_ready=0`.

O package foi sucedido pelo `0.4.0-acceptance.8` somente para diagnóstico dos cinco casos restantes; o contrato KD permanece `2.0.1`.

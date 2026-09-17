# Package `0.4.0-acceptance.8`

## Objetivo

Build temporário e read-only para diagnosticar os **5/622** Knowledge Documents ainda `not_ready` após o `acceptance.7`.

Este package **não corrige o parser**, não altera o Knowledge Document `2.0.1`, não altera `ai_readiness` e não relaxa o gate. Ele apenas adiciona telemetria agregada para separar as classes residuais de listas e tabelas.

## Baseline ambiental que motivou o build

`acceptance.7`:

- corpus 622 → 622;
- 622/622 documentos nas duas passagens;
- zero errors/throwables;
- zero source/document hash mismatch;
- zero canonical JSON mismatch;
- fingerprint editorial idêntico;
- zero posts alterados;
- `structure_incomplete`: **50 → 5** em relação ao `acceptance.6`;
- headings mismatch: **47 docs → 0**;
- resíduo: 3 docs de listas (`105 expected / 90 actual`) e 2 docs de tabelas (`6 / 4`);
- `ai_readiness`: 546 candidate_ready, 69 review_required, 5 not_ready, 2 not_applicable.

Evidência: `evidence/kd-v2-smoke-20260916T124150Z.json`.

## Diagnósticos adicionados

Somente em `Semantic_DOM_Expectation`:

- `HTML_DIAG_LIST_PARSER_UNREACHABLE:<n>`;
- `HTML_DIAG_LIST_NO_MATERIALIZABLE_CONTENT:<n>`;
- `HTML_DIAG_LIST_IMAGE_ONLY:<n>`;
- `HTML_DIAG_TABLE_PARSER_UNREACHABLE:<n>`;
- `HTML_DIAG_TABLE_NO_MATERIALIZABLE_TEXT:<n>`;
- `HTML_DIAG_TABLE_IMAGE_ONLY:<n>`.

Esses warnings são **telemetria diagnóstica**. Não entram nas listas de warning crítico/review do `ai_readiness` e não alteram `structure_complete`.

## Gate preservado

O smoke report permanece `1.3.0` e exige:

- DOMDocument disponível;
- corpus/fingerprint invariáveis;
- zero posts alterados;
- zero errors/throwables;
- zero hash/canonical JSON mismatch;
- `structure_incomplete=0` nas duas passagens;
- `ai_readiness.not_ready=0` nas duas passagens.

Como este é um build diagnóstico, é esperado que o gate possa continuar `false`; o objetivo da rodada é explicar os cinco casos restantes sem adivinhar a correção.

## Artefato

- arquivo: `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.8.zip`;
- SHA-256: `a089fbd5cdb0fe6cc04a418f9eface8d135ac9d0fee1232307c5a3463b9d0091`;
- runtime files: 28;
- PHP files: 24.

## Validação

- PHP lint: **24/24 PASS**;
- JS syntax: PASS;
- ZIP integrity: PASS;
- Knowledge Document regression: **12/12 PASS**;
- structural namespace regression: **6/6 PASS**;
- schema KD permanece `2.0.1`;
- parser Legacy permanece inalterado em relação ao `acceptance.7`;
- readiness/gate permanecem inalterados;
- safety scan focado: PASS.

## Git ↔ package parity

Arquivos modificados nesta rodada:

- bootstrap: `6f115f8241b774a9f04e7ecbc84a17fb4925730a`;
- `Semantic_DOM_Expectation`: `125908183b0e2fd931b9aa84cf01930d9205b499`.

Os hashes Git são idênticos aos blobs calculados a partir do ZIP extraído. Os demais arquivos runtime são os mesmos do `acceptance.7`, cuja parity já havia sido fechada.

## Execução de homologação

1. instalar/substituir pelo `0.4.0-acceptance.8`;
2. executar somente **Base de Conhecimento → Validação KD v2**;
3. não executar ainda `Aceitação G-240 v2`;
4. retornar `bdc-kb-spec004-kd-v2-smoke-*.json`;
5. analisar os seis warnings diagnósticos acima contra os 3 mismatches de listas e 2 de tabelas;
6. aplicar somente a correção comprovada;
7. reexecutar full-corpus até `structure_incomplete=0` e `not_ready=0`;
8. somente então liberar os mesmos oito casos A/B humanos.

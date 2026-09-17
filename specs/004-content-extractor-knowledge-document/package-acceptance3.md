# Package de homologação — `0.4.0-acceptance.3`

## Objetivo

Build temporário e estritamente diagnóstico para explicar os `82` documentos com `structure_complete=false` observados no smoke ambiental do `0.4.0-acceptance.2`.

Este package **não altera o parser estrutural, o Knowledge Document v2 nem o critério do gate**. Ele apenas amplia o relatório do runner `Validação KD v2` com agregados técnicos sem conteúdo editorial.

## Evidência que motivou o build

- `evidence/kd-v2-smoke-20260916T100412Z.json`
- 622/622 documentos nas duas passagens;
- zero errors;
- zero throwables;
- zero hash mismatch;
- zero canonical JSON mismatch;
- fingerprint editorial igual;
- zero posts alterados;
- `first_pass_structure_incomplete=82`;
- `second_pass_structure_incomplete=82`;
- `gate.pass=false`.

## Instrumentação adicionada

O schema do relatório temporário passa de `1.0.0` para `1.1.0` e adiciona somente agregados:

- `structure_incomplete_by_source_kind`;
- `structure_incomplete_by_strategy`;
- `structure_incomplete_by_elementor_compatibility`;
- `structure_mismatch_metrics`;
- `structure_mismatch_signatures`;
- `ai_readiness_reasons`.

Nenhum post ID, título, URL, conteúdo editorial, `_elementor_data` ou documento canônico é exportado.

## Validação local/package

- arquivos runtime: `27`;
- arquivos PHP: `23`;
- PHP lint: `23/23 PASS`;
- JS syntax: `1/1 PASS`;
- teste sintético KD v2: `ALL PASS`;
- ZIP integrity: PASS;
- staging ↔ ZIP parity: PASS;
- blob Git do runner: `bec678380a70371c6c3a934f23585b10dd61e70f`;
- blob Git do bootstrap: `0f06952d6c0343683f380b8e7dd36de791e54d17`;
- ZIP SHA-256: `31ad45e85053409cb7dce4b7547703d4f7a9c52b156174c9ff688c24ed5931f5`.

## Execução em homologação

1. instalar/substituir pelo `0.4.0-acceptance.3`;
2. confirmar a versão no WordPress;
3. não executar `Aceitação G-240 v2`;
4. executar apenas **Base de Conhecimento → Validação KD v2**;
5. retornar o JSON `bdc-kb-spec004-kd-v2-smoke-*.json`.

## Decisão de gate

O G-240 continua **BLOCKED** até:

- identificar a dimensão exata dos 82 mismatches;
- corrigir apenas causa comprovada;
- reexecutar o smoke com `structure_incomplete=0` nas duas passagens;
- manter zero mutation e determinismo;
- somente então executar a aceitação humana A/B v2.

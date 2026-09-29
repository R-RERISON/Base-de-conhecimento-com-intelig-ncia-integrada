# Package — SPEC-005 R-500/T502

Build: `0.5.0-r500-t502.1`  
SHA-256: `0b92d355be79c23e6837fa4b11cd6982674b643795f3e8de4f4949b9c47f5b86`

## Conteúdo

Build temporário de homologação contendo somente o diagnóstico read-only R-500/T502 além do runtime já aprovado da SPEC-004.

## Validação local

- 45 arquivos;
- 40 PHP;
- 40/40 PHP lint pré-ZIP;
- 40/40 PHP lint pós-extração;
- 39/39 requires ativos;
- missing requires = 0;
- ZIP íntegro;
- rebuild determinístico byte-a-byte;
- diagnóstico/bootstrap locais idênticos aos Git blobs do branch;
- forbidden write/network calls = 0.

## Flag temporária

`BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD=true`.

Esta flag deverá voltar a OFF/ser retirada do artefato final após o gate de discovery.

## Execução ambiental

Após instalar em homologação:

`Base de Conhecimento -> Diagnóstico Search R-500`

Executar e baixar o JSON.

PASS de segurança esperado:

`gate_result.t502_read_only_safety_pass=true`.

R-500 continuará aberto mesmo após T502 PASS, pois ainda exige interpretação dos gaps, decisão de superfície e Golden Dataset R-510.

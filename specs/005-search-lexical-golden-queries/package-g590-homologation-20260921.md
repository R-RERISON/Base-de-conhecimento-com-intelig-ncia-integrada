# G-590 — Homologation package/runbook

**Build:** `0.5.0-g590.1`  
**Branch:** `homologation/spec005-g590-20260921`  
**Status:** CANDIDATE_STATIC_PASS / ENV PENDING  
**Cutover:** PROIBIDO

## Objetivo

Validar no ambiente real:
- upgrade físico Search Projection 1.0 → 1.1;
- nenhum rebuild implícito;
- fallback/degraded seguro em version mismatch;
- rebuild explícito determinístico;
- Golden post-level sem regressão;
- Section Retrieval;
- deep-link materializável no HTML real;
- coverage de estruturas numeradas;
- budget de performance;
- zero mutação editorial.

## Perfil de build

- `BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD=true`;
- `BDC_KB_SPEC005_G590_SECTION_BUILD=true`;
- `BDC_KB_UX004_H030_TECHNICAL_BUILD=false`;
- Public Experience atual permanece presente;
- Word Cloud atual permanece presente;
- demais runners históricos permanecem desligados.

## Instalação

1. manter backup/snapshot normal do ambiente de homologação;
2. **não desativar ASI/GRE/KB2Ops para este gate**;
3. instalar/atualizar o BDC com este build;
4. confirmar versão `0.5.0-g590.1`;
5. não executar rebuild manual fora do runner antes da primeira coleta, pois o runner precisa observar o estado de transição.

## Execução

WordPress Admin:

**Base de Conhecimento → Section Retrieval G-590**

Executar **uma vez** e baixar o JSON.

O runner:
1. captura fingerprint editorial;
2. executa `prepare_schema()`;
3. verifica colunas/índices;
4. confirma ausência de rebuild implícito;
5. executa rebuild explícito;
6. audita corpus;
7. executa probes section/deep-link;
8. mede performance;
9. reroda Golden G-550;
10. compara fingerprint editorial.

## Critérios bloqueantes

O validador exige:
- schema current;
- version transition safe;
- rebuild PASS/ready/determinístico;
- corpus totalmente analisado;
- zero extractor errors;
- zero numbered strong signals sem heading contextual;
- mínimo 5 probes;
- zero section query failure;
- zero deep-link materialization failure;
- texto visível idêntico;
- p95 <= 900 ms;
- max <= 1500 ms;
- Golden post-level PASS;
- zero mutation editorial/network/ASI dependency;
- T590-14..T590-19 true.

## Revisão não bloqueante automática

`numbered_granularity_review_required=true` significa que existem itens numerados fortes dentro de uma seção heading.

Não é auto-falha porque o conteúdo já está recuperável na seção pai, mas exige revisão antes de declarar paridade granular com ASI.

## Validação do JSON

```bash
php tools/homologation/spec005/validate-g590-evidence.php caminho/para/bdc-kb-spec005-g590-section-*.json
```

Resultado exigido:

```text
RESULT passed=<N> failed=0
```

## Rollback

Se ocorrer problema:
1. reverter para o pacote BDC anterior;
2. a Projection é derivada e pode permanecer;
3. deactivation/uninstall não removem dados por default;
4. nenhum conteúdo editorial deve ter sido alterado;
5. ASI/GRE/KB2Ops continuam ativos, portanto não há cutover neste gate.

## Interpretação

Mesmo com G-590 PASS:
- ASI-003/004/005 só podem migrar de PARTIAL para VERIFIED depois que a evidência for revisada/versionada;
- G-585 ainda precisa ser retomado;
- ASI não pode ser aposentado;
- SPEC-006 continua bloqueada até fechamento da boundary SPEC-005.

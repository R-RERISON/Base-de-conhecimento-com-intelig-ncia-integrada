# P-630 — Domain Closure Contract v1

**Status:** IMPLEMENTATION
**Data:** 2026-09-29

## Objetivo

Fechar ownership de dados herdados de GRE/KB2Ops sem dual-write permanente, sem migração destrutiva e sem criar taxonomias novas apenas por analogia.

## Owners canônicos

### Knowledge Facts

O BDC passa a possuir os seguintes conceitos através de `Knowledge_Facts_Store`:

| Conceito | Chave física canônica | Compatibilidade |
|---|---|---|
| affected_service | `_bdc_es_affected_service` | fallback read-only de `_kb2ops_service` |
| systems_involved | `_bdc_es_systems_involved` | nenhuma fusão automática com technologies |
| technologies | `_kb2ops_technologies` | chave física histórica adotada pelo BDC |
| keywords | `_kb2ops_keywords` | chave física histórica adotada pelo BDC |
| versions | `_kb2ops_versions` | chave física histórica adotada pelo BDC |

Regras:
- adotar uma chave física histórica não significa depender do plugin legado;
- `_kb2ops_service` é somente fallback de leitura;
- writes de affected_service ocorrem apenas em `_bdc_es_affected_service`;
- systems_involved e technologies permanecem conceitos distintos;
- não há dual-write;
- normalização futura para taxonomias exige gate próprio e evidência de uso.

### Helpful Tips

- owner: `Helpful_Tips_Store`;
- physical key: `_bdc_es_helpful_tips`;
- estrutura: lista ordenada `{title:string, content:string}`;
- BDC assume read + write;
- update valida payload inteiro antes do primeiro write;
- capability por post;
- read-after-write;
- rollback para snapshot anterior se a confirmação falhar.

### Coverage

`Coverage_Read_Model` compõe os oito campos históricos GRE sem persistência adicional:

1. objective;
2. responsible_team;
3. catalog_item;
4. affected_service;
5. systems_involved;
6. audience;
7. escalation;
8. important.

Estados:
- EMPTY;
- PARTIAL;
- COMPLETE.

Coverage é read-only e derivado dos owners canônicos.

## KB2Ops disposition

| Capability histórica | Disposição |
|---|---|
| service | superseded pelo owner affected_service; legacy key read-only fallback |
| technologies | physical key adotada pelo Knowledge Facts owner |
| keywords | physical key adotada pelo Knowledge Facts owner |
| versions | physical key adotada pelo Knowledge Facts owner |
| include_ai / AI READY | fora da SPEC-006; permanece SPEC-012 |
| pre-analysis/checklist | fora deste slice; permanece SPEC-006/012 conforme Ledger |

## Não objetivos

- não converter facts em taxonomias neste gate;
- não apagar metas legadas;
- não migrar em massa;
- não alterar Search ranker;
- não alterar artigo/editorial content;
- não fazer retirement de GRE/KB2Ops.

## Gates internos

- P630-01 contract/ownership;
- P630-02 Knowledge Facts store;
- P630-03 Helpful Tips read/write;
- P630-04 Coverage read model;
- P630-05 Public Reader consome owner canônico;
- P630-06 Workspace editing;
- P630-07 regression/security;
- P630-08 environmental acceptance;
- P630-09 Ledger disposition.

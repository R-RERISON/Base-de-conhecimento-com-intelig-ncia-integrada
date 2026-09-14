# Prompt de Continuidade — SPEC-001 concluída

## Referência

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- SPEC: `SPEC-001 — Core mínimo + Summary narrativo`
- Estado: **CONCLUÍDA para desenvolvimento/homologação**.
- Baseline de package: `0.1.0-rc.1`.
- SHA-256: `c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`.

## Estado comprovado

- SPEC-000 concluída.
- S001/DoR SPEC-001: PASS.
- S002/runtime mínimo: PASS.
- Unitário: 15/15 PASS.
- Instalação inicial e leitura de metas GRE existentes: PASS.
- Onclick técnico: 16/16 PASS, 0 resíduos.
- G-001: PASS.
- G-020: PASS.
- B-006: PASS real (`FAIL_SAFE` + `PARTIAL_FAILURE_CRITICAL`).
- G-110: PASS, viewport mínimo 671x660, 0 resíduos.
- G-070 HTTP: PASS 11/11, 0 resíduos.
- G-130 lifecycle: PASS no `0.1.0-rc.1` — substituição, ativação, ausência de diagnóstico, smoke, leitura, desativação/reativação e preservação confirmados pelo operador.
- Nenhum conteúdo real foi modificado pelos runners de homologação.

## Runtime congelado

A baseline `0.1.0-rc.1` contém somente os seis arquivos de produto. Não reintroduzir instrumentos temporários no package de produto.

## Limite da conclusão

A conclusão da SPEC-001 é de **engenharia e homologação**, não de produção/cutover.

Antes de produção:

1. executar B-003/preflight;
2. mapear writers/consumers reais das três chaves Summary;
3. definir single-writer ou coexistência comprovada;
4. definir rollback/cutover;
5. somente então decidir remoção/desativação de GRE/KB2Ops/ASI quando aplicável.

## Próximo ciclo autorizado

Abrir **SPEC-002 — Classificação de Conhecimento** somente em planejamento/Definition of Ready.

Primeiro objetivo: profiling read-only dos conceitos e stores classificatórios históricos. Nenhum novo write classificatório é autorizado antes da decisão formal Taxonomy vs Post Meta e do fechamento do DoR da SPEC-002.

## Regra

A baseline `0.1.0-rc.1` é a referência de regressão. Toda evolução deve provar que não quebra Summary, editorial, segurança, UI e lifecycle já homologados.

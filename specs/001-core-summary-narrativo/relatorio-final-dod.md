# Relatório Final e Definition of Done — SPEC-001

## Decisão

**SPEC-001 — Core mínimo + Summary narrativo: CONCLUÍDA para desenvolvimento/homologação.**

A implementação cumpriu o vertical slice autorizado por T097 sem ampliar silenciosamente o escopo.

## Resultado entregue

Jornada homologada:

`selecionar artigo -> ler objective/escalation/important -> editar -> salvar -> reler -> confirmar estado`

Dados canônicos:

- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

Características confirmadas:

- WordPress/Elementor permanece fonte editorial;
- plugin não escreve `_elementor_data`, `post_content` ou `post_title`;
- Metadata API é o mecanismo de persistência;
- GET é read-only;
- POST usa nonce e capability por objeto;
- allowlist exata;
- empty = delete, omitido = preservar, NO_CHANGE = zero write;
- read-after-write;
- B-006 com compensação e detecção crítica;
- PRG;
- UI no shell nativo do wp-admin;
- package final sem instrumentos de homologação.

## Definition of Done

- [x] DoR fechado antes de código material.
- [x] Runtime mínimo implementado sem schema/tabela/REST/AJAX/SPA/IA.
- [x] PHP lint PASS.
- [x] Unitário 15/15 PASS.
- [x] G-001 PASS.
- [x] G-020 PASS.
- [x] G-070 PASS.
- [x] G-110 PASS.
- [x] B-006 PASS em WordPress real.
- [x] Runners temporários geraram evidência e limparam fixtures.
- [x] `cleanup.residual_fixtures=0` nas execuções relevantes.
- [x] Ferramentas temporárias removidas do package.
- [x] RC limpo produzido com checksum.
- [x] G-130 lifecycle PASS em WordPress real.
- [x] Dados preexistentes preservados.
- [x] Nenhuma migração desnecessária criada.
- [x] Próximo passo documentado sem antecipar nova capacidade.

## Baseline de regressão

`0.1.0-rc.1`

SHA-256:
`c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`

Esta baseline deve permanecer funcional durante toda a SPEC-002.

## Riscos residuais deliberadamente fora da SPEC-001

- produção/cutover;
- coexistência de writers GRE/novo plugin;
- classificação;
- review/governança;
- Content Extractor;
- Search;
- Analytics;
- operações/indexação;
- vetores/semântica;
- IA/Foundry/RAG.

## Próxima decisão

Autorizar somente o **planejamento e profiling read-only** da SPEC-002 — Classificação de Conhecimento.

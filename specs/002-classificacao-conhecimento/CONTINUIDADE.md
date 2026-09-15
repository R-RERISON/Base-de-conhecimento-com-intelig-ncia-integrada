# Prompt de Continuidade — SPEC-002 Classificação de Conhecimento

## Estado

- SPEC-001: CONCLUÍDA para desenvolvimento/homologação.
- SPEC-002: **CONCLUÍDA para desenvolvimento/homologação**.
- Baseline funcional atual: **`0.2.0-rc.1`**.
- SHA-256 do package: `a5120299ea907d271bc39b318857ba033cf0cf340fe1836289f42a4c717b8fb5`.
- Próxima linha ativa: **UX-001 — Product Experience & Knowledge Workspace**.
- SPEC-003 — Review & Governança permanece planejada, mas sua implementação fica bloqueada até o baseline UX-001.

## Gates fechados da SPEC-002

- C-001 Profiling: PASS.
- C-010 Primitive: PASS.
- G-001 Editorial: PASS.
- G-030 Classification: PASS.
- B-006 Compensação: PASS.
- G-070 Segurança HTTP: PASS.
- G-110 Browser Acceptance: PASS.
- G-130 Lifecycle/package: PASS por confirmação do operador em WordPress real.
- Regressão SPEC-001: PASS.

## Baseline canônico de Classificação

Taxonomias WordPress:

- `bdc_kb_audience` — multi;
- `bdc_kb_responsible_team` — multi;
- `bdc_kb_knowledge_type` — single;
- `bdc_kb_catalog_item` — multi.

Legado continua somente como referência read-only/advisory. Não há seed automático, auto-map textual, fallback canônico, dual-write ou migração destrutiva.

## Evidências principais

- profiling real: 622 posts / 36 rows / zero writes;
- diagnóstico técnico `0.2.0-dev.3`: 20/20 PASS;
- segurança HTTP `0.2.0-dev.7`: 18/18 PASS;
- browser acceptance `0.2.0-dev.8`: PASS, viewport mínimo 492x660;
- package limpo `0.2.0-rc.1`: PHP lint 8/8, sem runners/flags de homologação;
- lifecycle RC1: ativação, desativação/reativação e releitura confirmadas pelo operador sem regressão.

## Regra de continuidade

Não iniciar implementação funcional da SPEC-003 enquanto UX-001 não produzir e congelar, no mínimo:

1. arquitetura de informação;
2. inventário de telas;
3. Design System v1;
4. Knowledge Workspace master mockup;
5. mockup Review & Governança;
6. regras responsivas e de acessibilidade;
7. matriz de herança do legado KB2Ops: preservar / evoluir / descartar.

UX-001 é uma trilha transversal: define experiência e layout, mas não autoriza antecipação de regras de negócio ainda não aprovadas pelas SPECs funcionais.

**GO de desenvolvimento/homologação != GO de produção/cutover.**

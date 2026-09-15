# Tarefas — SPEC-002 Classificação de Conhecimento

## S001 — Baseline e profiling real

- [x] T001 Congelar `0.1.0-rc.1` como baseline de regressão.
- [x] T002 Confirmar baseline de abertura.
- [x] T003 Inventariar os 10 conceitos/11 stores históricos candidatos.
- [x] T004 Definir plano de profiling read-only e métricas mínimas.
- [x] T005 Preparar profiler temporário autossuficiente, sem writes.
- [x] T006 Executar profiler no WordPress real — PASS, 622 posts / 36 meta rows / zero writes.
- [x] T007–T013 Fechar cobertura, cardinalidade, qualidade, overlaps, coexistência e primeiro slice.
- [x] T014 Fechar C-001 — PASS e NO-GO para migração automática.

**Gate S001: PASS.**

## S002 — Contratos físicos e segurança

- [x] T020 Primitive: WordPress Taxonomy API para os quatro conceitos do slice.
- [x] T021 Slugs canônicos: `bdc_kb_audience`, `bdc_kb_responsible_team`, `bdc_kb_knowledge_type`, `bdc_kb_catalog_item`.
- [x] T022 Cardinalidade: audience/team/catalog multi; knowledge_type single; omit=preserva; `[]`=remove.
- [x] T023 Normalização por IDs existentes; sem auto-map textual/seed legado.
- [x] T024 Legado apenas read-only/advisory.
- [x] T025 Dual-write permanente proibido.
- [x] T026–T029 Matrizes, UI mínima e rollback/DoR fechados.

**Gate S002: PASS. C-010 PASS.**

## S003 — Runtime mínimo

- [x] T030 Implementar `Classification_Contract` e quatro taxonomias.
- [x] T031 Implementar `Classification_Admin` e gestão de vocabulário via UI nativa WordPress.
- [x] T032 Implementar `Classification_Store` com snapshot/diff/read-after-write/compensação.
- [x] T033 Unitário determinístico Classification Store — PASS 15/15.
- [x] T034 PHP lint do runtime — PASS 8/8; smoke `0.2.0-dev.1` preparado.

**Gate S003: PASS.**

## S004 — Evidência WordPress

- [x] T040 Smoke `0.2.0-dev.1` — PASS visual: Summary preservado, Classificação carrega, vocabulários acessíveis, nenhum legado autopromovido.
- [x] T041 Regressão obrigatória da SPEC-001 — PASS nos runners técnico/HTTP/browser.
- [x] T042A Diagnóstico técnico `0.2.0-dev.3` — PASS 20/20; G-030/B-006/G-001; cleanup zero.
- [x] T042B Segurança HTTP `0.2.0-dev.7` — PASS 18/18; G-070; cleanup zero.
- [x] T043 Browser acceptance `0.2.0-dev.8` — PASS; 2 manual PASS / 0 manual FAIL / 0 auto FAIL; viewport mínimo 492x660; cleanup zero.
- [x] T044 Zero write editorial e zero write nos stores legados — PASS nas três camadas de evidência.
- [x] T045 Retirar integralmente instrumentos temporários do RC — PASS; scan de instrumentação limpo.
- [x] T046A Preparar package limpo `0.2.0-rc.1` — PASS local; PHP lint 8/8; SHA-256 `a5120299ea907d271bc39b318857ba033cf0cf340fe1836289f42a4c717b8fb5`.
- [x] T046B Lifecycle real G-130 — PASS por confirmação do operador: substituição/ativação RC, ausência de homologação, desativação/reativação e releitura sem regressão.
- [x] T047 Fechar DoD/continuidade e autorizar UX-001 antes da SPEC-003.

## Estado final

**SPEC-002 — CONCLUÍDA para desenvolvimento/homologação.**

Baseline funcional congelada: `0.2.0-rc.1`.

A conclusão não é GO de produção/cutover. Preflight de coexistência produtiva continua obrigatório antes de qualquer rollout em produção.

## Próxima linha de trabalho

**UX-001 — Product Experience & Knowledge Workspace** é a próxima trilha ativa e deve anteceder a implementação da SPEC-003.

A UX-001 define arquitetura de informação, inventário de telas, Design System e mockups principais sem autorizar features futuras por antecipação.

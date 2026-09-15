# Tarefas — SPEC-002 Classificação de Conhecimento

## S001 — Baseline e profiling real

- [x] T001 Congelar `0.1.0-rc.1` como baseline de regressão.
- [x] T002 Confirmar baseline de abertura `main @ 8ec60e67c42afc6459ea6266c018c59730d86588`.
- [x] T003 Inventariar os 10 conceitos/11 stores históricos candidatos.
- [x] T004 Definir plano de profiling read-only e métricas mínimas.
- [x] T005 Preparar profiler temporário autossuficiente, sem writes.
- [x] T006 Executar profiler no WordPress real — PASS, 622 posts / 36 meta rows / zero writes.
- [x] T007 Analisar cobertura — GRE 0,96%–1,45%; KB2Ops candidatos zerados.
- [x] T008 Analisar cardinalidade/representação — scalars, baixa cobertura, praticamente 1 valor distinto/post.
- [x] T009 Analisar delimitadores/qualidade — vírgula/newline e formatos heterogêneos encontrados.
- [x] T010 Analisar audiência GRE↔KB2Ops — sem overlap observável; sem auto-merge.
- [x] T011 Analisar service↔affected e technologies↔systems — merge não autorizado.
- [x] T012 Confirmar coexistência — novo owner físico separado; legado read-only/advisory; sem dual-write.
- [x] T013 Selecionar slice: audiência, equipe responsável, tipo de conhecimento, item de catálogo.
- [x] T014 Fechar C-001 — PASS e NO-GO para migração automática.

**Gate S001: PASS.**

## S002 — Contratos físicos e segurança

- [x] T020 Primitive: WordPress Taxonomy API para os quatro conceitos do slice.
- [x] T021 Slugs canônicos: `bdc_kb_audience`, `bdc_kb_responsible_team`, `bdc_kb_knowledge_type`, `bdc_kb_catalog_item`.
- [x] T022 Cardinalidade: audience/team/catalog multi; knowledge_type single; omit=preserva; `[]`=remove.
- [x] T023 Normalização: IDs inteiros existentes; sem auto-map textual/seed legado.
- [x] T024 Compatibilidade: stores legados apenas como referência read-only não canônica.
- [x] T025 Dual-write permanente proibido; nenhuma bridge de write autorizada.
- [x] T026 Matriz de Mutação fechada.
- [x] T027 Matriz de Evidência fechada.
- [x] T028 UI mínima: seção Classificação no editor atual, formulário/handler separado do Summary, termos existentes apenas.
- [x] T029 Rollback/DoR: implementação autorizada.

**Gate S002: PASS. C-010 PASS.**

## S003 — Runtime mínimo

- [x] T030 Implementar `Classification_Contract`, quatro taxonomias e integração no bootstrap.
- [x] T031 Implementar `Classification_Admin` com referência legada read-only e gestão de vocabulário via UI nativa WordPress.
- [x] T032 Implementar `Classification_Store` com snapshot/diff/read-after-write/compensação.
- [x] T033 Unitário determinístico Classification Store — PASS 15/15.
- [x] T034 PHP lint do runtime — PASS 8/8; package de smoke `0.2.0-dev.1` gerado.

**Gate S003: PASS local. Nenhum gate WordPress real da SPEC-002 é promovido por isso.**

## S004 — Evidência WordPress

- [ ] T040 Smoke inicial `0.2.0-dev.1`: substituição/ativação, Summary preservado, seção Classificação carrega, vocabulários acessíveis.
- [ ] T041 Regressão obrigatória da SPEC-001 em WordPress real.
- [ ] T042 Onclick técnico G-030/G-070 com fixtures/termos temporários e cleanup.
- [ ] T043 Browser acceptance G-110.
- [ ] T044 Confirmar zero write editorial e zero write nos stores legados.
- [ ] T045 Retirar integralmente instrumentos temporários.
- [ ] T046 Package limpo/lifecycle G-130.
- [ ] T047 DoD/continuidade/decisão da próxima SPEC.

## Regra de avanço

O runtime permanente está autorizado somente para os quatro conceitos aprovados. Serviço, serviço afetado, tecnologias, sistemas, keywords e versões continuam bloqueados.

Nenhum valor histórico pode criar automaticamente termo canônico ou ser tratado como classificação vigente.
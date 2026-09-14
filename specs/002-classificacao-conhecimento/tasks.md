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
- [x] T012 Confirmar coexistência — novo owner físico será separado; legado read-only/advisory; sem dual-write.
- [x] T013 Selecionar primeiro vertical slice: audiência, equipe responsável, tipo de conhecimento, item de catálogo.
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
- [x] T027 Matriz de Evidência fechada para C-001/C-010/G-001/G-030/G-070/G-110/G-130.
- [x] T028 UI mínima: seção Classificação no editor atual, formulário/handler separado do Summary, termos existentes apenas.
- [x] T029 Rollback/DoR: remover módulo de classificação restaura baseline; taxonomias não deletam termos/relacionamentos automaticamente; implementação autorizada.

**Gate S002: PASS. C-010 PASS. S003 autorizado.**

## S003 — Runtime mínimo

- [ ] T030 Implementar contrato/taxonomias/Store/Admin do slice autorizado.
- [ ] T031 Implementar referência legada read-only sem preseleção automática.
- [ ] T032 Implementar write composto com snapshot/diff/read-after-write/compensação.
- [ ] T033 Criar unitários determinísticos de Classification Store e regressão Summary.
- [ ] T034 PHP lint + scan de escopo/markers.

## S004 — Evidência WordPress

- [ ] T040 Regressão obrigatória da SPEC-001 em WordPress real.
- [ ] T041 Onclick técnico G-030/G-070 com fixtures/termos temporários e cleanup.
- [ ] T042 Browser acceptance G-110.
- [ ] T043 Confirmar zero write editorial e zero write nos stores legados.
- [ ] T044 Retirar integralmente instrumentos temporários.
- [ ] T045 Package limpo/lifecycle G-130.
- [ ] T046 DoD/continuidade/decisão da próxima SPEC.

## Regra de avanço

O runtime permanente agora está autorizado **somente** para os quatro conceitos aprovados. Serviço, serviço afetado, tecnologias, sistemas, keywords e versões continuam bloqueados.

Nenhum valor histórico pode criar automaticamente termo canônico ou ser tratado como classificação vigente.
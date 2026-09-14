# Tarefas — SPEC-002 Classificação de Conhecimento

## S001 — Baseline e profiling real

- [x] T001 Congelar `0.1.0-rc.1` como baseline de regressão.
- [x] T002 Confirmar baseline de abertura `main @ 8ec60e67c42afc6459ea6266c018c59730d86588`.
- [x] T003 Inventariar os 10 conceitos/11 stores históricos candidatos.
- [x] T004 Definir plano de profiling read-only e métricas mínimas.
- [x] T005 Preparar profiler temporário autossuficiente, sem writes, para homologação.
- [ ] T006 Executar profiler no WordPress real e anexar JSON bruto.
- [ ] T007 Analisar cobertura por key/conceito.
- [ ] T008 Analisar cardinalidade, frequência e representação scalar/array/serialized/JSON.
- [ ] T009 Analisar delimitadores, whitespace, case e colisões de normalização.
- [ ] T010 Analisar equivalência/overlap: audiência GRE↔KB2Ops.
- [ ] T011 Analisar separação: service↔affected_service e technologies↔systems_involved.
- [ ] T012 Confirmar writers/readers atuais e riscos de coexistência.
- [ ] T013 Selecionar o primeiro vertical slice com no máximo quatro conceitos.
- [ ] T014 Fechar C-001 e Definition of Ready para decisões físicas.

## S002 — Contratos físicos e segurança

- [ ] T020 Decidir Taxonomy vs Post Meta por conceito do slice.
- [ ] T021 Definir nomes/slugs/chaves canônicas sem colisão.
- [ ] T022 Definir cardinalidade single/multi e semântica de vazio/remove.
- [ ] T023 Definir normalização válida para write sem destruir informação.
- [ ] T024 Definir compatibilidade de leitura com stores legados e gate de remoção.
- [ ] T025 Proibir dual-write permanente e documentar qualquer bridge temporária.
- [ ] T026 Criar Matriz de Mutação.
- [ ] T027 Fechar Matriz de Evidência G-001/G-030/G-070/G-110/G-130.
- [ ] T028 Definir UI mínima no shell atual sem novo shell administrativo.
- [ ] T029 Definir rollback e fechar DoR de implementação.

## S003 — Runtime mínimo

**BLOQUEADO até S001/S002 PASS.**

- [ ] T030 Implementar somente o slice autorizado.
- [ ] T031 Regressão obrigatória da SPEC-001.
- [ ] T032 Unitários e integração WordPress.
- [ ] T033 Homologação onclick/browser.
- [ ] T034 Package limpo/lifecycle.

## Regra de avanço

Nenhum código permanente de classificação entra no runtime antes de T029.

O profiler S001 é ferramenta temporária read-only e não pode sobreviver no package final da SPEC-002.

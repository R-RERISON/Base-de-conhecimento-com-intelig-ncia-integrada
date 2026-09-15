# Tarefas — SPEC-003 Review & Governança

## S001 — Descoberta / Current State

- [ ] T001 Congelar baseline `0.2.0-rc.1` + UX v1.
- [ ] T002 Inventariar artefatos históricos de review/governança no código GRE/KB2Ops disponível.
- [ ] T003 Mapear metas/taxonomias/tabelas candidatas.
- [ ] T004 Mapear writers e consumers históricos.
- [ ] T005 Mapear roles/capabilities e handlers relacionados.
- [ ] T006 Construir profiler read-only temporário.
- [ ] T007 Medir cobertura/distribuição de valores por store candidato.
- [ ] T008 Medir presença de actor/timestamp auditável.
- [ ] T009 Comparar com `post_status` e identificar sobreposição/conflito.
- [ ] T010 Registrar política de legado: migrável / advisory / descartado.
- [ ] T011 Gerar evidência JSON e cleanup do profiler.

**Gate R-001: NOT_RUN.**

## S002 — Domain Contract

- [ ] T020 Definir owner canônico do estado de governança.
- [ ] T021 Definir conjunto mínimo de estados.
- [ ] T022 Definir estado inicial/ausência de decisão.
- [ ] T023 Definir transições válidas.
- [ ] T024 Definir capabilities/atores por transição.
- [ ] T025 Decidir se reviewer/responsável é necessário na primeira slice.
- [ ] T026 Decidir primitiva do estado atual.
- [ ] T027 Decidir primitiva de histórico/auditoria.
- [ ] T028 Definir contrato de atomicidade/compensação.
- [ ] T029 Fechar política de migração/coexistência legada.

**Gate R-010: NOT_RUN.**

## S003 — Runtime mínimo

- [ ] T030 Registrar contrato/owner sem side effects.
- [ ] T031 Implementar leitura do estado.
- [ ] T032 Implementar transição determinística.
- [ ] T033 Implementar auditoria mínima aprovada.
- [ ] T034 Implementar read-after-write e consistência estado/histórico.
- [ ] T035 Implementar compensation/fail-safe ou estado crítico conforme contrato.
- [ ] T036 Unitários determinísticos.
- [ ] T037 PHP lint e package dev.

**Gate G-001/G-030: NOT_RUN.**

## S004 — HTTP e segurança

- [ ] T040 Handler POST + nonce vinculado.
- [ ] T041 Capability por objeto.
- [ ] T042 Allowlist de transição/payload.
- [ ] T043 PRG.
- [ ] T044 Runner HTTP temporário.
- [ ] T045 GET/nonce/mass-assignment/IDOR/payload inválido.
- [ ] T046 POST válido + reread + regressão SPEC-001/002.
- [ ] T047 Cleanup zero resíduos.

**Gate G-070: NOT_RUN.**

## S005 — UX / Browser Acceptance

- [ ] T050 Integrar Review ao Knowledge Workspace conforme UX v1.
- [ ] T051 Não exibir score/AI Ready não contratados.
- [ ] T052 Preservar Summary/Classificação e navegação.
- [ ] T053 Testar teclado/foco.
- [ ] T054 Testar desktop/782px/~492px.
- [ ] T055 Browser acceptance com fixture e cleanup.

**Gate G-110: NOT_RUN.**

## S006 — Lifecycle / fechamento

- [ ] T060 Remover profiler/runners/hooks temporários.
- [ ] T061 Gerar package clean RC.
- [ ] T062 deactivate/activate sem regressão.
- [ ] T063 Confirmar zero resíduos de teste.
- [ ] T064 Atualizar matriz de evidência e continuidade.
- [ ] T065 Congelar baseline da SPEC-003.

**Gate G-130: NOT_RUN.**

## Regra

Nenhuma tarefa de S003 ou posterior é autorizada enquanto R-001 e R-010 não estiverem PASS. O primeiro movimento é evidência, não implementação.

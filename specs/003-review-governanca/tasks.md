# Tarefas — SPEC-003 Review & Governança

## S001 — Descoberta / Current State

- [x] T001 Congelar baseline `0.2.0-rc.1` + UX v1.
- [x] T002 Inventariar artefatos históricos de review/governança no código GRE/KB2Ops disponível.
- [x] T003 Mapear metas/taxonomias/tabelas candidatas.
- [x] T004 Mapear writers e consumers históricos.
- [x] T005 Mapear roles/capabilities e handlers relacionados.
- [x] T006 Construir profiler read-only temporário.
- [x] T007 Medir cobertura/distribuição de valores por store candidato.
- [x] T008 Medir presença de actor/timestamp auditável.
- [x] T009 Comparar com `post_status` e identificar sobreposição/conflito.
- [x] T010 Registrar política de legado: migrável / advisory / descartado.
- [x] T011 Gerar evidência JSON e registrar resultado do profiler.

**Gate R-001: PASS.**  
Evidência: `evidencia-profiling-s001.md`. O ambiente possui 622 posts e zero rows nos seis stores históricos de Review/Governança; não há passivo de migração.

## S002 — Domain Contract

- [x] T020 Definir owner canônico do estado de governança.
- [x] T021 Definir conjunto mínimo de estados.
- [x] T022 Definir estado inicial/ausência de decisão.
- [x] T023 Definir transições válidas.
- [x] T024 Definir capabilities/atores por transição.
- [x] T025 Decidir se reviewer/responsável é necessário na primeira slice.
- [x] T026 Decidir primitiva do estado atual.
- [x] T027 Decidir primitiva de histórico/auditoria.
- [x] T028 Definir contrato de atomicidade/compensação.
- [x] T029 Fechar política de migração/coexistência legada.

**Gate R-010: PASS.**  
Contrato: `domain-contract.md`. Estado atual é derivado do último evento append-only `bdc_kb_review_event`; não existe meta paralela de current state.

## S003 — Runtime mínimo

- [x] T030 Registrar contrato/owner sem side effects.
- [x] T031 Implementar leitura do estado.
- [x] T032 Implementar transição determinística.
- [x] T033 Implementar auditoria mínima aprovada.
- [x] T034 Implementar read-after-write e consistência do evento canônico.
- [x] T035 Implementar compensation/fail-safe ou estado crítico conforme contrato.
- [x] T036 Unitários determinísticos — PASS 19/19.
- [x] T037 PHP lint PASS 10/10 e package `0.3.0-dev.1` gerado.
- [x] T038 Smoke ambiental `0.3.0-dev.1` — PASS por evidência do operador; Summary/Classificação sem regressão e nenhuma UI Review antecipada.
- [ ] T039 Executar integração real do `Review_Store` na WordPress Comments API via `0.3.0-dev.2`, exigir 17/17 e cleanup zero resíduos.

**Gate G-001: PASS.**  
Evidência: `evidencia-smoke-dev1-s003.md`.

**Gate G-030: PASS determinístico local / integração ambiental pendente T039.**

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

R-001, R-010 e G-001 estão PASS. O próximo passo obrigatório é T039: provar a integração real do event log na Comments API com fixtures temporárias antes de abrir o writer HTTP permanente.

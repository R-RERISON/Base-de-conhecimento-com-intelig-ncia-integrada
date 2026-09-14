# Tarefas — SPEC-001 Core mínimo + Summary narrativo

## S001 — Baseline / Definition of Ready

- [x] T001 Confirmar `main`/HEAD de entrada (`fced4a6015638b585d8817485fce8ef0fb8d7ccb`).
- [x] T002 Ler AGENTS, Manifesto, Constituição, T095/T096/T097, DoD e CONTINUIDADE.
- [x] T003 Investigar divergência dos placeholders antigos de SPEC-001/SPEC-002.
- [x] T004 Comprovar `post_type` suportado: somente `post`.
- [x] T005 Fixar contratos dos três metadados, empty/delete, limite e sanitização.
- [x] T006 Criar Matriz de Mutação.
- [x] T007 Criar Matriz de Evidência G-001/G-020/G-070/G-110/G-130 + B-006.
- [x] T008 Definir fault injection B-006.
- [x] T009 Definir UI/UX/browser acceptance.
- [x] T010 Definir aceite/não aceite/rollback e fechar DoR.

**Gate S001:** PASS documental.

## S002 — Runtime mínimo

- [x] T020 Definir árvore mínima do plugin e requisitos mínimos WordPress/PHP com base no baseline comprovado.
- [x] T021 Implementar bootstrap/lifecycle mínimo sem trabalho pesado.
- [x] T022 Implementar contrato das três metas para `post`.
- [x] T023 Implementar leitura side-effect free.
- [x] T024 Implementar update com allowlist/limites/sanitização/diff.
- [x] T025 Implementar B-006 com read-after-write e compensação.
- [x] T026 Implementar superfície wp-admin server-rendered + PRG.
- [x] T027 Implementar escaping/feedback/estados de erro.

**Gate S002:** PASS.

## S003 — Evidência e fechamento

- [x] T040 Testes unitários aplicáveis — PASS 15/15.
- [x] T040A Instalação inicial/smoke em WordPress real — PASS.
- [x] T041 Integração WordPress G-001/G-020/G-070 — PASS no ambiente alvo.
- [x] T041A Runner onclick temporário com JSON e cleanup.
- [x] T041B Onclick real — PASS 16/16, 0 resíduos.
- [x] T041C Hardening do runner onclick v2.
- [x] T041D Runner HTTP automático para negativos do handler.
- [x] T041E Runner HTTP `dev.6` — PASS 11/11, `overall=PASS`, 0 resíduos.
- [x] T042 Fault injection B-006 em WordPress real — `FAIL_SAFE` e `PARTIAL_FAILURE_CRITICAL` PASS.
- [x] T043A Browser acceptance com fixture temporária/cleanup.
- [x] T043B G-110 real — `dev.5` PASS; 2/2 humanos, 0 auto_fail, viewport mínimo 671x660, 0 resíduos.
- [x] T043C Automatização de shell/lista/labels/feedback/PRG/persistência/browser/viewport.
- [x] T044A Preparar package limpo `0.1.0-rc.1`: retirar integralmente instrumentos temporários, lint e scan de markers PASS, checksum gerado.
- [x] T044B Lifecycle G-130 em WordPress real — operador confirmou substituição/ativação do RC, ausência de instrumentos de diagnóstico, smoke funcional, leitura de dados existentes, desativação/reativação e preservação dos dados.
- [x] T045 Consolidar relatório final de evidência e Definition of Done.
- [x] T046 Encerrar SPEC-001 e autorizar apenas o planejamento/DoR da próxima SPEC canônica.

**Gate S003:** PASS.

## Estado final dos gates

- G-001: **PASS**.
- G-020: **PASS**.
- G-070: **PASS**.
- G-110: **PASS**.
- B-006: **PASS**.
- G-130: **PASS**.

## Baseline congelada

- Package homologado: `0.1.0-rc.1`.
- SHA-256: `c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`.
- Runtime de produto: seis arquivos, sem instrumentos temporários de homologação.

## Estado da SPEC

**SPEC-001 — CONCLUÍDA para desenvolvimento/homologação.**

Isto não constitui autorização de produção/cutover. B-003/preflight, single-writer e coexistência com GRE/KB2Ops/ASI retornam antes de qualquer mudança produtiva.

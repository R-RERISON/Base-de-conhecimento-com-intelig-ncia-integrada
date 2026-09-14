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

**Gate S002:** runtime implementado e PHP lint PASS. Integração WordPress/browser/fault injection ainda não executados; SPEC permanece Em implementação.

## S003 — Evidência

- [ ] T040 Testes unitários aplicáveis.
- [ ] T041 Integração WordPress G-001/G-020/G-070.
- [ ] T042 Fault injection B-006.
- [ ] T043 Browser acceptance G-110.
- [ ] T044 Lifecycle/package G-130 quando aplicável.
- [ ] T045 Relatório de evidência e DoD.
- [ ] T046 Atualizar CONTINUIDADE e decidir próximo gate.

## Regra

Não antecipar Classificação, Review, Search, Analytics, IA, queue, schema, REST/AJAX/SPA ou cutover.

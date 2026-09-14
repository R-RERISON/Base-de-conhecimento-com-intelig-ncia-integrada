# Tarefas — SPEC-000

## Preparação e inventários
- [x] T000–T002 preparação/governança.
- [x] T010–T019 KB2Ops.
- [x] T020–T034 ASI.
- [x] T040–T047 GRE.

## Cruzamento
- [x] T050 persistência.
- [x] T051 integrações.
- [x] T052 ownership.
- [x] T053 sobreposição.
- [x] T054 drifts/blockers/compatibilidade.
- [x] T055 regressão/Golden.
- [x] T056 WordPress-first.
- [x] T057 infraestrutura própria mínima.
- [x] T058 IA/vetor.
- [x] T059 paridade futura FINAL.

## Revisões finais
- [x] T090 Arquiteto WordPress.
- [x] T091 Crítico de Simplicidade.
- [x] T092 Segurança.
- [x] T093 QA/Regressão. _(`revisao-qa-t093.md`; PASS, matriz de evidência por slice, zero blockers globais)_
- [ ] T094 Produto/Conhecimento.
- [ ] T095 unknowns/blockers por slice.
- [ ] T096 relatório final.
- [ ] T097 GO/NO-GO SPEC-001.

## Resultado T093
- [x] estados canônicos de evidência: PASS, FAIL, NOT_RUN, NOT_CONFIGURED, STALE, N/A, POSTERGADO, WAIVED.
- [x] NOT_RUN/NOT_CONFIGURED/STALE em gate ativo = NO-GO.
- [x] N/A exige justificativa versionada.
- [x] Matriz de Evidência passa a ser obrigatória por SPEC.
- [x] candidato SPEC-001 recebeu matriz mínima G-001/G-020/G-070/G-110/G-130 + B-006.
- [x] cenários negativos de capability, nonce, IDOR, mass assignment e XSS tornaram-se obrigatórios quando aplicáveis.
- [x] browser/manual acceptance foi separado de unit/integration.
- [x] benchmark só nasce em caminho crítico medido.
- [x] Golden continua obrigatória antes de release Search, não antes do Summary.
- [x] item/deep-link, Analytics, queue, IA/vetor/agentes continuam postergados e não exigem harness antecipado.
- [x] defect taxonomy P0–P3 e release evidence bundle conceitual definidos.
- [x] nenhum runtime/teste executável criado.

## Próximo passo exato
**T094 — Revisão de Produto/Conhecimento.**

Validar se `Core mínimo + Summary narrativo` é realmente o melhor primeiro slice de valor e ajustar a ordem dos próximos domínios sem reabrir arquitetura desnecessariamente.

## Estado
T050–T059 + T090–T093 concluídos documentalmente. SPEC-001 permanece bloqueada até T097.
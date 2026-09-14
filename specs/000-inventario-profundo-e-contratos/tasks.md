# Tarefas — SPEC-000

## Preparação e inventários
- [x] T000–T002 preparação/governança.
- [x] T010–T019 KB2Ops.
- [x] T020–T034 ASI.
- [x] T040–T047 GRE.

## Cruzamento
- [x] T050–T059 consolidação arquitetural/documental.

## Revisões finais
- [x] T090 Arquiteto WordPress.
- [x] T091 Crítico de Simplicidade.
- [x] T092 Segurança.
- [x] T093 QA/Regressão.
- [x] T094 Produto/Conhecimento.
- [x] T095 unknowns/blockers por slice. _(`fechamento-blockers-t095.md`; zero BLOCKER_SPEC001 aberto, B-006 fechado conceitualmente)_
- [ ] T096 relatório final.
- [ ] T097 GO/NO-GO SPEC-001.

## Resultado T095
- [x] B-001 -> não aplicável à SPEC-001; Search/RAG.
- [x] B-002 -> não aplicável; Classificação/cutover.
- [x] B-003 -> não bloqueia dev/homolog; permanece para cutover/aliases/removal.
- [x] B-004 -> não aplicável; Analytics.
- [x] B-005 -> não aplicável; item/deep-link.
- [x] B-006 -> fechado conceitualmente com snapshot + write mínimo + read-after-write + compensação best-effort.
- [x] B-007 -> não aplicável; async/queue.
- [x] três meta keys GRE narrativas foram escolhidas como storage inicial canônico da SPEC-001 para evitar migration/dual-write.
- [x] capability baseline definida: `edit_post` no objeto alvo.
- [x] superfície técnica: server-rendered + POST/nonce; sem REST/AJAX/SPA.
- [x] sem settings, event bus, history/audit, migration, runtime version option no primeiro slice.
- [x] post types reais devem ser enumerados na baseline da SPEC-001 antes do código.
- [x] rollback simplificado: deactivate preserva postmeta; zero schema/migration/purge.
- [x] Definition of Ready documental da SPEC-001 candidata concluída.
- [x] BLOCKER_SPEC001 aberto = zero.

## Próximo passo exato
**T096 — Relatório Final da SPEC-000.**

Sintetizar estado, decisões, evidências e recomendação formal para T097 sem reabrir arquitetura.

## Estado
T050–T059 + T090–T095 concluídos documentalmente. SPEC-001 continua aguardando autorização T097.
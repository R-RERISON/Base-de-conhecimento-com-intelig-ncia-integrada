# Checklist de Requisitos — SPEC-000

## Preparação

- [x] Repositórios registrados.
- [x] Baselines SHA registrados.
- [x] Constituição ratificada.
- [x] Manifesto criado.
- [x] Agentes/skills preparados.
- [x] Nenhum runtime novo criado.

## Inventários individuais

- [x] KB2Ops completo.
- [x] ASI completo.
- [x] Resumo Executivo completo.

## Cruzamento

- [x] Catálogo de persistência consolidado por owner/conceito. _(T050)_
- [x] Catálogo de integrações consolidado por contrato futuro. _(T051)_
- [x] Mapa de ownership. _(T052)_
- [x] Matriz de sobreposição. _(T053)_
- [x] Drifts/contratos quebrados consolidados. _(T054 — `mapa-contratos-quebrados.md`)_
- [ ] Catálogo de regressão/Golden futuro consolidado. _(T055)_
- [ ] Matriz WordPress-first final. _(T056 — próximo)_
- [ ] Infraestrutura própria mínima justificada. _(T057)_
- [ ] Candidatos IA/vetor priorizados. _(T058)_
- [ ] Matriz de paridade futura final. _(T059)_

## Evidência T050/T051

- persistência organizada por owner, não por plugin histórico;
- integração organizada por contrato semântico;
- evento pós-write confirmado;
- dual-write permanente proibido;
- adapters temporários exigem gate de remoção;
- REST negado sem consumidor; AJAX só por live UX;
- queue ainda não autorizada.

## Evidência T052/T053

- owners lógicos definidos;
- audiência unificada semanticamente;
- service/affected_service e technologies/systems mantidos separados até profiling;
- Summary, Search, Classificação, Analytics e Design System convergem funcionalmente;
- approval de artigo continua distinto de Apply de Search Knowledge;
- qualidade de conteúdo continua distinta de Search Quality.

## Evidência T054 — Drifts/Compatibilidade

- [x] D-001–D-008 possuem classificação explícita.
- [x] Design futuro foi separado de necessidade de coexistência/cutover.
- [x] `Objective_Provider` e evento legado só admitem bridge/adapter se ASI legado realmente coexistir.
- [x] extractor único é direção definitiva, mas qualidade/completude permanece blocker técnico para Search/RAG final.
- [x] GAC ficou fora do core e depende de requisito/preflight.
- [x] classificação duplicada exige profiling antes de migração física.
- [x] CSS/menus antigos não são contrato permanente.
- [x] AI READY baseline foi fixada em `publish + approved + 8/8 + include_ai`.
- [x] hooks/actions antigos receberam política de compatibilidade/preflight.
- [x] shortcodes antigos receberam status `DEPENDE DE PREFLIGHT`, sem alias automático.
- [x] dados históricos que não podem ser perdidos foram explicitados.
- [x] blockers B-001–B-007 foram separados de dívidas postergáveis.
- [x] todo adapter temporário exige entrada, owner, modo, observabilidade, rollback, remoção e teste.
- [x] nenhuma migration/runtime/taxonomy/tabela foi criada.

## Gate final

- [ ] Revisão Orquestrador.
- [ ] Revisão WordPress.
- [ ] Revisão Simplicidade.
- [ ] Revisão Segurança.
- [ ] Revisão QA/Regressão.
- [ ] Revisão Produto/Conhecimento.
- [ ] Nenhum desconhecido crítico sem decisão.
- [ ] Relatório final SPEC-000.
- [ ] SPEC-001 autorizada formalmente.

## Estado

T050–T054 concluídas documentalmente. Próximo passo autorizado: **T056 — matriz WordPress-first**. Runtime novo continua inexistente.
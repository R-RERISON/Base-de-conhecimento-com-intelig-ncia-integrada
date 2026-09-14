# Tarefas — SPEC-000

## Preparação

- [x] T000 — Registrar repositórios e SHAs baseline.
- [x] T001 — Criar Constituição, Manifesto e regras de agentes.
- [x] T002 — Criar roadmap e SpecKit base.

## KB2Ops

- [x] T010 — Inventariar bootstrap/lifecycle.
- [x] T011 — Inventariar classes de domínio.
- [x] T012 — Inventariar metadata/options/transients/cron.
- [x] T013 — Inventariar admin routes/forms/actions.
- [x] T014 — Inventariar shortcodes/frontend/assets.
- [x] T015 — Inventariar Elementor Content Extractor.
- [x] T016 — Inventariar migration/installer/uninstall.
- [x] T017 — Inventariar testes/build/release.
- [x] T018 — Mapear Design System e componentes.
- [x] T019 — Classificar todos os itens.

## ASI

- [x] T020 — Inventariar bootstrap/lifecycle.
- [x] T021 — Inventariar schema/tabelas/índices.
- [x] T022 — Inventariar QueryContext/vocabulário/relevância.
- [x] T023 — Inventariar PostIndex/ItemKnowledge/ItemRanker.
- [x] T024 — Inventariar bindings/rules/curation/simulation.
- [x] T025 — Inventariar Search Events/Interactions/Outcomes.
- [x] T026 — Inventariar privacy/rate-limit/cache.
- [x] T027 — Inventariar Queue/Migrations/Reconciler/Orchestrator.
- [x] T028 — Inventariar Golden Queries/Quality/Diagnostics.
- [x] T029 — Inventariar Word Cloud.
- [x] T030 — Inventariar admin/public routes/AJAX/shortcodes/assets.
- [x] T031 — Inventariar legacy/compat e decidir relevância real.
- [x] T032 — Inventariar todos os testes e gates.
- [x] T033 — Inventariar build/release/rollback.
- [x] T034 — Classificar todos os itens.

## Resumo Executivo

- [x] T040 — Inventariar bootstrap/lifecycle.
- [x] T041 — Inventariar Meta Contract e Summary Store.
- [x] T042 — Inventariar Admin Page e Coverage Dashboard.
- [x] T043 — Inventariar Frontend Renderer/shortcode.
- [x] T044 — Inventariar CSS/assets.
- [x] T045 — Inventariar testes/build/release.
- [x] T046 — Confirmar existência/ausência de Objective Provider/evento esperado pelo ASI. _(ambos ausentes no GRE 0.6.0; drift confirmado)_
- [x] T047 — Classificar todos os itens.

## Cruzamento

- [ ] T050 — Criar catálogo unificado de persistência. _(evidência das três referências existe; consolidar agora usando ownership T052)_
- [ ] T051 — Criar catálogo unificado de hooks/rotas/integrações. _(evidência existe; consolidar usando sobreposição T053)_
- [x] T052 — Criar mapa de ownership atual de dados. _(`mapa-ownership-dados.md`; owners lógicos definidos sem fechar storage)_
- [x] T053 — Criar matriz de sobreposição funcional. _(`matriz-sobreposicoes.md`; fusões e separações funcionais definidas)_
- [ ] T054 — Criar mapa de contratos quebrados/drift. _(D-001/D-002 históricos quebrados; D-003/D-004/D-006/D-007 já têm direção; consolidar estado final)_
- [ ] T055 — Criar catálogo de regressão e Golden Queries. _(contratos das três referências catalogados; consolidar gate futuro após decisões de primitive/infra)_
- [ ] T056 — Identificar tudo que WordPress pode substituir. _(usar ownership para decidir taxonomy/meta/options/admin-post/Site Health/etc.)_
- [ ] T057 — Identificar tudo que realmente exige infraestrutura própria. _(índice/telemetria/queue somente por evidência)_
- [ ] T058 — Identificar candidatos a IA/vetor. _(ordenar por dependências/custo/risco depois do baseline)_
- [ ] T059 — Criar matriz de paridade futura consolidada. _(matriz preliminar existe; fechar após T050–T058)_

## Ordem restante do cruzamento

1. T050 + T051 — consolidar persistência e integrações usando ownership/sobreposição já resolvidos.
2. T054 — consolidar drifts e contratos quebrados.
3. T056 — WordPress-first campo/capacidade por campo/capacidade.
4. T057 — justificar infraestrutura própria mínima.
5. T055 — congelar regressões/Golden/gates em função das decisões anteriores.
6. T058 — priorizar IA/vetor como extensão opcional.
7. T059 — matriz de paridade final.

Não inverter a sequência para antecipar tabelas, vetores ou runtime.

## Gate

- [ ] T090 — Revisão do Arquiteto WordPress.
- [ ] T091 — Revisão do Crítico de Simplicidade.
- [ ] T092 — Revisão de Segurança.
- [ ] T093 — Revisão de Regressão.
- [ ] T094 — Revisão de Produto/Conhecimento.
- [ ] T095 — Fechar itens “AINDA NÃO SABEMOS” críticos.
- [ ] T096 — Emitir relatório final da SPEC-000.
- [ ] T097 — Autorizar ou bloquear SPEC-001.

## Estado do planejamento

Os três inventários individuais e o primeiro cruzamento de **ownership + sobreposição** estão concluídos documentalmente. **Nenhum runtime novo foi criado.** A próxima alteração permitida é T050/T051; SPEC-001 continua bloqueada até T097.

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

- [x] T050 — Criar catálogo unificado de persistência. _(`catalogo-persistencia.md` consolidado por owner/conceito; primitive física permanece T056/T057)_
- [x] T051 — Criar catálogo unificado de hooks/rotas/integrações. _(`catalogo-integracoes.md` consolidado por contrato futuro, compatibilidade e falha/idempotência)_
- [x] T052 — Criar mapa de ownership atual de dados. _(`mapa-ownership-dados.md`)_
- [x] T053 — Criar matriz de sobreposição funcional. _(`matriz-sobreposicoes.md`)_
- [ ] T054 — Criar mapa de contratos quebrados/drift. _(próximo bloco: consolidar D-001–D-008 + compatibilidade/preflight/blockers)_
- [ ] T055 — Criar catálogo de regressão e Golden Queries. _(fechar após decisões de primitive/infra)_
- [ ] T056 — Identificar tudo que WordPress pode substituir. _(taxonomy/meta/options/admin-post/Site Health/cache/cron campo a campo)_
- [ ] T057 — Identificar tudo que realmente exige infraestrutura própria. _(índice/telemetria/queue/Search Knowledge/Golden somente por evidência)_
- [ ] T058 — Identificar candidatos a IA/vetor. _(ordenar por dependências/custo/risco)_
- [ ] T059 — Criar matriz de paridade futura consolidada.

## Ordem restante do cruzamento

1. T054 — mapa final de contratos quebrados, compatibilidade e blockers.
2. T056 — WordPress-first por conceito/capacidade.
3. T057 — infraestrutura própria mínima justificada.
4. T055 — regressões/Golden/gates coerentes com as decisões anteriores.
5. T058 — IA/vetor opcional, degradável e custo-controlado.
6. T059 — matriz de paridade final.

Não inverter a sequência para antecipar tabelas, taxonomias, vetores ou runtime.

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

Inventários individuais, ownership, sobreposição e catálogos unificados de persistência/integrações estão concluídos documentalmente. **Nenhum runtime novo foi criado.** Próximo bloco: T054. SPEC-001 continua bloqueada até T097.
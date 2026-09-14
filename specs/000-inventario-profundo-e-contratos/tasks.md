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
- [x] T046 — Confirmar Objective Provider/evento esperado pelo ASI. _(ambos ausentes; drift confirmado)_
- [x] T047 — Classificar todos os itens.

## Cruzamento

- [x] T050 — Catálogo unificado de persistência.
- [x] T051 — Catálogo unificado de hooks/rotas/integrações.
- [x] T052 — Mapa de ownership.
- [x] T053 — Matriz de sobreposição.
- [x] T054 — Mapa de contratos quebrados/drifts/compatibilidade. _(`mapa-contratos-quebrados.md`; D-001–D-008 classificados, adapters/aliases com gates, blockers separados de dívidas)_
- [ ] T055 — Catálogo de regressão e Golden Queries. _(fechar após T057)_
- [x] T056 — Identificar tudo que WordPress pode substituir. _(`matriz-wordpress-first.md`; Core cobre domínio/governança/config/segurança/cache/health; T057 reduzida a três famílias condicionais)_
- [ ] T057 — Identificar tudo que realmente exige infraestrutura própria. _(próximo bloco: F-057-01 Search Retrieval Projection; F-057-02 Analytics Facts condicional; F-057-03 Durable Job State condicional)_
- [ ] T058 — Identificar candidatos a IA/vetor.
- [ ] T059 — Criar matriz de paridade futura consolidada.

## Ordem restante do cruzamento

1. T057 — infraestrutura própria mínima justificada.
2. T055 — regressões/Golden/gates alinhados às decisões de arquitetura.
3. T058 — IA/vetor opcional, degradável e custo-controlado.
4. T059 — matriz de paridade final.

Não inverter a sequência para antecipar tabelas, vetores ou runtime. T057 recebe **candidatos**, não tabelas pré-aprovadas.

## Gate

- [ ] T090 — Revisão do Arquiteto WordPress.
- [ ] T091 — Revisão do Crítico de Simplicidade.
- [ ] T092 — Revisão de Segurança.
- [ ] T093 — Revisão de Regressão.
- [ ] T094 — Revisão de Produto/Conhecimento.
- [ ] T095 — Fechar itens “AINDA NÃO SABEMOS” críticos e blockers aplicáveis.
- [ ] T096 — Emitir relatório final da SPEC-000.
- [ ] T097 — Autorizar ou bloquear SPEC-001.

## Estado

T050–T054 e T056 concluídas documentalmente. **Nenhum runtime novo foi criado.** Próximo bloco autorizado: T057. SPEC-001 continua bloqueada até T097.

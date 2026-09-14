# Tarefas — SPEC-000

## Preparação

- [x] T000 — Registrar repositórios e SHAs baseline.
- [x] T001 — Criar Constituição, Manifesto e regras de agentes.
- [x] T002 — Criar roadmap e SpecKit base.

## Inventários individuais

- [x] T010–T019 — KB2Ops completo.
- [x] T020–T034 — ASI completo.
- [x] T040–T047 — Resumo Executivo completo.

## Cruzamento

- [x] T050 — Catálogo unificado de persistência.
- [x] T051 — Catálogo unificado de hooks/rotas/integrações.
- [x] T052 — Mapa de ownership.
- [x] T053 — Matriz de sobreposição.
- [x] T054 — Drifts/compatibilidade/blockers.
- [x] T055 — Catálogo de regressão e Golden Queries.
- [x] T056 — Matriz WordPress-first.
- [x] T057 — Infraestrutura própria mínima.
- [x] T058 — IA/vetor priorizados.
- [x] T059 — Matriz de paridade futura FINAL. _(`matriz-paridade-futura.md`; owners, storage, momento, gates, blockers, dados de cutover, fallback e decisão final consolidados)_

## Resultado T059

- [x] Todas as capacidades foram classificadas como `PRIMEIRO_RUNTIME | POSTERIOR | POSTERGADO | COMPAT_CUTOVER | DESCARTADO`.
- [x] “Primeiro runtime” foi definido como primeira onda de vertical slices, não big-bang.
- [x] WordPress/Elementor continuam autoridade editorial absoluta.
- [x] Summary/Review/Core/Security/DS permanecem WordPress-first.
- [x] Classificações já decididas permanecem em primitives WP; quatro conceitos continuam limitados a Metadata vs Taxonomy sob B-002.
- [x] Search Retrieval Projection continua sendo a única família própria aprovada no baseline.
- [x] Search lexical/projection ficou POSTERIOR e depende de B-001 + Golden + benchmark.
- [x] Analytics detalhado e durable queue continuam POSTERGADOS.
- [x] IA P1 permanece opcional/posterior; primeiro runtime pode ter zero IA externa.
- [x] RAG P2 permanece posterior/retrieval-first; P3/P4 continuam postergados.
- [x] dados históricos que não podem ser perdidos foram listados.
- [x] índices/caches/queues/telemetria/derivados não foram promovidos a canônicos.
- [x] blockers B-001–B-007 foram mapeados por capacidade e momento.
- [x] T095/T097 podem tratar blockers contextuais por slice, sem bloquear capacidade não relacionada.
- [x] nenhum runtime/schema/provider/vector foi criado.

## Gate final

- [ ] T090 — Revisão do Arquiteto WordPress.
- [ ] T091 — Revisão do Crítico de Simplicidade.
- [ ] T092 — Revisão de Segurança.
- [ ] T093 — Revisão de QA/Regressão.
- [ ] T094 — Revisão de Produto/Conhecimento.
- [ ] T095 — fechar unknowns/blockers aplicáveis por slice.
- [ ] T096 — emitir relatório final da SPEC-000.
- [ ] T097 — autorizar ou bloquear SPEC-001.

## Próximo passo exato

**T090 — Revisão do Arquiteto WordPress.**

T090 deve confrontar `matriz-paridade-futura.md` contra Constituição/Manifesto/T056 e procurar infraestrutura própria, endpoint, storage ou abstração que ainda possa ser eliminada em favor do Core.

## Estado

T050–T059 concluídas documentalmente. **Nenhum runtime novo foi criado.** Próximo bloco autorizado: **T090**. SPEC-001 continua bloqueada até T097.

# Tarefas — SPEC-000

## Preparação

- [x] T000 — Registrar repositórios e SHAs baseline.
- [x] T001 — Criar Constituição, Manifesto e regras de agentes.
- [x] T002 — Criar roadmap e SpecKit base.

## KB2Ops

- [x] T010–T019 — Inventário completo de bootstrap, domínio, persistência, rotas, frontend, extractor, lifecycle, testes/build, Design System e classificação.

## ASI

- [x] T020–T034 — Inventário completo de schema, retrieval/ranking, Search Knowledge, telemetria, queue/migrations, Golden/Quality, compatibilidade, testes, build/release e classificação.

## Resumo Executivo

- [x] T040–T047 — Inventário completo de bootstrap, Meta Contract, Summary Store, admin/frontend, build/testes, drift `Objective_Provider`/evento e classificação.

## Cruzamento

- [x] T050 — Catálogo unificado de persistência.
- [x] T051 — Catálogo unificado de hooks/rotas/integrações.
- [x] T052 — Mapa de ownership.
- [x] T053 — Matriz de sobreposição.
- [x] T054 — Mapa de contratos quebrados/drifts/compatibilidade.
- [x] T055 — Catálogo de regressão e Golden Queries. _(`catalogo-testes-regressao.md`; gates MUST/CONDICIONAL/POSTERGADO, Golden como evidência de release, blockers B-001–B-007 mapeados)_
- [x] T056 — Matriz WordPress-first. _(`matriz-wordpress-first.md`)_
- [x] T057 — Infraestrutura própria mínima. _(`infraestrutura-propria-minima.md`; somente Search Retrieval Projection unificada aprovada no baseline)_
- [ ] T058 — Identificar/priorizar candidatos a IA/vetor.
- [ ] T059 — Criar matriz de paridade futura consolidada.

## Resultado T055

- Golden Queries permanecem em `WP_Post` interno + Metadata/Revisions no baseline; nenhuma tabela Golden foi autorizada.
- suíte Golden vazia = `NOT_CONFIGURED`, nunca PASS.
- Golden não executada/stale = NO-GO quando Search é afetada.
- falha `blocking` = NO-GO; warning exige decisão explícita.
- evidência Golden deve estar vinculada ao conjunto ativo, ranker/item-ranker, extractor/index e dataset/projection do release candidate.
- Content Extractor/B-001 ganhou gate de corpus representativo + custom widgets.
- Search Projection ganhou gates de determinismo, identidade, rebuild, freshness, FULLTEXT/fallback e revalidação WordPress.
- baseline Analytics postergado ganhou teste negativo: Search funciona sem Analytics e não persiste query text silenciosamente.
- baseline queue postergada ganhou teste negativo: produto não depende de queue, WP-Cron não vira durable store e activation não dispara rebuild massivo.
- performance exige benchmark real futuro; nenhum p95/QPS foi inventado em T055.
- IA/vetor receberam somente invariantes constitucionais; gates específicos ficam para T058.

## Ordem restante do cruzamento

1. **T058** — IA/vetor opcionais, degradáveis e custo-controlados.
2. **T059** — paridade futura final incorporando T055/T058.

## Gate final

- [ ] T090 — Revisão do Arquiteto WordPress.
- [ ] T091 — Revisão do Crítico de Simplicidade.
- [ ] T092 — Revisão de Segurança.
- [ ] T093 — Revisão de Regressão.
- [ ] T094 — Revisão de Produto/Conhecimento.
- [ ] T095 — Fechar unknowns críticos/blockers aplicáveis.
- [ ] T096 — Emitir relatório final da SPEC-000.
- [ ] T097 — Autorizar ou bloquear SPEC-001.

## Estado

T050–T057 concluídas documentalmente, incluindo **T055**. **Nenhum runtime novo foi criado.** Próximo bloco autorizado: **T058**. SPEC-001 continua bloqueada até T097.

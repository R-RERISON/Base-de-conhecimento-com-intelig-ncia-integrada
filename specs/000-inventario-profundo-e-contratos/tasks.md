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
- [x] T059 — Matriz de paridade futura FINAL.

## Revisões finais

- [x] T090 — Revisão do Arquiteto WordPress. _(`revisao-wordpress-t090.md`; PASS, zero bloqueantes)_
- [x] T091 — Revisão do Crítico de Simplicidade. _(`revisao-simplicidade-t091.md`; PASS, zero bloqueantes, execução futura reduzida a vertical slices mínimos)_
- [ ] T092 — Revisão de Segurança.
- [ ] T093 — Revisão de QA/Regressão.
- [ ] T094 — Revisão de Produto/Conhecimento.
- [ ] T095 — fechar unknowns/blockers aplicáveis por slice.
- [ ] T096 — emitir relatório final da SPEC-000.
- [ ] T097 — autorizar ou bloquear SPEC-001.

## Resultado T091

- [x] `PRIMEIRO_RUNTIME` foi reafirmado como onda de slices, não pacote único.
- [x] recomendação provisória para primeira SPEC: Core mínimo + Summary narrativo.
- [x] Content Extractor foi postergado até existir consumidor real.
- [x] Design System será incremental, apenas componentes usados por telas reais.
- [x] Settings só nascem quando consumidos.
- [x] Review foi separado do primeiro Summary slice.
- [x] Classificação será entregue por eixos/slices, não toda de uma vez.
- [x] event bus genérico foi descartado; WordPress hooks específicos bastam quando houver consumidor.
- [x] repository/service container/cache abstraction genéricos foram descartados no baseline.
- [x] Site Health só ganha checks de capacidades efetivamente implementadas.
- [x] Search Retrieval Projection permaneceu aprovada, mas post-level deve preceder item-level quando suficiente.
- [x] deep-link/anchors continuam postergados até item navegável.
- [x] Search Knowledge foi postergado até o ranker lexical mínimo provar necessidade.
- [x] Golden continua obrigatório para release de Search, porém sem exigir UI CRUD sofisticada inicialmente.
- [x] Operations UI/migration framework genérico foram descartados.
- [x] compatibilidade/aliases continuam condicionados a B-003, sem adapter framework.
- [x] IA P1 só nasce depois do owner assistido estar estável; sem abstração multi-provider antecipada.
- [x] RAG/P3/P4 permanecem postergados.
- [x] nenhum runtime/schema/provider/vector foi criado.

## Entrada T092

Foco obrigatório de segurança:

- capability model por owner/slice;
- nonces/métodos/CSRF;
- sanitização/escaping;
- IDOR/scope de post;
- exposição de taxonomias;
- shortcodes/aliases legados;
- provider endpoint/SSRF/secrets/data egress;
- prompt injection futuro;
- Search detail/scope fail-closed;
- migration/purge/destructive actions.

## Próximo passo exato

**T092 — Revisão de Segurança.**

T092 deve produzir threat model e findings `PASS | ENDURECER | POSTERGAR | BLOQUEAR`, sem criar runtime.

## Estado

T050–T059 + T090 + T091 concluídos documentalmente. **Nenhum runtime novo foi criado.** Próximo bloco autorizado: **T092**. SPEC-001 continua bloqueada até T097.
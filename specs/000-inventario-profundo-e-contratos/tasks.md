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
- [x] T058 — IA/vetor priorizados. _(`matriz-ia-vetor.md`; IA assistiva P1 aprovada como opcional, RAG P2 opcional, embeddings/semantic/rerank P3 postergados, agentes P4 postergados, G-140A–H definidos)_
- [ ] T059 — Matriz de paridade futura consolidada/final.

## Resultado T058

- [x] P0 determinístico permanece obrigatório antes de IA.
- [x] Assistente de Classificação e Assistente de Summary foram aprovados como capacidades opcionais P1, sob demanda e human-in-the-loop.
- [x] `include_ai`/AI READY não foi reutilizado como permissão de autoria assistida.
- [x] LLM em toda consulta de Search foi rejeitado no baseline.
- [x] RAG/síntese foi aprovado apenas como evolução P2, retrieval-first e podendo nascer lexical-first.
- [x] chunking adicional foi postergado e não pode criar parser paralelo.
- [x] embeddings foram postergados até baseline lexical + Golden demonstrarem lacuna mensurável.
- [x] semantic/hybrid retrieval foi postergado; se reaberto, lexical permanece fallback e o critério de ganho deve ser fixado antes do experimento.
- [x] reranking por modelo foi postergado e, se existir, será top-K bounded/fail-open.
- [x] Microsoft Foundry foi classificado como provider preferencial candidato, nunca dependência de domínio.
- [x] Foundry Agent File Search foi rejeitado como Search/RAG canônico do plugin; só pode reaparecer como projection de agente específico.
- [x] agentes/tools foram postergados/negados no baseline.
- [x] AI Operation Receipt conceitual, budget, NO_CHANGE, data egress e prompt-injection foram tratados.
- [x] G-140 foi detalhado em G-140A–H em `matriz-ia-vetor.md`.
- [x] nenhum runtime, provider, embedding, vector store, schema ou chamada externa foi criado.

## Ordem restante

1. **T059** — fechar a matriz de paridade futura final, incorporando T054–T058.
2. T090 — revisão do Arquiteto WordPress.
3. T091 — revisão do Crítico de Simplicidade.
4. T092 — revisão de Segurança.
5. T093 — revisão de QA/Regressão.
6. T094 — revisão de Produto/Conhecimento.
7. T095 — fechar unknowns/blockers aplicáveis.
8. T096 — relatório final da SPEC-000.
9. T097 — autorizar ou bloquear SPEC-001.

## Regra de continuidade

T059 ainda é documental. Não antecipar runtime, DDL, taxonomies finais, Foundry, embeddings, semantic search, agentes, Analytics detalhado ou durable queue.

## Estado

T050–T058 concluídas documentalmente. **Nenhum runtime novo foi criado.** Próximo bloco autorizado: **T059**. SPEC-001 continua bloqueada até T097.

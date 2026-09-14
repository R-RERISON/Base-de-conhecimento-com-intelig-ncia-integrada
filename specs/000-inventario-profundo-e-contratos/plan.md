# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor e cruzar os três repositórios de referência em contratos verificáveis antes de qualquer runtime novo.

## Agentes convocados

- Orquestrador Principal;
- Arquiteto WordPress;
- Arquiteto de Conhecimento;
- Especialista em Busca e Retrieval;
- Especialista em MariaDB e Dados;
- Especialista em IA/Foundry;
- Especialista em Segurança WordPress;
- Especialista em UI/UX WordPress;
- Especialista em Qualidade e Regressão;
- Especialista em Performance/Observabilidade;
- Crítico de Simplicidade.

## Fases A–F — concluídas

Inventário e classificação completos de KB2Ops, ASI e GRE:

- topologia/runtime;
- persistência;
- integrações WordPress;
- fluxos de produto;
- testes/regressão/build;
- classificação `MANTER | REDESENHAR | SUBSTITUIR POR WORDPRESS | EVOLUIR COM IA/VETOR | DESCARTAR | AINDA NÃO SABEMOS`.

## Fase G — cruzamento

1. [x] T052 — ownership lógico;
2. [x] T053 — sobreposição funcional;
3. [x] T050 — persistência consolidada;
4. [x] T051 — integrações consolidadas;
5. [x] T054 — drifts/compatibilidade/blockers;
6. [x] T056 — WordPress-first;
7. [x] T057 — infraestrutura própria mínima;
8. [x] T055 — regressões/Golden finais;
9. [ ] T058 — IA/vetor priorizados;
10. [ ] T059 — paridade futura final.

## Artefatos centrais

- `mapa-ownership-dados.md`;
- `matriz-sobreposicoes.md`;
- `catalogo-persistencia.md`;
- `catalogo-integracoes.md`;
- `mapa-contratos-quebrados.md`;
- `matriz-wordpress-first.md`;
- `infraestrutura-propria-minima.md`;
- `catalogo-testes-regressao.md`;
- `matriz-paridade-futura.md`;
- `riscos-e-drifts.md`;
- `research.md`.

## Decisões consolidadas T056/T057

### WordPress Core permanece dominante

- Editorial: `WP_Post` + Elementor read-only.
- Summary: Metadata API.
- Review/Governança: Metadata + Users + Revisions quando aplicável.
- Classificação: Metadata/Taxonomy conforme evidência.
- Search Knowledge/Golden: `WP_Post` interno + Metadata/Revisions inicialmente.
- Settings/Security/Health/Cache/Scheduling: primitives WordPress.
- AJAX apenas por live UX; REST somente com consumidor formal.

### Infra própria mínima

Apenas **Search Retrieval Projection** foi aprovada documentalmente, reduzida a um store lógico futuro de documentos `post|item`.

Não aprovados no baseline:

- Analytics Facts detalhados;
- durable queue;
- audit/quality rollups/migration registry permanentes;
- tabelas próprias para Summary, Classificação, Review, Search Knowledge ou Golden.

## Resultado T055 — estratégia de regressão

T055 tornou o catálogo de testes um contrato de release futuro.

### Classes

- `MUST`: obrigatório; falha/ausência de evidência = NO-GO.
- `CONDICIONAL`: obrigatório quando a feature existe.
- `POSTERGADO`: não deve nascer silenciosamente.
- `N/A`: exige justificativa explícita.

### Gates principais

- **G-001:** fronteira editorial WordPress/Elementor.
- **G-010:** Content Extractor/B-001.
- **G-020:** Summary/Metadata/B-006.
- **G-030:** Classificação/B-002.
- **G-040:** Review/governança/eventos.
- **G-050:** Search Retrieval Projection.
- **G-060:** QueryContext/ranking/explicabilidade.
- **Golden:** dataset governado + execução explícita + evidência corrente.
- **G-070:** scope/security/exposição.
- **G-080:** ausência intencional de Analytics detalhado no baseline.
- **G-090:** independência de durable queue no baseline.
- **G-100:** compatibilidade/cutover/B-003.
- **G-110:** UI/UX/a11y.
- **G-120:** performance/benchmark real.
- **G-130:** lifecycle/build/rollback.
- **G-140:** IA/vetor reservado para T058.

### Golden Queries

Princípios finais de T055:

- configuração de QA, não telemetria;
- storage WordPress-first inicialmente;
- suíte vazia = `NOT_CONFIGURED`, nunca PASS;
- suíte não executada ou stale = NO-GO para mudança/release de Search;
- `blocking` fail = NO-GO;
- warning exige decisão explícita;
- evidência deve vincular conjunto ativo, rankers, extractor/index e dataset/projection;
- dashboard lê status sem executar ranking;
- não depende de identidade/session/journey;
- cobertura por famílias de comportamento, sem número artificial mínimo.

### Performance

Nenhum p95/QPS foi fabricado. A SPEC de implementação deverá definir thresholds antes do GO e executar benchmark reproduzível no ambiente/corpus representativo.

## Próximo passo — T058

Identificar e priorizar IA/vetor sem violar os contratos T055.

T058 deve, no mínimo:

1. enumerar capacidades candidatas: classificação assistida, Summary assistido, embeddings, semantic retrieval, reranking, RAG/síntese, agentes/Foundry;
2. aplicar princípio de negação a cada uma;
3. separar valor de produto de dependência tecnológica;
4. definir onde IA é apenas sugestão versus leitura;
5. preservar lexical como fallback independente;
6. tratar custo, quota, timeout e indisponibilidade;
7. definir rastreabilidade de provider/model/prompt/version;
8. evitar batch/embedding sem `NO_CHANGE`/hash;
9. decidir quais capacidades ficam fora do primeiro runtime;
10. produzir gates que complementem G-140.

## Gate de conclusão da SPEC-000

Nenhuma SPEC de runtime começa antes de T059 + T090–T097. T097 é a única autorização formal para SPEC-001.

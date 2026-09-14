# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões documentais do cruzamento das baselines. Hipótese não vira fato sem evidência versionada; compatibilidade, provider e infraestrutura derivada não viram owner permanente.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Estado final do cruzamento T050–T059

Artefato executivo canônico: `matriz-paridade-futura.md`.

Fatos consolidados:

- um conceito canônico = um owner lógico;
- WordPress/Elementor são fonte editorial absoluta;
- Content Extractor único alimenta downstream;
- projection/index/cache/vector são derivados reconstruíveis;
- persistir -> confirmar -> emitir;
- dual-write permanente é proibido;
- Search lexical independe de IA/vetor;
- Search Retrieval Projection `post|item` é a única família própria aprovada no baseline;
- Analytics detalhado e durable queue permanecem postergados;
- Golden é QA governada e evidência de release;
- IA é assistiva, opcional e degradável;
- nenhum runtime novo foi criado na SPEC-000 até T059.

## 3. Fronteira temporal T059

A matriz final classifica capacidades como:

- `PRIMEIRO_RUNTIME` — primeira onda de vertical slices após eventual T097;
- `POSTERIOR` — capacidade aprovada, mas dependente de contratos/gates anteriores;
- `POSTERGADO` — complexidade não comprada;
- `COMPAT_CUTOVER` — somente com consumidor/coexistência comprovados;
- `DESCARTADO` — não transportar ao baseline.

“Primeiro runtime” não significa big-bang. É uma sequência de slices pequenos, cada um com DoR/DoD próprios.

## 4. Paridade por domínio

### Editorial

`WP_Post`, Elementor e publicação permanecem canônicos. Qualquer downstream é read-only em relação ao conteúdo editorial.

### Summary

`objective`, `escalation` e `important` permanecem em Metadata API. Os cinco campos classificatórios históricos do GRE pertencem ao owner Classificação, ainda que a mesma UI possa editá-los por composição.

### Classificação

Taxonomy é preferida para `audience`, `knowledge_type`, `service` e `technologies`, sob B-002 no cutover. `responsible_team`, `catalog_item`, `affected_service` e `systems_involved` continuam entre Metadata e Taxonomy; esse unknown não justifica tabela própria.

### Review/Governança

Estado, notas, reviewer/time, `include_ai` e histórico bounded permanecem WordPress-first. AI READY continua derivado de `publish + approved + 8/8 + include_ai` enquanto essa regra estiver vigente.

### Search

Search madura é posterior ao Content Extractor e exige:

- projection lexical `post|item` reconstruível;
- QueryContext/ranking determinísticos;
- Golden Queries;
- scope/security;
- benchmark;
- fallback/degradação.

Native WP search é fallback, não paridade final.

### Analytics/Queue

Ambos permanecem postergados. Não há query logging default nem fila improvisada em Options/Transients.

## 5. IA/vetor

T058 permanece integralmente válida após T059:

- P0 determinístico obrigatório;
- P1 Classificação ou Summary assistidos como capacidades opcionais;
- P2 RAG/síntese posterior e retrieval-first;
- P3 embeddings/semantic/hybrid/rerank postergados;
- P4 agentes/tools postergados.

Foundry é provider preferencial candidato, nunca domínio. File Search não é Search/RAG canônico.

## 6. Dados que precisam sobreviver ao cutover

Preservação obrigatória até decisão/migração comprovada:

- conteúdo/editorial WordPress/Elementor;
- oito valores GRE;
- Review/include_ai/notas/revisor/histórico KB2Ops válidos;
- classificações KB2Ops realmente usadas;
- Search Knowledge ASI manual real;
- Golden Queries ASI reais/úteis.

Não são automaticamente canônicos/migráveis: índices, caches, queues, rollups, telemetria histórica, migration registry, embeddings/vectors e projections reconstruíveis.

## 7. Blockers contextualizados

| ID | Capacidade bloqueada |
|---|---|
| B-001 | Search/RAG/embedding produtivos |
| B-002 | profiling/cutover classificatório |
| B-003 | remoção de plugins/aliases/adapters |
| B-004 | Analytics detalhado/query logging |
| B-005 | deep-link/anchors públicos de item |
| B-006 | write path composto definitivo |
| B-007 | durable queue/async indexing |

Conclusão T059: blocker contextual não bloqueia slice que não consome a capacidade correspondente. T095 deve fechar/postergar por slice, e T097 pode autorizar uma SPEC-001 que declare explicitamente quais blockers não toca.

## 8. Fallbacks sistêmicos

- domínio: manter estado canônico anterior em falha;
- Search: `degraded` + native/bounded fallback + rebuild;
- IA P1: sem provider = sem sugestão, sem write;
- RAG: sem geração = retrieval/evidências;
- semantic futuro: lexical fallback;
- sem queue: sync/bounded/manual batches;
- compatibilidade: coexistência/rollback até cutover validado.

## 9. Unknowns que permanecem legítimos

- quatro decisões Metadata vs Taxonomy dependentes de profiling;
- DDL/nome/índices físicos da projection Search;
- NFRs reais de extraction/index/Search;
- Analytics/query policy;
- necessidade futura de queue;
- anchors/deep-link;
- preflight real de consumidores;
- meta revisions finais;
- primeiro caso IA P1;
- provider/model/prompt/deployment concretos;
- política institucional de secrets/data egress;
- necessidade/tecnologia real de vetores;
- layout/runtime físico final.

Esses unknowns possuem owner, momento e gate; não exigem adivinhação antes de T097.

## 10. Próximo passo

**T090 — Revisão do Arquiteto WordPress.**

Objetivo: tentar eliminar qualquer infraestrutura, endpoint, storage ou abstração da matriz final que ainda possa ser substituída por WordPress Core, sem alterar o comportamento necessário.

## Estado

T050–T059 concluídas documentalmente. Nenhum runtime/schema/provider/vector foi criado. SPEC-001 continua bloqueada até T097.

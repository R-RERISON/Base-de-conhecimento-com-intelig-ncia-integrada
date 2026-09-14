# Riscos, Drifts e Dívidas — SPEC-000

> Estado após **T059**. Fontes canônicas: `mapa-contratos-quebrados.md`, `matriz-wordpress-first.md`, `infraestrutura-propria-minima.md`, `catalogo-testes-regressao.md`, `matriz-ia-vetor.md` e `matriz-paridade-futura.md`.

## 1. Drifts D-001–D-008

Permanecem válidos e não foram apagados pela consolidação:

- D-001 `Objective_Provider`: adapter somente por coexistência comprovada.
- D-002 evento Objective: futuro pós-write confirmado; bridge apenas se necessário.
- D-003 `post_content` vs Elementor: extractor único; B-001.
- D-004 múltiplos parsers: descartados.
- D-005 GAC: fora do core.
- D-006 classificação duplicada: owner resolvido; B-002 antes do cutover.
- D-007 UI/CSS fragmentados: DS único futuro.
- D-008 AI READY: contrato único testado `publish + approved + 8/8 + include_ai` enquanto vigente.

## 2. Blockers após T059

- **B-001:** extractor representativo — Search/RAG/embedding produtivos.
- **B-002:** profiling classificatório — cutover/migração e quatro decisões Metadata vs Taxonomy.
- **B-003:** preflight de consumidores — retirada de plugins/aliases/adapters.
- **B-004:** política Analytics/query text — Analytics detalhado/query logging.
- **B-005:** anchors/deep-link — exposição pública navegável de item.
- **B-006:** falha multi-campo — write path composto definitivo.
- **B-007:** stale/async queue — durable queue/async indexing.

**Regra T059:** blocker contextual não é blocker global. T095 precisa fechar/postergar por slice e registrar dependência explícita.

## 3. Riscos ativos preservados

Continuam vigentes:

- extração incompleta/custom widgets;
- projection stale usada como autoridade;
- múltiplos stores por herança;
- fallback lexical ilimitado;
- Golden stale/vazia tratada como PASS;
- adapters permanentes;
- evento antes de consistência;
- supercoleta de Analytics;
- queue prematura/falsa em Options/Transients;
- activation pesada;
- scans/meta LIKE sem bound;
- benchmark fictício;
- UI fragmentada;
- LLM no caminho crítico;
- confusão `include_ai`/assistência editorial;
- IA persistindo owner diretamente;
- provider lock-in;
- batch sem budget/NO_CHANGE;
- embedding/chunk drift;
- semantic piorando Golden blocking;
- File Search virando segunda fonte;
- síntese sem evidência;
- prompt injection;
- secrets/payload em logs;
- failover silencioso;
- preços/defaults de provider rigidificados;
- RAG acoplado a vetor sem necessidade;
- agentes antes de jornada multi-step.

Tratamento permanece G-001–G-140 + B-001–B-007.

## 4. Riscos específicos de consolidação T059

### X-038 — “primeiro runtime” virar big-bang

**Risco:** Core, domínio, Search, IA e cutover entrarem na mesma SPEC por estarem marcados como necessários ao produto final.  
**Tratamento:** T059 define PRIMEIRO_RUNTIME como **onda de vertical slices**, não pacote único; sequência de risco está versionada.

### X-039 — blocker contextual virar NO-GO global

**Risco:** projeto ficar paralisado tentando fechar B-001–B-007 antes de qualquer slice.  
**Tratamento:** matriz relaciona cada blocker à capacidade que ele realmente bloqueia; T095/T097 avaliam dependência por slice.

### X-040 — derived data tratado como patrimônio de cutover

**Risco:** migrar índices, caches, queue, rollups, telemetria ou embeddings por inércia, criando dívida no greenfield.  
**Tratamento:** lista explícita de dados que devem sobreviver; projections reconstruíveis não são migração obrigatória.

### X-041 — unknown de primitive virar permissão para tabela própria

**Risco:** `responsible_team`, `catalog_item`, `affected_service` ou `systems_involved` ganharem store próprio porque Metadata vs Taxonomy ainda está aberto.  
**Tratamento:** unknown está restrito a primitives WordPress sob B-002; não entra em T057 retroativamente.

### X-042 — compatibilidade definir arquitetura permanente

**Risco:** alias/shortcode/adapter histórico virar API oficial só porque existe consumidor antigo.  
**Tratamento:** COMPAT_CUTOVER exige consumidor, observabilidade, rollback, equivalência e gate de remoção.

## 5. Infraestrutura após T059

### Aprovada documentalmente

- uma Search Retrieval Projection futura `post|item`, reconstruível;
- IA P1 como capacidade opcional posterior, sem storage/provider runtime aprovado;
- RAG P2 opcional posterior, retrieval-first.

### Postergada/não autorizada

- Analytics detalhado;
- durable queue;
- vector table/vector store;
- MariaDB VECTOR/Azure AI Search como dependência;
- Foundry File Search como core;
- embeddings;
- semantic/hybrid retrieval;
- reranking;
- agentes/tools;
- batch IA automático;
- audit genérico/quality rollups;
- REST/SPA sem consumidor.

## 6. Dados de cutover

Devem sobreviver até decisão/migração comprovada:

- WordPress/Elementor/taxonomias editoriais;
- oito valores GRE;
- Review/include_ai/notas/revisor/histórico KB2Ops válidos;
- classificações KB2Ops efetivamente usadas;
- Search Knowledge ASI manual real;
- Golden Queries ASI úteis.

Telemetria histórica não migra automaticamente. Índices/caches/queues/rollups/migrations registry/embeddings são derivados ou operacionais e não ganham preservação canônica por padrão.

## 7. Dívidas postergáveis

- Word Cloud;
- GAC sem requisito;
- side panel GRE;
- `quality_daily`;
- SPA/REST;
- Analytics detalhado;
- durable queue;
- embeddings/semantic/rerank;
- agentes;
- rollups/materializações sem benchmark.

Implementação silenciosa de item POSTERGADO continua NO-GO arquitetural.

## 8. Próxima revisão — T090

O Arquiteto WordPress deve atacar principalmente X-038/X-041 e procurar qualquer ponto da matriz final em que o Core ainda possa substituir infraestrutura própria ou abstração desnecessária.

## Status

**T059 concluída documentalmente.** Próximo passo: **T090 — Revisão do Arquiteto WordPress**. Nenhum runtime/provider/vector foi autorizado.

# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T058. Fontes canônicas: `mapa-contratos-quebrados.md`, `matriz-wordpress-first.md`, `infraestrutura-propria-minima.md`, `catalogo-testes-regressao.md` e `matriz-ia-vetor.md`.

## 1. Drifts D-001–D-008 preservados

- D-001 `Objective_Provider`: adapter somente por coexistência comprovada.
- D-002 evento Objective: futuro pós-write confirmado; bridge apenas se necessário.
- D-003 `post_content` vs Elementor: extractor único; B-001.
- D-004 múltiplos parsers: descartados.
- D-005 GAC: fora do core.
- D-006 classificação duplicada: owner resolvido; B-002 antes do cutover.
- D-007 UI/CSS fragmentados: DS único futuro.
- D-008 AI READY: regra futura testada `publish + approved + 8/8 + include_ai`.

## 2. Blockers B-001–B-007

- **B-001:** extractor representativo — blocker de Search/RAG/embeddings produtivos.
- **B-002:** profiling classificatório — blocker de cutover classificatório.
- **B-003:** preflight de consumidores — blocker de retirada de aliases/plugins.
- **B-004:** Analytics/query text — mantém Analytics detalhado postergado.
- **B-005:** anchors/deep-link — blocker de paridade pública completa de item.
- **B-006:** falha multi-campo — blocker do write path composto definitivo.
- **B-007:** stale/async queue — blocker se durable queue/async for reaberto.

T058 não elimina esses blockers e adiciona gates de IA sem transformá-los em infraestrutura.

## 3. Riscos ativos anteriores

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
- UI fragmentada.

Tratamento permanece G-001–G-130 + B-001–B-007.

## 4. Novos riscos T058 — IA/Vetor

### X-023 — LLM no caminho crítico da Search

**Risco:** latência/custo/quota/provider outage derrubam a Search.  
**Tratamento:** LLM em toda query foi descartado; lexical independente; G-140A.

### X-024 — confundir `include_ai` com autorização de assistência editorial

**Risco:** drafts/revisões ficam acoplados à elegibilidade de corpus downstream.  
**Tratamento:** AI READY permanece distinto de eventual `AI Assist Allowed`; G-140C.

### X-025 — sugestão persistir diretamente

**Risco:** IA vira owner factual/editorial.  
**Tratamento:** Generate != Apply; Apply humano usa handlers canônicos; G-140C.

### X-026 — provider SDK contaminar domínio

**Risco:** lock-in e lógica de negócio dependente de Foundry.  
**Tratamento:** provider seam mínimo, WordPress HTTP API quando adequado, Foundry como adapter; G-140B.

### X-027 — batch sem budget/NO_CHANGE

**Risco:** custo imprevisível, processamento redundante, throttling.  
**Tratamento:** preview/estimativa/budget/stop condition/hash; G-140D. Worker durável exige reabrir F-057-03/B-007.

### X-028 — embedding/chunk drift

**Risco:** vetores incompatíveis coexistem após mudança de modelo/dimensão/extractor/chunking.  
**Tratamento:** fingerprint/lineage/version; re-embed seletivo; G-140E.

### X-029 — semantic/hybrid piorar casos críticos

**Risco:** média melhora, Golden blocking piora.  
**Tratamento:** baseline lexical congelado, Golden antes/depois, critério fixado antes do experimento, lexical fallback; G-140E.

### X-030 — File Search/vector store virar segunda fonte de conhecimento

**Risco:** duplicação de chunking/identity/freshness/cutover.  
**Tratamento:** Foundry File Search descartado como core; projection somente para agente específico; G-140H.

### X-031 — síntese sem evidência

**Risco:** resposta plausível sem suporte no corpus.  
**Tratamento:** retrieval -> evidências -> síntese -> fontes, abstenção e grounded evaluation; G-140F.

### X-032 — prompt injection via conteúdo recuperado

**Risco:** artigo malicioso manipula instrução/tool.  
**Tratamento:** conteúdo é dado, não comando; tools allowlisted/server-validated; G-140F/G.

### X-033 — logs de IA vazarem secrets/payload

**Risco:** credenciais/dados sensíveis em diagnostics/receipts.  
**Tratamento:** minimização, sem secrets/payload completo por default; G-140B.

### X-034 — failover silencioso

**Risco:** muda provider/região/custo/política sem decisão.  
**Tratamento:** failover explícito e governado; G-140B.

### X-035 — defaults/preços atuais virarem arquitetura rígida

**Risco:** provider evolui e contrato do produto quebra.  
**Tratamento:** não hard-code preço/default como regra de domínio; registrar snapshot/fonte para estimativa.

### X-036 — RAG depender de vetor desnecessariamente

**Risco:** atrasar valor de síntese e criar infraestrutura prematura.  
**Tratamento:** RAG P2 pode usar lexical; embedding/semantic permanecem P3.

### X-037 — agentes antes de caso multi-step

**Risco:** tool security, estado e custo sem valor comprovado.  
**Tratamento:** agentes P4 postergados/negados no baseline; G-140G.

## 5. Infraestrutura e IA após T058

### Aprovada documentalmente

- Search Retrieval Projection lexical/item futura;
- IA assistiva P1 como capacidade opcional de produto, ainda sem runtime/provider/storage;
- RAG/síntese P2 opcional posterior, retrieval-first.

### Postergada/não autorizada

- Analytics detalhado;
- durable queue;
- vector table/vector store;
- MariaDB VECTOR;
- Azure AI Search como dependência;
- Foundry File Search como core;
- embeddings;
- semantic/hybrid retrieval;
- reranking;
- agentes/tools;
- batch IA automático.

## 6. Custos e observabilidade

Toda chamada de IA futura precisa de AI Operation Receipt conceitual e budget quando aplicável. T058 não autoriza um novo stream/tabela de receipts; storage deve ser avaliado WordPress-first na SPEC concreta.

Preço/provider limit é dado operacional versionado, não constante arquitetural.

## 7. Dados/privacidade

- context egress precisa ser explícito/minimizado;
- query enviada ao provider não implica persistência local;
- B-004 continua governando Search Analytics;
- AI READY governa elegibilidade downstream quando ativo;
- assistência editorial em draft requer política/capability separada;
- secrets não entram em logs/prompt/export.

## 8. G-140

Subgates canônicos definidos em `matriz-ia-vetor.md`:

- G-140A independência/degradação;
- G-140B provider/rastreabilidade/data egress;
- G-140C human-in-the-loop;
- G-140D custo/budget/NO_CHANGE;
- G-140E embedding/semantic/hybrid;
- G-140F RAG/síntese;
- G-140G agentes/tools;
- G-140H provider-managed knowledge.

## 9. Dívidas postergáveis

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

Não bloqueiam slices que não dependem delas. Implementação silenciosa de item POSTERGADO continua NO-GO arquitetural.

## 10. Próximo passo — T059

T059 deve transformar as decisões T054–T058 em uma matriz final única e preparar as revisões T090–T097, sem criar runtime.

## Status

**T058 concluída documentalmente.** Próximo passo: **T059**. Nenhum runtime/provider/vector foi autorizado.

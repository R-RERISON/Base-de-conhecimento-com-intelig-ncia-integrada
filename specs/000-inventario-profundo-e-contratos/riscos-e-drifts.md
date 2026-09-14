# Riscos, Drifts e Dívidas — SPEC-000

> Estado após **T090**. Fontes canônicas: `mapa-contratos-quebrados.md`, `matriz-wordpress-first.md`, `infraestrutura-propria-minima.md`, `catalogo-testes-regressao.md`, `matriz-ia-vetor.md`, `matriz-paridade-futura.md` e `revisao-wordpress-t090.md`.

## 1. Drifts D-001–D-008

Permanecem válidos e não foram apagados pelas revisões:

- D-001 `Objective_Provider`: adapter somente por coexistência comprovada.
- D-002 evento Objective: futuro pós-write confirmado; bridge apenas se necessário.
- D-003 `post_content` vs Elementor: extractor único; B-001.
- D-004 múltiplos parsers: descartados.
- D-005 GAC: fora do core.
- D-006 classificação duplicada: owner resolvido; B-002 antes do cutover.
- D-007 UI/CSS fragmentados: DS único futuro.
- D-008 AI READY: contrato único testado `publish + approved + 8/8 + include_ai` enquanto vigente.

## 2. Blockers B-001–B-007

Continuam contextuais conforme T059:

- B-001 Search/RAG/embedding produtivos.
- B-002 profiling/cutover classificatório.
- B-003 retirada de plugins/aliases/adapters.
- B-004 Analytics detalhado/query logging.
- B-005 deep-link público de item.
- B-006 write path composto definitivo.
- B-007 durable queue/async indexing.

T090 não adicionou blocker global.

## 3. Riscos ativos preservados

Continuam vigentes os riscos de:

- extração parcial/custom widgets;
- projection stale;
- múltiplos stores por herança;
- fallback lexical ilimitado;
- Golden stale/vazia tratada como PASS;
- adapters permanentes;
- evento antes de persistência confirmada;
- supercoleta de Analytics;
- queue prematura/falsa;
- activation pesada;
- scans/meta LIKE sem bounds;
- benchmark fictício;
- UI fragmentada;
- LLM no caminho crítico;
- IA persistindo owner;
- provider lock-in;
- batch sem budget/NO_CHANGE;
- embedding/chunk drift;
- semantic piorando Golden blocking;
- File Search como segunda fonte;
- prompt injection;
- secrets/payload em logs;
- failover silencioso;
- RAG acoplado a vetor sem necessidade;
- agentes antes de jornada multi-step;
- “primeiro runtime” virar big-bang;
- blocker contextual virar NO-GO global;
- derived data virar patrimônio de cutover;
- unknown de primitive virar tabela própria;
- compatibilidade definir arquitetura permanente.

## 4. Novos riscos/guardrails T090

### X-043 — histórico duplicado de Review

**Risco:** manter bounded history e meta revisions para a mesma finalidade, aumentando storage/complexidade e criando duas narrativas de auditoria.  
**Tratamento:** escolher mecanismo mínimo por requisito; revisions somente quando recuperação de snapshot gerar valor real.

### X-044 — entidade interna ganhar CRUD/storage próprio cedo demais

**Risco:** Search Knowledge/Golden ganharem tabela/admin framework/REST apesar de `WP_Post` interno + metadata/revisions atenderem o workload de governança.  
**Tratamento:** WordPress-first obrigatório; reabrir store próprio somente com evidência de volume/consulta.

### X-045 — taxonomia sistêmica virar superfície pública por acidente

**Risco:** archive/rewrite público expor classificação interna sem jornada aprovada.  
**Tratamento:** exposição pública fail-closed; habilitar somente por requisito de produto.

### X-046 — depender de meta revisions sem versão mínima compatível

**Risco:** runtime usar `revisions_enabled` em ambiente abaixo do mínimo suportado.  
**Tratamento:** T095/SPEC aplicável fixa versão mínima ou define fallback.

### X-047 — endpoint de provider configurável permitir SSRF

**Risco:** URL arbitrária administrável ser usada pela WordPress HTTP API para alcançar destinos indevidos.  
**Tratamento:** T092/SPEC de IA deve revisar allowlist de host/esquema e uso de API segura quando aplicável.

## 5. Resultado T090

- findings bloqueantes: **0**;
- Search Retrieval Projection própria: **mantida**;
- nenhuma tabela/endpoints adicionais autorizados;
- nenhuma capacidade postergada reaberta;
- nenhum runtime criado.

## 6. Próximo passo — T091

O Crítico de Simplicidade deve tentar remover capacidades mesmo quando tecnicamente válidas, perguntando se o produto perde resultado material sem elas.

Foco especial:

- DS/components antecipados;
- history/revisions;
- Search Knowledge inicial;
- item layer/deep-links;
- compatibilidade;
- Site Health checks excessivos;
- IA P1/P2;
- qualquer abstração/provider seam prematuro.

## Status

**T090 concluída documentalmente.** Próximo passo: **T091**. Nenhum runtime/provider/vector foi autorizado.

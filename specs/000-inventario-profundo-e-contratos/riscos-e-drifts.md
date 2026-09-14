# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T055. O mapa canônico de compatibilidade permanece em `mapa-contratos-quebrados.md`; WordPress-first em `matriz-wordpress-first.md`; infraestrutura própria em `infraestrutura-propria-minima.md`; regressão/Golden em `catalogo-testes-regressao.md`.

## 1. Drifts D-001–D-008

| ID | Tema | Estado |
|---|---|---|
| D-001 | `Objective_Provider` inexistente | arquitetura futura corrige; adapter só por coexistência comprovada |
| D-002 | evento `bdc_es_objective_updated` inexistente | futuro evento pós-write confirmado; bridge só se necessário |
| D-003 | `post_content` vs Elementor | extractor único; B-001 permanece blocker |
| D-004 | múltiplos parsers | descartados; downstream usa extractor/projection canônicos |
| D-005 | GAC acoplado | fora do core; adapter somente por requisito real |
| D-006 | classificação duplicada | owner resolvido; profiling B-002 antes de cutover |
| D-007 | UI/CSS fragmentados | DS único futuro |
| D-008 | AI READY docs/runtime | contrato futuro único/testado: `publish + approved + 8/8 + include_ai` |

## 2. Blockers após T055

### B-001 — Content Extractor representativo

**BLOCKER para:** Search/RAG produtivos.  
**Gate T055:** G-010 exige corpus Elementor real, custom widgets, detecção de omissão, determinismo e zero write editorial.

### B-002 — Profiling classificatório

**BLOCKER para:** migração/cutover de classificação.  
**Gate T055:** G-030 exige cardinalidade, vocabulário, colisões, filtros e migração idempotente.

### B-003 — Consumidores externos/aliases

**BLOCKER para:** retirar plugins/aliases antigos.  
**Gate T055:** G-100 exige preflight real, compat limitada, observabilidade, rollback e gate de remoção.

### B-004 — Analytics/query text

**BLOCKER para:** Analytics detalhado.  
**Após T055:** G-080 é gate negativo do baseline: Search funciona sem Analytics e query text não é persistida silenciosamente.

### B-005 — Deep-link/anchors

**BLOCKER para:** paridade pública completa de item/deep-link.  
**Após T055:** documento lexical item pode existir; exposição de link público exige identity/anchor/browser fail-closed.

### B-006 — falha multi-campo

**BLOCKER para:** write path composto definitivo.  
**Gate T055:** G-020 proíbe sucesso falso; a futura SPEC deve escolher/testar falha total, parcial detectável ou compensação.

### B-007 — stale/async queue

**BLOCKER para:** reabrir durable queue/async indexing.  
**Após T055:** G-090 prova independência de queue no baseline; se fila voltar, lease/retry/dead/recovery tornam-se MUST.

## 3. Riscos ativos

### Content Extraction / Search

- **R-KB-001:** custom widget relevante pode desaparecer em extração parcialmente não vazia.
- **X-003:** índice rápido sobre conteúdo incompleto.
- **X-013:** projection stale não observada.
- **X-014:** projection stale usada como autoridade de exposição.
- **X-015:** recriar múltiplos stores Search por herança ASI.
- **X-016:** fallback lexical virar LIKE/scan ilimitado.
- **X-019:** Golden PASS antigo continuar aceito após mudança material de ranker/extractor/dataset.
- **X-020:** suíte Golden vazia ou não executada ser apresentada como saudável.

**Tratamento T055:** G-010/G-050/G-060 + Golden com invalidation/evidence freshness e revalidação WP.

### Golden/QA

- **R-055-01:** quantidade alta de Golden sem cobertura de comportamentos críticos gera falsa confiança.
- **R-055-02:** importar query real de usuário para Golden sem política pode carregar dado sensível.
- **R-055-03:** warning failures ignoradas acumulam drift silencioso.
- **R-055-04:** dashboard executar ranking automaticamente pode criar custo/side effect inesperado.

**Tratamento:** cobertura por famílias de comportamento; Golden é curada; telemetria futura depende B-004; warnings exigem decisão; execução explícita/read-only.

### Compatibilidade/Migração

- **X-001:** dual-read/adapter virar permanente.
- **X-005:** migration virar arquitetura.
- **X-010:** alias/shortcode portado sem consumidor.

**Tratamento:** G-100 + B-003.

### Eventos/Governança

- **X-002:** evento antes de persistência confirmada.
- **X-011:** invalidation storm.
- **R-KB-002:** approval histórico emitido sem comprovar writes.
- **R-GRE-002:** falha tardia multi-campo.

**Tratamento:** G-020/G-040; persistir -> confirmar -> emitir; consumer idempotente.

### Analytics/Privacidade

- **X-004 / R-ASI-003 / R-KB-005:** supercoleta de query text/identidade.
- **R-ASI-004:** copiar bucket IP+UA histórico.
- **X-021:** alguém “instrumentar provisoriamente” Search e criar telemetria antes de B-004.

**Tratamento:** G-080 torna ausência de logging detalhado uma decisão testável do baseline.

### Queue/Operações

- **X-017:** durable queue antes de necessidade medida.
- **X-018:** Options/Transients usados como fila falsa.
- **X-022:** activation disparar rebuild massivo.

**Tratamento:** G-090 + G-130.

### Performance

- **R-GRE-003 / R-KB-004 / X-008:** scans integrais e meta LIKE sem bounds.
- **R-057-01:** FULLTEXT/índices finais não testados no MariaDB/MySQL real.
- **R-057-02:** item cardinality/rebuild ainda sem benchmark.
- **R-055-05:** transformar guardrail estrutural em “benchmark aprovado”.
- **R-055-06:** inventar p95/QPS sem medição de ambiente.

**Tratamento:** G-120 exige benchmark reproduzível e thresholds definidos pela SPEC futura antes do GO.

### UI/UX

- **X-006/X-009:** copiar fragmentos CSS/menus antigos ou permitir UI acessar stores lateralmente.

**Tratamento:** G-110 + DS único.

## 4. Infraestrutura após T057/T055

### Aprovada documentalmente

- uma Search Retrieval Projection futura e unificada para documentos `post|item`.

### Postergada/não autorizada

- Analytics Facts;
- durable queue;
- audit table genérica;
- `quality_daily`/rollups;
- migration registry permanente;
- Golden table;
- tabelas Search Knowledge;
- stores separados post/item sem prova.

T055 adiciona uma regra: **implementar uma capacidade postergada sem reabertura formal é regressão arquitetural e NO-GO**, mesmo que “funcione”.

## 5. Dívidas postergáveis

Não bloqueiam uma SPEC futura que não dependa delas:

- Word Cloud;
- GAC sem requisito;
- side panel GRE;
- `quality_daily`;
- SPA/REST;
- Analytics detalhado;
- durable queue;
- vetor/embeddings/Foundry;
- rollups/materializações sem benchmark.

## 6. Regras de regressão consolidadas

- MUST sem evidência = NO-GO;
- CONDICIONAL ativado sem teste = NO-GO;
- POSTERGADO implementado silenciosamente = NO-GO;
- `NOT_CONFIGURED`, `NOT_RUN`, `DEGRADED` e warning não são PASS;
- Golden blocking fail = NO-GO;
- Golden stale = NO-GO para Search afetada;
- suite Golden PASS não fecha B-001/performance/security;
- Search funciona sem IA/vetor e sem Analytics;
- baseline não depende de durable queue;
- WordPress/Elementor permanecem autoridade editorial e de exposição.

## 7. Próximo risco a avaliar — T058

T058 deve tratar explicitamente:

- custo/quota/provider lock-in;
- indisponibilidade/timeout;
- batch sem NO_CHANGE;
- embedding/chunk drift;
- semantic retrieval piorando lexical;
- síntese sem evidência;
- persistência automática de sugestão de IA;
- dados enviados ao provider;
- rastreabilidade de prompt/model/version.

## Status

**T055 concluída documentalmente.** Próximo passo: **T058 — candidatos IA/vetor**. Nenhum runtime foi autorizado.

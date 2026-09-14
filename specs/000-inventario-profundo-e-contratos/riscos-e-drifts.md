# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T057. O mapa canônico de compatibilidade/drifts permanece em `mapa-contratos-quebrados.md`. As decisões de primitives e infraestrutura estão em `matriz-wordpress-first.md` e `infraestrutura-propria-minima.md`.

## 1. Drifts D-001–D-008 — estado preservado

| ID | Tema | Estado |
|---|---|---|
| D-001 | `Objective_Provider` inexistente | arquitetura futura corrige; adapter temporário apenas se ASI legado coexistir |
| D-002 | `bdc_es_objective_updated` nunca emitido | arquitetura futura corrige; bridge temporário apenas se ASI legado precisar de invalidação |
| D-003 | `post_content` versus Elementor | direção corrigida por extractor único; qualidade do extractor permanece BLOCKER |
| D-004 | múltiplos parsers | parsers duplicados descartados; Word Cloud/anchors dependem de produto/preflight |
| D-005 | GAC acoplado | depende de requisito/preflight; fora do core |
| D-006 | classificação GRE/KB2Ops | ownership resolvido; primitives avaliadas; profiling/migração continuam blockers de cutover |
| D-007 | UI/CSS fragmentados | DS único futuro; CSS/menus antigos descartados como contrato permanente |
| D-008 | AI READY docs/runtime | contrato futuro = baseline runtime testada; sem adapter complexo |

## 2. Blockers reais após T057

### B-001 — Content Extractor representativo

**BLOCKER para:** implementar Search Retrieval Projection final e qualquer RAG/indexação produtiva.  
**Risco:** índice tecnicamente rápido, mas semanticamente incompleto.  
**Gate:** corpus Elementor real + custom widgets + diagnóstico de omissão + regressão de conteúdo relevante.

### B-002 — Profiling classificatório

**BLOCKER para:** migração/cutover classificatório.  
**Após T056/T057:** continua entre Metadata/Taxonomy; não justifica tabela própria.  
**Gate:** cardinalidade, vocabulário, equivalências, colisões, uso em filtros/templates e estratégia de migração.

### B-003 — Preflight de consumidores externos/shortcodes

**BLOCKER para:** remover plugins/aliases antigos com segurança.  
**Gate:** inventário de uso real e condição objetiva de remoção.

### B-004 — Política de Analytics/query text

**BLOCKER para:** habilitar Analytics detalhado.  
**Após T057:** F-057-02 foi postergada; não há tabela de events/interactions/outcomes autorizada.  
**Gate:** finalidade, minimização, retenção, acesso, sensibilidade e necessidade de correlação.

### B-005 — Deep-link/anchors

**BLOCKER para:** paridade completa de Item Knowledge/deep-link.  
**Após T057:** não impede armazenar/indexar documento `item`; impede tratá-lo como link público comprovado sem estratégia de destino.

### B-006 — Falha multi-campo

**BLOCKER para:** write path composto definitivo de Summary/Classificação.  
**Após T056/T057:** não é justificativa para banco/tabela própria.  
**Gate:** semântica de falha tardia + read-after-write + testes de parcialidade/compensação conforme contrato.

### B-007 — Projection stale observável

**BLOCKER para:** reabrir Durable Job State/async indexing em produção.  
**Após T057:** fila foi postergada. Freshness básica da projection Search ainda deve ser observável mesmo no modo síncrono/manual.

## 3. Riscos ativos por domínio

### Content Extraction / Search

- **R-KB-001:** saída parcialmente não vazia pode omitir custom widget e evitar fallback.
- **X-003:** Search/RAG pode indexar conteúdo incompleto silenciosamente.
- **X-013:** projection pode ficar stale sem observabilidade.
- **X-014:** projection stale pode virar bypass de publicação/permissão se Search confiar no índice como autoridade.
- **X-015:** separar post/item em múltiplas tabelas por herança pode recriar complexidade ASI desnecessária.
- **X-016:** fallback lexical pode degenerar em scans/LIKE ilimitados se não houver bounds.

**Tratamento T057:** um único store lógico de documentos derivados; WordPress revalida exposição; FULLTEXT + fallback bounded; hashes/version/freshness; B-001 antes do Search final.

### Compatibilidade/Migração

- **X-001:** dual-read/adapter virar permanente.
- **X-005:** migration virar arquitetura.
- **X-010:** shortcodes/aliases portados sem consumidor.

**Tratamento:** cada compatibilidade exige entrada, owner, modo, observabilidade, rollback, remoção e teste.

### Eventos/Projections

- **X-002:** evento antes de consistência.
- **X-011:** tempestade de invalidations/eventos.
- **X-013:** projection stale sem observabilidade.

**Contrato:** persistir -> confirmar -> emitir; consumers idempotentes; coalescing somente se medição exigir.

### Analytics/Privacidade

- **R-ASI-003 / R-KB-005 / X-004:** query text e telemetria podem ser supercoletados ou subdimensionados.
- **R-ASI-004:** bucket IP+UA não deve ser portado literalmente.
- **T057:** Analytics detalhado foi deliberadamente postergado; ausência de política é motivo para **não persistir**, não para escolher schema provisório.

### Queue/Operações

- **X-017:** criar durable queue antes de medir extractor/index transforma solução de falha hipotética em dívida permanente.
- **X-018:** usar Options/Transients como fila improvisada cria semântica falsa de durabilidade.

**Tratamento T057:** zero queue table no baseline; primeiro medir indexação síncrona/bounded e rebuild manual/batched. Se fila voltar, preservar lease/retry/dead/recovery e resolver B-007.

### Governança

- **X-007:** approval do artigo != Apply de Search Knowledge.
- **R-KB-002:** `kb2ops_post_approved` histórico emitia antes de comprovar todos os writes; comportamento rejeitado.
- **R-GRE-002:** multi-campo pode aplicar parcialmente em falha tardia.

### Performance

- **R-GRE-003 / R-KB-004 / X-008:** scans integrais/meta LIKE/dashboards sem bound não podem virar baseline.
- **T057:** ~700 posts é evidência do corpus atual, não NFR futuro. Não foram inventadas metas de latência.
- **R-057-01:** FULLTEXT e índices finais precisam ser validados no MariaDB/MySQL real e contra queries reais.
- **R-057-02:** número de itens por post e custo do rebuild ainda não estão medidos.

### UI

- **X-006/X-009:** DS único não significa copiar CSS antigo nem permitir UI acessar stores lateralmente.

## 4. Resultado de T057 sobre complexidade

### Infraestrutura própria aprovada documentalmente

- **uma Search Retrieval Projection unificada**, futura, reconstruível e não-canônica, cobrindo documentos de post e item.

### Infraestrutura própria não aprovada/postergada

- Analytics facts/events/interactions/outcomes;
- Durable Job State/queue;
- audit table genérica;
- quality rollup;
- migration registry permanente;
- tabelas separadas de post/item sem benchmark;
- tabelas Search Knowledge/Golden.

A palavra “aprovada” em T057 não autoriza DDL. Autoriza somente o desenho em SPEC futura após gates.

## 5. Unknowns WordPress-first que não viraram infraestrutura

- responsible team: Metadata versus Taxonomy;
- catalog item: Metadata versus Taxonomy;
- affected service: Metadata versus Taxonomy;
- systems involved: Metadata versus Taxonomy;
- campos que realmente precisam de meta revisions;
- REST futuro somente se surgir consumidor;
- anchors/deep-link;
- Word Cloud;
- query text/retention.

## 6. Dívidas explicitamente postergáveis

Não bloqueiam SPEC-001 por si só quando o slice não depende delas:

- Word Cloud;
- GAC sem requisito comprovado;
- side panel GRE;
- `quality_daily`;
- SPA/REST;
- vetor/embeddings/Foundry;
- Analytics detalhado;
- queue durável;
- materializações/rollups sem benchmark.

## 7. O que T055 deve proteger

- Content Extractor único/read-only;
- B-001 com fixtures reais/custom widgets;
- projection post/item unificada e determinística;
- item identity/hash/generation;
- rebuild idempotente;
- remoção/despublicação não vaza resultado stale;
- FULLTEXT e fallback bounded;
- ranking explicável + Golden;
- scope/detail recheck no WordPress;
- estado stale/degraded observável;
- Search funciona sem Analytics;
- baseline não grava query text silenciosamente;
- baseline não depende de queue;
- activation não executa rebuild massivo.

## 8. Regras que não podem regredir

- um conceito canônico = um owner;
- dual-write permanente proibido;
- adapter temporário possui gate de remoção;
- projection não é canônico;
- persistência confirmada precede evento;
- consumers idempotentes;
- Analytics non-fatal;
- Content Extraction único;
- IA não decide/persiste sem humano;
- lexical funciona sem IA/vetor;
- nenhum runtime antes do gate T097.

## Status

**T057 concluída documentalmente.** Próximo passo: **T055 — catálogo consolidado de regressão e Golden Queries**.
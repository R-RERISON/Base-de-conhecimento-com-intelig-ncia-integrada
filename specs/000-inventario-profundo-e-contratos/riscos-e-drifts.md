# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T056. O mapa canônico de compatibilidade/drifts permanece em `mapa-contratos-quebrados.md`. A matriz canônica de primitives WordPress-first está em `matriz-wordpress-first.md`.

## 1. Drifts D-001–D-008 — estado preservado

| ID | Tema | Estado |
|---|---|---|
| D-001 | `Objective_Provider` inexistente | arquitetura futura corrige; adapter temporário apenas se ASI legado coexistir |
| D-002 | `bdc_es_objective_updated` nunca emitido | arquitetura futura corrige; bridge temporário apenas se ASI legado precisar de invalidação |
| D-003 | `post_content` versus Elementor | direção corrigida por extractor único; qualidade do extractor permanece BLOCKER |
| D-004 | múltiplos parsers | parsers duplicados descartados; Word Cloud/anchors dependem de produto/preflight |
| D-005 | GAC acoplado | depende de requisito/preflight; fora do core |
| D-006 | classificação GRE/KB2Ops | ownership resolvido; T056 avaliou primitives; profiling/migração continuam blockers de cutover |
| D-007 | UI/CSS fragmentados | DS único futuro; CSS/menus antigos descartados como contrato permanente |
| D-008 | AI READY docs/runtime | contrato futuro = baseline runtime testada; sem adapter complexo |

## 2. Blockers reais

### B-001 — Content Extractor representativo

**BLOCKER para:** Search/Indexing/RAG final.  
**Risco:** índice semanticamente incorreto mesmo com ranker correto.  
**Gate:** corpus Elementor real + custom widgets + diagnóstico de omissão + regressão de conteúdo relevante.

### B-002 — Profiling classificatório

**BLOCKER para:** migração/cutover de audiência, serviço, serviço afetado, tecnologias, sistemas, equipe/item catálogo conforme aplicável.  
**Após T056:** não bloqueia a conclusão WordPress-first; os unknowns continuam entre Metadata/Taxonomy, sem tabela própria.  
**Gate:** cardinalidade, vocabulário, equivalências, colisões, uso em filtros/templates e estratégia de migração validados.

### B-003 — Preflight de consumidores externos/shortcodes

**BLOCKER para:** remover plugins/aliases antigos com segurança.  
**Gate:** inventário de uso real em posts/pages/templates/widgets e condição objetiva de remoção.

### B-004 — Política de Analytics/query text

**BLOCKER para:** habilitar telemetria detalhada em produção e para aprovar F-057-02.  
**Gate:** minimização, retenção, acesso, finalidade e sensibilidade explícitos.

### B-005 — Deep-link/anchors

**BLOCKER para:** paridade completa de Item Knowledge/deep-link.  
**Não bloqueia:** Search lexical de post nem primeiros slices de Summary/Classificação se não usarem anchors.

### B-006 — Falha multi-campo

**BLOCKER para:** write path composto definitivo de Summary/Classificação.  
**Após T056:** não é justificativa para tabela própria.  
**Gate:** semântica de falha tardia + read-after-write + testes de parcialidade/compensação conforme contrato.

### B-007 — Projection stale observável

**BLOCKER para:** indexação assíncrona/queue em produção e aprovação de F-057-03.  
**Condicional:** não aplicável até existir projection assíncrona.

## 3. Riscos ativos por domínio

### Content Extraction

- **R-KB-001:** saída parcialmente não vazia pode omitir custom widget e evitar fallback.
- **X-003:** Search/RAG pode indexar conteúdo incompleto silenciosamente.

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
- **T056:** Options/postmeta foram rejeitados como store definitivo de stream detalhado; isso **não aprova** F-057-02 sem B-004/volumetria.

### Governança

- **X-007:** approval do artigo != Apply de Search Knowledge.
- **R-KB-002:** `kb2ops_post_approved` histórico emitia antes de comprovar todos os writes; comportamento rejeitado.
- **R-GRE-002:** multi-campo pode aplicar parcialmente em falha tardia.

### Performance

- **R-GRE-003 / R-KB-004 / X-008:** scans integrais/meta LIKE/dashboards sem bound não podem virar baseline.
- **T056:** `WP_Query` permanece para read models bounded/fallback, não como justificativa para reproduzir Search KB2Ops provisória.

### UI

- **X-006/X-009:** DS único não significa copiar CSS antigo nem permitir UI acessar stores lateralmente.

## 4. Resultado de T056 sobre complexidade

### Infraestrutura própria rejeitada nesta fase

- tabela para Resumo;
- tabela para Classificação;
- tabela para Review/history bounded;
- tabela para Search Knowledge/Golden sem prova de volume;
- health dashboard técnico paralelo;
- scheduler próprio;
- cache durável próprio;
- REST sem consumidor;
- queue por herança ASI;
- rollups/materializações sem benchmark.

### Candidatos permitidos a T057

- **F-057-01 — Search Retrieval Projection**;
- **F-057-02 — Analytics Facts**, condicional;
- **F-057-03 — Durable Job State**, condicional.

A palavra “candidato” significa que T057 ainda pode concluir **NÃO CONSTRUIR**.

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

Não bloqueiam SPEC-001 por si só:

- Word Cloud;
- GAC sem requisito comprovado;
- side panel GRE;
- `quality_daily`;
- SPA/REST;
- vetor/embeddings/Foundry;
- queue durável quando o slice não exigir;
- materializações/rollups sem benchmark.

## 7. O que T057 deve provar

Para cada F-057:

- por que a primitive WP avaliada falha no comportamento alvo;
- volume e cardinalidade;
- padrão de leitura/escrita;
- índices/consultas;
- latência/SLA;
- concorrência/durabilidade;
- reconstruibilidade;
- failure/recovery;
- custo operacional;
- menor extensão possível.

Não começar T057 desenhando tabela.

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

**T056 concluída documentalmente.** Próximo passo: T057 — infraestrutura própria mínima justificada, limitada inicialmente às três famílias F-057.

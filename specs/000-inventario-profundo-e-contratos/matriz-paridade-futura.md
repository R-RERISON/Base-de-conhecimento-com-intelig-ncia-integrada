# Matriz de Paridade Futura — SPEC-000

> Matriz incremental após T050–T058. T059 permanece aberta e será o fechamento final desta matriz antes das revisões T090–T097.

## Legenda

- **MANTER** — comportamento necessário.
- **REDESENHAR** — comportamento necessário, implementação nova.
- **SUBSTITUIR POR WORDPRESS** — primitive Core suficiente.
- **INFRA PRÓPRIA MÍNIMA** — extensão própria justificada.
- **APROVADO OPCIONAL** — capacidade de produto aprovada, não baseline obrigatório.
- **POSTERGAR** — não comprou complexidade; reabrir com evidência.
- **DESCARTAR** — não transportar ao baseline.
- **AINDA NÃO SABEMOS** — depende de evidência/preflight/profiling.

## 1. Editorial, domínio e governança

| Capacidade | Owner | Direção | Momento | Gate |
|---|---|---|---|---|
| conteúdo/título/publicação | WP/Elementor | MANTER WP | primeiro runtime | G-001 |
| Content Extractor | Content Extraction | REDESENHAR/MANTER contrato | primeiro runtime quando Search/IA depender | G-010/B-001 |
| Summary | Summary | MANTER Metadata API | primeiro runtime | G-020/B-006 |
| classificação | Classificação | Metadata/Taxonomy conforme evidência | primeiro runtime por slice | G-030/B-002 |
| review/include_ai/history | Revisão | MANTER WP | primeiro runtime | G-040 |
| AI READY | Revisão | MANTER derivado | quando fluxo IA existir | G-040 |
| Search Knowledge | Search Knowledge | WP_Post interno + meta/revisions inicialmente | posterior conforme Search | G-060 |
| Golden Queries | Search Quality | WP_Post interno + meta/revisions | antes de release/mudança Search | Golden |

## 2. Search

| Capacidade | Direção | Momento | Gate/Fallback |
|---|---|---|---|
| native WP search | MANTER fallback | primeiro runtime possível | G-060/G-070 |
| lexical/FULLTEXT | MANTER/REDESENHAR | Search slice | G-010/G-050/G-060/Golden/G-120 |
| projection `post|item` unificada | INFRA PRÓPRIA MÍNIMA | Search slice após B-001 | G-050 |
| QueryContext determinístico | MANTER/REDESENHAR | Search slice | G-060 |
| ranking explicável | MANTER | Search slice | G-060 + Golden |
| item identity | MANTER/REDESENHAR | Search/Item slice | G-050 |
| deep-link público | REDESENHAR | posterior | B-005 + browser/E2E |
| LLM em toda query | DESCARTAR baseline | não implementar | G-140A |
| embeddings | POSTERGAR | P3 | G-140E; lexical fallback |
| semantic/hybrid | POSTERGAR | P3 | G-140E; lexical fallback |
| model rerank | POSTERGAR | P3 | G-140E; deterministic fail-open |

## 3. Analytics/Operations

| Capacidade | Direção | Momento | Gate |
|---|---|---|---|
| Search Analytics detalhado | POSTERGAR | somente após B-004 | G-080/B-004 |
| query text logging | não baseline | somente política explícita | B-004 |
| durable queue | POSTERGAR | somente workload medido | G-090/B-007 |
| WP-Cron | MANTER como trigger | quando necessário | G-090 |
| Site Health | SUBSTITUIR POR WORDPRESS + checks | primeiro runtime | G-130 |
| activation pesada | DESCARTAR | nunca baseline | G-130 |

## 4. IA Assistiva — decisão T058

| Capacidade | Owner afetado | Direção | Prioridade | Autoridade |
|---|---|---|---:|---|
| pré-análise determinística | domínio | MANTER | P0 | determinística |
| classificação assistida | Classificação | APROVADO OPCIONAL | P1 | sugestão; humano aplica |
| Summary assistido | Summary | APROVADO OPCIONAL | P1 | sugestão; humano aplica |
| RAG/síntese | AI Assist/Search | APROVADO OPCIONAL POSTERIOR | P2 | síntese sobre evidência |
| chunking adicional | Search Indexing/AI | POSTERGAR condicional | P2/P3 | projection |
| embeddings | Search Indexing/AI | POSTERGAR | P3 | projection |
| semantic/hybrid retrieval | Search | POSTERGAR | P3 | ranker versionado |
| model rerank | Search | POSTERGAR | P3 | ranker bounded |
| agentes/tools | AI Assist | POSTERGAR/NEGAR baseline | P4 | read-only default; humano para mutação |

## 5. IA Assistiva P1

### Classificação

Fluxo futuro permitido:

`conteúdo extraído + fatos + vocabulário -> gerar sugestão -> validar estrutura -> humano revisa -> Apply -> owner canônico persiste -> read-after-write`

Não permitido:

- criação automática de termos;
- persistência pelo adapter/modelo;
- colapsar service/affected_service ou technologies/systems sem B-002;
- usar confiança do modelo como aprovação.

### Summary

Pode sugerir `objective`, `escalation`, `important`, com evidência/abstenção. `post_title` continua canônico. Apply humano usa G-020/B-006.

### AI READY

`include_ai` continua parte da elegibilidade downstream. Não é permissão de autoria assistida. Eventual `AI Assist Allowed` é conceito separado e deve ser definido pela SPEC futura.

## 6. RAG P2

Direção:

`query -> retrieval lexical/híbrido se aprovado -> evidências -> síntese opcional -> fontes`

RAG não exige vetor. Uma primeira síntese futura pode usar lexical confiável.

Quando gate de confiança estiver ativo, corpus produtivo de IA respeita AI READY e revalidação WP de scope/status/capability.

Falha de provider -> retrieval sem síntese, não outage do core.

## 7. Vetor/Semantic P3

Não aprovados para primeiro runtime.

Condições de reabertura:

- B-001 fechado;
- lexical baseline medido;
- Golden com lacunas semânticas representativas;
- hipótese/critério de sucesso fixados antes do experimento;
- chunking/fingerprint/modelo/dimensão explicitados;
- custo/latência/storage/fallback medidos.

Hybrid é hipótese preferida em relação a vector-only. Lexical permanece fallback.

## 8. Provider

### Microsoft Foundry

**Provider preferencial candidato**, não domínio.

- provider seam mínimo nasce com primeiro caso real;
- WordPress HTTP API quando adequada;
- model/deployment/prompt/config versionados;
- errors/timeout/quota explícitos;
- secrets não logados;
- failover não silencioso.

### Foundry Agent File Search

**DESCARTAR como Search/RAG core.** Pode reaparecer somente como projection de agente específico, alimentada por corpus canônico extraído, com lineage/hash/freshness/custo e G-140H.

### MariaDB Vector / Azure AI Search / outros

Nenhum foi escolhido. A futura SPEC compara capabilities reais e custo/operabilidade antes de selecionar storage vetorial.

## 9. G-140 — gates IA/Vetor

Canônico em `matriz-ia-vetor.md`:

- G-140A independência/degradação;
- G-140B provider/rastreabilidade/data egress;
- G-140C human-in-the-loop;
- G-140D custo/budget/NO_CHANGE;
- G-140E embeddings/semantic/hybrid;
- G-140F RAG/síntese;
- G-140G agentes/tools;
- G-140H provider-managed knowledge/File Search.

## 10. Compatibilidade

Nenhuma decisão T058 cria compatibilidade externa. Regras T054/T055 permanecem:

- adapter somente com consumidor comprovado;
- observabilidade e rollback;
- gate de remoção;
- dual-write permanente proibido;
- shortcodes históricos ainda em B-003/preflight.

## 11. First-runtime boundary após T058

O primeiro runtime **não precisa de IA externa**.

Capacidades que podem compor o primeiro conjunto de Specs após T097:

- Core/Settings/Security/DS;
- Summary/Review/Classificação por vertical slices;
- Content Extractor;
- Search lexical/projection quando B-001 e gates correspondentes forem atacados.

IA P1 só entra em SPEC própria quando os owners/handlers que ela pretende auxiliar estiverem estáveis.

## 12. Estado de T059

Ainda precisa fechar, em uma visão única:

- cada capacidade e owner;
- first runtime/posterior/postergado;
- storage/primitive;
- gates/blockers;
- dados de cutover;
- fallback;
- decisão final de paridade.

**T059 permanece aberta. Nenhum runtime foi autorizado.**

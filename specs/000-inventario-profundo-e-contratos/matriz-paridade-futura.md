# Matriz de Paridade Futura FINAL — SPEC-000 — T059

> Estado: **T059 concluída documentalmente**.  
> Baseline de entrada: `main @ c79bf04ebc3587721464c39a58d9c328108a2482`.  
> Esta matriz consolida T050–T058 e é a visão executiva de entrada para T090–T097. Ela não cria runtime, DDL, taxonomy, migration, provider, vetor ou integração externa.

## 1. Regra central

A paridade futura não significa reproduzir plugins antigos. Significa preservar o **comportamento necessário**, com owner único, primitive mínima, gate verificável, rollback/fallback e decisão explícita sobre o que morre.

Princípios finais:

1. WordPress/Elementor são a fonte editorial absoluta.
2. Um conceito canônico possui um owner lógico.
3. Projection/index/cache/vector são reconstruíveis e nunca viram fonte da verdade.
4. Content Extraction único alimenta Search, qualidade e IA.
5. Persistência confirmada precede evento.
6. Dual-write permanente é proibido.
7. Compatibilidade é temporária e possui gate de remoção.
8. Search lexical funciona sem IA/vetor.
9. IA sugere; humano decide; owner persiste.
10. Retrieval precede síntese.
11. Capacidade POSTERGADA implementada sem reabertura formal é regressão arquitetural.
12. T059 fecha arquitetura documental; T097 continua sendo o único gate que pode autorizar SPEC-001.

## 2. Classes temporais finais

- **PRIMEIRO_RUNTIME** — elegível para a primeira onda de vertical slices após autorização; não significa implementar tudo na mesma SPEC.
- **POSTERIOR** — capacidade aprovada, mas deve nascer depois das dependências/gates indicados.
- **POSTERGADO** — não comprou complexidade; reabrir somente com evidência nova.
- **COMPAT_CUTOVER** — existe apenas se coexistência/consumidor real for comprovado.
- **DESCARTADO** — não transportar para o baseline futuro.

## 3. Matriz mestre de paridade

| Capacidade/conceito | Owner lógico | Primitive/storage final | Momento | Gates/blockers | Fallback/degradação | Cutover/dado preservado | Decisão final |
|---|---|---|---|---|---|---|---|
| `post_title`, `post_content`, `_elementor_data`, publicação | Editorial WP/Elementor | `WP_Post` + Elementor | PRIMEIRO_RUNTIME | G-001 | WordPress nativo | preservar integralmente; zero write derivado | MANTER WP |
| Content Extractor | Content Extraction | lógica de domínio sobre fontes WP; cache WP apenas se medido | PRIMEIRO_RUNTIME | G-010 / B-001 para Search/RAG produtivos | fallback seguro `post_content`; estado parcial/degradado explícito | substitui parsers concorrentes; não migra cache | REDESENHAR/MANTER contrato |
| Summary narrativo `objective/escalation/important` | Resumo Executivo | Metadata API | PRIMEIRO_RUNTIME | G-020 / B-006 | falha não produz sucesso falso; valores anteriores permanecem | preservar `_bdc_es_objective/_escalation/_important` | MANTER WP |
| audiência | Classificação | Taxonomy API preferida | PRIMEIRO_RUNTIME por slice; CUTOVER sob B-002 | G-030 / B-002 | leitura legacy/adaptador somente se necessário | preservar `_bdc_es_target_audience` + `_kb2ops_target_audience`; um conceito canônico | MANTER WP / unificar semântica |
| tipo de conhecimento | Classificação | Taxonomy API preferida | PRIMEIRO_RUNTIME por slice | G-030 / B-002 no cutover | valor legado permanece até migração validada | preservar `_kb2ops_knowledge_type` | MANTER WP |
| serviço | Classificação | Taxonomy API preferida | PRIMEIRO_RUNTIME por slice | G-030 / B-002 | não fundir com `affected_service` | preservar `_kb2ops_service` | MANTER WP |
| tecnologias | Classificação | Taxonomy API preferida | PRIMEIRO_RUNTIME por slice | G-030 / B-002 | não fundir com `systems_involved` | preservar `_kb2ops_technologies` | MANTER WP |
| equipe responsável | Classificação | Metadata **ou** Taxonomy; profiling decide | POSTERIOR/CUTOVER | G-030 / B-002 / D-005 | legacy read se necessário | preservar `_bdc_es_responsible_team` | AINDA NÃO SABEMOS entre primitives WP |
| item de catálogo | Classificação | Metadata **ou** Taxonomy; profiling decide | POSTERIOR/CUTOVER | G-030 / B-002 | legacy read se necessário | preservar `_bdc_es_catalog_item` | AINDA NÃO SABEMOS entre primitives WP |
| serviço afetado | Classificação | Metadata **ou** Taxonomy; profiling decide | POSTERIOR/CUTOVER | G-030 / B-002 | manter distinto de serviço | preservar `_bdc_es_affected_service` | AINDA NÃO SABEMOS entre primitives WP |
| sistemas envolvidos | Classificação | Metadata **ou** Taxonomy; profiling decide | POSTERIOR/CUTOVER | G-030 / B-002 | manter distinto de tecnologias | preservar `_bdc_es_systems_involved` | AINDA NÃO SABEMOS entre primitives WP |
| keywords/versions | Classificação | Metadata API | PRIMEIRO_RUNTIME por slice | G-030 | ausência não quebra domínio | preservar `_kb2ops_keywords/_versions` | MANTER WP |
| review state/notes/reviewer/time/include_ai | Revisão/Governança | Metadata + WP Users | PRIMEIRO_RUNTIME | G-040 | falha mantém estado anterior | preservar metas KB2Ops válidas | MANTER WP |
| histórico bounded de revisão | Revisão/Governança | Metadata estruturada; Revisions quando requisito justificar | PRIMEIRO_RUNTIME inicial | G-040 | sem histórico transversal próprio | preservar `_kb2ops_review_history` válido | MANTER WP; mecanismo detalhado em SPEC |
| AI READY | Revisão/Governança | cálculo derivado | PRIMEIRO_RUNTIME quando review existir | G-040 | `not_ready` é estado válido | regra baseline `publish + approved + 8/8 + include_ai` | MANTER derivado |
| Core settings/runtime version | Core Configuration | Options/Settings API | PRIMEIRO_RUNTIME | G-130 | defaults seguros | não copiar múltiplos owners de settings | SUBSTITUIR/MANTER WP |
| capabilities/nonces/admin mutations | Core/Security | Roles/Capabilities + Nonces + `admin-post` | PRIMEIRO_RUNTIME | G-070/G-130 | fail-closed | não portar endpoints sem consumidor | MANTER WP |
| Design System/UI administrativa | UI | wp-admin + DS próprio, server-rendered baseline | PRIMEIRO_RUNTIME | G-110 | funcional sem JS onde baseline exigir | não copiar CSS/menu fragmentado | REDESENHAR |
| Site Health/diagnóstico | Operations | Site Health + checks próprios mínimos | PRIMEIRO_RUNTIME | G-130 | diagnóstico read-only/degradado | descartar dashboards técnicos duplicados | SUBSTITUIR POR WORDPRESS |
| Search Knowledge: vocabulary/bindings/rules | Search Knowledge | `WP_Post` interno + Metadata/Revisions inicialmente | POSTERIOR | G-060 + segurança/Apply | Search lexical básica pode operar sem regras adicionais | preservar dados ASI manuais reais quando existirem | MANTER comportamento / redesenhar storage |
| Golden Queries/evidência | Search Quality | `WP_Post` interno + Metadata/Revisions + evidência bounded | POSTERIOR, **antes de release de Search** | Golden T055 | `NOT_CONFIGURED/NOT_RUN/stale` nunca PASS | preservar Golden ASI reais quando houver | MANTER contrato |
| Search Retrieval Projection `post|item` | Search Indexing | **uma única família/store próprio reconstruível** | POSTERIOR | G-010/G-050/G-060/Golden/G-070/G-120/G-130; B-001 | native WP/bounded degraded; rebuild | não migrar índice como canônico; reconstruir dos owners | INFRA PRÓPRIA MÍNIMA APROVADA |
| QueryContext/normalização/ranking explicável | Search | lógica determinística versionada | POSTERIOR | G-060 + Golden | lexical degradado | não copiar equivalências hardcoded sem curadoria | MANTER/REDESENHAR |
| item identity | Search Indexing | mesma projection `post|item` | POSTERIOR | G-050 | item pode ficar não navegável | reconstruir; preservar bindings válidos por reconciliação | MANTER conceito |
| deep-link/anchors públicos | Search Indexing/Public | estratégia ainda aberta | POSTERIOR | B-005 + browser/E2E | fail-closed: item sem destino não vira link válido | não escrever editorial para “consertar” link | REDESENHAR |
| native WordPress search | Search fallback | `WP_Query` search | PRIMEIRO_RUNTIME como fallback possível | G-060/G-070 | próprio fallback | sem migração | MANTER fallback, não paridade final |
| Search Analytics detalhado | Analytics/Search Intelligence | nenhum store no baseline | POSTERGADO | G-080 / B-004 | Search funciona sem Analytics | telemetria histórica não migra automaticamente | POSTERGAR |
| query text logging | Analytics | proibido por default | POSTERGADO | B-004 | zero logging | não importar queries históricas sem política | NÃO BASELINE |
| durable queue | Search Operations | nenhum store no baseline; WP-Cron só trigger | POSTERGADO | G-090 / B-007 | sync/bounded + rebuild manual/batched | não usar Options/Transients como fila | POSTERGAR |
| audit genérico/quality_daily/rollups | Governança/Analytics | não criar | POSTERGADO/DESCARTADO | requisito + benchmark futuro | logs/diagnósticos mínimos | não migrar por inércia | DESCARTAR baseline |
| pré-análise determinística | Review/Qualidade | cálculo local sobre extractor/domínio | PRIMEIRO_RUNTIME | gates do domínio | sempre disponível sem provider | substituir IA onde regra suficiente | MANTER P0 |
| Assistente de Classificação | AI Assist + Classificação owner | provider seam futuro; nenhuma persistência canônica de IA | POSTERIOR P1 | G-140A/B/C/D + G-030 | sem IA, classificação manual | nenhum write direto; humano aplica | APROVADO OPCIONAL |
| Assistente de Summary | AI Assist + Summary owner | provider seam futuro | POSTERIOR P1 | G-140A/B/C/D + G-020 | sem IA, Summary manual | humano aplica via owner | APROVADO OPCIONAL |
| RAG/síntese | AI Assist/Search | provider externo sobre retrieval confiável | POSTERIOR P2 | G-140A/B/D/F + Search gates | retrieval/evidências sem síntese | nenhum corpus paralelo obrigatório | APROVADO OPCIONAL POSTERIOR |
| chunking adicional | Search Indexing/AI | derivado do extractor/item contract | POSTERGADO até necessidade | G-140D/E | usar `post|item` estruturais | reconstruível | POSTERGAR |
| embeddings/vector store | Search Indexing/Semantic projection | **nenhuma tecnologia escolhida** | POSTERGADO P3 | G-140E + B-001 + Golden + benchmark | lexical | nenhum embedding histórico é canônico | POSTERGAR |
| semantic/hybrid retrieval | Search | nenhuma engine escolhida | POSTERGADO P3 | G-140E | lexical | só reabrir com ganho pré-definido | POSTERGAR |
| model rerank | Search | top-K bounded se futuro | POSTERGADO P3 | G-140E | ranking determinístico fail-open | nenhuma dependência no baseline | POSTERGAR |
| Microsoft Foundry | AI provider adapter | WordPress HTTP API quando adequada; seam mínimo | POSTERIOR, junto ao primeiro caso P1/P2 | G-140B/D | IA desabilitada/degradada | provider não é owner | CANDIDATO PREFERENCIAL, não obrigatório |
| Foundry Agent File Search | projection específica eventual | store gerenciado externo | POSTERGADO P4 | G-140H | Search/RAG canônico local continua | nunca segunda fonte de verdade | DESCARTAR COMO CORE |
| agentes/tools | AI Assist | nenhum runtime no baseline | POSTERGADO P4 | G-140G | fluxos normais manuais/determinísticos | read-only default; mutação humana | POSTERGAR/NEGAR baseline |
| Word Cloud | Search/Analytics projection | nenhum pipeline próprio | POSTERGADO | produto + B-003 se shortcode usado | ausência não quebra core | `[bdc_word_cloud]` em preflight | POSTERGAR |
| GAC | sistema externo/adaptador | nenhum core próprio | COMPAT_CUTOVER somente se consumidor real | B-003/D-005 | Classificação local funciona sem GAC | preservar apenas requisito comprovado | FORA DO CORE |
| REST/SPA | superfície técnica | não criar sem consumidor | POSTERGADO/DESCARTADO baseline | G-070/G-110 | server-rendered/admin-post | não portar por modernidade | NÃO BASELINE |

## 4. Dados que não podem ser perdidos no cutover

Preservar até decisão/migração comprovada:

1. `WP_Post`, Elementor e taxonomias editoriais existentes.
2. Oito valores históricos GRE por post, respeitando ownership futuro.
3. Review state, notes, reviewer/time, `include_ai` e histórico válido KB2Ops.
4. Classificações KB2Ops efetivamente utilizadas.
5. Search Knowledge ASI manual: vocabulary, bindings e relevance rules, **quando houver dados reais**.
6. Golden Queries/expectativas ASI reais e úteis.
7. Configurações estritamente necessárias para coexistência até o cutover.

Não recebem preservação automática como dado canônico:

- search index/item index antigos;
- caches;
- queue antiga;
- `quality_daily`;
- telemetria histórica;
- migrations registry histórico;
- embeddings/vectors;
- snapshots derivados reconstruíveis.

Telemetria histórica só migra sob política explícita B-004.

## 5. Compatibilidade e remoção

Shortcodes ainda sujeitos a B-003/preflight:

- `[asi_search_form]`;
- `[bdc_word_cloud]`;
- `[bdc_resumo_executivo]`;
- `[kb2ops_search]`;
- `[kb2ops_portal]`.

Nenhum alias está aprovado automaticamente.

Qualquer adapter/dual-read exige:

`consumidor comprovado -> owner canônico -> modo limitado -> observabilidade -> rollback -> teste de equivalência -> gate de remoção`.

Dual-write permanente continua proibido.

## 6. Blockers por momento — leitura correta para T095/T097

Blockers são contextuais; não impedem uma SPEC que não use a capacidade correspondente.

| Blocker | Bloqueia | Não bloqueia automaticamente |
|---|---|---|
| B-001 extractor representativo | Search/RAG/embedding produtivos | Core/Summary/Review sem Search |
| B-002 profiling classificação | migração/cutover e decisões ainda abertas de classificação | Summary/Review e classificações já decididas sem cutover |
| B-003 preflight consumidores | retirada de plugins/aliases/adapters | runtime novo coexistente |
| B-004 Analytics/query policy | Analytics detalhado/query logging | Search lexical sem Analytics |
| B-005 anchors/deep-link | links públicos de item | post-level Search e item não navegável |
| B-006 falha multi-campo | write composto definitivo de Summary/flows equivalentes | leituras e slices sem write composto |
| B-007 stale/async | durable queue/async indexing | sync/bounded/manual rebuild |

**Consequência:** T095 deve fechar ou formalmente postergar cada blocker **por slice**. T097 pode autorizar SPEC-001 com blockers contextuais ainda abertos, desde que a SPEC-001 autorizada não dependa deles e declare seu gate de entrada.

## 7. Fallback e rollback sistêmicos

- **Editorial/domínio:** falha preserva estado canônico anterior; nunca “corrigir” conteúdo automaticamente.
- **Search projection:** `degraded`, native search/fallback bounded e rebuild; WordPress permanece autoridade de exposição.
- **IA P1:** provider falhou -> nenhuma sugestão/nenhum write.
- **RAG P2:** provider falhou -> retrieval + evidências sem síntese.
- **Semantic P3:** falhou -> lexical.
- **Queue inexistente:** processamento sync/bounded/manual; não improvisar store.
- **Compatibilidade:** plugins antigos podem permanecer/ser reativados em homologação até paridade/cutover.
- **Dados derivados:** apagar/reconstruir projection não exige restaurar editorial.

## 8. Sequência recomendada após T097, se autorizada

A ordem abaixo é uma **sequência de risco**, não autorização de implementação:

1. Core/Settings/Security/Design System mínimo.
2. Summary + contrato de persistência/write seguro.
3. Review/Governança + AI READY derivado.
4. Classificação por conceitos já resolvidos; profiling separado para unknowns/cutover.
5. Content Extractor + corpus B-001.
6. Search Quality/Golden e Search Knowledge mínimos.
7. Search lexical/projection/ranking.
8. Compatibilidade/cutover somente após B-003.
9. IA P1 em **uma** jornada estreita, quando owner correspondente estiver estável.
10. RAG P2 somente após retrieval confiável.
11. P3/P4 apenas por reabertura formal e evidência.

Nenhuma etapa exige big-bang.

## 9. Unknowns restantes — não confundir com falha de T059

Permanecem deliberadamente abertos para T095 ou SPECs concretas:

- quatro escolhas Metadata versus Taxonomy dependentes de B-002;
- schema/nome/índices físicos da Search Projection;
- NFRs reais de extractor/index/Search/rebuild;
- política Analytics/query text;
- necessidade real de durable queue;
- estratégia final de anchors/deep-link;
- preflight real de consumidores/shortcodes;
- meta revisions finais;
- primeiro caso IA P1 (Classificação **ou** Summary);
- provider/deployment/model/prompt reais;
- política institucional de secrets/data egress;
- necessidade de chunking/embedding/vector e tecnologia correspondente;
- layout/runtime físico final do plugin.

Esses unknowns possuem **dono, momento e gate**; portanto não exigem adivinhação estrutural antes de T097.

## 10. Critério T059

T059 está fechada porque:

- todas as famílias inventariadas possuem decisão temporal;
- owner lógico está definido ou o unknown está limitado a primitive WordPress;
- storage aprovado/postergado está explícito;
- blockers/gates estão associados à capacidade correta;
- dados de cutover que não podem ser perdidos estão listados;
- fallback/degradação estão definidos;
- compatibilidade não escolhe arquitetura permanente;
- IA/vetor permanecem opcionais/degradáveis;
- não há runtime criado.

## 11. Próxima fase

**T090 — Revisão do Arquiteto WordPress.**

T090 deve confrontar esta matriz contra Constituição, Manifesto, T056 e primitives do Core, procurando principalmente:

- infraestrutura própria que WordPress ainda possa eliminar;
- misuse de Metadata/Taxonomy/Options/Revisions/Site Health/WP-Cron;
- endpoint/SPA/REST desnecessário;
- risco de transformar projection em fonte da verdade;
- divergência entre first-runtime e princípio de negação.

Depois: T091 Simplicidade -> T092 Segurança -> T093 QA/Regressão -> T094 Produto/Conhecimento -> T095 blockers/unknowns -> T096 relatório final -> T097 GO/NO-GO da SPEC-001.

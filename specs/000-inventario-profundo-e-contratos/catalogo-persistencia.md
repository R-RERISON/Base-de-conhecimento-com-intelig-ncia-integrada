# Catálogo Unificado de Persistência — SPEC-000 — T050

> Estado: **T050 concluída documentalmente**.  
> Baselines cruzadas: ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`, KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este documento consolida persistência por **conceito/owner do produto futuro**, preservando rastreabilidade histórica. Ele **não** autoriza schema, taxonomias, tabelas, migração ou runtime. A escolha física final pertence a T056/T057.

## 1. Regras de persistência do produto futuro

1. **Um conceito canônico possui um único owner lógico.**
2. Chaves históricas (`_bdc_es_*`, `_kb2ops_*`, `asi_*`) são evidência/origem de compatibilidade, não arquitetura futura por si só.
3. Projections, índices, caches, chunks, embeddings e agregados nunca são fonte da verdade.
4. Dados derivados devem ser reconstruíveis sempre que tecnicamente possível.
5. Compatibilidade pode usar adapter/dual-read temporário, mas **dual-write permanente é proibido**.
6. Todo adapter de cutover precisa de owner canônico, consumidor comprovado, condição de entrada e gate de remoção.
7. Query text, identidade, IP/UA e demais telemetrias não ganham direito automático de retenção.
8. Nenhuma tabela própria nasce antes de T057 justificar volume, consulta, durabilidade ou performance.
9. Metadata/taxonomy/options permanecem primitives candidatas; a decisão campo a campo pertence a T056.
10. Uninstall/purge é não destrutivo por default; remoção definitiva é deliberada e auditável.

## 2. Classes de dados

| Classe | Definição | Exemplos | Regra |
|---|---|---|---|
| **Canônico editorial** | conteúdo oficial do artigo | `post_title`, `post_content`, `_elementor_data`, status | WordPress/Elementor é owner absoluto |
| **Canônico de domínio** | decisão humana/metadata do produto | resumo, classificação, review state, vocabulary | um owner; writer controlado |
| **Projection derivada** | reconstruível a partir de canônicos | texto extraído, índice, item index, AI READY | pode ficar stale; nunca reescreve fonte |
| **Observacional** | fato de uso/comportamento | search event, interaction, outcome | minimizar e reter por política |
| **Operacional** | estado de execução/lifecycle | queue, migration checkpoint | não é dado de negócio |
| **Configuração** | parâmetro do produto/runtime | settings, versão instalada | Options/Settings API primeiro |
| **Compatibilidade** | representação antiga lida no cutover | metas/shortcodes/stores legados | temporária, com gate de remoção |

## 3. Editorial WordPress / Elementor

| Conceito | Origem histórica | Owner | Writers futuros | Readers futuros | Natureza | Reconstruível? | Direção |
|---|---|---|---|---|---|---:|---|
| título | `post_title` | Editorial WP | autores/editores | Resumo, Search, UI, IA | canônico editorial | NÃO | MANTER nativo |
| corpo WordPress | `post_content` | Editorial WP | autores/editores | Content Extraction fallback | canônico editorial | NÃO | MANTER nativo |
| estrutura Elementor | `_elementor_data` | Elementor | Elementor | Content Extraction | canônico editorial | NÃO | read-only pelo plugin |
| status/publicação | `WP_Post` | Editorial WP | fluxo WP | Review, Search scope, IA | canônico editorial | NÃO | MANTER nativo |
| categorias/tags existentes | taxonomias WP | Editorial WP | fluxo editorial atual | Search/Classificação como sinal | canônico editorial | NÃO | consumir; não sequestrar ownership |

**Invariante:** nenhum downstream de Search, IA, Analytics ou Revisão escreve `_elementor_data` ou reescreve `post_content` silenciosamente.

## 4. Resumo Executivo

### 4.1 Dados narrativos canônicos

| Conceito | Chave histórica | Owner | Writers | Readers | Natureza | Primitive preliminar |
|---|---|---|---|---|---|---|
| objetivo | `_bdc_es_objective` | Resumo Executivo | fluxo autenticado do owner | UI, Search, IA | canônico de domínio | postmeta é baseline forte; T056 confirma |
| escalonamento | `_bdc_es_escalation` | Resumo Executivo | owner | UI, Search, IA | canônico de domínio | postmeta forte; T056 |
| informação importante | `_bdc_es_important` | Resumo Executivo | owner | UI, Search, IA | canônico de domínio | postmeta forte; T056 |

### 4.2 Campos históricos do GRE cujo owner futuro é Classificação

| Conceito | Chave histórica | Owner futuro | Compatibilidade |
|---|---|---|---|
| equipe responsável | `_bdc_es_responsible_team` | Classificação de Conhecimento | preservar leitura até cutover |
| item de catálogo | `_bdc_es_catalog_item` | Classificação de Conhecimento | preservar leitura até cutover |
| serviço afetado | `_bdc_es_affected_service` | Classificação de Conhecimento | conceito distinto de `service` até profiling |
| sistemas envolvidos | `_bdc_es_systems_involved` | Classificação de Conhecimento | conceito distinto de `technologies` até profiling |
| audiência | `_bdc_es_target_audience` | Classificação de Conhecimento | mesmo conceito lógico da audiência KB2Ops |

**Decisão:** o workspace de Resumo pode exibir/editar classificações por conveniência de UX, mas os writes passam pelo owner Classificação. O Resumo não mantém cópia canônica paralela.

## 5. Classificação de Conhecimento

| Conceito | Fontes históricas | Owner | Natureza | Leitores | Estado da primitive |
|---|---|---|---|---|---|
| audiência | `_bdc_es_target_audience`, `_kb2ops_target_audience` | Classificação | canônico | Resumo, Review, Search, Insights, IA | **um conceito**; taxonomy/meta T056 |
| equipe responsável | `_bdc_es_responsible_team` | Classificação | canônico | UI, Search, Insights | T056 |
| item de catálogo | `_bdc_es_catalog_item` | Classificação | canônico | UI, Search | T056 |
| serviço | `_kb2ops_service` | Classificação | canônico | UI, Search, Insights | manter distinto de serviço afetado; T056 |
| serviço afetado | `_bdc_es_affected_service` | Classificação | canônico | Resumo, Search, IA | profiling antes de qualquer merge; T056 |
| tecnologias | `_kb2ops_technologies` | Classificação | canônico | Search, Review, Insights | manter distinto de sistemas; T056 |
| sistemas envolvidos | `_bdc_es_systems_involved` | Classificação | canônico | Resumo, Search, IA | profiling; T056 |
| tipo de conhecimento | `_kb2ops_knowledge_type` | Classificação | canônico | Review, Search, Insights | forte candidato a taxonomy; T056 decide |
| keywords | `_kb2ops_keywords` | Classificação | canônico/manual | Search, Review | primitive/cardinalidade T056 |
| versões | `_kb2ops_versions` | Classificação | canônico/manual | Search, Review | primitive/cardinalidade T056 |

### Política de cutover classificatório

- nenhum campo é mesclado apenas por nome parecido;
- audiência possui equivalência semântica suficiente para um owner único, mas migração física ainda exige profiling;
- `service`/`affected_service` e `technologies`/`systems_involved` permanecem separados até análise dos valores/cardinalidade/uso;
- categorias/tags editoriais existentes não são convertidas silenciosamente em classificações sistêmicas.

## 6. Revisão e Governança

| Conceito | Chave histórica | Owner | Natureza | Direção |
|---|---|---|---|---|
| review state | `_kb2ops_review_state` | Revisão/Governança | canônico workflow | MANTER semântica |
| notas | `_kb2ops_review_notes` | Revisão/Governança | canônico local | Metadata API forte |
| revisado em | `_kb2ops_reviewed_at` | Revisão/Governança | evidência canônica | MANTER |
| revisado por | `_kb2ops_reviewed_by` | Revisão/Governança | referência WP User | MANTER |
| incluir em IA | `_kb2ops_include_ai` | Revisão/Governança | decisão humana | MANTER |
| histórico | `_kb2ops_review_history` | Revisão/Governança | histórico | mecanismo/revisions T056/T095 |

**Regra:** `validar -> persistir -> reler/confirmar -> emitir evento`. O evento nunca antecipa a confirmação do estado final.

## 7. Estados e métricas derivadas

| Projection | Fontes | Owner da regra | Persistir? |
|---|---|---|---|
| completude 0/8–8/8 | contrato do Resumo | Resumo Executivo | derivar; cache só com benchmark |
| AI READY | publish + approved + 8/8 + include_ai | Revisão/Governança | derivar/projetar; não duplicar canônico |
| checklist/scores/suggestions | extractor + metadata | Qualidade de Conteúdo/Revisão | derivar |
| excerpt/quick steps/facts | Content Extractor | Content Extraction | derivar/cache reconstruível |
| dashboard coverage | canônicos | domain owners + Insights leitor | não criar owner paralelo |

## 8. Content Extraction

| Projection | Fonte | Owner | Reconstruível | Uso |
|---|---|---|---:|---|
| HTML/texto derivado | WP/Elementor | Content Extraction | SIM | Search, Review, IA |
| estrutura/headings/tabelas | WP/Elementor | Content Extraction | SIM | items/deep-links/quality |
| hash de conteúdo extraído | representação versionada | Content Extraction | SIM | NO_CHANGE/invalidation futura |

**Regra:** downstream não relê `_elementor_data`/`post_content` com parser próprio. O risco de extração parcial de custom widgets precisa de regressão antes de index/RAG final.

## 9. Search Knowledge — dados canônicos da recuperação

| Conceito ASI | Owner | Natureza | Writer | Readers | Primitive |
|---|---|---|---|---|---|
| vocabulary | Search Knowledge | canônico/manual | curador de Search | QueryContext/ranker | T056/T057 |
| term bindings | Search Knowledge | canônico/manual | curador de Search | retrieval/ranker | T056/T057 |
| relevance rules | Search Knowledge | canônico/manual | curador de Search | ranker/simulation | T056/T057 |

Esses dados não são classificação editorial. Apply de Search Knowledge é workflow próprio e não é efeito colateral de aprovação do artigo.

## 10. Search Indexing — projections reconstruíveis

| Projection histórica | Owner | Fonte futura | Reconstruível | Estado |
|---|---|---|---:|---|
| `search_index` | Search Indexing | extractor + metadata/classificação | SIM | comportamento necessário; schema T057 |
| `search_items` | Search Indexing | extractor/estrutura | SIM | necessário para trecho/deep-link; schema T057 |
| item identity/deep-link | Search Indexing | estrutura versionada | SIM | contrato permanece; estratégia final aberta |
| cache de query | Search Indexing | query + versions | SIM | usar Object Cache/transient primeiro; T056 |
| Word Cloud snapshot | Search/Analytics projection | índice + facts | SIM | opcional; não ter pipeline paralelo |
| embeddings/vectors | semantic projection futura | chunks extraídos + model version | SIM | **não autorizados**; T058/Specs futuras |

**Regra:** projection stale/falha não altera fonte canônica. Rebuild nunca edita post/Elementor.

## 11. Search Quality

| Dado | Origem | Owner | Natureza | Estado |
|---|---|---|---|---|
| Golden Query | ASI | Search Quality | canônico QA/governança | MANTER |
| expected target/rank/blocking | ASI | Search Quality | canônico QA | MANTER |
| evidência da última execução | ASI | Search Quality | derivado/versionado | MANTER semântica |
| quality diagnostics | ASI | Search Quality | derivado | preferir Site Health + relatório mínimo |
| `quality_daily` | ASI | Analytics projection | derivado | DESCARTAR inicialmente |

Storage final de Golden permanece T056/T057; ausência de suíte Golden nunca é PASS.

## 12. Analytics / Search Intelligence

| Representação histórica | Owner futuro | Natureza | Direção |
|---|---|---|---|
| ASI `search_events` | Analytics/Search Intelligence | observacional | preservar somente facts mínimos necessários |
| ASI `search_interactions` | Analytics/Search Intelligence | observacional | condicional; HMAC/idempotência se existir |
| outcomes/journey | Analytics/Search Intelligence | observacional | preservar semântica se analytics habilitado |
| `kb2ops_search_analytics` option | Analytics/Search Intelligence | observacional legado | NÃO manter como store paralelo |
| `_kb2ops_view_count` | Analytics/Search Intelligence | contador legado | não usar como fonte principal futura |
| Search Intelligence reports | Analytics/Insights | derivado | perguntas permanecem; storage não |

### Política preliminar

- telemetria é non-fatal para Search;
- modo mínimo não persiste identidade/session/IP/UA;
- query text exige decisão explícita de minimização/retenção em T057/T095;
- agregados só são materializados após benchmark;
- facts observacionais não se tornam metadata canônica do post.

## 13. Governança/Audit

`audit_log` ASI demonstra necessidade potencial de trilha para mutações sensíveis (Apply de Search Knowledge, purge, operações). O futuro audit deve registrar **somente fatos necessários**, com retenção e acesso definidos. Não criar log genérico duplicando logs WordPress/servidor.

Primitive/storage: T056/T057.

## 14. Configuração e cache

### Configuração

- settings futuros pertencem a **Core Configuration**, organizados por domínio;
- Options/Settings API é primitive inicial;
- `asi4_settings` e `kb2ops_options` não sobrevivem como dois owners;
- versões técnicas podem usar option pequena se upgrade exigir;
- secrets antigos não são automaticamente importados como contrato futuro.

### Cache

- Object Cache/transients primeiro;
- cache deve ser efêmero e reconstruível;
- invalidação preferencialmente por version token/namespace;
- cache não carrega identidade de tracking compartilhável entre usuários.

## 15. Operações / Lifecycle

| Estado | Origem histórica | Natureza | Futuro |
|---|---|---|---|
| index queue | ASI | operacional | somente se T057 provar durabilidade/workload |
| migration checkpoints | ASI/KB2Ops | transitório | apenas durante upgrade/cutover real |
| post-install orchestrator state | ASI | histórico/operacional | não copiar; mínimo necessário |
| purge/migration evidence | KB2Ops | evidência operacional | limitado e explícito |
| runtime version | KB2Ops | configuração/lifecycle | option pequena se necessária |

Migration/adapter nunca vira owner do dado migrado.

## 16. Compatibilidade e política de coexistência

### 16.1 Regra geral

O produto futuro pode precisar ler dados históricos antes do cutover completo. A compatibilidade deve obedecer:

`origem antiga -> adapter read-only/dual-read limitado -> owner canônico -> gate de remoção`.

### 16.2 Proibições

- dual-write indefinido entre `_bdc_es_*` e `_kb2ops_*`;
- bridge interna permanente duplicando mapa de chaves;
- migration/reconciler permanente por medo do legado;
- tornar uma tabela ASI fonte da verdade editorial;
- apagar dados antigos automaticamente na ativação.

### 16.3 Dados que não podem ser perdidos no cutover

- `WP_Post`/Elementor e taxonomias editoriais;
- oito valores históricos do GRE;
- decisões humanas KB2Ops de review/include AI/notas/revisor/histórico quando válidas;
- classificações KB2Ops utilizadas;
- vocabulary/bindings/rules/Golden manuais do ASI se houver dados reais instalados;
- telemetria histórica somente se política/requisito determinar retenção;
- evidências necessárias de migração/purge/audit.

A existência de uma estrutura não prova que há dados reais nela. Preflight de ambiente pertence ao plano de migração futuro.

## 17. Matriz resumida de primitive — ainda não é T056/T057

| Necessidade | Candidato mais simples | Estado |
|---|---|---|
| resumo narrativo local | postmeta | forte evidência; confirmar T056 |
| revisão local | postmeta + user refs | forte evidência; histórico aberto |
| classificação reutilizável | taxonomy ou postmeta conforme semântica/cardinalidade | **T056** |
| settings | Options/Settings API | forte evidência |
| cache | Object Cache/transient | forte evidência |
| Search Knowledge | WP primitive ou store próprio mínimo | **T056/T057** |
| índice lexical/items | projection própria provável | **T057** |
| Analytics facts | store mínimo se perguntas/volume exigirem | **T057** |
| queue | WP-Cron trigger ou queue durável | **T057** |
| Golden | WP primitive ou store próprio mínimo | **T056/T057** |
| vectors/chunks | nenhuma decisão | **T058/Specs futuras** |

## 18. Decisões T050

T050 consolida as seguintes decisões documentais:

1. persistência futura é organizada por owner, não por plugin histórico;
2. projections ficam formalmente separadas de canônicos;
3. audiência possui um único owner e não terá duas fontes canônicas no estado final;
4. bridges e dual-read são mecanismos de compatibilidade temporários;
5. dual-write permanente é proibido;
6. ASI `quality_daily`, stores históricos de migration e analytics option/view count KB2Ops não têm direito automático de nascer;
7. não existe justificativa para tabela de Resumo Executivo;
8. Search Index/Items permanecem capacidades necessárias, mas schema próprio só pode ser aprovado por T057;
9. telemetry/query retention continua bloqueada até política explícita;
10. nenhuma taxonomy/tabela/schema foi criada nesta tarefa.

## 19. Itens explicitamente adiados

- taxonomy versus postmeta campo a campo;
- nomes/chaves finais;
- cardinalidade e profiling;
- histórico/revisions final;
- schema Search Index/Items;
- storage vocabulary/bindings/rules/Golden;
- necessidade concreta de queue;
- schema/retention de Analytics;
- coexistência/migração detalhada;
- chunks/vectors/embeddings.

**Próximo uso deste catálogo:** T056 aplica WordPress-first campo/capacidade por campo/capacidade; T057 justifica somente a infraestrutura própria restante.
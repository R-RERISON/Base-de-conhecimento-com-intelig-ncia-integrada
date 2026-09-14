# Matriz WordPress-first — SPEC-000 — T056

> Estado: **T056 concluída documentalmente**.  
> Baseline de entrada: `main @ fe40f8a56e2efb440f748963ef58c3b69cba4470`.  
> Baselines de referência: ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`, KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este documento decide **primitives e limites**, não implementação. Não registra taxonomy/CPT/meta, não cria tabela, schema, endpoint, queue, migration, adapter ou runtime. T057 só poderá analisar a infraestrutura própria que restou desta matriz.

## 1. Regra de decisão

T056 aplica, nessa ordem, o princípio de negação:

1. definir a necessidade sem partir da implementação legada;
2. avaliar `WP_Post`/Elementor;
3. avaliar Metadata API;
4. avaliar Taxonomy API;
5. avaliar Options/Settings API;
6. avaliar Users/Roles/Capabilities;
7. avaliar Hooks/Filters/Shortcodes;
8. avaliar `admin-post`;
9. avaliar AJAX somente se houver interação live;
10. avaliar Revisions;
11. avaliar Transients/Object Cache;
12. avaliar WP-Cron como trigger;
13. avaliar WordPress HTTP API, quando houver integração externa;
14. avaliar REST somente se existir consumidor formal;
15. avaliar Site Health;
16. somente então admitir candidato a infraestrutura própria em T057.

### Estados usados

- `SUBSTITUIR_POR_WORDPRESS` — construção própria/histórica perde justificativa porque uma primitive do Core atende o contrato.
- `MANTER WP` — a primitive WordPress já é ou deve continuar sendo a solução de baseline.
- `CANDIDATO_INFRA_PROPRIA_T057` — existe limitação objetiva do Core para o comportamento requerido; T057 ainda deve provar a menor extensão possível e **não recebe uma tabela pré-escolhida**.
- `AINDA_NAO_SABEMOS` — falta evidência para escolher entre primitives ou até para confirmar que a capacidade existe; não é autorização automática para T057.

## 2. Evidência técnica do WordPress Core considerada

Além das baselines versionadas do projeto, T056 verificou as primitives do WordPress Core relevantes:

- `register_meta()` suporta tipo, cardinalidade, default, sanitização, autorização e `revisions_enabled` para post meta; suporte a revisão de meta existe desde WordPress 6.4;
- Taxonomy API é a primitive nativa para conceitos reutilizáveis/compartilhados e relações termo↔objeto;
- Options/Settings API é o mecanismo nativo para configuração global;
- `WP_Query` possui busca nativa por `post_title`, `post_excerpt` e `post_content`; ela não cobre por si só a representação textual derivada do Elementor, Resumo/Classificação e Item Knowledge;
- Site Health aceita checks próprios diretos ou assíncronos;
- Transients são cache temporário e podem desaparecer antes do prazo máximo; Object Cache não é persistente por padrão;
- WP-Cron é scheduler disparado por tráfego/request e não oferece, sozinho, semântica de lease/retry/dead-letter de uma fila durável.

Referências oficiais consultadas em 2026-09-14:

- https://developer.wordpress.org/reference/functions/register_meta/
- https://developer.wordpress.org/plugins/taxonomies/
- https://developer.wordpress.org/plugins/settings/options-api/
- https://developer.wordpress.org/reference/classes/wp_query/parse_search/
- https://developer.wordpress.org/reference/hooks/site_status_tests/
- https://developer.wordpress.org/apis/transients/
- https://developer.wordpress.org/reference/classes/wp_object_cache/
- https://developer.wordpress.org/plugins/cron/

## 3. Editorial e Content Extraction

| Necessidade/comportamento | Owner lógico | Primitive WP candidata | Avaliação / constraints | Compatibilidade/migração | Decisão | Evidência/gate restante |
|---|---|---|---|---|---|---|
| título do artigo | Editorial WP | `WP_Post.post_title` | já é canônico; duplicar em meta cria drift | preservar conteúdo atual; `_bdc_es_title` continua proibido | **MANTER WP** | regressão de ausência de título duplicado |
| corpo WordPress | Editorial WP | `WP_Post.post_content` | fonte editorial nativa, mas não representa sozinho todo documento Elementor | nenhuma reescrita derivada | **MANTER WP** | garantir read-only |
| estrutura Elementor | Editorial WP/Elementor | post meta `_elementor_data` pertencente ao Elementor | é fonte editorial absoluta quando usada; o novo plugin só lê | nenhuma migration/write derivado | **MANTER WP** | B-001 para completude de leitura, não para ownership |
| status/publicação | Editorial WP | `WP_Post.post_status` | atende publicação/visibilidade editorial | preservar fluxo WP existente | **MANTER WP** | testes de scope |
| categorias/tags editoriais existentes | Editorial WP | Taxonomy API existente | podem ser sinais de Search; não devem ser sequestradas por classificação sistêmica | zero conversão silenciosa | **MANTER WP** | profiling apenas se alguma classificação quiser mapear valores |
| representação textual/estrutural derivada | Content Extraction | `WP_Post` + Metadata API read-only + hooks/Elementor controlados | algoritmo próprio é necessário, mas **não uma persistência própria**; saída é reconstruível | substituir parsers ASI/KB2Ops duplicados | **SUBSTITUIR_POR_WORDPRESS** para fontes/persistência; extractor permanece lógica de domínio | B-001: corpus Elementor/custom widgets e diagnóstico de omissão |
| cache de extração | Content Extraction | cache por request + Transients/Object Cache quando medido | cache pode desaparecer; nunca é verdade canônica | sem migração de cache legado | **MANTER WP** | benchmark antes de cache cross-request |

## 4. Resumo Executivo

| Necessidade | Owner | Primitive | Por que atende / limites | Compatibilidade | Decisão | Gate |
|---|---|---|---|---|---|---|
| `objective` | Resumo | `register_post_meta` / Metadata API | atributo textual local ao post; leitura/escrita pontual; GRE já prova o contrato | ler `_bdc_es_objective` no cutover | **MANTER WP** | B-006 para write composto final |
| `escalation` | Resumo | Metadata API | texto local, baixa cardinalidade, sem faceta global obrigatória | preservar `_bdc_es_escalation` | **MANTER WP** | B-006 |
| `important` | Resumo | Metadata API | texto local, sem necessidade relacional comprovada | preservar `_bdc_es_important` | **MANTER WP** | B-006 |
| completude 0/8–8/8 | Resumo | cálculo sobre WP/meta; cache WP opcional | é projection barata; não precisa novo owner/store | nenhum rollup legado obrigatório | **SUBSTITUIR_POR_WORDPRESS** | benchmark antes de materializar |
| Coverage Dashboard | Resumo/Insights | `WP_Query` paginado/bounded + meta cache | pergunta de produto cabe em Core; scans `-1` são rejeitados | não portar scan GRE literal | **SUBSTITUIR_POR_WORDPRESS** | benchmark/corpus realista |
| evento de alteração | Resumo/Classificação | Plugin API action após read-after-write | Core já fornece hooks; nome final permanece aberto | bridge legado só se ASI coexistir | **MANTER WP** | teste `persistir -> confirmar -> emitir` |

**Resultado:** Resumo Executivo não entra em T057. Não existe justificativa de tabela própria para seu estado canônico.

## 5. Classificação — metadata versus taxonomy

### 5.1 Regra usada

Taxonomia **não** foi escolhida apenas porque o campo é “classificatório”. Ela só é preferida quando há evidência de vocabulário reutilizável + filtro/faceta/agrupamento/relacionamento entre posts. Metadata permanece preferida para atributo local, contextual ou sem navegação global comprovada.

### 5.2 Matriz campo a campo

| Conceito | Evidência atual | Primitive T056 | Constraints | Decisão | Gate de migração/profiling |
|---|---|---|---|---|---|
| audiência | mesmo conceito GRE/KB2Ops; filtro/escopo e reutilização global | **Taxonomy API** | vocabulário controlado; relação termo↔posts; não usar duas taxonomias para o mesmo conceito | **MANTER WP** | B-002: valores reais, cardinalidade e mapping `_bdc_es_*`/`_kb2ops_*` |
| tipo de conhecimento | conjunto conhecido (`procedure`, `troubleshooting`, `reference`, etc.) e usado em filtros/relatórios | **Taxonomy API** | vocabulário finito e reutilizável | **MANTER WP** | B-002 para mapear valores existentes |
| serviço | KB2Ops usa para filtro/insights | **Taxonomy API** | conceito próprio; não fundir com serviço afetado | **MANTER WP** | B-002 |
| tecnologias | KB2Ops usa como contexto/filtro; tendência multivalor | **Taxonomy API** | flat/facetável; não fundir com sistemas | **MANTER WP** | B-002; confirmar separação/normalização de strings atuais |
| equipe responsável | valor reutilizável, porém relação com GAC/organização ainda não comprovada | Metadata **ou** Taxonomy API | se vocabulário local/estável e filtro global forem comprovados, taxonomy; se referência contextual/externa, meta/ID é mais simples | **AINDA_NAO_SABEMOS** | B-002 + D-005: vocabulário, hierarquia, GAC, filtros reais |
| item de catálogo | potencial identificador/vocabulário corporativo, consumo global ainda não perfilado | Metadata **ou** Taxonomy API | não transformar ID externo em taxonomy sem necessidade de faceta local | **AINDA_NAO_SABEMOS** | B-002: cardinalidade, origem, estabilidade e uso em filtros |
| serviço afetado | campo GRE distinto de `service`; reutilização/faceta ainda não comprovada isoladamente | Metadata **ou** Taxonomy API | se faceta global, taxonomy própria; se descrição/valor local, metadata | **AINDA_NAO_SABEMOS** | B-002; não fundir com `service` |
| sistemas envolvidos | campo GRE distinto de `technologies`; reutilização/faceta não comprovada | Metadata **ou** Taxonomy API | pode ser multivalor; sem merge por semelhança nominal | **AINDA_NAO_SABEMOS** | B-002; profiling de vocabulário/uso |
| keywords | sinal manual de recuperação; não há requisito comprovado de navegação/faceta | **Metadata API** | normalizar representação; Search pode indexar sem transformar em taxonomy | **MANTER WP** | migrar strings somente após profiling |
| versões | contexto funcional potencialmente livre/alta cardinalidade; não há faceta global comprovada | **Metadata API** | manter local ao post; evitar explosão de terms | **MANTER WP** | profiling de cardinalidade; taxonomy só se requisito real aparecer |

### 5.3 Consequência para T057

Nenhum campo classificatório entra em T057 neste momento. Mesmo os quatro `AINDA_NAO_SABEMOS` estão entre **duas primitives WordPress**, não entre WordPress e tabela própria. B-002 decide a escolha/cutover, não autoriza infraestrutura própria.

## 6. Revisão e Governança

| Necessidade | Primitive WP | Avaliação | Decisão | Gate |
|---|---|---|---|---|
| review state | Metadata API | estado local e escalar por post | **MANTER WP** | transições testadas |
| review notes | Metadata API | texto local; sem consulta global prioritária | **MANTER WP** | sanitização/escaping |
| reviewed_at | Metadata API | timestamp/evidência local | **MANTER WP** | formato único |
| reviewed_by | Metadata API com `WP_User` ID | não reinventar identidade; autorização continua via capabilities | **MANTER WP** | usuário inexistente/deletado deve degradar com segurança |
| include_ai | Metadata API boolean | decisão humana local | **MANTER WP** | teste positivo/negativo |
| histórico de revisão bounded | Metadata API com valor estruturado registrado | histórico atual é por post, bounded (50), sem consulta transversal comprovada | **MANTER WP** | definir schema/sanitização; se surgir requisito de audit transversal/imutabilidade/alto volume, reabrir antes de T057 |
| snapshots/revisões de metas selecionadas | Revisions + meta `revisions_enabled`, se requisito de produto exigir | Core suporta meta revisionável; não é necessário transformar toda mutação em audit log | **MANTER WP** como primitive disponível, **não obrigatória** | definir quais campos realmente precisam snapshot; compatibilidade com versão mínima WP |
| AI READY | cálculo sobre status + review + Resumo + include_ai | é derivado; não deve virar meta canônica paralela | **SUBSTITUIR_POR_WORDPRESS** para leitura das fontes; cálculo de domínio permanece | regra baseline única testada |

B-006 permanece aplicável ao **write path composto**; ele não justifica banco próprio. T057 não recebe “atomicidade” como argumento automático para tabela.

## 7. Search Knowledge e Search Quality

| Capacidade | Primitive WP candidata | Avaliação | Decisão | Evidência/gate |
|---|---|---|---|---|
| vocabulary | `WP_Post` não público como registro governável + Metadata API; taxonomy somente para faceta/classificação, não por conveniência | volume conhecido é de curadoria manual, não stream de eventos; Core oferece CRUD, autor, status e revisão | **MANTER WP** | preflight de quantidade real; benchmark somente se cardinalidade surpreender |
| term bindings | `WP_Post` não público + postmeta para alvo/escopo/peso/versão | relação é governada e de baixa frequência; não há evidência de volume que exija store próprio | **MANTER WP** | quantidade de bindings e padrão de lookup reais |
| relevance rules | `WP_Post` não público + postmeta/revisions | regra é entidade de governança, não dado de alta taxa | **MANTER WP** | validar versionamento/Apply/expected-state |
| Golden Queries | `WP_Post` não público + postmeta/revisions | suíte é QA governada, editável, pequena/moderada; WP oferece lifecycle e histórico | **MANTER WP** | import/preflight do conjunto ASI real; suíte vazia nunca PASS |
| evidência de execução Golden | postmeta/option bounded associada à suíte/dataset ou artefato de release | é evidência versionada, não telemetria de usuário | **MANTER WP** inicialmente | tamanho/retention do relatório |
| simulação/Apply | `admin-post` + capability + nonce + estado esperado/proof | não precisa endpoint próprio; AJAX só se UX live justificar | **MANTER WP** | regressão stale-state/HMAC/proof conforme desenho final |

**Nota:** usar `WP_Post` para registros internos de Search Knowledge **não** torna esses registros conteúdo editorial do artigo. O owner continua Search Knowledge/Search Quality e eles não podem escrever `post_content`/Elementor do artigo.

## 8. Search Retrieval e Indexing

| Necessidade | Primitive WP avaliada | Limitação objetiva | Decisão | T057 recebe o quê? |
|---|---|---|---|---|
| busca nativa básica/fallback | `WP_Query` com `s`, tax/meta filters | útil como fallback, mas busca nativa cobre essencialmente título/excerpt/conteúdo e não a representação canônica derivada Elementor + Summary/Classificação/Item Knowledge | **MANTER WP** como fallback, **não** como engine de paridade | nada isoladamente |
| índice lexical por post | `WP_Query`/postmeta/taxonomy | `post_content` não é documento canônico Elementor; meta LIKE legado foi classificado como provisório; ranking explicável e sinais compostos não cabem no search nativo sem reconstruir um motor lateral sobre queries ad hoc | **CANDIDATO_INFRA_PROPRIA_T057** | uma projection reconstruível mínima; nenhum schema/tabela pré-escolhido |
| Item Knowledge/trechos pesquisáveis | postmeta estruturado / child `WP_Post` avaliados | retrieval transversal por muitos itens, ranking por item, identidade estável e reconciliação tornam arrays/meta ou posts-filhos uma sobrecarga/consulta inadequada para paridade ASI | **CANDIDATO_INFRA_PROPRIA_T057** | avaliar junto com Search Index, evitando segundo store se um único desenho cobrir post+item |
| filtros/scope | status + Taxonomy/Metadata + capabilities | Core atende filtragem e autorização de exposição | **MANTER WP** | nenhum |
| detalhe/result page | rewrite/query vars/shortcode/server rendering nativo | não há necessidade comprovada de SPA/REST | **MANTER WP** | nenhum |
| live typing | AJAX WordPress apenas se UX justificar | server-rendered/GET já atende baseline; AJAX é enhancement | **MANTER WP** | nenhum endpoint próprio fora da API WP |
| REST | WP REST API | não existe consumidor formal atual | **AINDA_NAO_SABEMOS** quanto à necessidade; **não criar agora** | não entra T057 sem consumidor |
| query cache | Transients/Object Cache com version token | efêmero/reconstruível; não pode carregar identidade compartilhada | **MANTER WP** | nenhum |
| deep-link/anchors | hooks/filters WordPress + estrutura extraída | primitive existe, mas semântica de destino/identidade ainda é B-005 | **AINDA_NAO_SABEMOS** | não entra T057 antes de fechar B-005 |
| Word Cloud | Taxonomy APIs poderiam servir apenas se a feature fosse literalmente taxonômica; caso contrário consumir Search/Analytics | requisito de produto/preflight ainda aberto | **AINDA_NAO_SABEMOS** | dívida postergável, sem pipeline próprio |

### Conclusão Search

O Core continua sendo transporte, autorização, filtros, fallback e cache. A limitação comprovada está na **projection de retrieval lexical/itens**, não no WordPress como plataforma.

## 9. Analytics, métricas e audit

| Necessidade | Primitive WP avaliada | Avaliação | Decisão | Gate |
|---|---|---|---|---|
| Coverage/review/AI Ready metrics | `WP_Query` + meta/tax + cache | deriváveis de canônicos; consultas precisam ser bounded | **MANTER WP** | benchmark antes de rollup |
| search events | options/postmeta/`WP_Post` avaliados | event stream possui taxa, concorrência, retenção e consultas temporais; option agregada KB2Ops já foi rejeitada; postmeta liga fato ao objeto errado | **CANDIDATO_INFRA_PROPRIA_T057** **somente se Analytics detalhado for habilitado** | B-004 + volumetria/retention/queries |
| interactions/outcomes/journey | mesmas primitives | correlação/replay/outcome adiciona padrão append/query temporal incompatível com simples option/meta sem distorção | **CANDIDATO_INFRA_PROPRIA_T057** condicional e na **mesma família de facts** dos events | B-004; evitar stores separados sem necessidade |
| query text | nenhuma primitive resolve a decisão de privacidade | problema é política, não storage | **AINDA_NAO_SABEMOS** | B-004: finalidade, minimização, retenção, acesso |
| view count simples | postmeta seria possível, mas criaria segundo pipeline se Analytics existir | não manter `_kb2ops_view_count` como owner paralelo | **SUBSTITUIR_POR_WORDPRESS** somente para contagem local temporária; preferir derivar de facts se Analytics aprovado | decidir se a métrica é necessária |
| audit de mutações sensíveis de baixa frequência | `WP_Post` interno/postmeta ou evidência operacional bounded | Core atende enquanto volume for baixo e requisito for trilha administrativa, não SIEM | **MANTER WP** inicialmente | reabrir somente se retenção/imutabilidade/consulta externa provar insuficiência |

`quality_daily` continua descartado: nenhum rollup/materialização nasce sem benchmark.

## 10. Configuração, segurança e superfícies administrativas

| Capacidade | Primitive WP | Avaliação | Decisão |
|---|---|---|---|
| settings globais | Settings + Options API | solução natural; agrupar por domínio, sem `asi4_settings` e `kb2ops_options` como owners paralelos | **SUBSTITUIR_POR_WORDPRESS** |
| runtime version | Options API | pequeno estado técnico | **MANTER WP** |
| checkpoint simples de upgrade/cutover | Options API | suficiente para estado pequeno/transitório; não carregar state machine histórica ASI | **MANTER WP** |
| identidade/revisor | Users API | não reinventar usuário | **MANTER WP** |
| autorização por objeto | `current_user_can( 'edit_post', $post_id )` | baseline comprovado GRE/KB2Ops | **MANTER WP** |
| capabilities globais/especializadas | Roles & Capabilities | Search Knowledge/Quality/Operations podem ter caps próprias sem ACL paralela | **MANTER WP** |
| CSRF | Nonces | obrigatório junto com capability; não substitui autorização | **MANTER WP** |
| mutação admin server-rendered | `admin-post.php` + POST | baseline simples e comprovado | **MANTER WP** |
| interação live | AJAX WP | somente quando UX live precisa; manter nonce/rate-limit/server authority | **MANTER WP** |
| API externa futura | WP REST API | usar apenas com consumidor formal; nenhum atual | **AINDA_NAO_SABEMOS** / não criar |
| HTTP externo futuro | WordPress HTTP API | se Foundry/GAC/outro provider for aprovado no futuro, não criar cliente HTTP paralelo | **MANTER WP** |
| shortcodes/aliases | Shortcode API | primitive atende render; **existência do alias** depende B-003 | **MANTER WP** como primitive; aliases ainda não aprovados |
| menus/telas | wp-admin + server rendering | um shell; JS por progressive enhancement | **MANTER WP** |

## 11. Revisions, cache, health e scheduling

| Capacidade | Primitive WP | Limite relevante | Decisão | Consequência |
|---|---|---|---|---|
| revisão de post/meta selecionada | Revisions + registered meta revisionável | só usar quando snapshot é requisito; não substituir histórico de workflow/evento | **MANTER WP** | nenhuma infra própria agora |
| cache de projections/queries | Transients/Object Cache | cache pode sumir e persistent object cache não é garantido | **MANTER WP** | nunca usar como durable state |
| saúde técnica | Site Health / `site_status_tests` | checks específicos podem ser adicionados; ação operacional continua em tela própria apenas se necessária | **SUBSTITUIR_POR_WORDPRESS** | eliminar health dashboard paralelo como default |
| tarefas periódicas simples | WP-Cron | timing depende de tráfego; é trigger, não lease/job store | **MANTER WP** | usar para trigger/retry leve quando suficiente |
| fila durável para index/rebuild | WP-Cron + options/transients avaliados | não oferecem naturalmente claim/lease/retry budget/dead-letter/recovery e não devem transformar options/cache em fila relacional improvisada | **CANDIDATO_INFRA_PROPRIA_T057** **somente se workload assíncrono justificar** | T057 mede custo/timeout/SLA/retentativa e decide se fila sequer nasce |
| activation/deactivation/uninstall | Plugin API hooks | atende lifecycle; activation mínima e uninstall não destrutivo | **MANTER WP** | não portar orchestrator/migration runner históricos |

## 12. Lista final que segue para T057

T057 **não** recebe todos os stores do ASI. Recebe somente três famílias candidatas:

### F-057-01 — Search Retrieval Projection

Inclui, idealmente no menor desenho coerente:

- documento lexical por post;
- Item Knowledge/trechos pesquisáveis;
- identidade/version/hash necessários à reconstrução;
- índices de consulta/ranking que o WordPress native search não fornece.

**Por que passou T056:** a limitação do search nativo está demonstrada pela fonte Elementor derivada, pelos sinais compostos e pela paridade de ranking/items.  
**T057 ainda precisa provar:** corpus, consultas, latência, volume, rebuild, índices mínimos e se uma única infraestrutura cobre post+item.

### F-057-02 — Analytics Facts, condicional

Inclui no máximo uma família coerente para events/interactions/outcomes se Analytics detalhado continuar requisito.

**Por que passou T056:** Options/postmeta e contador por post não modelam bem facts append-only concorrentes/correlacionados.  
**T057 ainda precisa provar:** B-004, volume, retenção, perguntas gerenciais, taxa de escrita, necessidade real de interaction/outcome e minimização.

Se a política concluir que analytics detalhado não é necessário, **F-057-02 morre sem implementação**.

### F-057-03 — Durable Job State, condicional

Somente para indexação/rebuild assíncrono que exija lease, retry, dead/recovery e retomada.

**Por que passou T056:** WP-Cron resolve trigger, não durabilidade/estado de worker.  
**T057 ainda precisa provar:** custo do extractor/index por post, tamanho do corpus, timeout, SLA, concorrência e necessidade de background.

Se processamento bounded/síncrono ou rebuild manual atender, **F-057-03 morre sem implementação**.

## 13. Itens explicitamente retirados de T057

Não justificam infraestrutura própria nesta fase:

- Resumo Executivo;
- classification fields, inclusive os quatro ainda em profiling meta↔taxonomy;
- review state/notas/revisor/include_ai/histórico bounded;
- settings/runtime version/checkpoints simples;
- identities/capabilities/nonces;
- admin mutations;
- shortcodes;
- health checks;
- caches;
- Coverage/AI READY/read models simples;
- vocabulary/bindings/relevance rules;
- Golden Queries;
- evidência operacional/audit de baixa frequência;
- HTTP/REST/AJAX como transportes;
- Revisions.

## 14. Unknowns remanescentes após T056

`AINDA_NAO_SABEMOS` não significa “criar tabela”. Permanecem abertos:

1. `responsible_team`: meta versus taxonomy e eventual mapping GAC;
2. `catalog_item`: meta versus taxonomy;
3. `affected_service`: meta versus taxonomy, mantendo semântica separada de `service`;
4. `systems_involved`: meta versus taxonomy, mantendo separado de `technologies`;
5. quais campos merecem snapshots por Revisions além do histórico explícito;
6. necessidade real de REST externo;
7. deep-link/anchor B-005;
8. Word Cloud como produto;
9. política de query text B-004.

Esses unknowns são resolvidos por profiling/preflight/política. Nenhum entra automaticamente na infraestrutura própria.

## 15. Compatibilidade T054 não altera T056

- `_bdc_es_*` e `_kb2ops_*` podem exigir dual-read/adapters temporários, mas não mudam a primitive futura;
- hooks/shortcodes antigos continuam condicionados a B-003;
- `Objective_Provider`/`bdc_es_objective_updated` não justificam provider/evento legado permanente;
- GAC não justifica storage/classificação paralela;
- nenhum parser antigo sobrevive para “facilitar” migração;
- dual-write permanente permanece proibido.

## 16. Revisão pelos papéis da SPEC

### Arquiteto WordPress

**APROVA T056:** Core permanece plataforma dominante. Infra própria foi reduzida a três famílias condicionais.

### Arquiteto de Conhecimento

**APROVA COM GATES:** taxonomias foram escolhidas somente onde reutilização/filtro/faceta possuem evidência; quatro classificações continuam em profiling em vez de serem forçadas.

### Especialista de Segurança

**APROVA COM INVARIANTES:** `edit_post`/capabilities, nonce, validação/sanitização e least privilege permanecem obrigatórios; Analytics continua bloqueado por B-004.

### Crítico de Simplicidade

**APROVA:** não há tabela por inércia, Search Knowledge/Golden ficam em primitives WP enquanto não houver prova contrária, WP-Cron não foi confundido com fila e cache não foi confundido com persistência.

## 17. Critério de fechamento T056

- [x] cada família de dado/capacidade possui primitive WP avaliada;
- [x] Metadata vs Taxonomy foi avaliado por semântica/cardinalidade/consulta, não por rótulo;
- [x] nenhuma tabela/schema foi escolhida;
- [x] WordPress-first retirou do escopo T057 tudo que Core atende;
- [x] T057 recebeu somente limitações objetivas/condicionais;
- [x] compatibilidade T054 não escolheu arquitetura futura;
- [x] blockers contextuais foram preservados;
- [x] nenhum runtime foi criado.

## 18. Próximo passo autorizado

**T057 — identificar a infraestrutura própria mínima justificada**, limitada inicialmente a `F-057-01..03`.

T057 deve aplicar novamente princípio de negação. “Candidato” não significa “aprovado”; cada família pode ser reduzida ou descartada.

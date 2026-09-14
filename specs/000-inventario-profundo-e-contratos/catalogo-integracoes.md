# Catálogo Unificado de Integrações, Hooks e Superfícies — SPEC-000 — T051

> Estado: **T051 concluída documentalmente**.  
> Baselines cruzadas: ASI `4.6.8`, GRE `0.6.0`, KB2Ops `0.2.1` nas SHAs fixadas pela SPEC-000.
>
> Este documento descreve o produto unificado por responsabilidade futura. Nomes finais de hooks, actions, shortcodes e rotas **não são congelados aqui**; o contrato semântico vem antes do identificador técnico.

## 1. Regras de integração

1. Toda integração cross-module deve ter produtor, consumidor, payload mínimo, condição de emissão, falha esperada e idempotência conhecida.
2. Evento de domínio só é emitido **depois de persistência confirmada**.
3. Falha de projection/Analytics nunca reescreve ou desfaz silenciosamente o dado canônico já persistido.
4. `admin-post` é baseline para mutações administrativas server-rendered; AJAX entra apenas quando interação live realmente exigir.
5. REST não nasce sem consumidor real.
6. Capability e nonce são obrigatórios nas mutações WordPress; nonce não substitui capability.
7. Integração pública de tracking, se existir, preserva nonce/rate-limit/HMAC/idempotência e autoridade do servidor.
8. Adapter de compatibilidade é temporário e deve ter gate de remoção.
9. Módulos não acessam storage interno de outro módulo por conveniência; consomem contrato do owner.
10. Design System é camada de apresentação, não barramento de integração entre domínios.

## 2. Lifecycle do plugin unificado

| Momento | Contrato futuro | Origem de referência | Decisão |
|---|---|---|---|
| activation | registrar estado mínimo/necessário; nunca trabalho pesado/destrutivo | ASI + KB2Ops | MANTER princípio |
| `plugins_loaded` | boot único/modular | três referências | MANTER |
| `init` | registrar metadata/taxonomias/shortcodes quando aprovados | GRE + KB2Ops | MANTER WordPress-first |
| `admin_init` | upgrade idempotente pequeno, se necessário | KB2Ops | CONDICIONAL |
| deactivation | limpar apenas schedules/runtime efêmero próprio | ASI | MANTER se houver cron |
| uninstall | preservar dados por default; purge separado | ASI + KB2Ops | MANTER |

**Não portar:** PostInstallOrchestrator/MigrationRunner/BaseReconciler históricos como state machine padrão do greenfield.

## 3. Contratos cross-module canônicos

Os identificadores abaixo são IDs documentais. Nomes WordPress finais serão definidos na SPEC de implementação.

### EVT-001 — Summary Changed

**Produtor:** Resumo Executivo / Classificação quando alteração afeta representação pesquisável.  
**Pré-condição:** autorização + validação + persistência + read-after-write/estado final confirmado.  
**Payload mínimo:** `post_id`, campos/conceitos alterados, versão/hash lógico quando disponível.  
**Consumidores:** Search Indexing; Content/Insights cache invalidation; futuro semantic projection.  
**Falha do consumidor:** non-fatal para dado canônico; projection fica stale/pendente e precisa ser diagnosticável.  
**Idempotência:** consumidores devem poder processar a mesma versão mais de uma vez sem duplicar efeito.

**Substitui conceitualmente:** `bdc_es_objective_updated` inexistente + dependência quebrada do ASI.

### EVT-002 — Review State Confirmed

**Produtor:** Revisão e Governança.  
**Pré-condição:** estado final relido e confirmado.  
**Payload:** `post_id`, estado anterior, estado atual, `include_ai`/readiness inputs relevantes sem duplicar payload inteiro.  
**Consumidores:** Search scope/readiness invalidation; Insights; futura IA somente se política permitir.  
**Falha:** não desfaz aprovação; downstream pode ficar stale e ser reprocessado.

**Não pode:** aplicar vocabulary/binding/rule automaticamente.

### EVT-003 — Classification Changed

**Produtor:** Classificação de Conhecimento.  
**Pré-condição:** writer canônico confirmou persistência.  
**Payload:** `post_id`, conceitos alterados.  
**Consumidores:** Search Indexing, Insights, filtros/cache.  
**Idempotência:** por estado/version/hash.

### EVT-004 — Search Knowledge Applied

**Produtor:** Search Knowledge.  
**Pré-condição:** capability própria + nonce + expected-state/simulation proof quando aplicável + persistência confirmada.  
**Payload:** tipo da mudança, ID/versão da entidade de Search Knowledge, versão do algoritmo/config afetada.  
**Consumidores:** cache invalidation, Golden evidence stale, Search runtime.  
**Falha:** não deve editar artigo/classificação.

### EVT-005 — Content Source Changed / Projection Invalidated

**Origem primária:** hooks nativos WordPress/Elementor sobre post/status/metadata aprovados.  
**Produtor lógico:** Core/Content Extraction apenas converte o fato em invalidação derivada.  
**Consumidores:** Search Indexing e demais projections.  
**Regra:** não criar segundo editor nem reprocessar em massa na request de save sem budget.

### EVT-006 — Search Fact Recorded

**Produtor:** Search runtime público/admin.  
**Consumidor:** Analytics/Search Intelligence.  
**Falha:** sempre non-fatal para Search.  
**Privacidade:** payload mínimo; query/identidade conforme política T057/T095.

## 4. Content Extraction como contrato interno

### Inputs permitidos

- `WP_Post`;
- `_elementor_data` read-only;
- renderização Elementor controlada como fallback;
- `post_content` como fallback final;
- shortcodes explicitamente allowlisted, inicialmente referência `table/tablepress`.

### Outputs

- texto/HTML derivado normalizado;
- estrutura/headings/tabelas/facts;
- warnings/diagnósticos de extração;
- hash/versionamento quando implementado.

### Consumidores

- Search Indexing;
- Item/Deep Link projection;
- Review/Quality;
- Word Cloud opcional;
- futura chunking/IA.

**Proibição:** nenhum consumidor mantém parser paralelo de Elementor/post_content.

## 5. Admin — superfície unificada

T053 definiu um único shell/navegação. T051 consolida os tipos de mutação:

| Responsabilidade | Canal baseline | Autorização | CSRF | Estado |
|---|---|---|---|---|
| salvar Resumo | admin-post | `edit_post(post_id)` | nonce por objeto/ação | canônico |
| salvar Classificação | admin-post | `edit_post(post_id)` ou capability específica se governança exigir | nonce | canônico |
| salvar Review | admin-post | `edit_post(post_id)` | nonce | canônico |
| Settings globais | admin-post | `manage_options` ou cap específica | nonce | canônico |
| Apply Search Knowledge | admin-post/AJAX admin apenas se UX justificar | capability Search específica | nonce + expected state/proof | canônico |
| rodar Golden/diagnóstico | admin action | capability de Search Quality | nonce | operacional/QA |
| rebuild/reindex | admin action | capability operacional | nonce + confirmação para massivo | operacional |
| migration/purge | admin-post | `manage_options`/cap operacional | nonce + confirmação explícita | transitório/destrutivo controlado |

### Princípio

Não reproduzir páginas/actions de cada plugin histórico. A superfície final é única, mas cada handler chama o owner correto e mantém capabilities por responsabilidade.

## 6. Search público/admin

### Contrato canônico de experiência

- uma única Search;
- scope aplicado antes de exposição (`published`, `approved`, `ai_ready` ou política futura);
- detail route revalida scope/permissão;
- retrieval lexical funciona sem IA/vetor;
- progressive disclosure;
- deep-link somente para destino comprovado;
- tracking não é requisito para a busca funcionar.

### Transporte

**Baseline:** server-rendered/GET é funcional e simples.  
**Enhancement possível:** WordPress AJAX para live typing/debounce/cancel/tracking, somente se a UX final exigir.  
**REST:** não criar no baseline sem consumidor externo real.

### Se AJAX for adotado

Portar contratos ASI, não endpoints literalmente:

- nonce;
- rate limit;
- limites de tamanho;
- server authority;
- journey/token não cru;
- target HMAC para interação;
- deduplicação/idempotência;
- cache neutro de identidade de tracking;
- estados `idle/success/empty/error/rate_limit` equivalentes.

## 7. Shortcodes e superfícies públicas históricas

| Contrato histórico | Origem | Status T051 | Regra futura |
|---|---|---|---|
| `[asi_search_form]` | ASI | **COMPAT A PROVAR** | preflight de consumidores antes de alias |
| `[bdc_word_cloud]` | ASI | **OPCIONAL/COMPAT A PROVAR** | feature não define arquitetura |
| `[bdc_resumo_executivo]` | GRE | **COMPAT A PROVAR** | preservar current-post-only se alias existir |
| `[kb2ops_search]` | KB2Ops | **COMPAT A PROVAR** | possível alias para Search canônica |
| `[kb2ops_portal]` | KB2Ops | **COMPAT A PROVAR** | alias histórico; remover se sem consumidor |
| side panel automático GRE | GRE | **NÃO CANÔNICO** | decisão de UX futura; não invariante |

### Política

Nenhum shortcode histórico será portado por medo. Antes do cutover:

1. preflight de conteúdo/uso real;
2. identificar consumidores;
3. definir superfície canônica;
4. criar alias temporário somente se necessário;
5. medir/registrar uso quando viável;
6. definir gate de remoção.

## 8. Hooks/eventos históricos — destino

| Hook/integração histórica | Estado | Destino |
|---|---|---|
| `bdc_es_loaded` | compatibilidade | não necessário internamente no plugin único salvo consumidor comprovado |
| `kb2ops_loaded` | compatibilidade | idem |
| `bdc_es_objective_updated` | **quebrado/inexistente no GRE** | substituir por contrato EVT-001 |
| `kb2ops_post_approved` | intenção válida, implementação frágil | substituir por EVT-002 pós-confirmação |
| `save_post` ASI | intenção válida | usar invalidation central; ownership único do listener |
| `the_content` Anchor Manager | implementação específica | deep-link será redesenhado; não assumir filter global |
| `site_status_tests` | referência forte | Site Health preferido para saúde técnica |
| hooks Word Cloud | feature opcional | não portar pipeline próprio |
| legacy migration hooks | histórico | DESCARTAR salvo adapter temporal documentado |

## 9. Segurança e capabilities

### Por objeto

- edição de Resumo/Classificação/Review: `edit_post(post_id)` como baseline forte;
- shortcode/read público nunca recebe acesso arbitrário a `post_id` privado apenas por parâmetro.

### Globais/especializadas

- configuração/lifecycle: `manage_options` inicialmente;
- Search Knowledge: capability própria se houver papel distinto de curador;
- Search Quality/Analytics: capability de leitura/execução conforme sensibilidade;
- Operations destrutivas: menor privilégio + confirmação explícita.

### Regras

- nonce + capability sempre em mutação;
- sanitize/validate antes de write;
- escape na saída;
- método HTTP esperado;
- cliente não define fatos autoritativos que o servidor conhece;
- dados ambientais GAC não concedem ownership/capability automaticamente.

## 10. Analytics e tracking

### Contrato

Search produz facts mínimos; Analytics consome. Analytics falha sem derrubar Search.

### Se interactions/outcomes forem habilitados

- event/journey correlation sem identificador cru desnecessário;
- HMAC dos targets;
- replay exato idempotente;
- retention explícita;
- export redigido;
- query text sob política específica;
- não persistir IP/UA bruto em modo mínimo.

`kb2ops_search_analytics` e `_kb2ops_view_count` não permanecem como segundo pipeline de tracking.

## 11. Search Quality / Golden

### Operações

- criar/editar expectation;
- executar suíte;
- registrar evidência/versionamento;
- invalidar evidência quando dataset/ranker relevante mudar;
- blocker falha release;
- suíte vazia não é PASS.

### Integração

Search Knowledge Apply ou alteração significativa de ranker marca evidence stale. A execução de Golden não modifica posts.

## 12. Operações, scheduling e queue

### WP-Cron

É candidato a trigger para tarefas periódicas/retry. Não substitui estado de job quando durabilidade for necessária.

### Queue própria

**Ainda não aprovada.** T057 deve demonstrar necessidade a partir de:

- quantidade de posts;
- custo do extractor/index;
- SLA;
- retry/lease;
- risco de timeout;
- necessidade de retomada.

Se aprovada, preservar semântica ASI de idempotência/retry/dead/recovery, não copiar schema literal.

### Rebuild massivo

Nunca dispara silenciosamente em activation/save. Exige budget, operação explícita e diagnóstico.

## 13. Site Health e diagnósticos

- Site Health é superfície preferida para saúde técnica passiva;
- Operations UI só existe para ação humana real (rebuild, migration, purge, fila quando necessária);
- export diagnóstico precisa redigir dados sensíveis;
- warnings de Content Extraction devem ser observáveis sem interromper post/publicação.

## 14. Integrações externas

### Elementor

- fonte editorial externa ao plugin;
- leitura read-only;
- fallback render controlado;
- nenhuma escrita em `_elementor_data`.

### Table/TablePress

- execução somente via allowlist explícita no extractor;
- falha vira diagnóstico/placeholder, não execução arbitrária de shortcode.

### GAC

- dependência ambiental histórica do ASI;
- fora do core;
- adapter opcional somente se requisito real for confirmado;
- indisponibilidade não derruba Search/core.

### Microsoft Foundry / IA

- **não integrado na SPEC-000**;
- futuro provider desacoplado;
- queda não derruba Search lexical/curadoria local;
- fluxo de IA sempre assistivo.

## 15. Design System e UI como integração

- wp-admin continua shell;
- Design System único, reconstruído a partir dos princípios KB2Ops;
- componentes recebem view models/dados do domínio; não consultam stores lateralmente;
- JS é progressive enhancement;
- sem segunda sidebar;
- área única de Insights pode compor várias métricas, sem criar owner agregado próprio.

## 16. Matriz resumida de contratos cross-module

| Contrato | Produtor | Consumidor | Falha | Idempotência |
|---|---|---|---|---|
| Summary/Classificação changed | owner do dado | Indexing/Insights | projection stale, dado canônico permanece | por versão/hash/estado |
| Review confirmed | Revisão | scope/Insights/AI opcional | downstream stale | por transição/estado |
| Content extracted | Extraction | Search/Review/IA | warning/degradação controlada | mesma fonte+versão = mesmo resultado esperado |
| Search Knowledge applied | Search Knowledge | Search/Golden/cache | mudança não toca artigo | expected-state/version |
| Search fact | Search | Analytics | non-fatal | event/replay dedup se aplicável |
| Golden execution | Search Quality | release/diagnóstico | blocker = NO-GO | dataset/ranker version |
| migration adapter | Operations | owner destino | falha não apaga origem | checkpoint/idempotência |

## 17. Compatibilidade — classes de destino

### CANÔNICO

- contratos de owners futuros;
- eventos pós-persistência;
- Search scope/visibility;
- Content Extractor único;
- security boundaries;
- Site Health/operations mínimos.

### COMPAT TEMPORÁRIO

- aliases de shortcodes comprovadamente usados;
- leitura de keys GRE/KB2Ops antigas;
- leitura/import de stores ASI manuais que possuam dados reais;
- adapters GAC somente se requisito real.

### DESCARTADO

- bridges internas permanentes entre módulos do mesmo plugin;
- hooks `*_loaded` sem consumidor;
- REST criado apenas por modernidade;
- migrations/reconcilers históricos como core permanente;
- pipelines independentes de Word Cloud/Item/Search sobre conteúdo bruto.

### AINDA DEPENDE DE PREFLIGHT

- shortcodes históricos;
- side panel GRE;
- Word Cloud;
- consumidor GAC;
- aliases de rotas públicas.

## 18. Decisões T051

1. produto futuro possui contratos internos por domínio, não integrações entre “plugins dentro do plugin”;
2. Summary/Classification/Review emitem mudança somente depois de write confirmado;
3. Search Indexing reage como projection e falha de forma não destrutiva;
4. Search Knowledge Apply é separado de Review approval;
5. Content Extraction é serviço único para downstream;
6. server-rendered/admin-post é baseline; AJAX é enhancement específico de live Search; REST não nasce sem consumidor;
7. shortcodes e hooks históricos são compatibilidade a provar, não API canônica automática;
8. Analytics é non-fatal e privacy-first;
9. Operations/migration são mínimas/transitórias;
10. nenhum nome final de hook/endpoint foi congelado e nenhum runtime foi criado.

## 19. Próximo passo

T054 deve transformar os drifts D-001–D-008 e riscos de compatibilidade em um **mapa final de contratos quebrados**, distinguindo:

- corrigido pela arquitetura futura;
- compatibilidade temporária necessária;
- descartado deliberadamente;
- dependente de preflight/profiling;
- blocker para SPEC-001.

Depois, T056 aplica WordPress-first às primitives e T057 justifica infraestrutura própria.
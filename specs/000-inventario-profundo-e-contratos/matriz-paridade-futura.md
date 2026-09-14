# Matriz de Paridade Futura — SPEC-000

> Matriz incremental. Nesta versão, registra paridade derivada de ASI + Gerenciador de Resumo Executivo. Não define implementação nem schema final. KB2Ops e cruzamento T050–T059 ainda são obrigatórios.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — preferir primitive nativa.
- **EVOLUIR COM IA/VETOR** — evolução opcional, degradável e governada.
- **DESCARTAR** — não levar ao greenfield.
- **AINDA NÃO SABEMOS** — falta evidência/cross-reference.

## 1. ASI → produto futuro

| Capacidade | Paridade exigida | Direção | Gate futuro |
|---|---|---|---|
| busca lexical local | SIM | MANTER/REDESENHAR | Golden Queries + performance |
| fallback quando FULLTEXT indisponível | SIM | MANTER | busca continua funcional |
| QueryContext/plano limitado | SIM | MANTER/REDESENHAR | unit + Golden |
| ranking explicável | SIM | MANTER | evidência de sinais/scores |
| índice por post | PROVÁVEL | REDESENHAR | Content Extractor canônico |
| índice de trechos | SIM | REDESENHAR | identidade/anchor/navegação |
| extractor ASI sobre `post_content` | NÃO | DESCARTAR | substituir pelo extractor canônico Elementor-aware |
| vocabulary administrável | SIM | MANTER | storage após WordPress-first |
| bindings explícitos | SIM | MANTER | storage após WordPress-first |
| promote/demote rules | SIM | MANTER | simulation + audit |
| diagnóstico read-only | SIM | MANTER | sem mutação colateral |
| sugestões determinísticas/evidence-backed | SIM | MANTER | confidence/effect/risk |
| simulação com ranker real | SIM | MANTER | Apply proof + stale-state guard |
| Apply humano | SIM | MANTER | capability + nonce + evidência |
| durable queue | CONDICIONAL | REDESENHAR | justificar workload/durabilidade |
| migrations 4.x/WPUI | NÃO | DESCARTAR | greenfield |
| post-install state machine completo | NÃO | REDESENHAR | mínimo necessário ao runtime novo |
| Search Events | SIM, com minimização | REDESENHAR | privacy/retention |
| Search Interactions | SIM, se analytics habilitado | MANTER/REDESENHAR | HMAC/idempotência |
| Outcomes por journey | SIM | MANTER | semântica de consulta madura |
| `quality_daily` | NÃO inicialmente | DESCARTAR | reintroduzir só por benchmark |
| Golden Queries | SIM | MANTER | release NO-GO para blockers |
| Quality Diagnostics | SIM | SUBSTITUIR POR WORDPRESS + checks próprios | Site Health/export seguro |
| Search Intelligence gerencial | PROVÁVEL | REDESENHAR | capability/privacy/workload bounds |
| integração GAC direta | NÃO | DESCARTAR | adapter opcional se necessário |
| Word Cloud | OPCIONAL | REDESENHAR | consumir índice/telemetria canônicos |
| `[asi_search_form]` exato | NÃO | REDESENHAR | UX pública equivalente no DS novo |
| AJAX exato | AINDA NÃO SABEMOS | AINDA NÃO SABEMOS | escolher primitive WP pelo consumidor |
| public debounce/cancel | SIM | MANTER | E2E |
| progressive disclosure | SIM | MANTER | E2E/acessibilidade |
| anchor injection exata | AINDA NÃO SABEMOS | REDESENHAR | cruzar KB2Ops/Elementor |
| Objective canonical-only | SIM | MANTER | store interno GRE confirmado; drift legado não pode sobreviver |
| legacy/compat | NÃO | DESCARTAR | só estratégia de migração separada se necessária |
| uninstall não destrutivo | SIM | MANTER | política de retenção explícita |
| release local reproduzível | SIM | MANTER/REDESENHAR | lint + testes + package |
| vetor/semantic retrieval | OPCIONAL | EVOLUIR COM IA/VETOR | nunca derrubar lexical |
| IA para curadoria | OPCIONAL | EVOLUIR COM IA/VETOR | IA sugere, humano decide |
| IA para síntese | OPCIONAL | EVOLUIR COM IA/VETOR | retrieval precede síntese |

## 2. Gerenciador de Resumo Executivo → produto futuro

| Capacidade/contrato | Paridade exigida | Direção | Gate futuro |
|---|---|---|---|
| exatamente oito valores GRE conhecidos | SIM | MANTER semântica/compatibilidade | migração/compat + testes WP |
| título via `post_title` | SIM | MANTER | nenhuma `_bdc_es_title` |
| `_bdc_es_title` | NÃO | DESCARTAR/proibir | architecture guard |
| Metadata API | SIM | SUBSTITUIR POR WORDPRESS/MANTER | integração WordPress real |
| `show_in_rest=false` por default | SIM enquanto não houver API | MANTER | security regression |
| leitura side-effect free | SIM | MANTER | read nunca escreve |
| meta ausente => `''` em projection | SIM | MANTER | unit/integration |
| vazio canônico => delete meta | SIM | MANTER | integration |
| allowlist de payload | SIM | MANTER | fail-closed |
| sanitização centralizada | SIM | MANTER | unit/integration |
| capability `edit_post` por objeto | SIM | MANTER | security integration |
| nonce por post/ação | SIM | MANTER | CSRF test |
| read-after-write | SIM | MANTER | persistence failure tests |
| update parcial | SIM | MANTER | omitted fields intact |
| atomicidade multi-campo | PROVÁVEL | REDESENHAR/ENDURECER | teste de falha tardia |
| Admin server-rendered | SIM como baseline | MANTER/REDESENHAR UI | KB2Ops DS |
| Coverage Dashboard | SIM | MANTER comportamento / REDESENHAR workload | bounded performance |
| scan `posts_per_page=-1` | NÃO | REDESENHAR | benchmark/bounds |
| `[bdc_resumo_executivo]` | SIM como compatibilidade inicial | MANTER/REDESENHAR | preflight de consumidores |
| shortcode selecionar post arbitrário | NÃO | DESCARTAR/proibir | security regression |
| side panel automático | AINDA NÃO SABEMOS | AINDA NÃO SABEMOS | decisão UX após KB2Ops |
| frontend sem JS | PREFERÍVEL | MANTER simplicidade | adicionar JS só se comportamento exigir |
| CSS GRE atual | NÃO como DS final | REDESENHAR | tokens/componentes KB2Ops |
| `Objective_Provider` externo | NÃO | DESCARTAR como bridge; substituir por serviço interno | integration contract |
| evento de Objective alterado | SIM | MANTER intenção / REDESENHAR nome/payload | post-write -> reindex test |
| `bdc_es_loaded` | AINDA NÃO SABEMOS | compatibilidade apenas | consumidor real |
| tabela própria de resumo | NÃO | DESCARTAR | WP meta atende |
| REST próprio de resumo | NÃO no baseline | DESCARTAR | só com consumidor/benefício |
| AJAX próprio de resumo | NÃO no baseline | DESCARTAR | admin-post atende |
| cron próprio de resumo | NÃO | DESCARTAR | sem workload periódico |
| options/transients GRE | NÃO | DESCARTAR enquanto não necessários | princípio de negação |
| metadata revisions off | AINDA NÃO SABEMOS | AINDA NÃO SABEMOS | requisitos de histórico |
| integration tests WP real | SIM | MANTER | gate obrigatório |
| deterministic ZIP + SHA | SIM | MANTER | reproducible release |

## 3. Matriz WordPress-first preliminar

| Necessidade | Primitive WordPress a avaliar primeiro | Infra própria só se... |
|---|---|---|
| configuração | Options/Settings API | estado exceder perfil de configuração |
| metadata editorial por post | `register_post_meta` + Metadata API | relacionamento/consulta/volume não atender |
| classificação editorial reutilizável | taxonomias | necessidade for realmente classificatória e compartilhada |
| título | `post_title` | nunca duplicar em meta sem razão extraordinária |
| evento de mudança | actions/filters | integração externa exigir outro mecanismo |
| formulário admin | admin-post + nonces + capabilities | UX/realtime tiver requisito comprovado |
| saúde | Site Health | check precisar UI operacional especializada |
| scheduling | WP-Cron | durabilidade/job state exigir queue |
| cache | Object Cache/transients | backend especializado tiver benefício mensurável |
| autorização | roles/capabilities | nunca substituir sem necessidade |
| CSRF | nonces | sempre usar primitive WP na superfície WP |
| conteúdo | Posts + Elementor canônico | índice derivado é permitido, edição paralela não |
| busca derivada | WP_Query/native primeiro + projection própria se necessária | relevância/performance exigir |
| resumo executivo | post meta/taxonomia conforme semântica | tabela própria só com evidência contrária ao baseline GRE |
| dashboard de cobertura | query/cache nativos e workload limitado | materialização só após benchmark |

## 4. Contratos cross-module já exigidos

### Objective/Resumo Executivo → Search

O drift ASI↔GRE provou que o futuro contrato deve ser interno e explícito:

1. store canônico persiste metadata;
2. write é confirmado;
3. action de domínio é emitida;
4. index/projections recebem invalidação;
5. reindexação usa o Content Extractor canônico;
6. falha da projection não reescreve conteúdo editorial;
7. ausência de Objective continua vazia — sem síntese silenciosa do corpo.

### Elementor/content ownership

Nenhum componente de resumo, busca ou IA pode escrever `_elementor_data`. A representação para busca/RAG será derivada pelo extractor único a confirmar no KB2Ops.

## 5. Candidatos de evolução com IA/vetor

1. expansão semântica de consulta após normalização lexical;
2. candidate retrieval híbrido lexical+vetorial;
3. rerank semântico sobre shortlist limitada;
4. detecção assistida de lacunas/duplicidade;
5. sugestão de vocabulary/bindings/rules;
6. sugestão assistida de preenchimento do Resumo Executivo, **sem persistência automática**;
7. síntese/answering somente depois de retrieval com fonte.

Todos são **opcionais**. Queda de provider vetorial/IA não pode tornar a busca lexical indisponível. IA nunca deve editar Elementor ou persistir metadata editorial sem decisão humana explícita.

## 6. Itens que permanecem AINDA NÃO SABEMOS

- quais campos GRE classificatórios permanecem post meta versus viram taxonomia;
- storage final de vocabulary/bindings/rules/Golden Queries;
- necessidade concreta de tabela de queue;
- schema de post/item index;
- estratégia definitiva de anchors;
- AJAX versus REST para busca pública;
- coexistência/migração dos dados ASI instalados;
- necessidade de Search Intelligence por pessoa/equipe no produto final;
- política de query text e retenção;
- política de revisions/histórico das oito metas;
- side panel automático versus outra composição UI;
- MariaDB Vector/chunks/embedding store.

Esses itens não devem ser fechados até o bloco KB2Ops e o cruzamento T050–T059.
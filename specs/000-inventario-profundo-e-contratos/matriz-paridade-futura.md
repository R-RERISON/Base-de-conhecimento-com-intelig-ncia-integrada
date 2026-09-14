# Matriz de Paridade Futura — SPEC-000

> Matriz incremental. Nesta versão, registra a paridade mínima derivada do ASI. Não define implementação nem schema. Será cruzada com KB2Ops e Gerenciador de Resumo Executivo antes do gate final.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — preferir primitive nativa.
- **EVOLUIR COM IA/VETOR** — evolução opcional, degradável e governada.
- **DESCARTAR** — não levar ao greenfield.
- **AINDA NÃO SABEMOS** — falta evidência/cross-reference.

## ASI → produto futuro

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
| Objective canonical-only | SIM | MANTER | confirmar contrato GRE |
| legacy/compat | NÃO | DESCARTAR | só estratégia de migração separada se necessária |
| uninstall não destrutivo | SIM | MANTER | política de retenção explícita |
| release local reproduzível | SIM | MANTER/REDESENHAR | lint + testes + package |
| vetor/semantic retrieval | OPCIONAL | EVOLUIR COM IA/VETOR | nunca derrubar lexical |
| IA para curadoria | OPCIONAL | EVOLUIR COM IA/VETOR | IA sugere, humano decide |
| IA para síntese | OPCIONAL | EVOLUIR COM IA/VETOR | retrieval precede síntese |

## Matriz WordPress-first preliminar

| Necessidade | Primitive WordPress a avaliar primeiro | Infra própria só se... |
|---|---|---|
| configuração | Options/Settings API | estado exceder perfil de configuração |
| classificação editorial | taxonomias/post meta | consulta/volume/relacionamento não atender |
| evento de mudança | actions/filters | integração externa exigir outro mecanismo |
| saúde | Site Health | check precisar UI operacional especializada |
| scheduling | WP-Cron | durabilidade/job state exigir queue |
| cache | Object Cache/transients | backend especializado tiver benefício mensurável |
| autorização | roles/capabilities | nunca substituir sem necessidade |
| CSRF | nonces | sempre usar primitive WP na superfície WP |
| conteúdo | Posts + Elementor canônico | índice derivado é permitido, edição paralela não |
| busca derivada | WP_Query/native primeiro + projection própria se necessária | relevância/performance exigir |

## Candidatos de evolução com IA/vetor

1. expansão semântica de consulta após normalização lexical;
2. candidate retrieval híbrido lexical+vetorial;
3. rerank semântico sobre shortlist limitada;
4. detecção assistida de lacunas/duplicidade;
5. sugestão de vocabulary/bindings/rules;
6. síntese/answering somente depois de retrieval com fonte.

Todos são **opcionais**. Queda de provider vetorial/IA não pode tornar a busca lexical indisponível.

## Itens que permanecem AINDA NÃO SABEMOS

- storage final de vocabulary/bindings/rules/Golden Queries;
- necessidade concreta de tabela de queue;
- schema de post/item index;
- estratégia definitiva de anchors;
- AJAX versus REST para busca pública;
- coexistência/migração dos dados ASI instalados;
- necessidade de Search Intelligence por pessoa/equipe no produto final;
- política de query text e retenção;
- MariaDB Vector/chunks/embedding store.

Esses itens não devem ser fechados até os blocos KB2Ops, Gerenciador de Resumo Executivo e cruzamento T050–T059.
# Matriz de Paridade Futura — SPEC-000

> Matriz incremental após T050–T054 e T056. Define comportamentos que devem sobreviver, owners, primitives WordPress-first e políticas de compatibilidade já resolvidos. **Não define schema final nem encerra T059.** T057/T055/T058 ainda precisam ser concluídas.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — preferir primitive nativa.
- **EVOLUIR COM IA/VETOR** — opcional/degradável/governado.
- **DESCARTAR** — não levar ao greenfield.
- **AINDA NÃO SABEMOS** — depende de evidência/cutover ainda não fechado.

## 1. ASI -> produto futuro

| Capacidade | Paridade | Direção | Gate |
|---|---|---|---|
| busca lexical local | SIM | MANTER/REDESENHAR | Golden + performance |
| native search WP como fallback | SIM | MANTER | disponibilidade lexical mínima |
| QueryContext limitado | SIM | MANTER/REDESENHAR | unit + Golden |
| ranking explicável | SIM | MANTER | sinais observáveis |
| índice lexical por post | PROVÁVEL/NECESSÁRIO | **CANDIDATO T057** | extractor canônico + T057 |
| índice de trechos | SIM | **CANDIDATO T057** | identity/deep-link + T057 |
| parser direto `post_content` | NÃO | DESCARTAR | Content Extraction único |
| vocabulary/bindings/rules | SIM | MANTER em primitives WP inicialmente | owner Search Knowledge; volume/preflight |
| simulation + Apply humano | SIM | MANTER | capability/nonce/stale-state |
| durable queue | CONDICIONAL | **CANDIDATO T057** somente se workload exigir | T057 + B-007 |
| migrations/reconciler 4.x | NÃO | DESCARTAR | compat temporária separada |
| Search Events | CONDICIONAL/SIM mínimo | **CANDIDATO T057** se Analytics habilitado | B-004 + T057 |
| Interactions/Outcomes | CONDICIONAL | **CANDIDATO T057** mesma família de facts | B-004 + T057 |
| `quality_daily` | NÃO inicialmente | DESCARTAR | benchmark |
| Golden Queries | SIM | MANTER em `WP_Post` interno + metadata/revisions | NO-GO blockers |
| Quality Diagnostics | SIM | SUBSTITUIR POR WORDPRESS + checks | Site Health |
| GAC no core | NÃO | DESCARTAR | adapter somente com requisito |
| Word Cloud | OPCIONAL | REDESENHAR | product/preflight |
| live typing/debounce/cancel | SIM se UX exigir | MANTER via AJAX WP | E2E |
| AJAX ASI literal | NÃO | REDESENHAR | consumidor real |
| anchor injection literal | NÃO | REDESENHAR | B-005 |
| vetor/semantic | OPCIONAL | EVOLUIR COM IA/VETOR | lexical não cai |
| síntese/IA | OPCIONAL | EVOLUIR COM IA/VETOR | retrieval precede |

## 2. GRE -> produto futuro

| Capacidade | Paridade | Direção | Gate |
|---|---|---|---|
| oito valores históricos | SIM | MANTER semântica/compat | migration/preflight |
| título via `post_title` | SIM | MANTER | sem título duplicado |
| Summary narrativo em Metadata API | SIM | **MANTER WP** | WP integration |
| leitura side-effect free | SIM | MANTER | unit/integration |
| vazio -> delete meta | SIM | MANTER | integration |
| allowlist/sanitização | SIM | MANTER | fail-closed |
| `edit_post` + nonce | SIM | MANTER WP | security |
| read-after-write | SIM | MANTER | B-006 |
| update parcial | SIM | MANTER | omitted intact |
| atomicidade multi-campo | A DEFINIR | REDESENHAR/ENDURECER sem usar tabela como atalho | B-006 |
| Coverage | SIM | MANTER pergunta via `WP_Query` bounded | workload bounded |
| scan ilimitado | NÃO | DESCARTAR/REDESENHAR | benchmark |
| shortcode resumo | COMPAT | DEPENDE DE PREFLIGHT | B-003 |
| side panel automático | NÃO canônico | DESCARTAR como baseline | produto futuro |
| `Objective_Provider` | NÃO | DESCARTAR do core | adapter apenas coexistência |
| evento de mudança | SIM como intenção | REDESENHAR em Plugin API pós-write | persistência confirmada |
| tabela própria de Summary | NÃO | DESCARTAR | T056 fechou em Metadata API |
| REST/AJAX/cron próprios do Summary | NÃO | DESCARTAR | sem consumidor/workload |
| deterministic build/WP tests | SIM | MANTER | release |

### Ownership dos oito valores

- `objective`, `escalation`, `important` -> Resumo Executivo / Metadata API;
- `responsible_team`, `catalog_item`, `affected_service`, `systems_involved`, `target_audience` -> Classificação;
- UI pode compor sem duplicar ownership.

## 3. KB2Ops -> produto futuro

| Capacidade | Paridade | Direção | Gate |
|---|---|---|---|
| posts/Elementor fonte editorial | SIM | MANTER WP | zero write derivado |
| Content Extractor único | SIM | MANTER/REDESENHAR | **B-001** |
| parse determinístico + fallback | SIM | MANTER/REDESENHAR | corpus Elementor |
| allowlist de shortcodes | SIM princípio | MANTER | safety |
| parsers independentes | NÃO | DESCARTAR | extractor único |
| review states | SIM | MANTER em Metadata API | Revisão/Governança |
| review notes/reviewer/time | SIM | MANTER em Metadata/Users | WP integration |
| history bounded | SIM | MANTER inicialmente em Metadata API | schema/sanitização; reabrir só por requisito de audit transversal |
| `include_ai` | SIM | MANTER | decisão humana |
| AI READY | SIM | MANTER/REDESENHAR como derivado | regra única testada |
| Summary Bridge | NÃO | DESCARTAR do core | adapter só se cutover exigir |
| Search meta LIKE/token ranker | NÃO | DESCARTAR como motor | F-057-01 |
| Search scope/detail recheck | SIM | MANTER via primitives WP | bypass tests |
| portal/shortcodes | COMPAT | DEPENDE DE PREFLIGHT | B-003 |
| analytics option/view count | NÃO literal | DESCARTAR/REDESENHAR | F-057-02 condicional |
| Reports/perguntas | SIM | MANTER perguntas via queries bounded | bounds/privacy |
| DS principles | SIM | MANTER/REDESENHAR | DS próprio |
| server rendering | SIM baseline | MANTER | JS enhancement |
| SPA/framework externo | NÃO | DESCARTAR | negação |
| activation/uninstall reversíveis | SIM | MANTER princípio | lifecycle tests |
| hardcodes cleanup legado | NÃO | DESCARTAR | histórico |
| deterministic build | SIM | MANTER | package |

## 4. Classificação — primitive T056

| Conceito | Primitive preferida após T056 | Estado |
|---|---|---|
| audiência | Taxonomy API | MANTER WP; cutover B-002 |
| tipo de conhecimento | Taxonomy API | MANTER WP; cutover B-002 |
| serviço | Taxonomy API | MANTER WP; distinto de `affected_service` |
| tecnologias | Taxonomy API | MANTER WP; distinto de `systems_involved` |
| equipe responsável | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002/D-005 |
| item de catálogo | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002 |
| serviço afetado | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002 |
| sistemas envolvidos | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002 |
| keywords | Metadata API | MANTER WP no baseline |
| versões | Metadata API | MANTER WP no baseline |

Os quatro unknowns acima **não entram em T057**: a dúvida ainda está entre primitives do próprio WordPress.

## 5. Compatibilidade — estado T054 preservado

### Corrigidos pela arquitetura futura

- D-001 Objective Provider -> Summary Store interno;
- D-002 hook Objective -> evento pós-write confirmado;
- D-003/D-004 -> Content Extraction único;
- D-006 ownership classificatório -> domínio único;
- D-007 fragmentação visual -> DS único;
- D-008 AI READY -> regra canônica testada.

### Compat temporária somente se comprovada

- adapter `Objective_Provider` para ASI legado;
- tradução para `bdc_es_objective_updated` se ASI legado coexistir;
- aliases de shortcodes históricos;
- hooks `bdc_es_loaded`/`kb2ops_loaded`;
- bridge de meta durante cutover.

Todo compat precisa de consumidor, observabilidade e gate de remoção. Dual-write permanente é proibido.

### Dependem de preflight/profiling

- GAC;
- shortcodes e Word Cloud;
- service vs affected_service;
- technologies vs systems_involved;
- valores/cardinalidade de classificações;
- anchors/deep-links.

## 6. Blockers contextuais T054

- **B-001:** extractor completo para Search/RAG.
- **B-002:** profiling para migração classificatória.
- **B-003:** preflight para retirada de plugins/aliases.
- **B-004:** política de telemetria antes de Analytics detalhado.
- **B-005:** anchors antes de paridade Item Knowledge.
- **B-006:** falha multi-campo antes de write path composto definitivo.
- **B-007:** stale/diagnóstico antes de async indexing/queue.

Eles não bloqueiam automaticamente todo e qualquer primeiro slice; aplicabilidade deve ser declarada na SPEC correspondente.

## 7. Matriz WordPress-first final de T056

| Necessidade | Primitive decidida/avaliada | Resultado |
|---|---|---|
| conteúdo/título | `WP_Post` + Elementor | MANTER WP |
| atributos narrativos locais | registered post meta | MANTER WP |
| review/governança | Metadata + Users + Revisions quando aplicável | MANTER WP |
| classificação reutilizável comprovada | Taxonomy API | MANTER WP |
| classificação sem faceta comprovada | Metadata API primeiro | MANTER WP / profiling |
| settings | Settings/Options API | SUBSTITUIR_POR_WORDPRESS |
| revisor/identidade | Users/Capabilities | MANTER WP |
| mutação admin | `admin-post` + nonce + capability | MANTER WP |
| live interaction | AJAX WP | somente se necessidade real |
| REST | WP REST | nenhum consumidor atual; não criar |
| scheduling | WP-Cron | trigger; MANTER WP |
| cache | Transients/Object Cache | MANTER WP; efêmero |
| health | Site Health | SUBSTITUIR_POR_WORDPRESS |
| histórico bounded | registered metadata; Revisions avaliadas | MANTER WP |
| Search Knowledge | `WP_Post` interno + metadata/revisions | MANTER WP inicialmente |
| Golden | `WP_Post` interno + metadata/revisions | MANTER WP inicialmente |
| native search | `WP_Query` | MANTER fallback; insuficiente para paridade |
| Search lexical/items | Core insuficiente para contrato completo | F-057-01 |
| Analytics facts | Core inadequado se stream detalhado existir | F-057-02 condicional |
| durable queue state | WP-Cron não fornece durabilidade de worker | F-057-03 condicional |

## 8. Candidatos exclusivos para T057

1. **F-057-01 — Search Retrieval Projection**: lexical post + item/identity pesquisável.
2. **F-057-02 — Analytics Facts**: somente após B-004 e prova de requisito/volume.
3. **F-057-03 — Durable Job State**: somente se index/rebuild assíncrono exigir lease/retry/dead/recovery.

Nenhum schema/tabela foi escolhido. T057 pode matar qualquer um desses candidatos.

## 9. Contratos cross-module

`WP/Elementor -> Content Extraction -> lexical/items -> optional semantic -> Search/IA`

`validar -> persistir -> confirmar -> evento -> invalidar/rebuild projection`

- scope/permissão precedem exposição;
- Analytics não derruba Search;
- approval de artigo não aplica Search Knowledge;
- qualidade de conteúdo não é Search Quality.

## 10. Próximos fechamentos

1. **T057** — infraestrutura própria mínima justificada.
2. **T055** — regressão/Golden final.
3. **T058** — IA/vetor priorizados.
4. **T059** — paridade final.

**T059 permanece aberta. Nenhum runtime foi autorizado.**

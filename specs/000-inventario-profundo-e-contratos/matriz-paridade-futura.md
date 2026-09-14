# Matriz de Paridade Futura — SPEC-000

> Matriz incremental após T050–T054. Define comportamentos que devem sobreviver, owners e políticas de compatibilidade já resolvidos. **Não define schema final nem encerra T059.** T056/T057/T055/T058 ainda precisam ser concluídas.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — preferir primitive nativa.
- **EVOLUIR COM IA/VETOR** — opcional/degradável/governado.
- **DESCARTAR** — não levar ao greenfield.
- **AINDA NÃO SABEMOS** — depende de primitive/medição/cutover ainda não fechado.

## 1. ASI -> produto futuro

| Capacidade | Paridade | Direção | Gate |
|---|---|---|---|
| busca lexical local | SIM | MANTER/REDESENHAR | Golden + performance |
| fallback sem FULLTEXT | SIM | MANTER | disponibilidade lexical |
| QueryContext limitado | SIM | MANTER/REDESENHAR | unit + Golden |
| ranking explicável | SIM | MANTER | sinais observáveis |
| índice por post | PROVÁVEL | REDESENHAR | extractor canônico |
| índice de trechos | SIM | REDESENHAR | identity/deep-link |
| parser direto `post_content` | NÃO | DESCARTAR | Content Extraction único |
| vocabulary/bindings/rules | SIM | MANTER | owner Search Knowledge; storage T056/T057 |
| simulation + Apply humano | SIM | MANTER | capability/nonce/stale-state |
| durable queue | CONDICIONAL | REDESENHAR | T057: workload/durabilidade |
| migrations/reconciler 4.x | NÃO | DESCARTAR | compat temporária separada |
| Search Events | SIM mínimo | REDESENHAR | privacy/retention |
| Interactions/Outcomes | CONDICIONAL/SIM | REDESENHAR | T057 + privacy |
| `quality_daily` | NÃO inicialmente | DESCARTAR | benchmark |
| Golden Queries | SIM | MANTER | NO-GO blockers |
| Quality Diagnostics | SIM | SUBSTITUIR POR WORDPRESS + checks | Site Health |
| GAC no core | NÃO | DESCARTAR | adapter somente com requisito |
| Word Cloud | OPCIONAL | REDESENHAR | product/preflight |
| live typing/debounce/cancel | SIM se UX exigir | MANTER | E2E |
| AJAX ASI literal | NÃO | REDESENHAR | consumidor real |
| anchor injection literal | NÃO | REDESENHAR | B-005 |
| vetor/semantic | OPCIONAL | EVOLUIR COM IA/VETOR | lexical não cai |
| síntese/IA | OPCIONAL | EVOLUIR COM IA/VETOR | retrieval precede |

## 2. GRE -> produto futuro

| Capacidade | Paridade | Direção | Gate |
|---|---|---|---|
| oito valores históricos | SIM | MANTER semântica/compat | migration/preflight |
| título via `post_title` | SIM | MANTER | sem título duplicado |
| Metadata API | SIM | MANTER/WP-first | WP integration |
| leitura side-effect free | SIM | MANTER | unit/integration |
| vazio -> delete meta | SIM | MANTER | integration |
| allowlist/sanitização | SIM | MANTER | fail-closed |
| `edit_post` + nonce | SIM | MANTER | security |
| read-after-write | SIM | MANTER | B-006 |
| update parcial | SIM | MANTER | omitted intact |
| atomicidade multi-campo | A DEFINIR | REDESENHAR/ENDURECER | B-006 |
| Coverage | SIM | MANTER pergunta | workload bounded |
| scan ilimitado | NÃO | DESCARTAR/REDESENHAR | benchmark |
| shortcode resumo | COMPAT | DEPENDE DE PREFLIGHT | B-003 |
| side panel automático | NÃO canônico | DESCARTAR como baseline | produto futuro |
| `Objective_Provider` | NÃO | DESCARTAR do core | adapter apenas coexistência |
| evento de mudança | SIM como intenção | REDESENHAR | pós-write confirmado |
| tabela/REST/AJAX/cron próprios | NÃO | DESCARTAR | só novo requisito |
| deterministic build/WP tests | SIM | MANTER | release |

### Ownership dos oito valores

- `objective`, `escalation`, `important` -> Resumo Executivo;
- `responsible_team`, `catalog_item`, `affected_service`, `systems_involved`, `target_audience` -> Classificação;
- UI pode compor sem duplicar ownership.

## 3. KB2Ops -> produto futuro

| Capacidade | Paridade | Direção | Gate |
|---|---|---|---|
| posts/Elementor fonte editorial | SIM | MANTER | zero write derivado |
| Content Extractor único | SIM | MANTER/REDESENHAR | **B-001** |
| parse determinístico + fallback | SIM | MANTER/REDESENHAR | corpus Elementor |
| allowlist de shortcodes | SIM princípio | MANTER | safety |
| parsers independentes | NÃO | DESCARTAR | extractor único |
| review states | SIM | MANTER | Revisão/Governança |
| knowledge type/classificações | SIM | MANTER semântica | T056 + B-002 |
| review notes/reviewer/time | SIM | MANTER | WP primitives |
| history bounded | SIM intenção | REDESENHAR | T056/T095 |
| `include_ai` | SIM | MANTER | decisão humana |
| AI READY | SIM | MANTER/REDESENHAR | regra única testada |
| Summary Bridge | NÃO | DESCARTAR do core | adapter só se cutover exigir |
| Search meta LIKE/token ranker | NÃO | DESCARTAR como motor | engine ASI-inspired |
| Search scope/detail recheck | SIM | MANTER | bypass tests |
| portal/shortcodes | COMPAT | DEPENDE DE PREFLIGHT | B-003 |
| analytics option/view count | NÃO literal | DESCARTAR/REDESENHAR | T057 |
| Reports/perguntas | SIM | MANTER perguntas | bounds/privacy |
| DS principles | SIM | MANTER/REDESENHAR | DS próprio |
| server rendering | SIM baseline | MANTER | JS enhancement |
| SPA/framework externo | NÃO | DESCARTAR | negação |
| activation/uninstall reversíveis | SIM | MANTER princípio | lifecycle tests |
| hardcodes cleanup legado | NÃO | DESCARTAR | histórico |
| deterministic build | SIM | MANTER | package |

## 4. Compatibilidade — estado T054

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

## 5. Blockers contextuais T054

- **B-001:** extractor completo para Search/RAG.
- **B-002:** profiling para migração classificatória.
- **B-003:** preflight para retirada de plugins/aliases.
- **B-004:** política de telemetria antes de Analytics detalhado.
- **B-005:** anchors antes de paridade Item Knowledge.
- **B-006:** falha multi-campo antes de write path composto definitivo.
- **B-007:** stale/diagnóstico antes de async indexing/queue.

Eles não bloqueiam automaticamente todo e qualquer primeiro slice; aplicabilidade deve ser declarada na SPEC correspondente.

## 6. Matriz WordPress-first preliminar

| Necessidade | Primitive a avaliar em T056 | Infra própria só se... |
|---|---|---|
| conteúdo/título | `WP_Post` + Elementor | nunca duplicar edição |
| atributos locais | `register_post_meta` | semântica/consulta não atender |
| classificação reutilizável | taxonomy/terms | profiling mostrar inadequação |
| settings | Settings/Options API | não for configuração |
| revisor/identidade | Users/Capabilities | nunca reinventar usuário |
| mutação admin | admin-post + nonce | live UX exigir outro canal |
| live interaction | AJAX WP | necessidade real |
| REST | nenhum default | consumidor formal |
| scheduling | WP-Cron trigger | durabilidade exigir queue |
| cache | transient/object cache | benefício medido |
| health | Site Health | ação especializada exigir tela |
| histórico | revisions/meta | consulta/retenção exigir mais |
| search | native primeiro, projection se insuficiente | Golden/performance justificarem |

## 7. Contratos cross-module

`WP/Elementor -> Content Extraction -> lexical/items -> optional semantic -> Search/IA`

`validar -> persistir -> confirmar -> evento -> invalidar/rebuild projection`

- scope/permissão precedem exposição;
- Analytics não derruba Search;
- approval de artigo não aplica Search Knowledge;
- qualidade de conteúdo não é Search Quality.

## 8. Próximos fechamentos

1. **T056** — primitive WordPress por campo/capacidade.
2. **T057** — infraestrutura própria que sobrar.
3. **T055** — regressão/Golden final.
4. **T058** — IA/vetor priorizados.
5. **T059** — paridade final.

**T059 permanece aberta. Nenhum runtime foi autorizado.**
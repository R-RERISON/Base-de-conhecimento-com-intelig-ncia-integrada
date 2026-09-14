# Matriz de Paridade Futura — SPEC-000

> Matriz incremental após T050–T054, T056 e T057. Define comportamentos que devem sobreviver, owners, primitives WordPress-first, infraestrutura própria mínima e políticas de compatibilidade já resolvidos. **Não encerra T059.** T055/T058 ainda precisam ser concluídas.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — preferir primitive nativa.
- **INFRA PRÓPRIA MÍNIMA** — Core foi insuficiente para workload comprovado; extensão própria limitada foi justificada.
- **EVOLUIR COM IA/VETOR** — opcional/degradável/governado.
- **DESCARTAR** — não levar ao greenfield.
- **POSTERGAR** — não comprou complexidade no baseline; reabrir somente por requisito/evidência.
- **AINDA NÃO SABEMOS** — depende de evidência/cutover ainda não fechado.

## 1. ASI -> produto futuro

| Capacidade | Paridade | Direção após T057 | Gate |
|---|---|---|---|
| busca lexical local | SIM | MANTER/REDESENHAR | Golden + performance |
| native search WP como fallback | SIM | MANTER | disponibilidade lexical mínima |
| QueryContext limitado | SIM | MANTER/REDESENHAR | unit + Golden |
| ranking explicável | SIM | MANTER | sinais observáveis |
| índice lexical por post | SIM | **INFRA PRÓPRIA MÍNIMA** dentro de projection unificada | B-001 + Golden + benchmark |
| índice de trechos/items | SIM | **INFRA PRÓPRIA MÍNIMA** no mesmo store lógico do post | identity; B-005 apenas para deep-link público |
| duas tabelas `search_index` + `search_items` | NÃO como requisito | DESCARTAR no baseline; tentar store unificado | benchmark futuro se unificado falhar |
| parser direto `post_content` | NÃO | DESCARTAR | Content Extraction único |
| vocabulary/bindings/rules | SIM | MANTER em primitives WP inicialmente | owner Search Knowledge; volume/preflight |
| simulation + Apply humano | SIM | MANTER | capability/nonce/stale-state |
| durable queue | CONDICIONAL | **POSTERGAR** | benchmark real + B-007 |
| migrations/reconciler 4.x | NÃO | DESCARTAR | compat temporária separada |
| Search Events | CONDICIONAL | **POSTERGAR** | B-004 + finalidade/retention/volume |
| Interactions/Outcomes | CONDICIONAL | **POSTERGAR** | B-004 + requisito real |
| `quality_daily` | NÃO inicialmente | DESCARTAR | benchmark |
| Golden Queries | SIM | MANTER em `WP_Post` interno + metadata/revisions | suíte ativa; NO-GO blockers |
| Quality Diagnostics | SIM | SUBSTITUIR POR WORDPRESS + checks | Site Health |
| GAC no core | NÃO | DESCARTAR | adapter somente com requisito |
| Word Cloud | OPCIONAL | REDESENHAR/POSTERGAR | product/preflight |
| live typing/debounce/cancel | SIM se UX exigir | MANTER via AJAX WP | E2E |
| AJAX ASI literal | NÃO | REDESENHAR | consumidor real |
| anchor injection literal | NÃO | REDESENHAR | B-005 |
| vetor/semantic | OPCIONAL | EVOLUIR COM IA/VETOR | T058; lexical não cai |
| síntese/IA | OPCIONAL | EVOLUIR COM IA/VETOR | T058; retrieval precede |

## 2. GRE -> produto futuro

| Capacidade | Paridade | Direção | Gate |
|---|---|---|---|
| oito valores históricos | SIM | MANTER semântica/compat | migration/preflight |
| título via `post_title` | SIM | MANTER | sem título duplicado |
| Summary narrativo em Metadata API | SIM | MANTER WP | WP integration |
| leitura side-effect free | SIM | MANTER | unit/integration |
| vazio -> delete meta | SIM | MANTER | integration |
| allowlist/sanitização | SIM | MANTER | fail-closed |
| `edit_post` + nonce | SIM | MANTER WP | security |
| read-after-write | SIM | MANTER | B-006 |
| update parcial | SIM | MANTER | omitted intact |
| atomicidade multi-campo | A DEFINIR | REDESENHAR/ENDURECER sem tabela como atalho | B-006 |
| Coverage | SIM | MANTER pergunta via `WP_Query` bounded | workload bounded |
| scan ilimitado | NÃO | DESCARTAR/REDESENHAR | benchmark |
| shortcode resumo | COMPAT | DEPENDE DE PREFLIGHT | B-003 |
| side panel automático | NÃO canônico | DESCARTAR como baseline | produto futuro |
| `Objective_Provider` | NÃO | DESCARTAR do core | adapter apenas coexistência |
| evento de mudança | SIM como intenção | REDESENHAR em Plugin API pós-write | persistência confirmada |
| tabela própria de Summary | NÃO | DESCARTAR | Metadata API suficiente |
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
| history bounded | SIM | MANTER inicialmente em Metadata API | reabrir só por requisito transversal |
| `include_ai` | SIM | MANTER | decisão humana |
| AI READY | SIM | MANTER/REDESENHAR como derivado | regra única testada |
| Summary Bridge | NÃO | DESCARTAR do core | adapter só se cutover exigir |
| Search meta LIKE/token ranker | NÃO | DESCARTAR como motor | projection lexical unificada |
| Search scope/detail recheck | SIM | MANTER via primitives WP | bypass tests |
| portal/shortcodes | COMPAT | DEPENDE DE PREFLIGHT | B-003 |
| analytics option/view count | NÃO literal | DESCARTAR/POSTERGAR | B-004 |
| Reports/perguntas | SIM | MANTER perguntas quando política permitir | bounds/privacy |
| DS principles | SIM | MANTER/REDESENHAR | DS próprio |
| server rendering | SIM baseline | MANTER | JS enhancement |
| SPA/framework externo | NÃO | DESCARTAR | negação |
| activation/uninstall reversíveis | SIM | MANTER princípio | lifecycle tests |
| hardcodes cleanup legado | NÃO | DESCARTAR | histórico |
| deterministic build | SIM | MANTER | package |

## 4. Classificação — primitive T056

| Conceito | Primitive preferida | Estado |
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

Os quatro unknowns acima não justificam infraestrutura própria.

## 5. Infraestrutura própria — decisão T057

### Aprovada

**Search Retrieval Projection unificada**:

`WP/Elementor + Summary/Classificação canônicos -> Content Extractor -> [store lexical derivado post|item] -> ranker -> revalidação WP -> resultado`

Requisitos:

- um único store lógico inicial para `post|item`;
- identidade estável;
- texto derivado normalizado;
- hash/version/generation/freshness;
- FULLTEXT quando suportado;
- fallback lexical bounded;
- rebuild idempotente;
- estado degraded/stale;
- nenhuma autoridade editorial no índice.

### Não aprovada no baseline

**Analytics Facts**:

- sem tabela events/interactions/outcomes;
- sem query text silencioso;
- reabrir somente após B-004 + requisito/volume/retention.

**Durable Job State**:

- sem queue table;
- primeiro testar processamento síncrono/bounded + rebuild manual/batched;
- WP-Cron pode disparar trabalho, mas não vira durable state;
- reabrir somente após benchmark + B-007.

### Infra histórica deliberadamente não reproduzida

- tables de vocabulary/bindings/rules;
- Golden table;
- audit table genérica;
- `quality_daily`;
- migration registry permanente;
- múltiplos stores de Search quando um store unificado atender.

## 6. Compatibilidade — estado T054 preservado

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

## 7. Blockers contextuais

- **B-001:** extractor completo antes de implementar Search final.
- **B-002:** profiling para migração classificatória.
- **B-003:** preflight para retirada de plugins/aliases.
- **B-004:** mantém Analytics detalhado postergado.
- **B-005:** anchors antes de paridade completa de deep-link; não impede item lexical sem link público.
- **B-006:** falha multi-campo antes de write path composto definitivo.
- **B-007:** queue/async permanece postergada até stale/diagnóstico + necessidade real.

## 8. Contratos cross-module

`WP/Elementor -> Content Extraction -> lexical/items -> optional semantic -> Search/IA`

`validar -> persistir -> confirmar -> evento -> invalidar/rebuild projection`

- scope/permissão precedem exposição;
- Search projection falha sem alterar canônico;
- Analytics não derruba Search;
- approval de artigo não aplica Search Knowledge;
- qualidade de conteúdo não é Search Quality.

## 9. Próximos fechamentos

1. **T055** — regressão/Golden final alinhado a T056/T057.
2. **T058** — IA/vetor priorizados.
3. **T059** — paridade final.

**T059 permanece aberta. Nenhum runtime foi autorizado.**
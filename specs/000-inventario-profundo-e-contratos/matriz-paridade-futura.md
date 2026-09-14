# Matriz de Paridade Futura — SPEC-000

> Matriz incremental após T050–T057, incluindo T055. Define comportamentos que devem sobreviver, owners, primitives WordPress-first, infraestrutura própria mínima e gates de regressão. **T059 continua aberta** até incorporar T058 e fechar paridade final.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — primitive nativa vence.
- **INFRA PRÓPRIA MÍNIMA** — extensão própria justificada.
- **POSTERGAR** — não comprou complexidade no baseline.
- **EVOLUIR COM IA/VETOR** — opcional/degradável; T058 decide.
- **AINDA NÃO SABEMOS** — depende de evidência/preflight/profiling.

## 1. Search/ASI -> produto futuro

| Capacidade | Direção | Gate T055 |
|---|---|---|
| busca lexical local | MANTER/REDESENHAR | G-010, G-050, G-060, Golden, G-070, G-120 |
| native WP search | MANTER como fallback | G-060/G-070 |
| QueryContext/normalização | MANTER/REDESENHAR | G-060 |
| ranking explicável | MANTER | G-060 + Golden |
| projection lexical por post | INFRA PRÓPRIA MÍNIMA | G-050 |
| item/trecho pesquisável | INFRA PRÓPRIA MÍNIMA no mesmo store lógico | G-050 + B-005 para deep-link público |
| stores separados post/item | DESCARTAR no baseline | nova prova/benchmark se necessário |
| parsers diretos concorrentes | DESCARTAR | G-010 |
| vocabulary/bindings/rules | MANTER em primitives WP inicialmente | G-060/curadoria futura |
| simulation + Apply humano | MANTER | capability/nonce/stale-state |
| Golden Queries | MANTER em WP_Post interno + meta/revisions | Golden T055 |
| Search Events/Interactions/Outcomes | POSTERGAR | G-080 + B-004 |
| durable queue | POSTERGAR | G-090 + B-007 se reaberta |
| `quality_daily` | DESCARTAR inicialmente | benchmark/requisito futuro |
| Site Health/diagnóstico | SUBSTITUIR POR WORDPRESS + checks | G-130/DoD |
| GAC no core | DESCARTAR | adapter só por requisito/preflight |
| Word Cloud | POSTERGAR/REDESENHAR | produto/preflight |
| live typing | CONDICIONAL via AJAX WP | G-070/G-110 |
| REST | não criar sem consumidor | G-070 |
| anchors/deep-link | REDESENHAR | B-005 + browser/E2E |
| vetor/semantic | EVOLUIR COM IA/VETOR | T058 + G-140 |
| síntese/IA | EVOLUIR COM IA/VETOR | T058 + G-140 |

## 2. GRE -> produto futuro

| Capacidade | Direção | Gate T055 |
|---|---|---|
| oito valores históricos | MANTER semântica/compat | G-020/G-030/G-100 |
| título via `post_title` | MANTER | G-001 |
| Summary narrativo | MANTER em Metadata API | G-020 |
| leitura side-effect free | MANTER | G-020 |
| empty-delete | MANTER | G-020 |
| allowlist/sanitização | MANTER | G-020/G-070 |
| `edit_post` + nonce | MANTER WP | G-070 |
| read-after-write | MANTER | G-020/B-006 |
| update parcial | MANTER | G-020 |
| falha multi-campo | REDESENHAR/endurecer | B-006 + G-020 |
| Coverage | MANTER bounded | G-120 |
| scan ilimitado | DESCARTAR | G-120 |
| shortcode resumo | COMPAT condicional | G-100/B-003 |
| side panel automático | DESCARTAR como baseline | produto futuro |
| `Objective_Provider` legado | DESCARTAR do core | adapter só coexistência |
| evento de mudança | REDESENHAR pós-write confirmado | G-040 |
| tabela Summary | DESCARTAR | WordPress-first |
| REST/AJAX/cron próprios | DESCARTAR sem consumidor/workload | G-070/G-090 |
| build determinístico | MANTER | G-130 |

## 3. KB2Ops -> produto futuro

| Capacidade | Direção | Gate T055 |
|---|---|---|
| WP/Elementor fonte editorial | MANTER | G-001 |
| Content Extractor único | MANTER/REDESENHAR | G-010/B-001 |
| fallback/allowlist shortcode | MANTER princípio | G-010 |
| review states/notes/reviewer | MANTER WP | G-040 |
| history bounded | MANTER inicialmente | G-040 |
| `include_ai` | MANTER | G-040 |
| AI READY | MANTER como derivado | G-040 |
| Summary Bridge | DESCARTAR do core | G-100 se coexistência |
| Search meta LIKE | DESCARTAR | G-050/G-060 |
| Search scope/detail recheck | MANTER | G-070 |
| portal/shortcodes | COMPAT condicional | G-100/B-003 |
| analytics option/view count | DESCARTAR/POSTERGAR | G-080/B-004 |
| DS principles | MANTER/REDESENHAR | G-110 |
| server rendering | MANTER baseline | G-110 |
| SPA/framework externo | DESCARTAR | princípio de negação |
| activation/uninstall reversíveis | MANTER | G-130 |
| deterministic build | MANTER | G-130 |

## 4. Classificação WordPress-first

| Conceito | Primitive preferida | Estado/Gate |
|---|---|---|
| audiência | Taxonomy API | G-030 + B-002 cutover |
| tipo de conhecimento | Taxonomy API | G-030 + B-002 |
| serviço | Taxonomy API | distinto de affected_service |
| tecnologias | Taxonomy API | distinto de systems_involved |
| equipe responsável | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002/D-005 |
| item de catálogo | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002 |
| serviço afetado | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002 |
| sistemas envolvidos | Metadata ou Taxonomy | AINDA NÃO SABEMOS; B-002 |
| keywords | Metadata API | baseline |
| versions | Metadata API | baseline |

Nenhum desses unknowns justifica tabela própria.

## 5. Search Retrieval Projection — paridade aprovada

Fluxo futuro:

`WP/Elementor + Summary/Classificação -> Content Extractor -> projection lexical post|item -> ranker -> revalidação WordPress -> resultado`

Contratos:

- um store lógico inicial;
- identidade estável;
- texto derivado normalizado;
- hash/version/generation/freshness;
- FULLTEXT quando suportado;
- fallback bounded;
- rebuild idempotente;
- estado stale/degraded observável;
- zero autoridade editorial.

Gates: **G-001 + G-010 + G-050 + G-060 + Golden + G-070 + G-120 + G-130**.

## 6. Golden Queries — paridade de qualidade

Direção após T055:

- QA governada em primitives WP, não telemetria;
- suite ativa não pode estar vazia para release inicial/alteração de Search;
- expectation inclui target/rank/severity;
- `blocking` failure = NO-GO;
- warning exige decisão explícita;
- evidence stale/not-run/not-configured não é PASS;
- run evidence referencia conjunto, rankers, extractor/index e dataset/projection;
- execução explícita/read-only;
- independente de Analytics e identidade do usuário.

Golden PASS é necessária para Search afetada, mas não suficiente: B-001, security e benchmark permanecem independentes.

## 7. Capacidades postergadas protegidas por gates negativos

### Analytics detalhado

- sem events/interactions/outcomes por default;
- sem query text silenciosa;
- Search não depende de Analytics;
- reabrir somente após B-004.

Gate: **G-080**.

### Durable queue

- baseline sem queue;
- WP-Cron somente trigger;
- Options/Transients não são durable store;
- activation não inicia rebuild massivo;
- reabrir somente após benchmark + B-007.

Gate: **G-090**.

## 8. Compatibilidade

Todo compat temporário exige consumidor, owner, modo limitado, observabilidade, rollback, remoção e regressão de equivalência.

Gate: **G-100 + B-003**.

Nenhum alias histórico está aprovado automaticamente.

## 9. Release evidence

Estados não podem ser colapsados:

- PASS;
- FAIL;
- NOT_VERIFIED;
- NOT_CONFIGURED;
- DEGRADED;
- N/A justificado.

Gate de release futuro deve incluir build/commit, ambiente, testes, Golden run, benchmarks aplicáveis, gaps/waivers e rollback.

## 10. IA/vetor — aberto para T058

Invariantes já fixados:

- lexical continua funcional sem IA/vetor;
- retrieval precede síntese;
- IA sugere, humano decide;
- provider não vira owner de domínio;
- custo/NO_CHANGE/fallback/rastreabilidade precisam ser tratados em T058.

## 11. Próximos fechamentos

1. **T058** — IA/vetor priorizados.
2. **T059** — paridade futura final incorporando gates de IA.
3. T090–T097 — revisões e autorização.

**T059 permanece aberta. Nenhum runtime foi autorizado.**

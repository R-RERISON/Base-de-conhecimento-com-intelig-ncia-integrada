# Matriz de Paridade Futura — SPEC-000

> Matriz incremental após inventário das três referências e conclusão de T050–T053. Define comportamentos que devem sobreviver, owners funcionais e contratos de persistência/integração já consolidados. **Não define schema final nem encerra T059.** T054/T056/T057/T055/T058 ainda precisam ser concluídas.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — preferir primitive nativa.
- **EVOLUIR COM IA/VETOR** — opcional/degradável/governado.
- **DESCARTAR** — não levar ao greenfield.
- **AINDA NÃO SABEMOS** — depende de primitive/medição/cutover ainda não fechado.

## 1. ASI -> produto futuro

| Capacidade | Paridade | Direção | Gate futuro |
|---|---|---|---|
| busca lexical local | SIM | MANTER/REDESENHAR | Golden + performance |
| fallback sem FULLTEXT | SIM | MANTER | disponibilidade lexical |
| QueryContext limitado | SIM | MANTER/REDESENHAR | unit + Golden |
| ranking explicável | SIM | MANTER | sinais/scores observáveis |
| índice por post | PROVÁVEL | REDESENHAR | extractor canônico |
| índice de trechos | SIM | REDESENHAR | identity/deep link |
| parser direto `post_content` | NÃO | DESCARTAR | Content Extraction único |
| vocabulary | SIM | MANTER | owner Search Knowledge; storage T056/T057 |
| bindings | SIM | MANTER | owner Search Knowledge; storage T056/T057 |
| relevance rules | SIM | MANTER | simulation + audit |
| diagnóstico read-only | SIM | MANTER | sem side effect |
| suggestions evidence-backed | SIM | MANTER | confidence/effect/risk |
| simulation com ranker real | SIM | MANTER | stale-state guard |
| Apply humano | SIM | MANTER | capability + nonce; separado de review approval |
| durable queue | CONDICIONAL | REDESENHAR | workload/durabilidade |
| migrations 4.x/WPUI | NÃO | DESCARTAR | greenfield |
| post-install state machine completa | NÃO | REDESENHAR | mínimo necessário |
| Search Events | SIM, mínimo | REDESENHAR | privacy/retention |
| Interactions | CONDICIONAL | MANTER/REDESENHAR | HMAC/idempotência |
| Outcomes | SIM se analytics | MANTER | journey semantic |
| `quality_daily` | NÃO inicialmente | DESCARTAR | benchmark |
| Golden Queries | SIM | MANTER | owner Search Quality; NO-GO blockers |
| Quality Diagnostics | SIM | SUBSTITUIR POR WORDPRESS + checks | Site Health |
| Search Intelligence | PROVÁVEL | REDESENHAR | perguntas reais + bounds |
| GAC no core | NÃO | DESCARTAR | adapter opcional |
| Word Cloud | OPCIONAL | REDESENHAR | projections canônicas |
| live typing/debounce/abort | SIM para live UX | MANTER | E2E |
| AJAX ASI exato | NÃO | REDESENHAR se necessário | consumidor/UX |
| anchor injection atual | NÃO literal | REDESENHAR | deep-link contract |
| legacy/compat permanente | NÃO | DESCARTAR | migração separada |
| vetor/semantic retrieval | OPCIONAL | EVOLUIR COM IA/VETOR | lexical nunca cai |
| IA de curadoria | OPCIONAL | EVOLUIR COM IA/VETOR | humano decide |
| síntese | OPCIONAL | EVOLUIR COM IA/VETOR | retrieval precede |

## 2. Gerenciador de Resumo Executivo -> produto futuro

| Capacidade/contrato | Paridade | Direção | Gate |
|---|---|---|---|
| oito `_bdc_es_*` | SIM | MANTER compat/semântica | WP integration/migration |
| título via `post_title` | SIM | MANTER | sem `_bdc_es_title` |
| Metadata API | SIM | SUBSTITUIR POR WORDPRESS/MANTER | WP real |
| `show_in_rest=false` default | SIM até API justificada | MANTER | security |
| leitura sem side effect | SIM | MANTER | read nunca escreve |
| meta ausente -> vazio projection | SIM | MANTER | unit/integration |
| vazio -> delete meta | SIM | MANTER | integration |
| allowlist/sanitização | SIM | MANTER | fail-closed |
| `edit_post` por objeto | SIM | MANTER | security |
| nonce por post/ação | SIM | MANTER | CSRF |
| read-after-write | SIM | MANTER | failure tests |
| update parcial | SIM | MANTER | omitted intact |
| atomicidade multi-campo | A DEFINIR | REDESENHAR/ENDURECER | late-failure test |
| admin server-rendered | SIM baseline | MANTER/REDESENHAR | DS único |
| coverage metrics | SIM | MANTER | workload bounded |
| scan ilimitado | NÃO | REDESENHAR | benchmark |
| shortcode current-post-only | COMPAT | MANTER/REDESENHAR | consumer preflight |
| side panel automático | AINDA NÃO SABEMOS | REDESENHAR/decidir | product SPEC futura |
| `Objective_Provider` bridge | NÃO | DESCARTAR | serviço interno |
| evento de mudança | SIM | MANTER intenção/REDESENHAR | confirmed-write event |
| tabela REST AJAX cron próprios | NÃO no baseline | DESCARTAR | só com requisito novo |
| metadata revisions off | AINDA NÃO SABEMOS | decidir T056/T095 | histórico |
| WP real integration tests | SIM | MANTER | release gate |
| deterministic ZIP/SHA | SIM | MANTER | release |

### Ownership dos oito campos após T052

- `objective`, `escalation`, `important` -> **Resumo Executivo**;
- `responsible_team`, `catalog_item`, `affected_service`, `systems_involved`, `target_audience` -> **Classificação de Conhecimento**;
- a UI de Resumo pode compor todos os oito sem criar uma segunda fonte da verdade.

## 3. KB2Ops -> produto futuro

| Capacidade/contrato | Paridade | Direção | Gate |
|---|---|---|---|
| posts/Elementor fonte editorial | SIM | MANTER | zero write editorial derivado |
| Content Extractor único | SIM | MANTER/REDESENHAR | Elementor corpus tests |
| parse JSON antes de render pesado | SIM | MANTER | extraction performance |
| rendered fallback | SIM controlado | MANTER/REDESENHAR | custom-widget coverage |
| allowlist `table/tablepress` | SIM princípio | MANTER | shortcode safety |
| multiple independent parsers | NÃO | DESCARTAR | one extractor contract |
| review states | SIM | MANTER | owner Revisão/Governança |
| knowledge type | SIM | MANTER | owner Classificação; primitive T056 |
| technologies/service/audience | SIM valores | REDESENHAR storage | owner Classificação já resolvido |
| keywords/versions | PROVÁVEL | MANTER valor/REDESENHAR | owner Classificação |
| review notes/reviewer/time | SIM | MANTER via WP | Meta API |
| history bounded | SIM intenção | REDESENHAR | explicit contract/history |
| `include_ai` humano | SIM | MANTER | owner Revisão/Governança |
| AI READY | SIM | MANTER/REDESENHAR | single canonical rule |
| pre-analysis local | SIM | MANTER | deterministic/no provider |
| Summary_Bridge | NÃO no unificado | DESCARTAR | single owners internos |
| Search `WP_Query/meta LIKE` | NÃO | REDESENHAR | ASI-inspired engine |
| token-coverage ranker | NÃO final | DESCARTAR | Golden |
| Search scope/visibility | SIM | MANTER | bypass tests |
| public portal/shortcode | PROVÁVEL | MANTER/REDESENHAR | product/compat |
| query analytics option | NÃO literal | REDESENHAR | owner Analytics; privacy/retention |
| view count postmeta | NÃO literal | REDESENHAR | owner Analytics |
| reports/questions de produto | SIM | MANTER comportamento | bounds/privacy |
| Design System principles | SIM | MANTER/REDESENHAR | owner UI/DS único |
| CSS values/namespace exatos | NÃO | REDESENHAR | DS unificado |
| server rendering | SIM baseline | MANTER | JS only when needed |
| SPA/framework externo | NÃO | DESCARTAR | negation principle |
| activation reversible | SIM | MANTER princípio | install/upgrade tests |
| legacy cleanup lists | NÃO | DESCARTAR | historical only |
| uninstall non-destructive | SIM | MANTER | retention |
| deterministic build | SIM | MANTER | package tests |
| release report sem executable suite | NÃO suficiente | REDESENHAR | tests versionados |

## 4. Ownership funcional consolidado — T052

| Família de dados | Owner futuro |
|---|---|
| post/título/conteúdo/Elementor | WordPress/Elementor |
| objetivo/escalonamento/importante | Resumo Executivo |
| team/catalog/audience/services/systems/technologies/type/keywords/versions | Classificação de Conhecimento |
| review state/notas/revisor/data/include AI/histórico | Revisão e Governança |
| texto/estrutura extraídos | Content Extraction — projection read-only |
| vocabulary/bindings/rules | Search Knowledge |
| índices lexical/item/vector | Search Indexing — projections |
| Golden Queries | Search Quality |
| events/interactions/outcomes | Analytics / Search Intelligence |
| settings | Core Configuration |
| queue/migração | Operations — transitório/operacional |
| sugestões IA | AI Assist — não canônico |

**Importante:** ownership não decide storage. Audience é um conceito único; service/affected_service e technologies/systems continuam distintos até profiling.

## 5. Sobreposição funcional consolidada — T053

Convergências definidas:

- um Summary Store/Resumo;
- uma experiência de Search;
- um domínio de Classificação;
- um Analytics/Search Intelligence;
- um Design System/shell;
- uma navegação de Insights/Reports/Settings.

Responsabilidades mantidas separadas:

- artigo aprovado != Search Knowledge Apply;
- qualidade de conteúdo != qualidade de Search;
- classificação editorial existente != futura classificação sistêmica sem migração explícita.

## 6. Persistência e integração — T050/T051

- dados são classificados como canônicos, projections, observacionais, operacionais, configuração ou compatibilidade;
- chaves/stores legados não definem owner futuro;
- dual-write permanente é proibido;
- adapter/dual-read é temporário e exige gate de remoção;
- evento de domínio ocorre somente após persistência confirmada;
- consumers de projection são idempotentes e falham sem corromper fonte canônica;
- server-rendered/admin-post é baseline; AJAX é enhancement de live UX; REST exige consumidor real;
- shortcodes/hooks históricos são compatibilidade a provar por preflight.

## 7. Matriz WordPress-first preliminar

| Necessidade | Primitive a testar primeiro | Infra própria só se... |
|---|---|---|
| conteúdo editorial | `WP_Post` + Elementor | nunca duplicar edição |
| título | `post_title` | razão extraordinária |
| atributos locais | `register_post_meta` | relação/query/volume não atender |
| classificação compartilhada | taxonomy/terms | T056 provar que semântica/cardinalidade/uso justificam |
| settings | Settings/Options API | estado não for configuração |
| usuário/revisor | users/capabilities | nunca reinventar identidade |
| autorização | `edit_post`, `edit_posts`, custom caps apenas quando necessário | responsabilidade realmente distinta |
| CSRF | nonce | sempre em WP mutante |
| mutation server-rendered | admin-post | realtime/UX exigir outro canal |
| public live interaction | AJAX WP | somente se live UX exigir |
| REST | nenhuma por default | consumidor/API formal justificar |
| scheduling | WP-Cron | durabilidade/state/retry exigir queue |
| cache | Object Cache/transient | benefício mensurável |
| health | Site Health | UI especializada for indispensável |
| search simples | WP native | relevância/performance insuficientes |
| search avançada | projection própria | já comprovado pelo ASI, mas schema mínimo T057 |
| audit/histórico | revisions/meta/event facts | requisitos de consulta/retenção exigirem tabela |
| analytics | facts mínimos | volume/journey/outcome exigirem relational store |
| coverage/reports | queries/cache bounded | materialização só após benchmark |

## 8. Contratos cross-module obrigatórios

### Content pipeline

`WP_Post/Elementor -> Content Extractor -> projections lexical/items -> optional vector -> Search/IA`

- extractor é read-only;
- qualidade de extração precede indexação;
- nenhum downstream reparseia `_elementor_data`/`post_content` por conta própria;
- custom widget omission precisa de diagnóstico/teste.

### Summary/curadoria -> projections

1. validar autorização/input;
2. persistir estado canônico;
3. confirmar estado final;
4. emitir evento de domínio mínimo;
5. marcar/enfileirar projections stale;
6. rebuild derivado sem write editorial.

### AI READY

Baseline histórica: publish + approved + Summary 8/8 + include_ai. Evolução só por SPEC/regressão.

### Search quality

- lexical disponível sem IA/vetor;
- Golden blockers;
- explainability;
- retrieval candidates antes de rerank/synthesis;
- scope/permissions antes de exposição.

### UX

- wp-admin shell;
- DS único;
- progressive disclosure;
- Search não finge chat se é busca;
- IA é melhoria opcional;
- JS é progressive enhancement.

## 9. Candidatos a IA/vetor — ainda opcionais

1. expansão semântica após normalização lexical;
2. hybrid lexical+vector candidate retrieval;
3. rerank semântico bounded;
4. detecção de gaps/duplicidade;
5. sugestões de classification/vocabulary/bindings;
6. sugestões de Resumo Executivo sem auto-save;
7. assisted review sobre payload canônico;
8. answer/synthesis com fontes depois de retrieval.

Nenhum entra antes de baseline lexical/metadata/extractor/Golden estar estável.

## 10. Itens ainda AINDA NÃO SABEMOS antes do fechamento T059

- taxonomy versus postmeta campo a campo;
- nomes/chaves/cardinalidade finais de classificação;
- storage final vocabulary/bindings/rules/Golden;
- schema post/item index;
- necessidade concreta de queue;
- analytics facts/schema/retention;
- migration/coexistência;
- side panel/shortcodes finais;
- anchors/deep links finais;
- revisions/histórico;
- MariaDB Vector/chunks/embeddings;
- provider Foundry/IA.

**Não está mais em aberto:** ownership lógico, sobreposição funcional e regras gerais de persistência/integração. T054 agora fecha drifts/compatibilidade; depois T056/T057/T055/T058 alimentam o fechamento T059.

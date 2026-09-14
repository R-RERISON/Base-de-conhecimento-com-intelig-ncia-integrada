# Matriz de Paridade Futura — SPEC-000

> Matriz incremental após inventário das três referências. Define comportamentos que devem sobreviver, não schema final nem desenho de classes. O cruzamento T050–T059 ainda precisa consolidar ownership, sobreposição e primitives.

## Legenda

- **MANTER** — comportamento precisa sobreviver.
- **REDESENHAR** — objetivo permanece, implementação não.
- **SUBSTITUIR POR WORDPRESS** — preferir primitive nativa.
- **EVOLUIR COM IA/VETOR** — opcional/degradável/governado.
- **DESCARTAR** — não levar ao greenfield.
- **AINDA NÃO SABEMOS** — depende do cruzamento/medição.

## 1. ASI -> produto futuro

| Capacidade | Paridade | Direção | Gate futuro |
|---|---|---|---|
| busca lexical local | SIM | MANTER/REDESENHAR | Golden + performance |
| fallback sem FULLTEXT | SIM | MANTER | disponibilidade lexical |
| QueryContext limitado | SIM | MANTER/REDESENHAR | unit + Golden |
| ranking explicável | SIM | MANTER | sinais/scores observáveis |
| índice por post | PROVÁVEL | REDESENHAR | extractor canônico |
| índice de trechos | SIM | REDESENHAR | identity/deep link |
| parser direto `post_content` | NÃO | DESCARTAR | extractor único |
| vocabulary | SIM | MANTER | storage T056/T057 |
| bindings | SIM | MANTER | storage T056/T057 |
| relevance rules | SIM | MANTER | simulation + audit |
| diagnóstico read-only | SIM | MANTER | sem side effect |
| suggestions evidence-backed | SIM | MANTER | confidence/effect/risk |
| simulation com ranker real | SIM | MANTER | stale-state guard |
| Apply humano | SIM | MANTER | capability + nonce |
| durable queue | CONDICIONAL | REDESENHAR | workload/durabilidade |
| migrations 4.x/WPUI | NÃO | DESCARTAR | greenfield |
| post-install state machine completa | NÃO | REDESENHAR | mínimo necessário |
| Search Events | SIM, mínimo | REDESENHAR | privacy/retention |
| Interactions | CONDICIONAL | MANTER/REDESENHAR | HMAC/idempotência |
| Outcomes | SIM se analytics | MANTER | journey semantic |
| `quality_daily` | NÃO inicialmente | DESCARTAR | benchmark |
| Golden Queries | SIM | MANTER | NO-GO blockers |
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
| side panel automático | AINDA NÃO SABEMOS | REDESENHAR/decidir | T053 |
| `Objective_Provider` bridge | NÃO | DESCARTAR | serviço interno |
| evento de mudança | SIM | MANTER intenção/REDESENHAR | confirmed-write event |
| tabela REST AJAX cron próprios | NÃO no baseline | DESCARTAR | só com requisito novo |
| metadata revisions off | AINDA NÃO SABEMOS | decidir T056/T095 | histórico |
| WP real integration tests | SIM | MANTER | release gate |
| deterministic ZIP/SHA | SIM | MANTER | release |

## 3. KB2Ops -> produto futuro

| Capacidade/contrato | Paridade | Direção | Gate |
|---|---|---|---|
| posts/Elementor fonte editorial | SIM | MANTER | zero write editorial derivado |
| Content Extractor único | SIM | MANTER/REDESENHAR | Elementor corpus tests |
| parse JSON antes de render pesado | SIM | MANTER | extraction performance |
| rendered fallback | SIM controlado | MANTER/REDESENHAR | custom-widget coverage |
| allowlist `table/tablepress` | SIM princípio | MANTER | shortcode safety |
| multiple independent parsers | NÃO | DESCARTAR | one extractor contract |
| review states | SIM | MANTER | state tests |
| knowledge type | SIM | MANTER | storage T052/T056 |
| technologies/service/audience | SIM valores | REDESENHAR storage | ownership/taxonomy |
| keywords/versions | PROVÁVEL | MANTER valor/REDESENHAR | consumers |
| review notes/reviewer/time | SIM | MANTER via WP | Meta API |
| history bounded | SIM intenção | REDESENHAR | explicit contract/history |
| `include_ai` humano | SIM | MANTER | AI READY tests |
| AI READY | SIM | MANTER/REDESENHAR | single canonical rule |
| pre-analysis local | SIM | MANTER | deterministic/no provider |
| Summary_Bridge | NÃO no unificado | DESCARTAR | single Summary Store |
| Search `WP_Query/meta LIKE` | NÃO | REDESENHAR | ASI-inspired engine |
| token-coverage ranker | NÃO final | DESCARTAR | Golden |
| Search scope/visibility | SIM | MANTER | bypass tests |
| public portal/shortcode | PROVÁVEL | MANTER/REDESENHAR | product/compat |
| query analytics option | NÃO literal | REDESENHAR | privacy/retention |
| view count postmeta | NÃO literal | REDESENHAR | analytics model |
| reports/questions de produto | SIM | MANTER comportamento | bounds/privacy |
| Design System principles | SIM | MANTER/REDESENHAR | a11y/responsive |
| CSS values/namespace exatos | NÃO | REDESENHAR | DS unificado |
| server rendering | SIM baseline | MANTER | JS only when needed |
| SPA/framework externo | NÃO | DESCARTAR | negation principle |
| activation reversible | SIM | MANTER princípio | install/upgrade tests |
| legacy cleanup lists | NÃO | DESCARTAR | historical only |
| uninstall non-destructive | SIM | MANTER | retention |
| deterministic build | SIM | MANTER | package tests |
| release report sem executable suite | NÃO suficiente | REDESENHAR | tests versionados |

## 4. Matriz WordPress-first consolidada preliminar

| Necessidade | Primitive a testar primeiro | Infra própria só se... |
|---|---|---|
| conteúdo editorial | `WP_Post` + Elementor | nunca duplicar edição |
| título | `post_title` | razão extraordinária |
| atributos locais | `register_post_meta` | relação/query/volume não atender |
| classificação compartilhada | taxonomy/terms | semântica não for classificatória ou escala exigir outra coisa |
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
| search avançada | projection própria | já comprovado pelo ASI, mas schema mínimo |
| audit/histórico | revisions/meta/event facts | requisitos de consulta/retenção exigirem tabela |
| analytics | facts mínimos | volume/journey/outcome exigirem relational store |
| coverage/reports | queries/cache bounded | materialização só após benchmark |

## 5. Contratos cross-module obrigatórios

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

Regra precisa ser única. Baseline runtime KB2Ops: publish + approved + Summary 8/8 + include_ai. Evolução só por SPEC e regressão.

### Search quality

- lexical disponível sem IA/vetor;
- Golden blockers;
- explainability;
- retrieval candidates antes de rerank/synthesis;
- scope/permissions aplicados antes de exposição.

### UX

- wp-admin shell;
- DS único;
- progressive disclosure;
- Search não finge chat se é busca;
- IA é melhoria opcional;
- JS é progressive enhancement, não fundação do shell.

## 6. Candidatos a IA/vetor — ainda opcionais

1. expansão semântica após normalização lexical;
2. hybrid lexical+vector candidate retrieval;
3. rerank semântico bounded;
4. detecção de gaps/duplicidade;
5. sugestões de classification/vocabulary/bindings;
6. sugestões de Resumo Executivo sem auto-save;
7. assisted review sobre payload canônico;
8. answer/synthesis com fontes depois de retrieval.

Nenhum entra antes de baseline lexical/metadata/extractor/Golden estar estável.

## 7. Itens ainda AINDA NÃO SABEMOS antes de T050–T059

- owner final de audiência/serviço/tecnologia;
- taxonomy vs postmeta campo a campo;
- storage final vocabulary/bindings/rules/Golden;
- schema post/item index;
- necessidade concreta de queue;
- analytics facts/retention;
- migration/coexistência;
- side panel/shortcodes finais;
- anchors/deep links finais;
- revisions/histórico;
- MariaDB Vector/chunks/embeddings;
- provider Foundry/IA.

O inventário de referências está completo; a próxima alteração desta matriz deve vir do **cruzamento consolidado**, não de novas suposições.
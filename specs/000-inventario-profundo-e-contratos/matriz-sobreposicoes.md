# Matriz de Sobreposição Funcional — SPEC-000 — T053

> Estado: **T053 concluída documentalmente**.  
> Escopo: cruzamento ASI 4.6.8 + GRE 0.6.0 + KB2Ops 0.2.1.  
> Objetivo: decidir onde há duplicação verdadeira, complementaridade ou legado antes de consolidar persistência/hooks em T050/T051.

## 1. Princípio de consolidação

Sobreposição funcional não significa “juntar tudo em uma classe/tela”. A pergunta é:

> **Existe uma única responsabilidade de produto sendo implementada por mais de um plugin?**

Classificação usada:

- **DUPLICAÇÃO REAL** — dois runtimes tentam resolver essencialmente a mesma responsabilidade;
- **COMPLEMENTAR** — responsabilidades diferentes que devem cooperar;
- **COMPAT/TRANSIÇÃO** — existe apenas por arquitetura multi-plugin/legado;
- **REFERÊNCIA DE IMPLEMENTAÇÃO** — um projeto possui o contrato mais maduro a preservar;
- **DESCARTÁVEL** — não deve nascer no greenfield.

## 2. Matriz principal

| Capacidade | ASI | GRE | KB2Ops | Diagnóstico | Convergência futura |
|---|---|---|---|---|---|
| fonte editorial | lê `post_content` | lê post/meta | lê post/Elementor | COMPLEMENTAR com conflito de leitura | WordPress/Elementor canônico + Content Extractor único |
| Content Extraction | vários parsers/pipelines | não possui | extractor Elementor-aware | DUPLICAÇÃO/CONFLITO | um serviço `Content Extraction`; ASI/KB downstream param de reparsear |
| Resumo Executivo — dados | consome Objective via bridge quebrada | owner dos 8 campos | `Summary_Bridge` read-only | DUPLICAÇÃO/COMPAT | um Summary Store interno; bridges desaparecem após cutover |
| Resumo Executivo — UI | usa Objective em cards | admin + shortcode + side panel | mostra 8/8/review/detail | COMPLEMENTAR | componentes DS únicos lendo mesmo store; side panel vira decisão de produto |
| classificação do artigo | sinais/taxonomias para ranking | parte dos 8 campos é classificatória | tipo/tech/audience/service/keywords/versions | DUPLICAÇÃO REAL parcial | domínio único de Classificação de Conhecimento |
| revisão editorial/knowledge readiness | não possui workflow do artigo | completude | review states + include AI + checklist | COMPLEMENTAR | módulo de Revisão/Governança, separado de Search curation |
| curadoria de Search | vocabulary/bindings/rules/simulation/apply | — | sugestões/classificação do artigo | COMPLEMENTAR, não fundir | Search Knowledge continua domínio próprio; usa classificação como sinal |
| AI READY | — | só completude | regra publish+approved+8/8+include_ai | REFERÊNCIA KB2Ops | uma política canônica de readiness; evolução só por SPEC |
| busca lexical | engine madura/FULLTEXT/fallback | — | WP Query/meta LIKE provisório | DUPLICAÇÃO REAL | contratos ASI + Content Extractor/UX KB2Ops; engine KB2Ops atual descartada |
| ranking | explicável, rules/bindings/coverage | — | score simples por cobertura | DUPLICAÇÃO REAL | ranker novo inspirado no ASI, Golden-gated |
| QueryContext | maduro/limitado | — | tokens simples | DUPLICAÇÃO REAL | preservar contrato ASI; reimplementar greenfield |
| Item Knowledge/trechos | maduro, identidade/deep-link | — | quick steps simples | SOBREPOSIÇÃO parcial | Item projection ASI-inspired sobre extractor canônico; quick steps vira apresentação derivada |
| busca pública | live AJAX + debounce/abort | — | portal server-rendered | DUPLICAÇÃO DE SUPERFÍCIE | uma experiência Search; server baseline + live enhancement apenas se necessário |
| escopo/visibilidade Search | controles ASI | — | published/approved/AI Ready + detail recheck | COMPLEMENTAR | política única de scope antes de retrieval/exposição |
| analytics de busca | events/interactions/outcomes | — | option de termos + view count | DUPLICAÇÃO REAL | facts mínimos inspirados no ASI; stores leves KB2Ops não continuam paralelos |
| coverage do Resumo | — | dashboard 0/8–8/8 | métricas 8/8 no Studio/Reports | DUPLICAÇÃO DE MÉTRICA/UI | uma métrica derivada; múltiplas views podem consumir o mesmo cálculo |
| content quality | — | completude | checklist/scores/suggestions | COMPLEMENTAR | Qualidade de Conteúdo dentro de Review/Insights; não misturar com Search Quality |
| Search Quality | Golden/diagnostics | — | relevância percentual superficial | REFERÊNCIA ASI | Golden Queries + diagnostics separados de content score |
| relatórios/insights | Search Intelligence | Coverage | Reports | SOBREPOSIÇÃO DE SHELL | uma área de Insights com seções: conteúdo, Search, uso; métricas preservam semântica própria |
| Word Cloud | pipeline próprio | — | — | OPCIONAL/ISOLADA | se mantida, vira view sobre projections/analytics; sem extractor próprio |
| Design System | estilos próprios | estilos próprios | DS coerente/tokens/components | DUPLICAÇÃO REAL | um Design System derivado do KB2Ops, implementação nova |
| admin navigation | menus ASI | menu GRE | menu KB2Ops | DUPLICAÇÃO REAL | um menu/shell do produto, módulos internos |
| settings | ASI settings | praticamente nenhum | `kb2ops_options` | SOBREPOSIÇÃO DE SHELL | uma área de Settings com seções por domínio, Options API primeiro |
| segurança mutante | nonces/caps/HMAC | nonce + `edit_post` | nonce + caps por superfície | COMPLEMENTAR | contract comum; `edit_post` para objeto, custom caps só para responsabilidades globais |
| health/diagnostics | Site Health + reports | — | migration verification | COMPLEMENTAR | Site Health para saúde; telas operacionais só para ação humana necessária |
| activation/upgrade | complexo/histórico | mínimo | hardened/reversível | SOBREPOSIÇÃO | activation mínima + upgrade idempotente; princípio KB2Ops/GRE, sem state machine histórica ASI |
| migration/cutover | runners/reconciler/legacy | — | cleanup/migration legado | COMPAT/TRANSIÇÃO | adapters/migrations temporários e removíveis; não viram arquitetura permanente |
| uninstall | preserva dados | sem rotina | não destrutivo por default | COMPLEMENTAR | política única não destrutiva + purge explícito |
| build/release | regressão ampla/local | WP integration + deterministic package | deterministic builder/audit | COMPLEMENTAR | combinar melhores gates: WP real + Golden + package determinístico + SHA |
| REST | não necessário ao core | nenhum | nenhum | AUSÊNCIA COERENTE | não criar REST sem consumidor real |
| AJAX | live search/tracking | nenhum | nenhum | CAPACIDADE ESPECÍFICA | usar somente para UX live; portar trust boundaries, não endpoints legados |
| GAC | acoplamento ambiental | — | — | DEPENDÊNCIA EXTERNA | adapter opcional, fora do core |
| IA/vetor | preparada/opcional | — | UI prevê evolução | FUTURO | camada opcional depois do lexical/extractor/Golden |

## 3. Fusões funcionais aprovadas em T053

### 3.1 Um único Resumo Executivo

**Desaparecem como arquitetura permanente:**

- ASI `Objective_Provider` adapter externo;
- KB2Ops `Summary_Bridge` duplicando keys;
- ownership duplicado de audiência/classificações.

**Sobrevive:**

- os oito valores históricos como contrato de compatibilidade;
- store interno explícito;
- leitura side-effect free;
- validação/allowlist/capability;
- evento após persistência confirmada;
- componentes de visualização em superfícies diferentes, todos lendo o mesmo owner.

### 3.2 Uma única experiência de Search

O produto não terá “Search do ASI” e “Search do KB2Ops”.

**Motor:** contratos maduros do ASI — retrieval lexical degradável, QueryContext limitado, ranking explicável, Item Knowledge, Golden.  
**Conteúdo:** Content Extractor KB2Ops-derived.  
**UX:** shell/Design System e experiência operacional KB2Ops, incorporando live typing/debounce/cancel do ASI apenas se o fluxo final justificar AJAX.

A busca `WP_Query + meta LIKE + _elementor_data` do KB2Ops é referência de protótipo, não baseline futura.

### 3.3 Uma única área de Classificação

GRE e KB2Ops não manterão classificações semanticamente duplicadas em owners distintos.

- audiência: um conceito;
- serviço e serviço afetado: mesmo domínio, conceitos separados até profiling;
- tecnologia e sistemas envolvidos: mesmo domínio, conceitos separados até profiling;
- tipo, keywords, versions, team, catalog item: mesmo domínio classificatório.

A tela pode agrupar campos por experiência de usuário, sem alterar ownership.

### 3.4 Um único Analytics / Search Intelligence

Stores leves KB2Ops não coexistirão como segunda telemetria permanente se facts robustos forem adotados.

- `kb2ops_search_analytics` deixa de ser store canônico futuro;
- `_kb2ops_view_count` não será fonte principal de uso;
- ASI events/interactions/outcomes fornecem contratos, mas storage será minimizado em T057;
- coverage/review continuam métricas derivadas dos owners de conteúdo/revisão.

### 3.5 Um único Design System e shell

- KB2Ops fornece referência principal de tokens/componentes/guardrails;
- CSS ASI e GRE não sobrevivem como dialetos independentes;
- wp-admin continua shell;
- nenhuma segunda sidebar;
- componentes públicos ficam namespaced e consistentes;
- JS entra por progressive enhancement.

## 4. Responsabilidades que **não** devem ser fundidas

### 4.1 Aprovação do artigo != Apply de Search Knowledge

`review_state=approved` significa confiança/governança do conteúdo.  
Apply de vocabulary/binding/rule significa alteração deliberada do comportamento de retrieval.

São state machines diferentes, com riscos e capabilities diferentes. Uma não pode autorizar automaticamente a outra.

### 4.2 Qualidade de conteúdo != qualidade de busca

- completude/checklist/estrutura avaliam o **conteúdo**;
- Golden Queries/ranking/outcomes avaliam a **recuperação**.

Não criar um “score global mágico” que esconda a causa do problema.

### 4.3 Serviço != serviço afetado sem prova

O KB2Ops usa `service`; o GRE usa `affected_service`. Mesmo domínio não implica mesma semântica/cardinalidade. O futuro profiling deve decidir mapeamento.

### 4.4 Tecnologia != sistema envolvido sem prova

Podem compartilhar vocabulários relacionados, mas não devem ser mesclados automaticamente.

### 4.5 Taxonomia editorial existente != taxonomia sistêmica futura

Categorias/tags do WordPress já têm ownership editorial. O novo plugin não deve reinterpretá-las ou migrá-las silenciosamente para uma taxonomia própria.

## 5. Mapa preliminar de módulos do plugin unificado

Este mapa é funcional, **não desenho de classes**.

1. **Core / Configuration / Lifecycle**
2. **UI / Design System**
3. **Content Extraction**
4. **Resumo Executivo**
5. **Classificação de Conhecimento**
6. **Revisão e Governança**
7. **Search Retrieval & Indexing**
8. **Search Knowledge** — vocabulary/bindings/rules/curadoria
9. **Search Quality** — Golden/diagnostics
10. **Analytics / Insights**
11. **AI Assist** — opcional
12. **Operations / Migration** — mínimo e preferencialmente transitório

A lista não obriga 12 namespaces/classes. É apenas separação de responsabilidades para impedir ownership lateral.

## 6. Consolidação preliminar de telas/superfícies

### 6.1 Home / Visão Geral

Uma home do produto pode mostrar **resumos** de:

- cobertura do Resumo;
- revisão/AI Ready;
- saúde de Search;
- gaps/uso.

Ela não persiste métricas próprias; apenas consome cálculos/facts dos respectivos domains.

### 6.2 Posts / Revisão

Um workspace único de revisão pode compor:

- Resumo Executivo;
- Classificação;
- Review state/notes/include AI;
- pré-análise local;
- sugestões assistidas futuras;
- histórico.

Isso não significa que todos os campos têm o mesmo owner interno.

### 6.3 Search

Uma única superfície administrativa/pública de Search, com detail operacional e progressive disclosure.

### 6.4 Insights / Relatórios

Uma única área de navegação com visões separadas:

- **Qualidade de Conteúdo** — completude, revisão, coverage;
- **Qualidade de Search** — Golden, empty results, relevance/outcomes;
- **Uso** — queries/interactions/outcomes conforme política de privacidade.

### 6.5 Settings

Uma única área com seções por domínio. Não reproduzir páginas de settings de cada plugin.

### 6.6 Operations

Criar tela própria somente para ações que exigem controle humano explícito, como purge/migração/rebuild. Saúde técnica simples deve preferir Site Health.

## 7. Compatibilidade e shortcodes

Shortcodes históricos:

- `[asi_search_form]`;
- `[bdc_word_cloud]`;
- `[bdc_resumo_executivo]`;
- `[kb2ops_search]`;
- `[kb2ops_portal]`.

T053 **não** decide quais aliases sobreviverão. Decisão futura deve usar preflight de consumidores reais e política de compatibilidade.

Princípio:

- uma superfície canônica nova;
- adapters/aliases temporários somente quando uso comprovado exigir;
- nenhum shortcode antigo define arquitetura interna.

## 8. Overlaps que foram resolvidos

| ID | Antes | Após T053 |
|---|---|---|
| D-003 | pipelines divergentes de conteúdo | **resolvido em direção funcional:** Content Extraction único |
| D-004 | múltiplos extractors | **resolvido em direção funcional:** downstream não reparseia fonte |
| D-006 | GRE/KB2Ops classification owners | **ownership resolvido:** Classificação de Conhecimento; storage ainda aberto |
| D-007 | UI/CSS fragmentados | **ownership visual resolvido:** Design System único KB2Ops-derived; composição final ainda evolui |

D-001/D-002 continuam contratos históricos quebrados, mas a convergência funcional já está definida: Summary Store interno + evento pós-write confirmado.

## 9. Novos riscos revelados por T053

### X-007 — Confundir aprovação de conteúdo com curadoria de Search

Um único botão/estado para ambos reduziria auditabilidade e poderia alterar ranking sem intenção explícita.

**Tratamento:** workflows/capabilities separados, embora dentro do mesmo shell.

### X-008 — Dashboard único virar “mega agregador” caro

Consolidar navegação não autoriza scans integrais ou materializações antecipadas.

**Tratamento:** cada card possui owner, query bounded e budget; rollup só por benchmark.

### X-009 — Unificação visual virar acoplamento de domínio

Compartilhar componentes não significa classes de UI acessarem stores internos arbitrariamente.

**Tratamento:** DS é apresentação; módulos expõem view models/serviços mínimos.

### X-010 — Compatibilidade virar duplicação permanente

Aliases, bridges e dual-read podem ser úteis no cutover, mas podem recriar a arquitetura multi-plugin dentro de um plugin único.

**Tratamento:** toda compatibilidade deve ter consumidor, prazo/gate de remoção e owner canônico inequívoco.

## 10. Decisões que T053 deliberadamente não toma

- layout final/pixel de todas as telas;
- nomes finais de menu/slugs;
- AJAX versus server-render em Search final;
- quais shortcodes históricos exigem alias;
- taxonomy/postmeta;
- schemas/tabelas;
- storage final de analytics/Golden/Search Knowledge;
- migration detalhada;
- vetor/Foundry.

Esses pontos dependem de T050/T051/T054/T056/T057/T055/T058/T059.

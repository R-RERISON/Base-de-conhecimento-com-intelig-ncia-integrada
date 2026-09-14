# Inventário Profundo — Advanced Search Intelligence 4.6.8

> SPEC-000 — baseline fixada em `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
>
> Regra de leitura: este documento registra **comportamentos e contratos**, não autoriza cópia do código legado. O novo produto permanece greenfield, WordPress-first e sem runtime durante a SPEC-000.

## 1. Conclusão executiva

O ASI 4.6.8 é uma referência madura de busca lexical, indexação derivada, curadoria assistida, telemetria, regressão e operação. Ao mesmo tempo, carrega complexidade histórica relevante: 12 tabelas, migrações 4.0→4.6, cutover de WPUI, módulos compatíveis pré-cutover, reconciler de base, orquestrador pós-instalação e integrações específicas do ambiente antigo.

O principal resultado do inventário é separar essas duas categorias:

1. **Contratos de produto que devem sobreviver**: retrieval antes de síntese, ranking explicável, busca degradável, índice de posts/trechos, Golden Queries, diagnóstico read-only, simulação antes de mutação, humano no controle, fila durável quando necessária, telemetria não fatal, outcomes por jornada, segurança por capability+nonce+HMAC, Site Health/diagnóstico redigido e uninstall não destrutivo.
2. **Complexidade que não ganha direito de nascer no novo plugin**: migrações históricas ASI/WPUI, compat 2.9/3.x, acoplamento GAC, segundo pipeline lexical da Word Cloud, `quality_daily` sem evidência de necessidade, heurísticas específicas da base tratadas como universais e qualquer reprodução automática das 12 tabelas.

Nenhuma decisão de schema do novo produto foi tomada aqui. Onde o comportamento está comprovado, mas o mecanismo WordPress-first ainda depende do inventário KB2Ops/Resumo Executivo e de volumetria, a classificação é **AINDA NÃO SABEMOS** para a persistência.

## 2. Baseline e árvore de runtime

- Plugin: Advanced Search Intelligence.
- Versão: 4.6.8.
- SHA de referência: `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- Bootstrap: `advanced-search-intelligence.php`.
- Núcleos: `src/Search`, `src/Infrastructure`, `src/Analytics`, `src/PublicSite`, `src/Admin`, `src/Support`, `src/WordCloud`.
- Legado quarantined: `legacy/MigrationBridge.php` e `legacy/compat/*`.
- Testes: suíte local PHP/Node/Python, agregada por `tools/validate-release.sh`.
- CI GitHub Actions: não existe `.github` nesse baseline; o gate versionado é executável localmente.

## 3. Bootstrap e lifecycle

### Ativação

A ativação é deliberadamente pequena: marca preparação pendente e faz flush de rewrites. Schema, migrações e preparação pesada não são executados de forma indiscriminada no activation hook.

### Bootstrap de preparação

Em `plugins_loaded` o plugin valida/repara schema e conduz migrações. O bridge legado só é carregado enquanto o clean-runtime cutover não está ativo.

### Bootstrap normal

Fila, busca pública, relatório, admin, diagnósticos e auditoria estrutural são registrados depois da preparação. O desenho separa inicialização, migração e steady state.

### Desativação/uninstall

A desativação remove agendas próprias. O `uninstall.php` **não apaga dados**: retenção/destruição exige procedimento explícito.

**Decisão:** MANTER o princípio de activation leve, bootstrap em fases e uninstall não destrutivo; REDESENHAR para o novo módulo sem herdar o state machine histórico.

## 4. Persistência — 12 stores canônicos do ASI

| Store ASI | Finalidade observada | Decisão preliminar |
|---|---|---|
| `search_index` | documento lexical derivado por post, FULLTEXT/fallback | MANTER comportamento; REDESENHAR schema/extractor |
| `search_items` | trechos/itens navegáveis derivados do conteúdo | MANTER comportamento; REDESENHAR schema/extractor |
| `term_bindings` | vínculo termo→post/trecho | MANTER comportamento; persistência AINDA NÃO SABEMOS |
| `vocabulary` | variantes/canonicalização ponderada | MANTER comportamento; persistência AINDA NÃO SABEMOS |
| `relevance_rules` | promoção/rebaixamento explícito | MANTER comportamento; persistência AINDA NÃO SABEMOS |
| `index_queue` | jobs duráveis de indexação | MANTER semântica; REDESENHAR implementação mínima |
| `audit_log` | trilha de mutações/operações | MANTER necessidade; REDESENHAR escopo mínimo |
| `search_events` | execução de consultas/latência/estado/jornada | MANTER fatos essenciais; REDESENHAR privacidade/retenção |
| `search_interactions` | clicks/switch/show-more correlacionados | MANTER comportamento; REDESENHAR schema se necessário |
| `golden_queries` | expectativas de ranking versionadas | MANTER contrato; persistência AINDA NÃO SABEMOS |
| `quality_daily` | rollup diário de contadores | DESCARTAR no baseline; só reintroduzir por evidência de performance |
| `migrations` | registry/checkpoint de migrações | DESCARTAR histórico ASI; MANTER apenas upgrade mechanism próprio se necessário |

O schema do ASI possui índices orientados ao workload, FULLTEXT e índices específicos de analytics/jornada. Isso é evidência de que tabelas próprias podem ser justificadas para buscas de alto volume, mas **não prova** que todas as tabelas são necessárias no novo produto.

## 5. Settings, options, transients e cache

- `asi4_settings` centraliza limites de busca, privacidade, retenção, cron e módulos opcionais.
- Cache de busca usa transients e versão do algoritmo na chave.
- Metadados de schema/tabela também usam cache curto.
- Há limpeza de caches por prefixo em `wp_options`, mecanismo que deve ser evitado no novo produto se houver alternativa com namespace/version token.
- Rate limiting público usa transient bucket; usuário autenticado usa ID; anônimo usa hash de IP + User-Agent.

**Decisão:** MANTER configuração central sanitizada e cache versionado. REDESENHAR invalidação de cache e rate limit. O uso de IP/UA para anônimo requer revisão de proxy/NAT e minimização de dados.

## 6. QueryContext e retrieval plan

`QueryContext` normaliza consulta, tokeniza, detecta tipo, produz variantes, n-grams, equivalências e um plano de retrieval limitado. Há também equivalências de domínio hardcoded.

**Valor comprovado:**

- normalização determinística;
- limites explícitos para explosão combinatória;
- distinção entre consulta curta e linguagem natural;
- expansão por vocabulário administrável;
- metadados explicáveis para debug/ranking.

**Dívida:** equivalências específicas do corpus não devem ficar hardcoded como conhecimento universal.

**Decisão:** MANTER contrato de contexto/plano limitado; REDESENHAR equivalências como dados governáveis. EVOLUIR COM IA/VETOR apenas como expansão/retrieval complementar e degradável, nunca substituindo o baseline lexical sem evidência.

## 7. Ranking de posts

`Relevance` combina sinais explícitos: regras, cobertura dos tokens, índice lexical, bindings e overlays de simulação. O fallback direto ao WordPress é emergencial, não caminho normal. O ranker expõe origem, cache/fallback e scores.

`PostIndex` usa FULLTEXT quando disponível e LIKE limitado como fallback. Para consultas longas, relaxa a exigência de todos os tokens. O índice atual extrai título, headings, corpo, categorias/tags e Objective.

### Problema estrutural para o novo produto

O ASI lê `post_content` diretamente. Em um produto Elementor-first isso não pode ser a fonte canônica única. Busca, auditoria, Word Cloud e futuro RAG devem consumir **um Content Extractor único, Elementor-aware**, para que todos calculem a mesma representação derivada.

**Decisão:** MANTER ranking lexical explicável + fallback; REDESENHAR extração e pesos. Golden Queries obrigatórias para mudança de ranking.

## 8. Item Knowledge, identidade e anchors

O ASI cria uma camada de trechos navegáveis abaixo do post:

- identidade estável por `item_key`;
- extração por estruturas numeradas/headings;
- fallback semântico conservador;
- deduplicação por identidade semântica;
- reconciliação de bindings após reindexação;
- estado explícito de anchor/navegabilidade;
- itens sem destino comprovado permanecem observáveis, mas não são publicados como links válidos.

O Anchor Manager injeta IDs no HTML renderizado via `the_content`; não grava o conteúdo editorial.

**Decisão:** MANTER o conceito de trechos, identidade estável, fail-closed de navegação e reconciliação. REDESENHAR o extractor sobre a representação canônica Elementor-aware. A estratégia final de anchors permanece AINDA NÃO SABEMOS até cruzar KB2Ops.

## 9. ItemRanker

Combina contexto do post pai, sinais lexicais, bindings, cobertura, limiar mínimo e estado de navegabilidade. Exibe estado `ready/partial/degraded` em vez de afirmar qualidade inexistente.

**Decisão:** MANTER o comportamento e a honestidade de degradação; REDESENHAR pesos e dependências. EVOLUIR COM IA/VETOR apenas como rerank opcional após retrieval determinístico.

## 10. Objective / Resumo Executivo

`ExecutiveSummaryObjective` considera uma única fonte canônica: `BDC\ExecutiveSummary\Objective_Provider::read_objective()`. Falta do provider, erro ou estado vazio resultam em string vazia. `PostContext` não inventa resumo a partir de `post_content`.

O teste `test-executive-summary-objective.php` cria explicitamente um stub `Objective_Provider`, provando que esse é um contrato esperado pelo ASI.

**Drift confirmado no lado ASI:** a existência desse provider é requisito do adaptador. A existência real no Gerenciador de Resumo Executivo 0.6.0 ainda deve ser confirmada em T046.

**Decisão:** MANTER fonte canônica e fail-empty; nunca sintetizar silenciosamente resumo editorial no caminho de apresentação.

## 11. Curadoria, diagnóstico e simulação

A camada Search & Knowledge é um dos melhores contratos do ASI:

1. diagnóstico é read-only;
2. sugestões são determinísticas, explicáveis e carregam evidência/confiança/risco;
3. mutações exigem capability dedicada;
4. estado esperado impede aplicar decisão sobre estado stale;
5. simulação usa o mesmo ranker de produção, em memória;
6. Apply exige prova HMAC vinculada ao usuário, sugestão, snapshot atual e versões dos algoritmos;
7. alterações são auditadas e invalidam cache/reindexam quando necessário.

**Decisão:** MANTER como padrão arquitetural. A futura IA pode gerar hipóteses/sugestões, mas o contrato humano-no-loop e simulação antes da persistência é obrigatório.

## 12. Queue

A fila não é apenas um cron. Ela possui:

- lease/claim;
- retry com backoff;
- dead-letter;
- recuperação de processing sem lease;
- correção de estados legados impossíveis;
- descoberta de posts stale;
- reabertura de job concluído com novo orçamento de tentativas;
- auditoria operacional.

WP-Cron é disparador, não garantia de durabilidade.

**Decisão:** MANTER semântica de job durável se a indexação assíncrona do novo produto exigir. REDESENHAR a menor implementação capaz de satisfazer lease/idempotência/retry. Não criar tabela de fila apenas por herança: justificar com workload e falhas reais.

## 13. Migration Runner, Base Reconciler e PostInstallOrchestrator

Esses componentes existem majoritariamente para conduzir a história do ASI 4.x:

- migrações ordenadas e resumíveis;
- importação de versões antigas/WPUI;
- preflight de consumidores externos;
- decisões explícitas antes de desabilitar módulos;
- reindexação e reconciliação sequencial;
- guided manual acceptance;
- evidência por build/revision;
- bloqueio fail-closed.

**Decisão:** DESCARTAR as migrações/cutovers históricas no greenfield. MANTER os princípios de upgrade idempotente, decisão explícita, preflight e rollback-safe. REDESENHAR um gate de instalação/upgrade muito menor quando houver runtime real.

## 14. Search Events, Interactions e Outcomes

### Events

Registra execução real de busca, contagens, duração, cache, origem e estado. Modos de privacidade: minimal, pseudonymous e audit. Identidade/session só são hasheadas quando o modo permite. Journey token nunca é persistido cru.

**Atenção:** o termo pesquisado é persistido em texto/normalizado mesmo no modo `minimal`. Isso pode conter dados sensíveis digitados pelo usuário. O novo produto precisa de política explícita de coleta, retenção, acesso e eventual redução/hash/tokenização — não apenas ausência de user ID.

### Interactions

Clicks e ações de navegação recebem token HMAC vinculado a event/type/target/rank. O servidor não confia no `request_source` enviado pelo cliente e deduplica replay exato.

### Outcomes

Colapsa live typing por jornada para considerar a última revisão como consulta gerencial; separa `zero_result`, `pending_engagement`, `engaged` e `no_engagement`, com janela de observação. Revalida lacunas históricas contra o ranker atual.

**Decisão:** MANTER semântica de eventos/interações/outcomes e telemetria não fatal. REDESENHAR privacidade, retenção e schema sob data minimization.

## 15. Golden Queries

Golden Queries pertencem ao ambiente e armazenam expectativa de post, item opcional, rank máximo e severidade. O gate:

- diferencia `blocking` e `warning`;
- guarda hash do conjunto de expectativas;
- invalida evidência quando versão do ranker de posts ou itens muda;
- considera suíte vazia como `not_configured`, nunca PASS;
- só executa ranking em ação explícita;
- produz relatório machine-readable sem acoplar identidade da telemetria.

**Decisão:** MANTER como gate obrigatório de regressão/release para qualquer alteração de ranking, parser, vocabulário, bindings, regras, vetor ou IA que afete retrieval/rerank.

## 16. Quality Diagnostics e Site Health

Integra com WordPress Site Health e produz diagnóstico de schema, migração, cobertura de índice, trechos, fila, cron, compatibilidade, telemetria e Golden Queries. Export default é redigido para não incluir queries cruas/IP/session/user identity.

**Decisão:** SUBSTITUIR POR WORDPRESS onde Site Health já fornece o framework; MANTER checks próprios realmente específicos do plugin e export seguro. Descartar checks de migração ASI antiga.

## 17. Word Cloud

É runtime canônico no ASI, com options próprias, cron horário, snapshot, shortcode `[bdc_word_cloud]`, eventos de intenção e ranking híbrido de termos. Porém coleta novamente conteúdo de posts/pages e extrai headings/body diretamente de `post_content`, criando um segundo pipeline lexical.

**Decisão:** REDESENHAR como feature opcional consumidora do índice/Content Extractor/telemetria canônicos. Não permitir pipeline paralelo de conhecimento. Persistência e cron próprios não têm direito automático de existir.

## 18. Admin, rotas, AJAX e frontend

### Admin

Superfícies: Visão Geral, Busca & Conhecimento, Operações/Indexação, Inteligência de Buscas, Qualidade & Diagnóstico e Configurações. Mutações usam capability+nonce. Há capability dedicada para curadoria e para relatório gerencial.

### Público

- shortcode `[asi_search_form]`;
- AJAX WordPress `asi_live_search` com `nopriv`;
- endpoint de interação com nonce, rate limit e HMAC de target;
- endpoint legado de quality signal mantido não mutante;
- CSS/JS enfileirados condicionalmente;
- live search com debounce/AbortController;
- progressive disclosure;
- ARIA/status;
- DOM montado por APIs seguras/textContent;
- cache compartilhado permanece neutro a event ID; tracking é decorado por request.

Não foi identificado REST próprio no fluxo principal de busca.

**Decisão:** MANTER trust boundaries, acessibilidade, cancelamento/debounce, progressive disclosure e cache event-neutral. REDESENHAR apresentação pelo Design System derivado do KB2Ops.

## 19. Search Intelligence e acoplamento organizacional

O relatório gerencial cria capability `asi_view_search_intelligence_report`, agrega consultas por perfil/equipe e pode resolver identidade auditada. Também conhece roles/tabelas GAC diretamente (`gac_admin`, `gac_coordinator`, `gac_v15_*`). Workloads possuem caps explícitos.

**Decisão:** MANTER capability separada, transparência de cobertura de identidade, limites e agregações úteis. DESCARTAR acoplamento direto ao GAC no core; se necessário, REDESENHAR como adapter de contexto organizacional opcional.

## 20. Legacy/compat

`legacy/MigrationBridge.php` é a única área autorizada a conhecer opções/tabelas históricas. `legacy/compat` só carrega módulos antigos pré-cutover.

**Decisão:** DESCARTAR do greenfield. Apenas contratos funcionais comprovadamente necessários podem ser reconstruídos no core moderno; nenhum código compat será copiado por default.

## 21. Testes e release

`tools/validate-release.sh` executa:

- lint de todo PHP;
- `node --check` em JS;
- 31 testes/contratos PHP+Node+Python;
- package layout/contract;
- integridade SHA-256 do `release-manifest.json`, quando presente.

Categorias observadas: source contracts, query context, relevance, cache, item identity/extractor/rank, anchors, public navigation, response quality, Objective, reconciliation, external preflight, Search Intelligence, telemetry/outcomes, public journey, assisted knowledge, curation, Golden Queries, quality, migrations/partial upgrade, performance bounds, security, orchestrator, queue, activation e package.

Há vários testes comportamentais valiosos e vários testes baseados em `strpos`/estrutura-fonte. No novo projeto, portar **intenções de regressão**, preferindo unit/integration/E2E sobre inspeção textual quando possível.

## 22. Matriz de classificação ASI

| Componente | Classificação | Razão |
|---|---|---|
| Bootstrap leve/fases | MANTER | reduz risco na ativação |
| 12 tabelas como pacote | DESCARTAR | arquitetura histórica não é requisito |
| Índice lexical de posts | MANTER + REDESENHAR | retrieval local/degradável é crítico; extractor muda |
| Índice de trechos | MANTER + REDESENHAR | melhora navegação/RAG; extractor muda |
| QueryContext | MANTER + REDESENHAR | plano limitado e explicável; remover hardcodes |
| Relevance/coverage | MANTER + REDESENHAR | baseline determinístico + Golden gate |
| Vocabulary | MANTER; storage AINDA NÃO SABEMOS | função comprovada; WP taxonomy/meta/table a decidir |
| Bindings | MANTER; storage AINDA NÃO SABEMOS | curadoria explícita valiosa |
| Rules | MANTER; storage AINDA NÃO SABEMOS | correção explícita de ranking |
| Durable Queue | MANTER + REDESENHAR | necessária se workload assíncrono justificar |
| Migrações ASI/WPUI | DESCARTAR | história específica do legado |
| Orchestrator 4.x | REDESENHAR | princípios bons, state machine excessivo para greenfield |
| Telemetry | MANTER + REDESENHAR | outcomes úteis; privacy/minimization precisa mudar |
| `quality_daily` | DESCARTAR inicialmente | derivável; materializar só por benchmark |
| Golden Queries | MANTER | principal gate de regressão de relevância |
| Quality/Site Health | SUBSTITUIR POR WORDPRESS + MANTER checks | usar framework nativo |
| Curadoria/simulação | MANTER | humano-no-loop e prova antes de Apply |
| Word Cloud | REDESENHAR | feature opcional sem pipeline lexical próprio |
| Search UI UX contracts | MANTER + REDESENHAR UI | comportamento robusto; visual virá do KB2Ops |
| GAC direct integration | DESCARTAR do core | dependência ambiental; usar adapter se necessário |
| Legacy/compat | DESCARTAR | greenfield |
| IA para sugestões | EVOLUIR COM IA/VETOR | assistiva, explicável e submetida a simulação |
| Vetor/semantic retrieval | EVOLUIR COM IA/VETOR | camada opcional; lexical permanece fallback/core |

## 23. Princípio de negação aplicado

Antes de cada mecanismo próprio, a pergunta para o futuro runtime será:

- WordPress metadata/taxonomy/options resolvem o domínio com consultas e volume aceitáveis?
- WP-Cron é suficiente como disparador? Se não, qual requisito de durabilidade exige fila própria?
- Site Health pode hospedar o check em vez de dashboard próprio?
- um único Content Extractor consegue alimentar busca, trechos, Word Cloud, IA e auditoria?
- analytics podem ser derivados de eventos sem uma tabela agregada?
- a feature precisa de persistência própria ou pode ser um projection/cache reconstruível?

Nenhuma tabela, cron ou endpoint nasce apenas porque existe no ASI.

## 24. Gates do bloco ASI

- [x] SHA/versionamento fixado.
- [x] Bootstrap/lifecycle lido.
- [x] Árvore de runtime mapeada.
- [x] Schema/tabelas/índices inventariados.
- [x] Settings/options/transients/cache/rate limit inventariados.
- [x] QueryContext/Relevance/PostIndex/ItemKnowledge/ItemRanker inventariados.
- [x] Vocabulary/Bindings/Rules/Curadoria/Simulação inventariados.
- [x] Queue/Migration/Reconciler/Orchestrator inventariados.
- [x] Events/Interactions/Outcomes/Quality/Golden inventariados.
- [x] Admin/AJAX/shortcodes/frontend/assets inventariados.
- [x] Word Cloud e legacy/compat classificados.
- [x] Testes/build/release/rollback inventariados.
- [x] Matriz preliminar de decisão/paridade criada.

**Status do bloco ASI: CONCLUÍDO para a SPEC-000.** Isso não autoriza SPEC-001 nem define schema futuro; o cruzamento ainda depende dos inventários KB2Ops e Gerenciador de Resumo Executivo.
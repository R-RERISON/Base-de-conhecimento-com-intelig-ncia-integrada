# Catálogo de Integrações, Hooks e Superfícies — SPEC-000

> Cobertura atual: Advanced Search Intelligence 4.6.8 + Gerenciador de Resumo Executivo 0.6.0 + KB2Ops 0.2.1. O inventário das três referências está completo; decisões finais de convergência pertencem a T050–T059.

## 1. Advanced Search Intelligence 4.6.8

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

### Contratos principais

- activation/deactivation e `plugins_loaded` separam lifecycle de steady state;
- `save_post` alimenta indexação;
- Queue espera `bdc_es_objective_updated` — drift confirmado com GRE;
- `the_content` é usado pelo Anchor Manager;
- `site_status_tests` integra Quality Diagnostics ao Site Health;
- AJAX público `asi_live_search` e `asi_search_interaction` usa nonce/rate limit/journey/HMAC/idempotência;
- admin actions cobrem settings, vocabulary/rules/bindings, Golden, curadoria, exports e operações;
- shortcodes `[asi_search_form]` e `[bdc_word_cloud]`;
- capabilities específicas para conhecimento/analytics, além de `manage_options`;
- integração ambiental GAC deve sair do core;
- frontend possui live typing/debounce/abort/tracking/progressive disclosure.

**Direção:** preservar trust boundaries, UX e contratos; redesenhar superfícies e remover dependências históricas/ambientais.

---

## 2. Gerenciador de Resumo Executivo 0.6.0

Baseline: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`.

### Hooks/superfícies

| Contrato | Tipo | Papel | Futuro preliminar |
|---|---|---|---|
| `plugins_loaded` | action | boot | MANTER princípio simples |
| `init` | action | registra oito metas | MANTER Metadata API |
| `bdc_es_loaded` | action emitida | runtime carregado | compatibilidade/consumidor a provar |
| `admin_menu` | action | gestão do resumo | REDESENHAR em UI única |
| `admin_enqueue_scripts` | action | CSS admin condicional | MANTER loading condicional |
| `admin_post_bdc_es_save_summary` | admin-post | mutação autenticada | MANTER trust boundary |
| `wp_enqueue_scripts` | action | CSS frontend condicional | MANTER princípio |
| `wp_footer` | action | side panel automático | UX final aberta |
| `[bdc_resumo_executivo]` | shortcode | render current-post-only | compatibilidade inicial |

Sem AJAX, REST, cron ou handlers nopriv.

Capabilities: `edit_posts` para menu e `edit_post(post_id)` para operação real. Nonce é vinculado ao post.

### Drift ASI ↔ GRE

ASI espera:

- `BDC\ExecutiveSummary\Objective_Provider::read_objective()`;
- `bdc_es_objective_updated`.

GRE 0.6.0 não expõe nenhum dos dois. No plugin unificado, substituir por store interno + evento pós-persistência confirmada.

---

## 3. KB2Ops 0.2.1

Baseline: `R-RERISON/KB2Ops-Operational-Knowledge-Engine@f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.

### 3.1 Lifecycle/hooks

| Contrato | Tipo | Papel | Futuro preliminar |
|---|---|---|---|
| activation hook | lifecycle | `Installer::activate()` | MANTER princípio reversível |
| `plugins_loaded` | action | `Plugin::boot()` | MANTER boot simples |
| `admin_init` | action | `Installer::maybe_upgrade()` | MANTER se upgrade exigir |
| `init` | action | registra metas Knowledge | MANTER Metadata API |
| `kb2ops_loaded` | action emitida | runtime carregado | consumidor real a provar |
| `kb2ops_post_approved` | action emitida | transição para aprovado | MANTER intenção; emitir só após persistência confirmada |
| `kb2ops_content_extractor_error` | action emitida | erro render Elementor | MANTER observabilidade não fatal |
| `kb2ops_content_extractor_shortcode_error` | action emitida | erro shortcode allowlisted | MANTER observabilidade não fatal |

Não existe deactivation hook no baseline.

### 3.2 Admin routes/actions

Menu `KB2Ops` contém:

- `kb2ops-dashboard`;
- `kb2ops-posts`;
- `kb2ops-reports`;
- `kb2ops-search`;
- `kb2ops-settings`;
- `kb2ops-migration`.

Mutações via admin-post:

| Action | Gate |
|---|---|
| `kb2ops_save_review` | POST + nonce por post + `edit_post` |
| `kb2ops_save_settings` | POST + nonce + `manage_options` |
| `kb2ops_verify_migration` | POST + nonce + `manage_options` |
| `kb2ops_purge_legacy_data` | POST + nonce + `manage_options` + confirmação explícita |

Capabilities observadas:

- `read` — Knowledge Search;
- `edit_posts` — Studio/Reports;
- `edit_post(post_id)` — revisão de artigo;
- `manage_options` — Settings/Migration.

**Direção:** preservar autorização por responsabilidade/objeto e nonces; consolidar menus/superfícies no plugin unificado.

### 3.3 Shortcodes/frontend

- `[kb2ops_search]`;
- `[kb2ops_portal]` como alias.

Ambos são server-rendered. Query/detail usam GET e preservam filtros/contexto. `require_login` pode restringir o portal. `search_scope` governa `published`, `approved` ou `ai_ready`. O detalhe revalida o scope para impedir acesso direto fora do conjunto permitido.

Não há AJAX nem REST no Knowledge Search 0.2.1.

**Direção:** MANTER comportamento de escopo/visibility e progressive disclosure; superfície final live search poderá usar AJAX somente se o UX exigir, aproveitando trust boundaries do ASI.

### 3.4 Content Extractor ↔ Elementor

Integração com Elementor é read-only:

- lê `_elementor_data`;
- prefere parsing determinístico do JSON;
- usa `Elementor\Plugin` rendering apenas como fallback;
- nunca escreve Elementor;
- captura falhas de widgets;
- `post_content` é último fallback.

Esse contrato é a base para resolver D-003/D-004. Search, Item Knowledge, Word Cloud, auditoria e IA futura devem consumir um único extractor.

### 3.5 Table/TablePress

O extractor executa somente shortcodes `table` e `tablepress` quando registrados. Falha vira placeholder textual e hook de diagnóstico.

**Direção:** manter allowlist explícita; não executar shortcodes arbitrários em pipelines de indexação.

### 3.6 Summary Bridge ↔ GRE

KB2Ops replica e lê diretamente as oito `_bdc_es_*`. Não chama GRE, não escreve e continua funcionando se o plugin GRE estiver inativo.

**Valor:** isolamento na arquitetura multi-plugin atual.

**Problema futuro:** contrato duplicado de chaves/labels. No bounded context unificado, eliminar bridge e consumir um único Summary Store.

### 3.7 Search atual

`Knowledge::matching_post_ids()` combina:

- native WordPress search;
- `meta_query LIKE` em metas KB2Ops/GRE e `_elementor_data`;
- busca por `#ID`.

`Search::relevance()` calcula score por cobertura de tokens em título + Content Extractor + metadata + resumo.

**Direção:** REDESENHAR retrieval/ranking com contratos ASI; MANTER filtros, scope, detail visibility e experiência operacional do KB2Ops.

### 3.8 Analytics/reports

- search terms: `kb2ops_search_analytics` option bounded a 500 chaves;
- view count: `_kb2ops_view_count`;
- Reports: métricas de revisão, resumo 8/8, tipos, top viewed e top searches.

**Direção:** MANTER perguntas de produto, REDESENHAR telemetria/storage/privacy usando maturidade ASI.

### 3.9 Design System/assets

`UI` centraliza componentes server-rendered. Assets:

- `design-system.css`: tokens/componentes/base responsiva;
- `admin.css`: Search/detail/reporting específicos;
- `public.css`: embed público namespaced;
- `admin.js`: somente confirmação progressiva.

Guardrails:

- wp-admin é shell;
- sem SPA/framework CSS/font externa;
- CSS namespaced `.kb2ops-ui`;
- cor nunca é único sinal;
- progressive disclosure;
- Search não imita chat;
- IA não sustenta o shell nem edita conteúdo.

**Direção:** referência visual principal, a ser renomeada/consolidada no DS único.

### 3.10 Migration/uninstall

Activation retira runtime legado sem apagar dados. Migration oferece verify/purge explícito. Uninstall preserva dados por default e só purga quando `KB2OPS_PURGE_ON_UNINSTALL === true`.

**Direção:** MANTER princípio reversível; DESCARTAR listas específicas do legado.

---

## 4. Superfícies sobrepostas a resolver em T050–T059

| Necessidade | ASI | GRE | KB2Ops | Pergunta do cruzamento |
|---|---|---|---|---|
| gestão/curadoria | Search Knowledge | editor resumo | Knowledge Studio | qual workspace único? |
| busca | forte/live AJAX | — | server-rendered provisória | motor ASI + UX KB2Ops? |
| resumo | consome provider quebrado | proprietário | bridge read-only | um store interno único |
| analytics | robusto/relacional | coverage | leve em option/meta | quais fatos realmente manter? |
| relatórios | Search Intelligence | coverage | Reports | qual dashboard mínimo? |
| conteúdo | pipelines `post_content` | não extrai | Content Extractor | extractor único obrigatório |
| frontend | search/word cloud | shortcode/side panel | portal/search/detail | superfície pública final |
| UI | estilos próprios | estilos próprios | Design System | convergir no DS KB2Ops-derived |
| lifecycle | complexo | mínimo | hardened/reversível | mínimo necessário |

## 5. REST/AJAX — decisão ainda por consumidor

- GRE prova que admin-post/server rendering atende Resumo Executivo;
- KB2Ops prova que Search pode existir sem AJAX, mas não oferece live typing;
- ASI prova trust boundaries maduros para live search/tracking.

**Conclusão provisória:** não criar REST. AJAX público só será adotado se a UX final exigir live search/telemetria interativa; nesse caso portar segurança/idempotência do ASI, não endpoints legados literalmente.

## 6. Contratos cross-module obrigatórios já evidenciados

1. **Summary Store -> evento confirmado -> index invalidation**.
2. **Content Extractor único -> lexical/items/IA/Word Cloud/auditoria**.
3. **Curadoria humana -> estado aprovado/include AI -> downstream**, sem edição automática.
4. **Search telemetry non-fatal** — analytics não pode derrubar busca.
5. **Design System único** — nenhuma feature cria shell visual independente por conveniência.

T050–T059 agora podem consolidar ownership, sobreposição e primitives finais. Nenhum runtime foi criado nesta etapa.
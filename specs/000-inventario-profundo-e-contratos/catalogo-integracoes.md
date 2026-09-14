# Catálogo de Integrações, Hooks e Superfícies — SPEC-000

> Documento incremental. Cobertura atual: Advanced Search Intelligence 4.6.8 + Gerenciador de Resumo Executivo 0.6.0. KB2Ops será incorporado antes do gate final da SPEC-000.

## 1. Advanced Search Intelligence 4.6.8

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`

### Lifecycle e hooks principais

| Tipo | Contrato observado | Finalidade | Futuro preliminar |
|---|---|---|---|
| activation | activation hook do plugin | marcar preparação/flush | MANTER activation leve |
| deactivation | limpa crons + flush | lifecycle seguro | MANTER |
| `plugins_loaded` | preparação/migrations e bootstrap normal | separar upgrade de steady state | MANTER princípio; REDESENHAR |
| `save_post` | ownership único da Queue | enfileirar reindexação | MANTER contrato único de indexação |
| `bdc_es_objective_updated` | evento externo esperado | reindexar quando Objective muda | intenção válida; **DRIFT confirmado no GRE 0.6.0** |
| `the_content` | Anchor Manager | IDs de navegação sem persistir edição | REDESENHAR com extractor/Elementor |
| `template_include` | busca full-page | superfície pública | AINDA NÃO SABEMOS no produto novo |
| `site_status_tests` | Site Health | health check nativo | SUBSTITUIR POR WORDPRESS/MANTER checks |
| `user_has_cap` | relatório de Search Intelligence | grant capability por roles GAC | REDESENHAR; remover GAC do core |

### Cron

- `asi4_index_cycle`: disparo periódico da fila de indexação.
- Word Cloud: geração horária de snapshot.
- hooks históricos da Word Cloud são limpos pelo bridge de migração.

**Decisão:** cron é trigger, não armazenamento de job. Manter somente crons cuja necessidade permaneça comprovada; Word Cloud não deve ter pipeline paralelo se for reconstruída.

### Shortcodes

- `[asi_search_form]`: busca pública.
- `[bdc_word_cloud]`: Word Cloud pública.
- legacy shortcodes são tratados como consumidores a detectar antes do cutover, não como core moderno.

**Decisão:** preservar contrato de composição pública, mas nomes/quantidade final serão decididos com KB2Ops/UI. Elementor deve permanecer editor/publicador canônico.

### AJAX público

#### `asi_live_search`

- autenticado e `nopriv`;
- nonce obrigatório;
- rate limit;
- sanitização e limites de tamanho;
- journey token validado;
- cache neutro de tracking;
- ranking + item ranking;
- telemetria non-fatal;
- estados `idle/success/empty/error/rate_limit`.

#### `asi_search_interaction`

- autenticado e `nopriv`;
- nonce + rate limit;
- target HMAC vinculado a event/type/post/item/rank;
- server-side authority do request source;
- deduplicação/idempotência.

#### `asi_quality_signal`

Endpoint legado/deprecated; reconhece sinais mas não deve continuar como caminho mutante.

**Decisão:** MANTER trust boundaries e semântica; superfície final AJAX versus REST permanece AINDA NÃO SABEMOS. Não existe razão para criar REST apenas por modernidade.

### Admin actions

Principais ações observadas:

- `wp_ajax_asi4_operation` para operações privilegiadas;
- `admin_post_asi4_save_settings`;
- add vocabulary/rule/binding;
- apply knowledge suggestion;
- add/toggle/run/export Golden Query;
- export deployment report;
- export quality report;
- ações específicas da Word Cloud.

Todas as mutações relevantes usam capability e nonce. Curadoria possui capability específica; operações de lifecycle exigem `manage_options`.

**Decisão:** MANTER separação de capabilities e CSRF guards; REDESENHAR menus/ações para a superfície única do novo plugin.

### Capabilities

- `manage_options`: administração/lifecycle.
- `asi_manage_search_knowledge`: curadoria Search & Knowledge.
- `asi_view_search_intelligence_report`: analytics gerencial.

O relatório ASI concede capability a roles ambientais `gac_admin` e `gac_coordinator`.

**Decisão:** MANTER capacidades por responsabilidade. DESCARTAR conhecimento direto de roles GAC no core; se requerido, usar adapter/configuração.

### Integrações externas observadas

#### Gerenciador de Resumo Executivo

Contrato esperado pelo ASI:

- classe `BDC\ExecutiveSummary\Objective_Provider`;
- método estático `read_objective(post_id)`;
- evento `bdc_es_objective_updated` para invalidar/reindexar.

Estado após T046: **DRIFT CONFIRMADO**. O GRE 0.6.0 não expõe o provider nem emite o evento. O único evento próprio identificado no bootstrap GRE é `bdc_es_loaded`.

#### GAC

- roles `gac_*`;
- tabelas `gac_v15_professional_profiles` e `gac_v15_teams` para contexto organizacional.

Estado: dependência ambiental do relatório, não requisito arquitetural do novo core.

#### WPUI / ASI legado

Conhecimento restrito a `legacy/MigrationBridge.php` e compat pré-cutover. Estado: DESCARTAR no greenfield, exceto eventual estratégia de coexistência/migração que será decidida após o cruzamento.

### Frontend/assets

- `assets/js/public-search.js`: live typing, debounce, abort, journey, tracking, progressive disclosure e DOM seguro.
- `assets/css/public.css`: isolamento visual e integração com Elementor/tema.
- `assets/css/anchors.css`: suporte de fragment navigation.
- Word Cloud possui CSS/JS próprios.
- Admin possui CSS e JS de operações.

**Decisão:** MANTER contratos de UX e acessibilidade; REDESENHAR o visual pelo Design System derivado do KB2Ops; reduzir bundles e superfícies duplicadas.

### REST

Nenhuma rota REST própria foi identificada como necessária ao fluxo principal ASI 4.6.8 inventariado. O produto atual opera predominantemente por WordPress AJAX/admin-post/hooks.

**Aplicação do princípio de negação:** não criar REST no novo plugin sem consumidor e benefício comprovados.

---

## 2. Gerenciador de Resumo Executivo 0.6.0

Baseline: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`

### Lifecycle/hooks

| Hook/superfície | Tipo | Finalidade | Futuro preliminar |
|---|---|---|---|
| `plugins_loaded` | action | boot do GRE | MANTER princípio de boot simples |
| `init` | action | registrar oito post metas | MANTER Metadata API |
| `bdc_es_loaded` | action emitida | sinalizar runtime carregado | compatibilidade; utilidade futura AINDA NÃO SABEMOS |
| `admin_menu` | action | registrar página de gestão | MANTER intenção / REDESENHAR UI |
| `admin_enqueue_scripts` | action | CSS somente na tela GRE | MANTER loading condicional |
| `admin_post_bdc_es_save_summary` | action | salvar resumo autenticado | MANTER trust boundary |
| `wp_enqueue_scripts` | action | CSS de frontend em post singular | MANTER loading condicional |
| `wp_footer` | action | painel lateral automático | comportamento de produto AINDA NÃO SABEMOS |
| `[bdc_resumo_executivo]` | shortcode | render inline do resumo do post atual | MANTER como compatibilidade inicial |

### Activation/deactivation/cron

Não foram identificados activation/deactivation hooks nem cron no GRE.

**Interpretação:** ausência é coerente com domínio simples em metadata. Não adicionar lifecycle pesado sem necessidade.

### Admin mutante

A única superfície mutante de negócio é `admin_post_bdc_es_save_summary`.

Trust boundary:

1. usuário autenticado;
2. nonce vinculado ao `post_id`;
3. payload precisa ser array;
4. `Summary_Store` valida `edit_post` do objeto;
5. allowlist exata dos oito campos;
6. sanitização antes do write;
7. read-after-write para confirmar persistência.

Não existe handler `admin_post_nopriv_bdc_es_save_summary`.

### AJAX

Nenhum `wp_ajax_*` foi identificado no runtime GRE.

**Decisão:** DESCARTAR necessidade de AJAX para Resumo Executivo no baseline. Server-render/admin-post já atende ao comportamento observado.

### REST

O runtime não registra `register_rest_route()` e as metas são explicitamente `show_in_rest = false`. Há teste arquitetural proibindo a abertura involuntária.

**Decisão:** não abrir REST apenas por conveniência. Qualquer API futura exige consumidor, autorização e SPEC próprios.

### Capabilities

- entrada no menu: `edit_posts`;
- operação sobre um artigo: `edit_post` com `post_id`.

**Decisão:** MANTER autorização por objeto. No plugin unificado, permissões adicionais só devem existir para responsabilidades realmente diferentes (ex.: curadoria global de busca), sem substituir a verificação `edit_post` sobre metadados editoriais.

### Shortcode/frontend

`[bdc_resumo_executivo]`:

- ignora atributos deliberadamente;
- nunca aceita `post_id` arbitrário;
- usa o post corrente;
- retorna vazio quando não há dados;
- escapa conteúdo;
- suprime painel lateral duplicado quando já renderizado inline.

**Contrato forte:** um shortcode público de metadata editorial não deve virar endpoint genérico de leitura de qualquer post por ID sem regra explícita de acesso/contexto.

### Assets

- somente CSS;
- nenhum JavaScript de frontend/admin no pacote runtime;
- assets carregados condicionalmente;
- namespaces próprios evitam colisões.

**Direção:** preservar simplicidade e carregamento condicional; reconstruir visual no Design System único KB2Ops.

### Contrato ASI ↔ GRE — conclusão T046

#### Esperado pelo ASI

- `BDC\ExecutiveSummary\Objective_Provider::read_objective()`;
- `bdc_es_objective_updated`.

#### Exposto pelo GRE 0.6.0

- `Summary_Store::read()` como API interna do GRE;
- `bdc_es_loaded` no boot;
- **nenhum** `Objective_Provider`;
- **nenhum** `bdc_es_objective_updated` pós-update.

#### Consequência

A integração atual das referências é incompatível: o ASI foi escrito contra uma API/evento que não existe no GRE 0.6.0 fixado. Como o adapter ASI falha vazio, a divergência pode permanecer silenciosa.

#### Direção futura

No plugin unificado:

- não criar bridge entre plugins que já estarão no mesmo bounded context;
- expor um serviço/store interno estável para leitura do Objective;
- emitir action de domínio **depois de persistência confirmada** para invalidar projections (índice lexical, trechos, cache e futuro vetor quando aplicável);
- payload do evento deve ser mínimo, documentado e testado;
- reindexação continua derivada; o evento nunca deve editar o post/Elementor.

---

## 3. Integrações a cruzar no próximo bloco KB2Ops

- Content Extractor KB2Ops versus leitura direta de `post_content` do ASI.
- Bridge KB2Ops dos oito `_bdc_es_*` versus GRE Meta Contract/Summary Store.
- Design System/Knowledge Studio/Knowledge Search do KB2Ops versus Admin GRE/ASI.
- ownership de taxonomias/metas versus campos GRE classificatórios.
- consumo real de `[bdc_resumo_executivo]` e demais shortcodes.
- lifecycle hardened do KB2Ops versus simplicidade GRE e complexidade ASI.

T050–T059 permanecem abertos até esse cruzamento.
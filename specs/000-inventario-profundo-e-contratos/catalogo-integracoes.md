# Catálogo de Integrações, Hooks e Superfícies — SPEC-000

> Documento incremental. Cobertura atual: Advanced Search Intelligence 4.6.8. As outras referências serão incorporadas antes do gate final da SPEC-000.

## Baseline ASI

`R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`

## Lifecycle e hooks principais

| Tipo | Contrato observado | Finalidade | Futuro preliminar |
|---|---|---|---|
| activation | activation hook do plugin | marcar preparação/flush | MANTER activation leve |
| deactivation | limpa crons + flush | lifecycle seguro | MANTER |
| `plugins_loaded` | preparação/migrations e bootstrap normal | separar upgrade de steady state | MANTER princípio; REDESENHAR |
| `save_post` | ownership único da Queue | enfileirar reindexação | MANTER contrato único de indexação |
| `bdc_es_objective_updated` | evento externo esperado | reindexar quando Objective muda | MANTER intenção; confirmar provider/evento no GRE |
| `the_content` | Anchor Manager | IDs de navegação sem persistir edição | REDESENHAR com extractor/Elementor |
| `template_include` | busca full-page | superfície pública | AINDA NÃO SABEMOS no produto novo |
| `site_status_tests` | Site Health | health check nativo | SUBSTITUIR POR WORDPRESS/MANTER checks |
| `user_has_cap` | relatório de Search Intelligence | grant capability por roles GAC | REDESENHAR; remover GAC do core |

## Cron

- `asi4_index_cycle`: disparo periódico da fila de indexação.
- Word Cloud: geração horária de snapshot.
- hooks históricos da Word Cloud são limpos pelo bridge de migração.

**Decisão:** cron é trigger, não armazenamento de job. Manter somente crons cuja necessidade permaneça comprovada; Word Cloud não deve ter pipeline paralelo se for reconstruída.

## Shortcodes

- `[asi_search_form]`: busca pública.
- `[bdc_word_cloud]`: Word Cloud pública.
- legacy shortcodes são tratados como consumidores a detectar antes do cutover, não como core moderno.

**Decisão:** preservar contrato de composição pública, mas nomes/quantidade final serão decididos com KB2Ops/UI. Elementor deve permanecer editor/publicador canônico.

## AJAX público

### `asi_live_search`

- autenticado e `nopriv`;
- nonce obrigatório;
- rate limit;
- sanitização e limites de tamanho;
- journey token validado;
- cache neutro de tracking;
- ranking + item ranking;
- telemetria non-fatal;
- estados `idle/success/empty/error/rate_limit`.

### `asi_search_interaction`

- autenticado e `nopriv`;
- nonce + rate limit;
- target HMAC vinculado a event/type/post/item/rank;
- server-side authority do request source;
- deduplicação/idempotência.

### `asi_quality_signal`

Endpoint legado/deprecated; reconhece sinais mas não deve continuar como caminho mutante.

**Decisão:** MANTER trust boundaries e semântica; superfície final AJAX versus REST permanece AINDA NÃO SABEMOS. Não existe razão para criar REST apenas por modernidade.

## Admin actions

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

## Capabilities

- `manage_options`: administração/lifecycle.
- `asi_manage_search_knowledge`: curadoria Search & Knowledge.
- `asi_view_search_intelligence_report`: analytics gerencial.

O relatório ASI concede capability a roles ambientais `gac_admin` e `gac_coordinator`.

**Decisão:** MANTER capacidades por responsabilidade. DESCARTAR conhecimento direto de roles GAC no core; se requerido, usar adapter/configuração.

## Integrações externas observadas

### Gerenciador de Resumo Executivo

Contrato esperado:

- classe `BDC\ExecutiveSummary\Objective_Provider`;
- método estático `read_objective(post_id)`;
- evento `bdc_es_objective_updated` para invalidar/reindexar.

Estado: **drift potencial formalizado**. O lado ASI está comprovado; a existência do provider/evento no runtime GRE deve ser confirmada em T046.

### GAC

- roles `gac_*`;
- tabelas `gac_v15_professional_profiles` e `gac_v15_teams` para contexto organizacional.

Estado: dependência ambiental do relatório, não requisito arquitetural do novo core.

### WPUI / ASI legado

Conhecimento restrito a `legacy/MigrationBridge.php` e compat pré-cutover. Estado: DESCARTAR no greenfield, exceto eventual estratégia de coexistência/migração que será decidida após o cruzamento.

## Frontend/assets

- `assets/js/public-search.js`: live typing, debounce, abort, journey, tracking, progressive disclosure e DOM seguro.
- `assets/css/public.css`: isolamento visual e integração com Elementor/tema.
- `assets/css/anchors.css`: suporte de fragment navigation.
- Word Cloud possui CSS/JS próprios.
- Admin possui CSS e JS de operações.

**Decisão:** MANTER contratos de UX e acessibilidade; REDESENHAR o visual pelo Design System derivado do KB2Ops; reduzir bundles e superfícies duplicadas.

## REST

Nenhuma rota REST própria foi identificada como necessária ao fluxo principal ASI 4.6.8 inventariado. O produto atual opera predominantemente por WordPress AJAX/admin-post/hooks.

**Aplicação do princípio de negação:** não criar REST no novo plugin sem consumidor e benefício comprovados.

## Integrações a cruzar nos próximos blocos

- Content Extractor do KB2Ops versus leitura direta de `post_content` do ASI.
- Design System/Knowledge Studio/Knowledge Search do KB2Ops versus Admin/Public do ASI.
- metadata `_bdc_es_*`, provider e evento do Resumo Executivo versus adapter ASI.
- ownership de taxonomias/metas versus vocabulary/bindings/rules.
- coexistência/migração de stores ASI existentes.

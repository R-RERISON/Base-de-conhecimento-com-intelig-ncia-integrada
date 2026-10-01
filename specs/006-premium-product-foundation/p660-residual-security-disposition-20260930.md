# P-660 — Residual Security / Privacy Disposition

**Data:** 2026-09-30  
**Package analisado:** `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.2.zip`  
**SHA-256:** `1d48ecc25d2f1bbe173ab83e6368f46306c5de054cdd19b411945c328b6051f7`

## Resultado

O inventory heurístico permanece um mecanismo de priorização, não um detector de vulnerabilidades.

Após o hardening p650.2:

- secrets críticos encontrados: 0;
- chamadas de rede de alto risco: 0;
- mutation handlers sem combinação mínima capability/nonce: 0;
- SQL dinâmico classificado como alto risco: 0;
- smoke ambiental do package endurecido: PASS.

## Disposition por categoria

### Mutation handlers — REVIEW / sem blocker alto identificado

Os handlers administrativos centrais seguem o contrato:

`POST -> capability -> nonce -> wp_unslash -> sanitização -> persistência`.

Arquivos centrais cobertos pelo contrato estático P660:

- `class-admin-page.php`;
- `class-classification-admin.php`;
- `class-knowledge-details-admin.php`;
- `class-review-admin.php`;
- Word Cloud AJAX com `check_ajax_referer` + `manage_options`.

Disposition: **REVIEW_LEVEL / manter sob Plugin Check oficial**.

### Public input — REVIEW

`Public_Auth_Bridge::current_url()` foi endurecido:

- não confia mais em `HTTP_HOST`;
- usa `home_url()`;
- utiliza somente path/query derivados de `REQUEST_URI`;
- o redirect final passa por APIs WordPress.

Disposition: **REMEDIATED**.

### Output — REVIEW / justified

Grande parte dos sinais heurísticos é:

- markup literal estático;
- saída já protegida com `esc_html`, `esc_attr`, `esc_url` ou equivalente;
- `sprintf()` interno incorretamente capturado pelo scanner como output;
- atributos booleanos produzidos por condições internas.

Dois casos deliberados exigem contexto:

1. `Public_Auth_Bridge` imprime o markup produzido pelo shortcode Entra instalado. O código contém waiver localizado e comentário de ownership; não é entrada direta do usuário.
2. `public-article-preview.php` imprime `$content_html`, conteúdo previamente produzido pelo pipeline/render model. Esse consumer deve permanecer sob Plugin Check e regressão pública; não será reescrito às cegas no P-660.

Disposition: **JUSTIFIED_OR_REVIEW_LEVEL**, não waiver global.

### Database — REVIEW / static identifiers + prepared values

Revisão dos arquivos sinalizados mostrou:

- journal stores usam `$wpdb->prepare()` para valores;
- Word Cloud usa `esc_like()` + `prepare()`;
- Search Projection Repository prepara valores dinâmicos;
- ocorrências sem `prepare()` são operações com nomes de tabela derivados internamente do próprio WordPress/plugin, por exemplo `SHOW COLUMNS`, `SHOW INDEX`, `COUNT(*)`, sem identificador vindo de request.

Disposition: **REVIEW_LEVEL**, exigir confirmação pelo Plugin Check; nenhum SQL user-controlled não preparado foi identificado nesta revisão.

### Network / SSRF

- chamadas de alto risco detectadas: 0;
- `curl_*` no runtime revisado: 0.

Disposition: **PASS baseline**.

### Secrets

- padrão de private key/API key conhecido no ZIP: 0.

Disposition: **PASS baseline**.

## Gate

P-660 não fecha apenas por esta análise.

Ainda obrigatório:

1. Plugin Check oficial no mesmo ZIP p650.2;
2. disposition de findings residuais reais do Plugin Check;
3. WPCS local dos arquivos alterados quando o tooling local estiver disponível;
4. evidência final versionada.

Nenhum cutover ou retirement é autorizado.


## Addendum — p650.4 / 2026-10-01

A execução real do companion 1.0.4 sobre p650.3 completou os 29 checks estáticos oficiais do Plugin Check 2.1.0 e confirmou runtime nativo/rollback, porém retornou 170 erros e 96 warnings.

Concentração dos erros:
- 140 `WordPress.WP.I18n.TextDomainMismatch`;
- 20 `WordPress.DB.PreparedSQL.NotPrepared`;
- 4 `WordPress.Security.EscapeOutput.OutputNotEscaped`;
- 3 `WordPress.WP.AlternativeFunctions.strip_tags_strip_tags`;
- 1 `PluginCheck.Security.DirectDB.UnescapedDBParameter`;
- 1 `WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters`;
- 1 `plugin_updater_detected`.

Disposition:
- Text Domain: REMEDIATED no p650.4 para o slug distribuído `base-conhecimento-inteligencia-integrada`;
- escaping/input/WordPress primitive: REMEDIATED pontualmente;
- Search Projection SQL: identifiers são internos e valores permanecem placeholder-bound; comments PHPCS localizados adicionados somente nos trechos revisados;
- `suppress_filters=true`: JUSTIFIED/LOCALIZED para seleção determinística do corpus canônico;
- `plugin_updater_detected`: waiver de distribuição privada já existente;
- `UPGRADE.md`: warning conhecido, arquivo exigido pelo contrato de distribuição P650;
- nenhum ignore global foi criado.

Novo candidato:
- `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip`;
- SHA-256 `a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2`;
- 102 arquivos / 83 PHP;
- PHP lint 83/83 PASS;
- 15/15 blobs de runtime alterados (não-bootstrap) correspondem ao source GitHub;
- exige nova execução ambiental/Plugin Check/rollback antes de qualquer closeout.

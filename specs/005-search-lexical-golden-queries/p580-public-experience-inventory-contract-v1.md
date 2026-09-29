# P-580A/H-001/A-001 — Public Experience Inventory Contract v1

**Status:** FROZEN PARA DISCOVERY  
**Data:** 2026-09-20  
**Build alvo:** `0.5.0-p580a.1`  
**Escopo:** read-only / homologação

## 1. Objetivo

Inventariar o runtime público real da Base de Conhecimento antes da reconstrução de UX-004 (Home) e UX-005 (Article Reader), sem executar shortcodes, sem alterar conteúdo e sem depender de suposições sobre snippets/tema/plugins externos.

## 2. Perguntas que o runner deve responder

### Home
- `show_on_front`;
- `page_on_front`;
- `page_for_posts`;
- candidatos de conteúdo que usam shortcodes da Home;
- post type/status/template/source kind dos candidatos;
- shortcodes encontrados e se estão registrados;
- callback real e arquivo/scope de origem de cada shortcode.

### Integrações runtime
Resolver origem de:
- `bc_home_config`;
- `bc_ultimas`;
- `bc_populares`;
- `asi_search_form`;
- `bdc_word_cloud`;
- `bdc_entra_login`;
- AJAX `bdc_home_filter_v270`;
- callbacks relevantes em `the_content`, `wp_footer`, `template_include`, `single_template` e `template_redirect`.

### Tema/CSS
- stylesheet/template ativos;
- child theme sim/não;
- custom CSS ativo: bytes + SHA-256;
- nunca exportar o conteúdo do Custom CSS no JSON.

### Corpus público
Para posts publicados:
- total;
- distribuição por `gutenberg|legacy_html|plain_text|elementor|mixed|empty|error`;
- até 8 IDs de amostra por source kind;
- warnings agregados por código;
- nenhuma exportação de conteúdo editorial.

### Dicas úteis
Detectar sem exportar conteúdo:
- meta keys cujo nome sugira `dica|tip|util|useful`;
- posts cujo `post_content` contenha heading/marcador “Dicas úteis”;
- posts cujo `_elementor_data` contenha “Dicas úteis”;
- contagens e até 12 IDs de amostra por origem.

### GRE
Para os oito meta keys históricos:
- quantidade de posts publicados com valor não vazio;
- até 8 IDs de amostra por campo;
- nunca exportar os valores.

## 3. Segurança

Runner:
- admin-only;
- `manage_options`;
- POST + nonce;
- side-effect free;
- nenhum `do_shortcode()`;
- nenhum `apply_filters('the_content')`;
- nenhum write;
- nenhuma rede externa;
- nenhum conteúdo editorial no artefato;
- nenhum valor de postmeta GRE;
- nenhum Custom CSS bruto;
- caminhos de arquivo sempre relativos a escopo conhecido ou basename redigido.

## 4. Attribution contract

Callbacks devem ser descritos como:
- type;
- callable;
- source_scope;
- source_path.

Scopes permitidos:
- `bdc_plugin`;
- `plugin`;
- `mu_plugin`;
- `theme`;
- `wordpress_core`;
- `external_or_unknown`;
- `internal_or_unknown`.

## 5. Resultado

O JSON é evidência de discovery, não gate de produção.

Ele deve permitir fechar:
- H-001 da UX-004;
- A-001 da UX-005;
- ownership dos shortcodes/actions;
- corpus inicial para regressão;
- discovery de Dicas úteis;
- gaps GRE ambientais.

## 6. Não autoriza

- Home runtime nova;
- Article Reader runtime novo;
- alteração de storage;
- desativação de ASI/GRE;
- mudança de ranking;
- migração editorial;
- merge;
- produção.

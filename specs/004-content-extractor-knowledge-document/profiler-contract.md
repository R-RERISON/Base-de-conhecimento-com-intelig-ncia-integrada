# Contrato do Profiler R-200 — `0.4.0-profile.1`

## Objetivo

Medir a composição real do corpus para orientar o Extraction Contract v1 sem modificar nem exportar o conteúdo editorial.

## Segurança

O profiler:

- exige `manage_options`;
- aceita somente POST com nonce;
- lê apenas posts do tipo `post` e metadados necessários;
- não chama `update_post_meta`, `wp_update_post`, `wp_insert_post`, `wp_set_object_terms` ou equivalentes;
- não executa shortcodes;
- não renderiza Elementor;
- não renderiza dynamic blocks;
- não persiste progresso, cache ou resultado;
- gera JSON diretamente para download;
- não exporta `post_content`, `_elementor_data`, títulos, URLs, IDs de posts ou valores textuais extraídos.

## Métricas autorizadas

- ambiente WordPress/PHP/plugin;
- contagem por `post_status`;
- distribuição exclusiva de source kind;
- flags de presença Elementor/Gutenberg/HTML/shortcode;
- JSON Elementor válido/inválido;
- tipos de widgets Elementor e contagem;
- nomes de campos semânticos allowlisted e contagem;
- nomes de blocos Gutenberg e contagem;
- tags de shortcodes e contagem;
- estrutura agregada: headings, lists, tables, images, links, code/pre, shortcodes;
- tamanho agregado de `post_content` e `_elementor_data` (p50/p95/max/total);
- quantidade heurística de posts Elementor sem campos semânticos conhecidos;
- runtime e peak memory;
- fingerprint editorial before/after e contagem de registros divergentes.

## Source kind exclusivo

Precedência apenas para classificação estatística do profiler:

1. `mixed_elementor_blocks`;
2. `elementor`;
3. `gutenberg`;
4. `legacy_html`;
5. `shortcode_plain`;
6. `plain_text`;
7. `empty`.

Essa precedência **não congela** o contrato definitivo de extração.

## Fingerprint

Para cada post, o profiler calcula em memória uma assinatura com:

- post ID apenas como entrada interna do hash;
- `post_status`;
- `post_modified_gmt`;
- SHA-256 de `post_content`;
- SHA-256 de `_elementor_data`.

O JSON exporta somente:

- fingerprint agregado before/after;
- boolean de igualdade;
- quantidade de posts divergentes.

Nenhum ID divergente é exportado.

## Gate

R-200 só pode passar quando:

- profiler concluir sem fatal/error;
- `editorial_fingerprint_equal=true`;
- `changed_posts_during_run=0` em uma execução limpa;
- o relatório for suficiente para decidir source precedence, adapters, shortcodes e fallback.

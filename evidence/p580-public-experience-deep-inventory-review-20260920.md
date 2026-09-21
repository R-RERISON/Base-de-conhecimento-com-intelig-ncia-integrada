# P-580 Public Experience Deep Inventory — Environmental Review #2

**Data:** 2026-09-20  
**Artifact:** `evidence/p580-public-experience-deep-inventory-20260920T185920Z.json`  
**SHA-256 upload:** `641f0ec8001d734718530033f08aaf23d5a6f37f082a0617a42ad947d14254b7`  
**Build:** `0.5.0-p580a.2`

## Resultado

PASS de discovery:
- schema 1.1.0;
- WordPress 6.9.4;
- PHP 8.5.10;
- MariaDB 12.2.2;
- errors=[];
- throwables=[];
- safety read-only integral;
- runtime ~2.09s.

## Plugins ambientais relevantes

- Code Snippets 3.9.6 — ativo;
- GRE 0.8.0 — ativo;
- GAC Acompanhamento 15.8.7-r2-b3.1.9.16 — ativo;
- WP Unified Indexer 2.4.10 — ativo;
- BdC Entra ID Authentication Gateway 1.1.7 — ativo;
- Login with Microsoft Entra ID 1.0.0 — inativo.

### GRE ambiental

Versão real em homologação: **0.8.0**.

Arquivos observados:
- `class-frontend-renderer.php` — SHA-256 `062e4eb9564e9390ed45c4011694072fe7950113efd28e57dc1776efe7bef3ee`;
- `class-helpful-tips-renderer.php` — SHA-256 `65913c0317a0820e2dafd4f733d73c03f4583c791dcf4a5af38b67405b9b3b8f`;
- `class-helpful-tips-store.php` — SHA-256 `86df682678a8b4b0c26d4ebaf2a4f63864ce4c5e1d46afae0c31c75ae5ca25f7`.

O repo GRE consultado anteriormente não contém esta mesma versão ambiental. O ambiente 0.8.0 passa a ser a baseline funcional de execução; o repo permanece referência histórica/documental.

## Home snippet

Code Snippet:
- ID 9;
- name: `BdC Home — Shortcodes, AJAX e Renderização Segura`;
- global;
- priority 10;
- active=true;
- 8,507 bytes;
- SHA-256 `a465269e50c069dd945c55c3ba5488a32664589d8bdb79755cbcbe23c3c42c5a`.

Symbols:
- `bdc_home_v270_config_shortcode`;
- `bdc_home_v270_ultimas_shortcode`;
- `bdc_home_v270_populares_shortcode`;
- `bdc_home_v270_ajax_filter`.

Behavioral signals:
- WP_Query=true;
- comment_count=true;
- category=true;
- JSON response=true;
- post_meta=false;
- tax_query=false;
- get_terms=false;
- explicit orderby date/modified/meta=false;
- nonce=false;
- capability check=false.

Interpretação segura:
- a Home usa WP_Query e lógica de categoria;
- `comment_count` faz parte do comportamento do snippet e é o único sinal de popularidade detectado;
- não inferir mais detalhes que o artefato não prova;
- o novo contrato pode melhorar a métrica de popularidade, mas deve declarar a mudança e aceitar visual/functional parity antes do cutover.

## Helpful Tips contract descoberto

Physical key:
`_bdc_es_helpful_tips`

Coverage:
- 7 posts publicados.

Shape em todos os posts observados:
- PHP array;
- list ordenada;
- 1–4 itens;
- item keys: `title`, `content`;
- ambos string;
- nenhuma outra key;
- valores não exportados.

Decisão:
- BDC pode absorver o storage existente sem migration obrigatória;
- criar API/Store BDC própria sobre a mesma physical key;
- nenhuma duplicação de storage durante a primeira absorção;
- writer futuro deve preservar lista ordenada `{title:string,content:string}`.

## Public article pipeline

Callbacks relevantes permanecem:
- GAC PostActions priority 12;
- GAC KnowledgeBridge priority 13;
- GRE Helpful Tips priority 15;
- WP Unified Indexer anchors priority 20;
- GRE Executive Summary Rail priority 30.

Decisão:
- Article Reader BDC não deve bypassar `the_content`;
- takeover deve ser progressivo;
- primeiro shell BDC preserva filtros;
- ao internalizar Tips/Rail, remover apenas callbacks GRE específicos e somente sob feature flag/cutover controlado;
- GAC/WPUI permanecem integrações externas até contrato próprio.

## Corpus

606 publish:
- 528 legacy_html;
- 41 plain_text;
- 31 elementor;
- 3 mixed;
- 3 gutenberg.

Article Reader compatibility priority:
1. legacy_html;
2. plain_text;
3. elementor;
4. mixed;
5. gutenberg/core-blocks native path.

## Discovery closeout

UX-004 H-001:
- ownership Home: CLOSED;
- exact snippet metadata/hash: CLOSED;
- external dependencies: CLOSED;
- exact internal algorithm of legacy snippet is not required to copy; parity is behavioral, not source-level.

UX-005 A-001:
- theme/template flow: CLOSED;
- source-kind corpus: CLOSED;
- GRE rail owner: CLOSED;
- Helpful Tips owner/storage/shape: CLOSED;
- regression samples: AVAILABLE;
- third-party content pipeline: CLOSED.

Next:
- freeze H-010 Home contract;
- freeze A-010 Article Reader contract;
- begin implementation behind non-disruptive feature flags/preview;
- no ASI/GRE decommission yet.

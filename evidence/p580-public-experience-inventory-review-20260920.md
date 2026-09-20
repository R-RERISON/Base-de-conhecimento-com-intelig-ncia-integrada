# P-580 Public Experience Inventory — Environmental Review #1

**Data:** 2026-09-20  
**Artifact:** `evidence/p580-public-experience-inventory-20260920T184051Z.json`  
**Artifact SHA-256:** `ea4b050f9d1f8cc52954498acb5ed363a7273d34b4528719088db0f7d4b5c65a`  
**Build:** `0.5.0-p580a.1`

## Resultado

Discovery runner PASS:
- safety read-only;
- errors=[];
- throwables=[];
- runtime ~2.1s.

## Home

- WordPress front mode: page;
- active Home: ID 41395;
- type: page;
- status: publish;
- template: elementor_header_footer;
- source kind: elementor;
- legacy/private Home ID 87 também existe.

Home ativa contém:
- bc_home_config;
- bc_ultimas;
- bc_populares;
- asi_search_form;
- bdc_word_cloud.

Owners:
- bc_home_config -> Code Snippets eval runtime;
- bc_ultimas -> Code Snippets eval runtime;
- bc_populares -> Code Snippets eval runtime;
- bdc_home_filter_v270 -> Code Snippets eval runtime;
- bdc_entra_login -> bdc-entra-id-authentication-gateway;
- asi_search_form -> não registrado com ASI desligado;
- bdc_word_cloud -> não registrado com ASI desligado.

## Theme / CSS

- Astra 4.13.3;
- sem child theme;
- Additional CSS presente;
- 36,804 bytes;
- SHA-256 b44b57c6a86df6b35ec1df5dfcba86c60e17555ecc2dd7d836c81c36499303a2.

## Article runtime owners observados

Em the_content:
- Elementor image optimization;
- Ultimate FAQs;
- GAC PostActions panel;
- GAC Knowledge Contribution bridge;
- GRE Helpful_Tips_Renderer;
- WP Unified Indexer anchor injection;
- GRE Frontend_Renderer summary side panel.

Conclusão: Article Reader BDC precisa preservar/acomodar integrações externas úteis antes de assumir o shell. Não remover filtros terceiros como efeito colateral do template.

## Corpus publicado

606 posts publicados:
- Gutenberg: 3;
- legacy_html: 528;
- plain_text: 41;
- Elementor: 31;
- mixed: 3;
- empty/error: 0.

A prioridade de compatibilidade do Article Reader deve ser:
1. legacy_html;
2. plain_text;
3. Elementor;
4. mixed;
5. Gutenberg/Core Blocks como destino futuro.

Warnings dominantes incluem:
- HTML_STRUCTURAL_WRAPPER_TRAVERSED: 147;
- HTML_PARSE_RECOVERED: 55;
- SHORTCODE_NOT_EXPANDED: 54;
- ELEMENTOR_JSON_INVALID: 41;
- HTML empty-heading families: 35.

## Helpful Tips

Meta key descoberta:
`_bdc_es_helpful_tips`

Cobertura:
- 7 posts publicados;
- zero occurrence em post_content;
- zero occurrence em _elementor_data.

Conclusão:
- Dicas úteis já são dados estruturados ambientais;
- owner runtime observado: GRE Helpful_Tips_Renderer;
- formato físico ainda não congelado; requer shape profiling antes do contrato BDC.

## GRE coverage

Posts publicados com valor não vazio:
- objective: 16;
- responsible_team: 9;
- catalog_item: 8;
- affected_service: 7;
- systems_involved: 6;
- target_audience: 6;
- escalation: 6;
- important: 6.

Affected Service e Systems Involved continuam gaps canônicos do BDC e não podem ser descartados.

## Gaps restantes de discovery

1. identificar snippet ID/name/hash que contém as funções Home v2.7.0;
2. obter sinais de comportamento de latest/popular/filter sem exportar código;
3. identificar versão real do GRE ambiental e hashes dos arquivos Tips/Frontend;
4. perfilar somente a estrutura de _bdc_es_helpful_tips (tipo, keys, item count), sem valores.

Esses quatro pontos serão coletados por `0.5.0-p580a.2`.

## Gate state

- P-580A: OPEN / environmental inventory #1 PASS;
- P-580B: OPEN;
- UX-004 H-001: PARTIAL PASS;
- UX-005 A-001: PARTIAL PASS;
- G-585: PAUSED;
- G-590: BLOCKED.

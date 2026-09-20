# Tasks — UX-005 Public Article Reader

## A-001 Inventory
- [x] A000-PKG discovery runner `0.5.0-p580a.1` preparado e validado localmente.
- [x] A000-ENV inventário `p580a.1` executado — evidence `p580-public-experience-inventory-20260920T184051Z.json`.
- [x] A001 Astra 4.13.3; sem child theme; nenhum `single_template` custom; Elementor/Royal atuam em `template_include`.
- [x] A002 baseline classificada: Product shell vs Legacy Elementor/HTML compatibility; detalhamento preservado em UX-005.
- [x] A003 corpus publicado: legacy_html 528, plain_text 41, Elementor 31, mixed 3, Gutenberg 3.
- [x] A004 GRE rail ambiental identificado em `Frontend_Renderer::append_side_panel`, priority 30.
- [x] A005 Helpful Tips fechado: `_bdc_es_helpful_tips`, list `{title:string,content:string}`, 7 posts, GRE 0.8.0.
- [x] A006 amostras por source kind registradas pelo artifact; incluir long/empty summary e tips present/absent na seleção final.

- [x] A007-PKG `0.5.0-p580a.2` deep inventory preparado.
- [x] A007-ENV p580a.2 PASS; GRE ambiental 0.8.0 + hashes capturados.

## A-010 Contract
- [x] A010 reader template contract — `a010-article-reader-contract-v1.md`.
- [x] A011 Executive Summary Rail composed read model frozen.
- [x] A012 Structured Tips contract frozen sobre physical key existente.
- [x] A013 source-kind/third-party compatibility contract frozen.
- [x] A014 conditional/scoped asset contract frozen.
- [x] A015 print/responsive/accessibility contract frozen.

## A-020 Implementation
- [ ] A020 plugin-owned Article Reader shell.
- [ ] A021 hero/metadados.
- [ ] A022 Structured Tips.
- [ ] A023 Executive Summary Rail.
- [ ] A024 content renderer integration.
- [ ] A025 legacy Elementor compat stylesheet.
- [ ] A026 Core Blocks native path.

## A-030 Regression
- [ ] A030 Gutenberg article.
- [ ] A031 legacy HTML article.
- [ ] A032 Elementor article.
- [ ] A033 mixed article.
- [ ] A034 table/media-heavy.
- [ ] A035 long/empty summary.
- [ ] A036 tips present/absent.

## A-040 Human
- [ ] A040 visual/scroll acceptance.

## A-050 Theme decoupling
- [ ] A050 Astra Custom CSS no longer required for BDC reader.

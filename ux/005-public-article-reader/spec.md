# UX-005 — BDC Public Article Reader

**Status:** DISCOVERY / CONTRACT  
**Data:** 2026-09-20  
**Inputs:** screenshot real 2026-09-20 + CSS Astra/BdC v3 preview + GRE 0.6.0 + UX-001 heritage addendum  
**Visual baseline:** Visual Contract v2

## 1. Objetivo

Fazer o plugin BDC controlar integralmente a experiência pública de leitura dos artigos da Base, sem depender do Astra Custom CSS como implementação de produto e sem exigir Elementor para layout.

O conteúdo editorial continua em WordPress. O BDC passa a possuir o **reader shell**.

## 2. Separação de responsabilidades

### WordPress
- permalink/request;
- post identity/status;
- autenticação/capabilities;
- canonical post content.

### Tema
- pode fornecer chrome global enquanto necessário;
- não é owner da experiência do artigo BDC.

### BDC plugin
- Article Reader shell;
- hero documental;
- metadados;
- conteúdo;
- structured tips;
- Executive Summary Rail;
- compatibilidade legado;
- typography/tables/media;
- responsive/accessibility;
- print.

## 3. Estratégia de template

A Home e os artigos não devem ser implementados como grandes blocos HTML/CSS em posts.

Direção:
- plugin identifica a superfície BDC;
- plugin fornece template/renderer próprio;
- `post_content` permanece somente conteúdo editorial;
- nenhum layout BDC precisa ser salvo no conteúdo;
- Elementor legacy pode ser renderizado por adapter/compat layer durante transição;
- Core Blocks são destino editorial canônico.

A decisão técnica exata de `template_include`/template hierarchy será congelada após smoke de compatibilidade com Astra e posts legados.

## 4. Article Reader layout

Desktop largo:

`Header BDC -> Hero documental -> [Main content + Executive Summary Rail]`

Main content:
- largura confortável de leitura;
- Structured Tips no topo quando existirem;
- conteúdo editorial;
- tabelas/imagens/código/blockquote;
- anchors/deep links quando contratados.

Rail:
- contexto estruturado;
- permanece visível enquanto o usuário percorre o artigo;
- não sobrepõe conteúdo;
- scroll interno quando necessário;
- read model composto;
- sem writer.

## 5. Structured Tips — “Dicas úteis”

Capacidade observada no ambiente atual e declarada pelo Product Owner.

Semântica:
- lista ordenada de informações operacionais curtas;
- cada item possui ao menos título/rótulo + conteúdo;
- renderização automática no início do artigo;
- ausência de itens => nenhum bloco;
- ordem preservada;
- edição pertence ao Workspace;
- não exigir HTML manual/Elementor/shortcode.

Requisitos de produto:
- adicionar;
- editar;
- remover;
- reordenar;
- validação de payload;
- capacidade por post;
- saída escapada;
- acessível;
- responsiva;
- indexável pelo Knowledge Document/Search após contrato correspondente.

Storage físico ainda NÃO congelado neste documento. A decisão deve comparar:
- postmeta estruturado;
- primitive WordPress alternativa;
- requisitos de revisão/versionamento/search.

## 6. Executive Summary Rail

Fontes:
- WP_Post;
- Summary;
- Classification;
- futuros owners de Serviço Afetado/Sistemas Envolvidos.

Não duplicar metadata.

Comportamento desktop:
- preferir `position: sticky` dentro da grid do reader quando o layout permitir;
- evitar `position: fixed` global como contrato final, porque fixed depende de gutter/tema e pode sobrepor conteúdo;
- se testes demonstrarem necessidade de fixed, exigir cálculo de gutter e regressão visual explícita.

Viewport menor:
- rail reflowa para bloco contextual;
- nada de painel flutuante cobrindo leitura.

## 7. CSS ownership

O CSS atual do Astra é baseline visual/compat, não runtime alvo.

Separar no plugin:
- `public-foundation.css` — tokens/shared;
- `public-header.css`;
- `public-home.css`;
- `public-article.css`;
- `public-summary-rail.css`;
- `public-legacy-compat.css`.

Carregamento condicional por superfície.

Proibido como arquitetura final:
- folha global única com Home + ASI + single-post + Elementor;
- seletores genéricos que afetam posts fora do escopo BDC;
- dependência de IDs/classes ASI;
- Custom CSS do tema como requisito funcional.

## 8. Legacy compatibility

O CSS atual contém hardening importante para Elementor:
- width/max-width;
- stretched sections;
- columns/widgets;
- media bounds;
- headings/text wrapping.

Não descartar esse conhecimento.

Migrar o que ainda for necessário para `public-legacy-compat.css`, estritamente escopado a artigos BDC detectados como legacy Elementor.

À medida que posts migram para Core Blocks, compat layer deixa de ser aplicada por post.

## 9. Gates

### A-001 Inventory
- current theme/template flow;
- CSS ownership;
- post source kinds;
- GRE rail;
- Structured Tips source;
- legacy article divergences.

### A-010 Contract
- reader template;
- data composition;
- tips contract;
- rail behavior;
- legacy compatibility;
- print/responsive.

### A-020 Implementation
- plugin-owned reader;
- hero;
- content;
- tips;
- rail;
- conditional assets.

### A-030 Corpus regression
- sample Gutenberg;
- legacy HTML;
- Elementor;
- mixed;
- table-heavy;
- image-heavy;
- long summary;
- no summary;
- with/without tips.

### A-040 Human acceptance
- visual/reference comparison;
- scroll behavior;
- no clipping/overlap;
- desktop + applicable breakpoints.

### A-050 Theme decoupling
- remove requirement for Astra Custom CSS;
- Astra CSS can remain fallback during acceptance window;
- prove BDC reader works with custom CSS disabled in homologation.

## 10. Não autoriza

- writer editorial automático;
- removal of Elementor adapter;
- removal of GRE;
- production;
- G-585/G-590 advance before acceptance.

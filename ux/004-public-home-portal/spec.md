# UX-004 — BDC Public Home / Portal de Entrada

**Status:** DISCOVERY / CONTRACT  
**Data:** 2026-09-20  
**Dependência:** P-580A ASI Functional Inventory  
**Visual baseline:** UX-002 / Visual Contract v2  
**Legacy references:** Home v2.7.0 + Header v2.6.5 fornecidos pelo Product Owner

## Objetivo

Transformar a página pública da Base de Conhecimento em uma superfície canônica do plugin BDC, preservando as funções existentes e eliminando a manutenção fragmentada entre HTML, header, shortcodes ASI e scripts isolados.

## Princípio

A Home atual é baseline funcional, não arquitetura obrigatória.

Pode ser redesenhada, simplificada e melhorada, desde que:
- nenhuma capacidade existente relevante desapareça;
- Search e Word Cloud sejam BDC-owned;
- o resultado siga Visual Contract v2;
- WordPress continue plataforma/shell;
- dados e autorização continuem sob WordPress.

## Capacidades obrigatórias v1

1. Header BDC;
2. marca/identidade;
3. perfil/SSO existente quando disponível;
4. links rápidos;
5. Search pública BDC;
6. Word Cloud BDC;
7. categorias/filtros;
8. Últimas Atualizações;
9. Instruções Populares;
10. loading;
11. zero result;
12. degraded;
13. error;
14. responsividade 782/520;
15. teclado/foco/ARIA.

## Arquitetura desejada

Evitar múltiplos fragmentos editoriais independentes.

Preferência de implementação:
- um único entry-point público BDC na página WordPress;
- componentes PHP coesos sob namespace BDC;
- assets CSS/JS próprios e escopados;
- configuração server-side via APIs WordPress;
- Search/Word Cloud reutilizam serviços BDC, não markup de outro plugin;
- Header e Body compartilham tokens e lifecycle.

A escolha final entre shortcode único, dynamic block ou template integration será decidida após inventário do ambiente atual e compatibilidade Elementor/Core.

## Search pública

Não criar segundo ranker.

A Search pública deve usar a engine BDC congelada, com uma fachada de autorização apropriada para frontend:
- publish/public visibility;
- private/restricted somente quando WordPress autorizar;
- Projection nunca decide autorização;
- nenhum draft/pending/future leak;
- same normalizer/ranker/result contracts quando aplicável;
- public-specific response contract e rate limits.

## Word Cloud

Reconstruir como módulo BDC, preservando:
- generator;
- quality;
- snapshot;
- scheduling;
- health;
- allow/blocklist;
- content/taxonomy/search/vocabulary signals;
- click-to-search;
- public rendering;
- admin operations.

A primeira versão BDC pode usar somente fontes já disponíveis no BDC, desde que qualquer fonte ainda não migrada seja explicitamente marcada como pending e não desapareça silenciosamente.

## Home legacy artifacts ainda a localizar

- `[bc_home_config]`;
- `[bc_ultimas]`;
- `[bc_populares]`;
- AJAX `bdc_home_filter_v270`;
- `[bdc_entra_login]`;
- ownership do header atual;
- origem de categorias/filtros;
- origem dos dados de popularidade.

## Gates UX-004

### H-001 Inventory
- localizar ownership de todos os shortcodes/actions/assets da Home;
- identificar source de popularidade e filtros;
- registrar dependências externas.

### H-010 Contract
- public Search auth contract;
- Word Cloud contract;
- Home component contract;
- data/shortcode migration plan;
- visual reference.

### H-020 Implementation
- BDC-owned Home;
- Search pública;
- Word Cloud;
- latest/popular/category;
- one maintained asset bundle/surface.

### H-030 Technical acceptance
- functional parity;
- security;
- no ASI runtime;
- responsive/accessibility;
- no duplicate search engine/ranker.

### H-040 Human visual acceptance
- comparação com Home atual;
- Visual Contract v2;
- desktop + breakpoint aplicável;
- Product Owner approval.

### H-050 Decommission proof
- ASI off;
- Home remains functional;
- no raw `[asi_*]` output;
- no missing Word Cloud/Search behavior.

## Não autoriza

- merge;
- produção;
- remoção física de ASI data;
- mudança de ranking;
- semantic/vector/AI.

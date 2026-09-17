# UX-002 — Mockup Visual Foundation

**Status:** IMPLEMENTAÇÃO INICIAL  
**Branch:** `ux002-mockup-visual-foundation`  
**Build alvo de homologação:** `0.4.0-ux002.1`

## 1. Problema

O runtime atual já respeita WordPress-first funcionalmente, porém sua apresentação ainda herda aparência genérica do wp-admin em vários pontos. Isso produz drift em relação ao Design System UX-001, ao protótipo UI-as-Code e aos mockups versionados em `scr/`.

## 2. Baseline

- WordPress permanece shell administrativo;
- não existe segunda sidebar do produto;
- Summary, Classificação, Review e Histórico funcionam com contratos próprios já homologados;
- Design System v1 já define tokens/componentes/responsividade;
- protótipo UX-001 já materializa Knowledge List, Workspace e estados;
- `scr/` contém 10 mockups visuais adicionados à `main` em `72f26121373b12fa08f08ea8d38b4c8d73f8637c`;
- CSS atual aproxima o Design System, mas ainda permite forte leitura visual de tela WordPress genérica.

## 3. Resultado esperado

Fazer o plugin parecer um único produto BDC dentro do shell WordPress, sem recriar WordPress e sem alterar contratos de dados.

A base deve ser suficientemente canônica para que novas telas reutilizem a mesma linguagem visual por padrão.

## 4. Autoridade e precedência

Em decisão visual:

1. segurança, acessibilidade e restrições funcionais aprovadas;
2. mockups vigentes em `scr/`;
3. `visual-contract-v2.md` e Design System UX-001;
4. protótipo UI-as-Code UX-001;
5. runtime legado, somente quando não conflitar.

Mockup nunca autoriza feature, writer ou contrato de dados inexistente.

## 5. Slice inicial

Esta primeira implementação atua somente na camada visual compartilhada já existente:

- shell/hero da Base de Conhecimento;
- Knowledge List;
- botões e feedback;
- campos/formulários;
- Knowledge Workspace header;
- tabs;
- Overview cards;
- Classificação;
- Review & Governança;
- Histórico;
- responsividade 782px/520px.

Não cria novas funcionalidades de busca, dashboard, IA, migração ou writer.

## 6. WordPress-first

Mantidos:

- menu/sidebar do wp-admin;
- `WP_Query`;
- forms e `admin-post.php`;
- nonces/capabilities;
- controles HTML e semântica WordPress onde úteis;
- notices e comportamento nativo, reestilizados somente dentro do escopo BDC.

Não serão introduzidos React, Tailwind, Bootstrap, webfonts externas, SPA ou segunda navegação lateral.

## 7. Princípio de negação

Não criar framework de UI próprio neste slice. Reutilizar classes já existentes e promover tokens CSS comprovados pelo UX-001. Nova abstração PHP só será criada quando múltiplas telas reais demonstrarem duplicação que a justifique.

## 8. Segurança e dados

Mudança visual não altera:

- payloads;
- endpoints;
- capabilities;
- nonce;
- storage;
- taxonomias;
- Comments API;
- `post_content`;
- `_elementor_data`.

## 9. Testes e gates

Obrigatórios antes de merge:

- PHP lint do pacote;
- CSS estrutural sem blocos desbalanceados;
- smoke funcional dos writers existentes sem mudança de payload;
- revisão visual desktop 1440;
- revisão em 782px;
- revisão em 520px;
- foco visível e navegação linear;
- contraste/estados sem depender somente de cor;
- comparação humana com mockups aplicáveis;
- nenhuma regressão funcional em Summary/Classificação/Review/Histórico.

## 10. Aceite do slice inicial

PASS somente se:

- o conteúdo do produto deixar de aparentar uma coleção de telas nativas desconectadas do WordPress;
- WordPress continuar reconhecível apenas como shell administrativo;
- componentes atuais convergirem para tokens e padrões do contrato;
- nenhuma feature não implementada parecer disponível;
- responsividade e foco permanecerem funcionais;
- homologação humana confirmar alinhamento visual com `scr/`.

## 11. Rollback

Rollback é simples e não destrutivo: reverter os CSS/build da UX-002. Não existe migration de dados.

## 12. Fora de escopo

- reconstruir todas as telas futuras;
- implementar busca/IA somente por estarem desenhadas em mockup;
- mudar Elementor/editorial;
- criar segunda sidebar;
- framework frontend;
- persistência nova.

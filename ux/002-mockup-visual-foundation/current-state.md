# Current State — UX-002

## Baseline

`main`: `72f26121373b12fa08f08ea8d38b4c8d73f8637c`.

A baseline contém 10 referências PNG em `scr/` e mantém UX-001 como contrato visual anterior: Design System v1 + protótipo UI-as-Code.

## Diagnóstico

O runtime era WordPress-first funcionalmente, porém apresentava drift visual:

- page title e tabelas ainda carregavam forte leitura de wp-admin;
- controles `.button`, `.widefat`, notices e campos mantinham aparência nativa em vários estados;
- shell do Workspace aproximava o protótipo, mas a linguagem não era suficientemente dominante/consistente;
- inexistia regra constitucional explícita tornando os mockups atuais autoridade do runtime.

## Implementação inicial concluída

Build de homologação: `0.4.0-ux002.1`.

Implementado:

- Constituição `1.2.0` com mockups/Design System como autoridade visual e princípio de IA orientado a reduzir esforço de leitura/tempo para resposta confiável;
- `visual-contract-v2.md`;
- camada compartilhada `assets/css/visual-foundation.css`;
- carregamento isolado pela classe `Visual_Foundation` somente na tela BDC;
- tokens canônicos para cor, radius, border, shadow, foco e tipografia;
- convergência de hero, botões, tabelas, paginação, formulários, tabs, cards, badges/notices e estados;
- helpers reutilizáveis para toolbar, métricas, field-grid, form-actions e empty-state;
- responsividade em 900/782/520px;
- nenhuma alteração de payload, endpoint, capability, nonce, storage, `post_content` ou `_elementor_data`.

## Validação técnica local

- PHP lint: 29 arquivos do pacote PASS;
- bootstrap UX-002: PASS;
- `class-visual-foundation.php`: PASS;
- CSS: 87 blocos abertos/87 fechados;
- nenhum seletor global `body` introduzido;
- ZIP reextraído e revalidado: PASS;
- raiz única: PASS;
- SHA-256 do pacote `0.4.0-ux002.1`: `d2e95e49600a0cd5776a8546ff34cc41109835b6838018fd379900d01179a215`.

## Estado do gate

**IMPLEMENTAÇÃO BASE: READY FOR HOMOLOGATION.**

Ainda não declarar UX-002 PASS visual. Falta revisão humana contra `scr/` em desktop, 782px e 520px e smoke funcional dos fluxos Summary/Classificação/Review/Histórico.

O PR #6 deve permanecer DRAFT até esse aceite.

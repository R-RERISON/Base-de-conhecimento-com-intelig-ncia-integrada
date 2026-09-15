# Package `0.3.0-dev.7` — Histórico + teclado

## Objetivo

Avançar o G-110 após o smoke ambiental do `0.3.0-dev.6`, adicionando somente a próxima slice autorizada do Knowledge Workspace.

## Mudanças

- adiciona tab `Histórico` ao Workspace;
- Histórico é projection read-only de `Review_Store::history()`;
- nenhuma nova tabela, meta ou writer foi criado;
- histórico exibe transição, ator, data, estado final e nota quando existente;
- estado vazio explícito quando não existem eventos;
- overview passa a apontar também para Histórico;
- adiciona navegação de foco por `ArrowLeft`, `ArrowRight`, `Home` e `End` entre tabs;
- adiciona `assets/js/workspace.js`;
- adiciona `assets/css/history.css` como extensão visual da foundation já validada;
- `Review_Store`, `Review_Contract`, `Summary_Store` e `Classification_Store` permanecem inalterados;
- runners G-070 permanecem temporariamente presentes até G-130.

## Validação local do artefato

- versão: `0.3.0-dev.7`;
- PHP lint: PASS `13/13`;
- JavaScript syntax check: PASS;
- ZIP instalável gerado com raiz correta do plugin;
- SHA-256: `1a8e85acdc63af7c5bc568bb9bf518019cf2644c403c9252d8d1761b76958dc9`.

## Critério da próxima homologação

O build deve comprovar no ambiente real:

1. a nova tab Histórico aparece sem regressão das quatro tabs existentes;
2. artigo sem eventos apresenta estado vazio correto;
3. após uma transição real, Histórico mostra exatamente o evento canônico correspondente;
4. setas/Home/End movem o foco entre tabs;
5. 1440px, 1024px, 782px e aproximadamente 492px não apresentam overflow horizontal indevido;
6. Summary, Classificação e Review continuam funcionais e permanecem no contexto após ação.

G-110 continua ACTIVE até Browser Acceptance final.

# Referências e Inventário Inicial

> **Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

Este documento registra o **Baseline Zero** dos três projetos que originam o novo produto. Ele não substitui a leitura linha a linha prevista na SPEC-000; serve como mapa inicial e como âncora de rastreabilidade.

## 1. Projetos de referência

### 1.1 KB2Ops — Operational Knowledge Engine

Repositório: https://github.com/R-RERISON/KB2Ops-Operational-Knowledge-Engine

Baseline observado:

- versão: `0.2.1` hardened;
- árvore `main`: `f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`;
- WordPress >= 6.6;
- PHP >= 8.1.

### Valor comprovado

- Knowledge Studio;
- Knowledge Search;
- fluxo de revisão;
- estados de conhecimento;
- AI READY;
- Content Extractor para Elementor/HTML;
- Design System;
- UI/UX integrada;
- migração segura em duas fases;
- hardening de ativação e rotas.

### Dívida/limitação deliberada

- busca atual é provisória e baseada em WordPress/meta;
- IA ainda não está conectada;
- semantic search/vetores ainda não existem;
- alguns campos de classificação são texto em postmeta e devem ser reavaliados como taxonomias no novo projeto.

### Regra para o novo produto

**KB2Ops é referência de produto e Design System, não base de código obrigatória.**

---

## 2. Advanced Search Intelligence — ASI

Repositório: https://github.com/R-RERISON/Advanced-search-Intelligence

Baseline observado:

- versão: `4.6.8`;
- DB schema: `4.6.0`;
- algoritmo público: `4.5.0`;
- árvore `main`: `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`;
- WordPress >= 6.1;
- PHP >= 8.0.

### Valor comprovado

- índice lexical próprio;
- FULLTEXT;
- índice de itens/trechos;
- QueryContext;
- vocabulário/aliases;
- regras de relevância;
- ranking de posts;
- ranking de itens;
- anchors/deep links;
- term bindings;
- fila durável com lease/retry/dead;
- Migration Runner;
- Base Reconciler;
- Post-Install Orchestrator;
- Search Events;
- Search Interactions;
- Search Outcomes;
- Inteligência de Buscas;
- Golden Queries;
- Quality Diagnostics;
- Word Cloud;
- privacidade minimal/pseudonymous/audit;
- cache e degradação parcial;
- pacote e validação de release.

### Stores/tabelas canônicas observadas

1. `asi_search_index`
2. `asi_search_items`
3. `asi_term_bindings`
4. `asi_vocabulary`
5. `asi_relevance_rules`
6. `asi_index_queue`
7. `asi_audit_log`
8. `asi_search_events`
9. `asi_search_interactions`
10. `asi_golden_queries`
11. `asi_quality_daily`
12. `asi_migrations`

### Risco arquitetural

ASI acumulou elevada complexidade. Há classes grandes e uma extensa camada operacional/compatibilidade. Essa maturidade é uma fonte de requisitos e testes, mas não deve ser copiada cegamente.

### Regra para o novo produto

**Preservar comportamento, dados valiosos, invariantes, casos de falha e Golden Queries; redesenhar o código.**

---

## 3. Gerenciador de Resumo Executivo

Repositório: https://github.com/R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento

Baseline observado:

- versão: `0.6.0`;
- árvore `main`: `1120a534d8eb2288460c2c675730deef0d67c365`;
- WordPress >= 6.6;
- PHP >= 8.1.

### Valor comprovado

- implementação pequena e coesa;
- WordPress Metadata API;
- contrato explícito de oito campos;
- Summary Store;
- dashboard de cobertura;
- editor administrativo;
- renderer/shortcode;
- sem tabela própria;
- sem REST/AJAX obrigatório;
- sem JavaScript obrigatório para domínio central.

### Campos canônicos atuais

- `_bdc_es_objective`
- `_bdc_es_responsible_team`
- `_bdc_es_catalog_item`
- `_bdc_es_affected_service`
- `_bdc_es_systems_involved`
- `_bdc_es_target_audience`
- `_bdc_es_escalation`
- `_bdc_es_important`

### Regra para o novo produto

**É a principal referência de clean code e WordPress-first.** Os meta keys devem ser preservados inicialmente para evitar migração de dados sem necessidade.

---

# 4. Sobreposição funcional atual

| Domínio | KB2Ops | ASI | Resumo Executivo | Novo produto |
|---|---|---|---|---|
| UI integrada | forte | própria/legada | própria | KB2Ops como referência visual |
| Resumo Executivo | lê via bridge | tenta ler via provider | proprietário | um único módulo interno |
| Revisão/curadoria | forte | curadoria de busca | resumo | Knowledge Studio único |
| Busca lexical | básica/provisória | forte | — | engine nova inspirada no ASI |
| Itens/trechos | resolução simples | forte | — | engine nova |
| Analytics | leves | forte | cobertura | analytics integrado |
| Golden Queries | — | forte | — | obrigatório para busca |
| IA | planejada | ausente | ausente | subsystem nativo do novo projeto |
| Vetores | planejados | ausentes | — | subsystem semântico opcional |
| Elementor | extractor | conteúdo é indexado | renderer não altera | fonte editorial read-only |

---

# 5. Gap de contrato já identificado

O ASI 4.6.8 declara integração com uma classe pública:

`\BDC\ExecutiveSummary\Objective_Provider::read_objective()`

O runtime `0.6.0` observado do Gerenciador de Resumo Executivo carrega:

- `Meta_Contract`;
- `Summary_Store`;
- `Coverage_Dashboard`;
- `Admin_Page`;
- `Frontend_Renderer`;
- `Plugin`.

No baseline lido, `Objective_Provider` não aparece no bootstrap do GRE.

O ASI também documenta o evento `bdc_es_objective_updated`, enquanto a atualização observada em `Summary_Store` deve ser confirmada linha a linha quanto à emissão desse evento.

### Consequência

Há indício forte de **drift entre documentação/contratos dos plugins**. A SPEC-000 deve confirmar isso e registrar o comportamento real antes de qualquer reconstrução.

No produto novo esse tipo de bridge deve desaparecer: o módulo de busca consome um serviço interno de Resumo Executivo.

---

# 6. Invariantes a preservar

## Editorial

- `WP_Post`/Elementor são fonte da verdade.
- Novo plugin não escreve em `_elementor_data`.
- Novo plugin não substitui editor/publicação.

## Resumo Executivo

- oito campos existentes não devem ser perdidos;
- post ID continua vínculo canônico;
- título não deve ser duplicado como dado canônico.

## Busca

- resultados devem ser determinísticos e explicáveis quando possível;
- busca lexical continua disponível mesmo sem IA/vetores;
- consultas representativas devem possuir regressão;
- deep links devem apontar para destino comprovado;
- cache não deve fabricar telemetria ou alterar semântica do resultado.

## Telemetria

- erro não é zero-result;
- clique/engajamento deve ser correlacionável de modo seguro;
- privacidade mínima deve ser possível;
- dados sensíveis não devem ser coletados sem necessidade.

## Operações

- instalação não é lugar para trabalho pesado/destrutivo;
- jobs precisam ser retomáveis quando realmente assíncronos;
- falhas precisam ser explícitas;
- estados pausado/bloqueado/falhou não podem ser exibidos como sucesso.

---

# 7. O que a SPEC-000 ainda precisa ler em detalhe

O inventário só será considerado concluído após leitura sistemática dos três repositórios, incluindo:

- bootstrap e lifecycle;
- hooks/actions/filters;
- shortcodes;
- admin menus/rotas;
- AJAX actions;
- REST routes;
- options;
- transients;
- cron hooks;
- capabilities;
- post meta;
- taxonomias;
- tabelas/colunas/índices;
- filas;
- caches;
- eventos de domínio;
- integrações externas;
- CSS/JS;
- templates;
- instalação/desinstalação;
- migrações;
- privacidade;
- testes;
- Golden Queries;
- build/release;
- comportamentos manuais de homologação;
- funcionalidades realmente utilizadas versus históricas/legadas.

Para cada item, classificar:

- **MANTER**;
- **REDESENHAR**;
- **SUBSTITUIR POR WORDPRESS**;
- **EVOLUIR COM IA/VETOR**;
- **DESCARTAR**;
- **AINDA NÃO SABEMOS**.

---

# 8. Regra de ouro do inventário

Não confundir quantidade de código com valor.

A pergunta central é:

> **Qual comportamento do usuário ou garantia sistêmica este código protege?**

Se não houver resposta, o código não ganha direito automático de existir no novo produto.

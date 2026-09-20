# P-580A — Inventário Funcional ASI 4.6.8 — Baseline v1

**Status:** EM INVENTÁRIO  
**Data:** 2026-09-20  
**Fonte:** `R-RERISON/Advanced-search-Intelligence @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`

## 1. Regra

Este documento registra capacidades, não código a copiar.

Cada capacidade receberá uma disposição:
- PARITY;
- IMPROVED;
- MISSING;
- PLANNED;
- SUPERSEDED_WITH_EVIDENCE;
- RETIRED_BY_PO.

Nenhuma capacidade relevante pode desaparecer silenciosamente.

## 2. Runtime ASI confirmado

Bootstrap 4.6.8 carrega:

### Search/Knowledge
- ItemKnowledge;
- ExecutiveSummaryObjective;
- PostIndex;
- QueryContext;
- Relevance;
- ItemRanker;
- StructuralAudit;
- PostContext;
- KnowledgeCuration;
- KnowledgeDiagnostics;
- KnowledgeSuggestions;
- RankingSimulation.

### Infrastructure
- Schema;
- IndexingCoordinator;
- Queue;
- MigrationRunner;
- BaseReconciler;
- PostInstallOrchestrator.

### Analytics/Quality
- SearchEvents;
- SearchInteractions;
- SearchOutcomes;
- QualitySignals;
- SearchIntelligenceReport;
- GoldenQueries.

### Public
- WordCloud;
- PublicSite/SearchController;
- generated/deep-link anchor support.

### Admin
- Admin;
- QualityDiagnostics.

## 3. Public Search

Fonte principal:
- `src/PublicSite/SearchController.php`;
- `assets/js/public-search.js`;
- `assets/css/public.css`;
- `templates/search-results.php`.

Capacidades verificadas:
- shortcode `[asi_search_form]`;
- endpoint AJAX autenticado e nopriv;
- search-as-you-type;
- Enter/submit;
- cancelamento de requests stale;
- minimum characters;
- loading;
- zero result;
- technical error;
- rate-limit;
- retry;
- progressive disclosure mostrar mais/menos;
- full-page search continuity via query string;
- post results + item/section results;
- interaction/quality telemetry;
- anchor/deep-link support.

Disposição inicial BDC:
- admin lexical core: PARITY parcial;
- public surface: MISSING/BLOCKING;
- item/section results: MISSING;
- telemetry: PLANNED;
- deep links: MISSING.

## 4. Word Cloud

Fontes:
- `src/WordCloud/bootstrap.php`;
- `asi-word-cloud-generator.php`;
- `asi-word-cloud-quality.php`;
- `asi-word-cloud-shortcode.php`;
- `asi-word-cloud-cron-health.php`;
- `asi-word-cloud-admin.php`;
- `asi-word-cloud-intent-events.php`.

Capacidades verificadas:

### Inputs de geração
- conteúdo WordPress publicado;
- títulos;
- headings;
- opcional body;
- categorias/tags;
- search events;
- vocabulary;
- allowlist;
- blocklist/stopwords.

### Peso/intenção
- submitted_search;
- result_click;
- word_cloud_click;
- no_result;
- search/live observed;
- taxonomy;
- content/title/heading;
- dictionary;
- allowlist boost.

### Quality Engine
- canonicalização;
- short acronym guard;
- fragment detection;
- prefix-noise detection;
- maturidade: candidate / observed / mature / promoted;
- block/fragment exclusion;
- public_allowed;
- quality report;
- fragment ratio / review_required.

### Operação
- snapshot cacheado;
- soft time limit;
- generation lock;
- skip recent snapshot;
- geração manual/cron;
- cron horário;
- overdue detection;
- lock health;
- run history;
- last generated state;
- admin configuration.

### Public UX
- shortcode `[bdc_word_cloud]`;
- termos clicáveis;
- integração com Search pública;
- intenção/search source `word_cloud`.

Disposição BDC:
- **MISSING/BLOCKING DECOMMISSION**;
- deve ser reconstruída mantendo essas capacidades ou melhorando-as;
- storage/options/cron serão BDC-owned;
- não copiar options/tables ASI como steady-state.

## 5. Search Intelligence / Telemetry

Fontes:
- `src/Analytics/SearchEvents.php`;
- `SearchInteractions.php`;
- `SearchOutcomes.php`;
- `QualitySignals.php`;
- `SearchIntelligenceReport.php`.

Capacidades verificadas:
- execução de busca separada de interação;
- jornada correlacionada;
- post_click/item_click;
- final-query management consultation;
- zero-result sem confundir erro;
- privacy modes;
- frequent terms;
- emerging terms;
- zero-result gaps;
- query type;
- ranking source;
- p95;
- cache hit;
- filtros de período/perfil/time/termo;
- demanda por time/perfil quando identity mode permite.

Disposição:
- MISSING por decisão de fase, mas **não aposentada**;
- migrar para SPEC de Telemetria/Search Intelligence;
- G-585 final precisa reconhecer essa capacidade como PLANNED, não como inexistente.

## 6. Knowledge Curation / Ranking Controls

Fontes:
- `KnowledgeCuration.php`;
- `KnowledgeDiagnostics.php`;
- `KnowledgeSuggestions.php`;
- `RankingSimulation.php`.

Capacidades verificadas:
- Analyze → Evidence → Suggest → Simulate → Apply;
- sugestões somente quando há evidência;
- vocabulary variant;
- explicit relevance rule;
- term binding;
- simulação usando production rankers;
- proof token para Apply;
- no DB mutation durante simulation.

Disposição:
- MISSING/PLANNED;
- não criar controles mutáveis antes de simulação e guardrails equivalentes.

## 7. Post/item retrieval

Capacidades:
- post index;
- item/section knowledge;
- item ranker;
- stable item identity;
- semantic/numbered structural extraction;
- related sections;
- exact anchors/deep links;
- structural audit.

BDC atual:
- post-level lexical Projection: PARITY/IMPROVED parcial;
- Content Extractor/KD: substitui parte do structural understanding;
- item-level retrieval/deep-link: MISSING;
- precisa de gate próprio; não deve ser perdido.

## 8. Operations

Capacidades:
- durable queue;
- indexing coordinator;
- ordered/resumable migrations;
- base reconciler;
- post-install orchestrator;
- retry/dead/error;
- explicit manual resume;
- safe rebuild;
- deployment/quality evidence.

BDC atual:
- rebuild/lifecycle mínimo: PARITY parcial;
- queue/retry/resume/post-install: MISSING/PLANNED;
- será tratado na fase operacional, preservando comportamento necessário.

## 9. Home pública atual

Fonte fornecida pelo Product Owner:
- Home v2.7.0;
- Header v2.6.5.

Capacidades:
- marca/header;
- perfil;
- links rápidos;
- Search;
- Word Cloud;
- categorias;
- Últimas Atualizações;
- Instruções Populares;
- AJAX de filtro;
- loading/error;
- responsive shell.

Problema:
- Header e Body são fragmentos isolados;
- Home depende de shortcode ASI;
- JS possui normalização específica do markup ASI;
- manutenção fragmentada;
- source ownership dos shortcodes `bc_home_config`, `bc_ultimas`, `bc_populares` e action `bdc_home_filter_v270` ainda precisa ser localizado.

Disposição:
- UX-004 — reconstruir como Public Home canônica BDC.

## 10. Próximos passos do inventário

- levantar schema/tabelas/options/hooks/cron do ASI por domínio;
- localizar ownership dos artefatos atuais da Home;
- mapear cada capacidade para SPEC/UX-SPEC destino;
- identificar dados históricos que precisam migração vs somente rebuild;
- definir cutover por capacidade, não por plugin inteiro;
- só retomar G-585 quando não houver MISSING bloqueante sem plano aprovado.

# Catálogo de Testes e Regressão — SPEC-000

> Inventário consolidado das três referências. Este documento registra contratos de regressão a portar; ainda não existe runtime do novo plugin.

## 1. Advanced Search Intelligence 4.6.8

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

### Gate observado

`tools/validate-release.sh` executa lint PHP, syntax check JavaScript, regressão PHP/Node/Python, package validation e integridade SHA-256 quando manifest existe.

### Contratos fortes a preservar

- QueryContext/normalização/retrieval limitado;
- ranking de posts/itens e explicabilidade;
- identidade/navegação fail-closed de trechos;
- vocabulary/bindings/rules;
- curadoria assistida + simulação + stale-state guard;
- Search Events/Interactions/Outcomes;
- HMAC, idempotência, rate-limit e journey;
- privacy modes;
- Queue lifecycle se fila sobreviver;
- Golden Queries como release blocker;
- Quality Diagnostics/Site Health;
- package/install/upgrade/rollback;
- performance bounds.

### Regra central

Golden suite vazia nunca é PASS. Mudança de ranker/dataset precisa invalidar evidência stale.

---

## 2. Gerenciador de Resumo Executivo 0.6.0

Baseline: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`.

### Gate observado

`tools/verify_local.py` cobre Composer validation/dependencies, PHP lint, WPCS, PHPUnit, package smoke, deterministic build e SHA-256.

### Suíte relevante

- `MetaContractTest.php`: oito metas exatas, sem `_bdc_es_title`, args/sanitizer/auth;
- `SummaryStoreTest.php`: read/write, allowlist, capability, partial update, empty-delete, read-after-write;
- Admin tests: menu, nonce, payload, server-rendered editor;
- `CoverageDashboardTest.php`: 0/8–8/8, published-only, read-only;
- `FrontendRendererTest.php`: current-post-only, escaping, sem duplicação/JS;
- bootstrap/architecture guardrails;
- duas integrações WP-CLI em WordPress real;
- package smoke e reproducible ZIP.

### Lacunas formalizadas

- atomicidade lógica multi-campo em falha tardia;
- evento pós-persistência/invalidação;
- workload limitado do Coverage Dashboard.

---

## 3. KB2Ops 0.2.1

Baseline: `R-RERISON/KB2Ops-Operational-Knowledge-Engine@f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.

### Evidência de release existente

`docs/audit/RELEASE-GATE-0.2.1.md` registra como aprovados:

- PHP lint;
- `node --check`;
- CSS namespaced/balanceado;
- smoke 23/23;
- migration/upgrade/purge 24/24;
- static/security 79/79;
- UI contract 11/11;
- ações mutáveis;
- 19/19 blobs auditados;
- gate estático final 16/16;
- ZIP final de 19 arquivos;
- lint do ZIP extraído.

### Limitação crítica de rastreabilidade

Na árvore `main` fixada não existe diretório `tests/` nem scripts de gate versionados capazes de reproduzir esses totais. O relatório comprova que uma auditoria ocorreu, mas não oferece a mesma reprodutibilidade de regressão observada no ASI/GRE.

**Regra futura:** nenhum contrato KB2Ops crítico será considerado portado apenas porque existe relatório de auditoria. Ele precisa de teste executável versionado no novo repositório.

### Build versionado

`tools/build_plugin.py` é reproduzível e deve inspirar o futuro gate:

- allowlist de 19 runtime files;
- versão validada;
- PHP lint quando disponível;
- raiz única instalável;
- main file renomeado no pacote;
- timestamp fixo/ordenação determinística;
- development artifacts proibidos;
- contagem exata de arquivos;
- SHA-256.

### Contratos KB2Ops que precisam virar testes executáveis

#### KB-T01 — Bootstrap/lifecycle

- activation não apaga dados históricos;
- `maybe_upgrade` é idempotente;
- options default só nascem quando ausentes;
- multisite não perde contexto de blog;
- nenhum legado é fisicamente apagado em activation.

#### KB-T02 — Metadata/curadoria

- Meta Contract registra todos os campos persistidos ou documenta explicitamente exceções;
- `edit_post` governa mudança por objeto;
- payload é allowlisted/sanitizado;
- transição de review state é determinística;
- history permanece bounded;
- evento de aprovação só ocorre depois de estado persistido/confirmado.

#### KB-T03 — AI READY

Provar regra canônica única:

- publish;
- approved;
- Resumo 8/8;
- include_ai = true.

Também deve existir teste que falhe se documentação/código divergirem no contrato publicado.

#### KB-T04 — Summary Bridge/compat

Enquanto coexistência existir:

- oito chaves exatas;
- read-only;
- ausência vira vazio;
- completion correto;
- nenhuma escrita GRE por KB2Ops.

No plugin unificado, substituir por testes do Summary Store interno.

#### KB-T05 — Content Extractor básico

Fixtures para:

- post_content sem Elementor;
- Elementor JSON válido;
- JSON inválido;
- HTML entities/whitespace/boundaries;
- headings/list/table/image counts;
- cache por request;
- zero writes em `_elementor_data`/`post_content`.

#### KB-T06 — Content Extractor custom widgets

Cenário crítico:

1. documento contém widget conhecido + widget customizado relevante;
2. parser allowlist retorna conteúdo não vazio porém incompleto;
3. sistema precisa detectar cobertura insuficiente ou ter política explícita de fallback.

Não aceitar perda silenciosa de conteúdo.

#### KB-T07 — Shortcode safety

- somente `table/tablepress` allowlisted;
- shortcode inexistente/falhando vira placeholder;
- exceção é non-fatal;
- nenhum shortcode arbitrário é executado pelo extractor.

#### KB-T08 — Search scope/security

- `published`, `approved`, `ai_ready`;
- detail route não contorna scope;
- `require_login` respeitado;
- query limitada a 200 chars;
- filtros/contexto preservados na navegação;
- output escaped.

#### KB-T09 — Search provisória/performance

Enquanto a implementação atual existir em migração/coexistência, medir/limitar:

- scans `numberposts=-1`;
- `meta_query LIKE` em `_elementor_data`;
- tecnologia/options derivadas;
- corpus de centenas/milhares de posts.

No runtime novo, substituído por Golden Queries + benchmarks da engine lexical.

#### KB-T10 — Analytics/privacy

- logging pode ser desligado;
- query normalizada/limitada;
- política de retenção/minimização explícita;
- analytics falhando não derruba Search;
- concorrência não perde fatos silenciosamente se store definitivo for relacional.

#### KB-T11 — Admin mutations

Settings/review/migration/purge:

- POST only;
- nonce;
- capability correta;
- purge exige confirmação;
- handlers nopriv ausentes.

#### KB-T12 — Design System/a11y

E2E/DOM regressions para:

- CSS namespacing;
- foco visível;
- estado com texto/ícone, não só cor;
- responsive breakpoints;
- progressive disclosure;
- navegação por teclado;
- sem dependência de IA/JS para shell básico.

#### KB-T13 — Uninstall/retention

- default não destrutivo;
- purge somente com opt-in explícito;
- purge não toca posts/Elementor/GRE quando não autorizado.

#### KB-T14 — Package reproducibility

- allowlist de runtime;
- single root;
- version consistency;
- deterministic ZIP hash;
- development-only artifacts ausentes.

---

## 4. Contratos combinados obrigatórios do novo produto

### Conteúdo

- um único Content Extractor alimenta Search, Item Knowledge, IA, auditoria e features derivadas;
- nunca escrever `_elementor_data` ou reescrever `post_content` por pipeline derivado;
- custom widgets não podem desaparecer silenciosamente sem evidência/diagnóstico.

### Summary/curadoria

- `post_title` permanece canônico;
- oito valores GRE preservados na coexistência;
- Objective ausente permanece ausente;
- mutation confirmada antes de evento;
- `edit_post` + nonce por objeto;
- IA nunca persiste metadata editorial sem decisão humana.

### Busca

- lexical funciona sem IA/vetor;
- Golden Queries bloqueiam regressão;
- ranking explicável;
- filtros/scope não podem ser bypassados;
- live UX, se existir, deve usar nonce/rate-limit/tracking integrity do padrão ASI.

### Telemetria

- erro ≠ zero results;
- analytics non-fatal;
- query text passa por política explícita de minimização/retention;
- tracking, se adotado, usa server authority/HMAC/idempotência.

### Operação

- activation leve/reversível;
- purge explícito;
- workload administrativo bounded ou benchmarkado;
- package determinístico;
- rollback/coexistência testados quando necessários.

---

## 5. Estratégia de teste futura

1. unitários puros — normalização, scoring, metadata contracts, extractor helpers e state transitions;
2. integração WordPress real — Meta/Taxonomy APIs, capabilities, nonces, hooks, Elementor fixtures;
3. Golden Queries — busca de posts e trechos;
4. E2E admin/público — Studio, Search, resumo, filtros, a11y;
5. performance — extractor, indexação, Search e dashboards com corpus realista;
6. privacy/security — analytics, endpoints, replay, HMAC, retention;
7. package/install/upgrade/rollback — deterministic build e coexistência;
8. cross-module — Summary/curadoria confirmados -> events -> projections stale/reindex.

Testes por inspeção textual são guardrails secundários; não substituem comportamento executável.

## Status

T017 do KB2Ops pode ser fechado: build/release foram inventariados e a ausência de suíte executável versionada foi registrada como dívida. O próximo trabalho de testes ocorrerá somente após arquitetura/spec de runtime correspondente.
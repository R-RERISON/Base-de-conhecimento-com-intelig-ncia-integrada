# Pesquisa Inicial — SPEC-000

## Objetivo

Registrar fatos observados e sua evolução durante a leitura exaustiva. Hipóteses deixam de ser hipóteses somente quando confirmadas contra runtime na baseline fixada.

## KB2Ops 0.2.1

Fatos iniciais observados:

- shell e Design System recentes;
- Knowledge Studio;
- Knowledge Search;
- Content Extractor com tratamento Elementor;
- metadados `_kb2ops_*` para revisão/classificação;
- leitura direta dos oito `_bdc_es_*` via bridge;
- ativação hardened/reversível;
- busca atual conscientemente provisória.

Hipóteses a validar:

- quais metas devem virar taxonomia;
- quais analytics leves possuem valor futuro;
- quais componentes visuais devem virar contrato novo;
- quais rotas/handlers merecem regressão.

## ASI 4.6.8 — bloco confirmado

Baseline fixada: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

Fatos confirmados por runtime:

- bootstrap em fases, com activation leve e preparação/migração separada do steady state;
- 12 stores/tabelas canônicas: post index, item index, bindings, vocabulary, rules, queue, audit, events, interactions, Golden Queries, quality daily e migrations;
- busca lexical/FULLTEXT com fallback limitado;
- QueryContext determinístico, expansão de vocabulário e plano de retrieval limitado;
- índice de posts e itens/trechos, com identidade estável e navegação fail-closed;
- Relevance/ItemRanker explicáveis, com regras/bindings/coverage e suporte a simulação;
- curadoria assistida baseada em diagnóstico read-only, sugestões com evidência e simulação antes de Apply;
- fila durável com lease, retry/backoff, dead-letter, recovery e stale discovery;
- migrações/reconciler/orchestrator amplamente ligados à evolução ASI 4.x e cutover de legado;
- Search Events/Interactions/Outcomes com jornada, HMAC de targets, deduplicação e telemetria non-fatal;
- Golden Queries versionadas por dataset/algoritmo e capazes de bloquear release;
- Quality Diagnostics integrado a Site Health e export seguro;
- Word Cloud canônica, porém com pipeline de extração lexical próprio;
- modos de privacidade; modo minimal não grava identity/session/IP/UA, mas ainda persiste query text;
- Admin com capabilities separadas e nonces;
- busca pública via WordPress AJAX/shortcode, debounce, cancelamento, progressive disclosure e acessibilidade;
- Search Intelligence com limites explícitos de workload e acoplamento ambiental a roles/tabelas GAC;
- legacy/compat explicitamente quarantined pré-cutover;
- uninstall preserva dados por default;
- gate local de release executa lint PHP/JS, 31 testes/contratos, package validation e manifest hash.

### Hipóteses ASI resolvidas

- **“As 12 tabelas são necessárias?”** Não como pacote. Cada store deve ser rejustificado. `quality_daily` é candidato a não nascer; migrations/compat históricas não pertencem ao greenfield.
- **“Quais contratos de ranking são indispensáveis?”** Retrieval lexical degradável, QueryContext limitado, explicabilidade, trechos navegáveis, simulação e Golden Queries são contratos fortes.
- **“Quais telas são consequência da arquitetura antiga?”** grande parte de Operations/Migration/PostInstall existe por cutover/histórico; deve ser redesenhada e reduzida.
- **“Quais testes devem ser portados?”** intenções comportamentais de ranking, segurança, privacy, queue, Objective, journeys e package devem sobreviver; source-string tests são guardrails secundários.
- **“Módulos opcionais têm consumidores reais?”** o ASI possui preflight para provar uso; no novo produto, não se deve inferir requisito pela mera existência do módulo.

### Decisões preliminares ASI

- MANTER comportamento de busca lexical, item knowledge, curadoria/simulação, Golden Queries, privacy-aware telemetry, outcomes e segurança.
- REDESENHAR extração inteira sobre um Content Extractor canônico Elementor-aware.
- DESCARTAR migrações/cutover ASI/WPUI e dependências legacy do runtime greenfield.
- DESCARTAR `quality_daily` inicialmente; reintroduzir apenas por evidência de performance.
- REDESENHAR Word Cloud para consumir índice/telemetria canônicos, sem segundo pipeline de conteúdo.
- DESCARTAR acoplamento GAC do core; usar adapter opcional se houver requisito real.
- EVOLUIR COM IA/VETOR somente como camada opcional/degradável após retrieval determinístico.
- AINDA NÃO SABEMOS o storage final de vocabulary/bindings/rules/Golden, schema lexical, chunks/vetores e estratégia de anchors.

### Drift confirmado no lado ASI

ASI exige `BDC\\ExecutiveSummary\\Objective_Provider::read_objective()` e possui teste específico desse contrato. A Queue também espera o evento `bdc_es_objective_updated`. A existência/ausência real desses contratos no Gerenciador de Resumo Executivo deve ser confirmada no bloco T040–T047.

## Gerenciador de Resumo Executivo 0.6.0

Fatos iniciais observados:

- seis classes principais no runtime;
- oito post metas canônicos;
- Metadata API;
- Summary Store;
- Coverage Dashboard;
- Admin Page;
- Frontend Renderer;
- sem tabela própria para domínio central.

Gap inicial agora formalizado como drift a confirmar:

- ASI espera `BDC\\ExecutiveSummary\\Objective_Provider`;
- ASI espera `bdc_es_objective_updated`;
- confirmar se o runtime 0.6.0 realmente expõe ambos ou se há incompatibilidade de versão/contrato.

## Decisões ainda não tomadas

- taxonomias definitivas do novo produto;
- schema lexical;
- necessidade de tabela de chunks;
- estratégia vetorial;
- provider contract de IA;
- retenção e minimização definitivas de telemetria/query text;
- mecanismo de histórico de revisão;
- estratégia de compatibilidade com meta `_kb2ops_*`;
- estratégia de coexistência/migração do índice `asi_*`;
- storage final de vocabulary/bindings/rules/Golden Queries;
- necessidade final de fila própria;
- estratégia definitiva de anchors.

Nenhuma dessas decisões deve ser fechada antes do inventário/cross-reference correspondente.

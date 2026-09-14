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
- quais rotas/handlers merecem regressão;
- como o Content Extractor representa Elementor e fallback de `post_content`;
- como a bridge GRE trata ownership/read-only dos `_bdc_es_*`.

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

## Gerenciador de Resumo Executivo 0.6.0 — bloco confirmado

Baseline fixada e confirmada no `main` da referência: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`.

### Fatos confirmados por runtime

- exatamente seis classes PHP compõem o runtime principal;
- bootstrap único em `plugins_loaded`, seguido por Meta Contract, Admin e Frontend;
- oito post metas privadas registradas via `register_post_meta`;
- `post_title` é título canônico; não existe `_bdc_es_title`;
- todas as metas são string/single/default vazio, `show_in_rest=false` e `revisions_enabled=false`;
- autorização de metadata usa `edit_post` por objeto;
- `Summary_Store::read()` é side-effect free;
- `Summary_Store::update()` faz allowlist, validação integral antes de mutação, sanitização, update parcial, empty-delete e read-after-write;
- Admin usa `admin-post` autenticado, nonce por post e server-side rendering;
- listagem de administração é bounded (até 50 candidatos/20 resultados editáveis);
- Coverage Dashboard é read-only, mas varre todos os posts publicados com `posts_per_page=-1`;
- Frontend possui `[bdc_resumo_executivo]` current-post-only e side panel automático;
- shortcode ignora atributos e não permite leitura arbitrária por `post_id`;
- frontend é CSS-only, sem JavaScript;
- não existem tabelas próprias, `$wpdb`, REST próprio, AJAX, WP-Cron, transients ou options de domínio;
- o único `get_option()` de runtime relevante lê `date_format` nativo do WordPress;
- não existem activation/deactivation hooks nem migrations próprias;
- não existe `uninstall.php`;
- testes incluem unitários, duas integrações reais via WP-CLI e smoke de package;
- gate local executa Composer validation/dependencies, lint, WPCS, PHPUnit, smoke, deterministic ZIP e SHA-256.

### Hipóteses GRE resolvidas

- **“O GRE precisa de tabela própria?”** Não há evidência. O domínio funciona integralmente em WordPress metadata.
- **“Há REST/AJAX necessário?”** Não no baseline. Admin-post e server rendering atendem ao produto observado.
- **“O provider esperado pelo ASI existe?”** Não. `BDC\ExecutiveSummary\Objective_Provider` e `read_objective()` não existem no GRE 0.6.0.
- **“O evento esperado pelo ASI existe?”** Não. `bdc_es_objective_updated` não é emitido; o único `do_action` próprio do bootstrap é `bdc_es_loaded`.
- **“O drift era apenas documental?”** Não. D-001/D-002 são incompatibilidades reais entre as baselines fixadas.

### Decisões preliminares GRE

- MANTER WordPress Metadata API como baseline do Resumo Executivo.
- MANTER os oito valores/contrato conhecido e `post_title` como título.
- MANTER `edit_post`, nonce por objeto, allowlist, sanitização e read-after-write.
- MANTER empty-delete e leitura sem side effect.
- REDESENHAR a API de Objective como serviço interno do plugin unificado; não reproduzir bridge frágil entre plugins.
- MANTER intenção de evento de mudança, mas REDESENHAR nome/payload e emitir somente após persistência confirmada.
- REDESENHAR Coverage Dashboard para workload limitado quando medição justificar; não criar rollup/table antecipadamente.
- REDESENHAR CSS/admin/frontend com o Design System único do KB2Ops.
- AINDA NÃO SABEMOS quais campos classificatórios devem permanecer meta versus virar taxonomia.
- AINDA NÃO SABEMOS se side panel automático sobreviverá como UX final.
- AINDA NÃO SABEMOS política de revisions/histórico das metas.

### Dívida técnica GRE identificada

A validação do `Summary_Store` é toda feita antes da primeira mutação, mas writes multi-campo posteriores são sequenciais. Se um campo posterior falhar após um anterior já ter sido persistido, não há rollback compensatório comprovado. O plugin unificado deve especificar e testar essa semântica antes da implementação.

## Drifts cruzados já confirmados

### D-001 — Objective Provider

- lado ASI: espera/testa `BDC\ExecutiveSummary\Objective_Provider::read_objective()`;
- lado GRE: classe/método ausentes;
- status: **CONFIRMADO QUEBRADO**.

### D-002 — Objective changed event

- lado ASI: Queue escuta `bdc_es_objective_updated`;
- lado GRE: evento ausente;
- status: **CONFIRMADO QUEBRADO**.

Direção: no plugin unificado, store interno + evento pós-persistência + invalidação/reindexação derivada.

## Decisões ainda não tomadas

- taxonomias definitivas do novo produto;
- destino final campo a campo dos `_bdc_es_*` classificatórios;
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
- estratégia definitiva de anchors;
- UX final do Resumo Executivo (side panel versus composição DS).

Nenhuma dessas decisões deve ser fechada antes do inventário KB2Ops e do cross-reference T050–T059.

## Próxima investigação

O próximo bloco deve ser KB2Ops 0.2.1 (T010–T019), com prioridade para:

1. Content Extractor Elementor-aware;
2. ownership/read-only da bridge `_bdc_es_*`;
3. metadados `_kb2ops_*` e candidatos a taxonomia;
4. Knowledge Studio/Search;
5. Design System/tokens/componentes;
6. lifecycle hardened;
7. testes/build/release.

Esse bloco é necessário para fechar D-003, D-006/D-007 e permitir o cruzamento T050–T059.
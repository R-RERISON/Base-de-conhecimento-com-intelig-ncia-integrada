# Continuidade — SPEC-005 Search Lexical e Golden Queries

## Prompt pronto para novo chat

```text
Você é o Orquestrador Principal do projeto Base de Conhecimento com Inteligência Integrada.
Idioma: português do Brasil.
Mantra: Quem não sabe onde está, não sabe para onde quer ir.

ANTES DE ALTERAR
1. Leia AGENTS.md, .specify/PROJECT_MANIFEST.md e .specify/memory/constitution.md.
2. Leia SPEC-005, ADR-005-001, ADR-005-002, ADR-005-003 e contratos G-520.
3. Leia docs/DEFINITION-OF-DONE.md, current-state.md e este CONTINUIDADE.md.
4. Confirme branch/HEAD e PR #7.
5. Repositório/Constituição prevalecem sobre memória de chat.

REPO
- R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- branch: spec005-search-lexical-golden-queries
- base: main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634
- PR #7: DRAFT / NÃO MERGEAR

ESTADO
- SPEC-004 CLOSED/main
- R-500 PASS/CLOSED
- R-510 PASS/CLOSED
- G-520 PASS/CLOSED
- G-530 PASS/CLOSED
- G-540 PASS/CLOSED
- G-550 PASS/CLOSED
- G-560 PASS/CLOSED
- G-570 PASS/CLOSED
- G-580 OPEN / gate atual
- G-585 obrigatório antes do RC

R-510 FREEZE
Golden:
- version golden-relevance-v1.0.0
- hash e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4
- 5 blocking + 1 quarantine

Challenge:
- version technical-challenge-v1.0.0
- hash 2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807
- 7 cases

G-520
- closeout: g520-closeout-20260919.md
- evidence: evidence/g520-contract-validation-20260919.json
- 25/25 PASS
- zero runtime changes during gate

CONTRACT VERSIONS
- search-normalizer-v1.0.0
- search-document-v1.0.0
- lexical-ranker-v1.0.0
- search-result-v1.0.0
- golden-runner-v1.0.0

STORAGE DECISION — ADR-005-003
- one table: {$wpdb->prefix}bdc_kb_search_documents
- one option: bdc_kb_search_projection_state
- SQL LIKE bounded
- ranking PHP
- WP_Query native fallback
- no FULLTEXT v1
- no Golden table
- no query logging
- no ASI storage/runtime dependency

G-530 FINAL
- closeout: g530-closeout-20260919.md
- evidence: evidence/g530-local-validation-20260919.json
- build local: 0.5.0-g530.1
- 17/17 tests PASS; 7/7 lint; zero editorial write/ASI/FULLTEXT/network

G-540 OBJETIVO
Executar full-corpus/projection rebuild automatizado em homologação:
1. ensure schema explícito;
2. full-corpus pass 1;
3. stale cleanup somente após pass 1 completa;
4. full-corpus pass 2;
5. hashes determinísticos;
6. pass 2 = NO_CHANGE;
7. coverage/source kinds/states;
8. fingerprint editorial before/after;
9. JSON machine-readable.

LIMITES
- não implementar Golden runner ainda: G-550;
- não implementar UI material: G-560;
- não criar FULLTEXT;
- não criar queue/telemetry;
- não criar semantic/vector/AI;
- não usar ASI;
- schema/rebuild deve seguir ADR-005-003 e rollback contract.

CRITÉRIO G-540
- corpus completo processado;
- duas passagens sem fatal/throwable;
- mesmos source/document hashes;
- segunda passagem sem rewrite;
- row count compatível com corpus;
- fingerprint editorial inalterado;
- somente então G-540 PASS.
```

## Estado humano

G-520 está **PASS/CLOSED**. Não há ZIP ou instalação necessária para esse gate.

Próximo trabalho: **G-530 — implementação local da engine lexical**.

## Continuidade G-540 — 2026-09-19

Candidato de homologação:
- versão: `0.5.0-g540.1`;
- evidência local: `evidence/g540-local-package-validation-20260919.json`;
- source/package parity 55/55;
- PHP lint 50/50;
- active requires 44/44;
- deterministic build 2/2;
- SHA-256 `b08a7abdef9c6287571ec137c2623618077a8ea6426d964bfc9e858659270a7f`.

Correção obrigatória realizada antes do build:
- `class-migration-fidelity-source.php`;
- missing closing brace detectado pelo lint full-package;
- commit `3b1f1631c8b004ba0b6923310359389d1721c01b`;
- blob corrigido `d884b22fc2f8c755c45667e703351b03a86494f4`.

Estado:
- G-530 CLOSED;
- G-540 PASS/CLOSED;
- G-550 OPEN.

G-540 AMBIENTAL
- evidence: evidence/g540-environmental-20260919T115727Z.json
- upload SHA-256: c2f1195c35917bc1863f262a7c0a930d622e93733aaccbfba3325becf1b789de
- corpus 623/623;
- pass1 623 WRITTEN;
- pass2 623 NO_CHANGE / 0 WRITTEN;
- determinism mismatch=0;
- DB snapshot mismatch=0;
- Projection ready;
- editorial fingerprint equal;
- errors/throwables=0;
- G-540 CLOSED.

PRÓXIMO PASSO — G-550
1. empacotar Golden Relevance v1.0.0 e Technical Challenge v1.0.0 como recursos próprios;
2. validar set_hash/version em runtime;
3. executar somente com Projection ready;
4. Golden blocking failure=0;
5. Technical Challenge failure=0;
6. quarantine permanece warning;
7. JSON machine-readable;
8. zero ASI/query logging.


## Continuidade G-550 — 2026-09-19

Build:
- `0.5.0-g550.1`;
- evidence local: `evidence/g550-local-package-validation-20260919.json`;
- SHA-256: `5c0fb99476aab84149341c1f069f64bfb9d8bdb57daaeca2636fe518164739b5`;
- 59 arquivos / 52 PHP;
- 52/52 lint;
- 45/45 active requires;
- deterministic build 2/2;
- Golden hash current;
- Challenge hash current;
- stale runtime guard ativo;
- G-540 runner OFF;
- G-550 runner ON.

Estado:
- G-540 CLOSED;
- G-550 package/local PASS;
- G-550 environmental NOT_RUN;
- G-550 ainda OPEN.

Próxima ação humana:
1. instalar o ZIP `0.5.0-g550.1` sobre a versão atual;
2. acessar **Base de Conhecimento → Golden Gate G-550**;
3. executar **Executar G-550 e baixar JSON**;
4. anexar o JSON para fechamento T550–T555.


## G-550 ambiental — PASS/CLOSED

- evidence: `evidence/g550-environmental-20260919T121422Z.json`;
- upload SHA-256: `e6f83104d330129cd0bd1835f4c1fd21f8d7117b461dbb646527f408d56cd07b`;
- Projection ready;
- Golden hash current;
- Challenge hash current;
- 13/13 result expectations PASS;
- blocking_failed=0;
- warning_failed=0;
- technical_failed=0;
- technical_error_count=0;
- status PASS;
- next gate G-560;
- zero ASI/network/query log/identity export.

G-560 agora é o gate ativo. Antes de implementar:
1. ler Visual Contract v2;
2. ler Design System/UI as Code canônicos;
3. preservar WordPress como shell sem voltar a wp-admin genérico;
4. Search deve distinguir success/zero_results/degraded/technical_error;
5. acessibilidade e responsividade são critérios executáveis;
6. nenhuma evolução de ranking durante G-560.


## Continuidade G-560 — pacote técnico 2026-09-19

Build:
- `0.5.0-g560.1`;
- evidence local: `evidence/g560-local-package-validation-20260919.json`;
- ZIP SHA-256: `9f4f14c775aaad4e7d2360ed11dd3b036587556b1497e210eccaf787c89b8297`;
- 60 arquivos / 53 PHP;
- lint 53/53;
- active requires 44/44;
- 26/26 checks de contrato;
- deterministic build 2/2.

Decisão UI:
- Search continua dentro da Knowledge List ADMIN-FIRST;
- não existe mockup Search direto; herda Knowledge List/UX-002.3;
- query não vazia usa Search_Service;
- listagem vazia preserva WP_Query modified DESC;
- estados Search são distintos e acessíveis;
- 782/520 cobertos;
- ranking/engine congelados durante G-560.

Validador ambiental:
- menu: **Base de Conhecimento → Search UX G-560**;
- executa estados reais success, Summary-semantic, zero_results e invalid_query;
- verifica Projection ready;
- fingerprint editorial before/after completo;
- gera JSON;
- nunca marca T564 humano automaticamente.

Estado:
- G-550 CLOSED;
- G-560 package/local PASS;
- G-560 environmental NOT_RUN;
- T564 human visual acceptance NOT_RUN;
- G-560 ainda OPEN.


## G-560 ambiental — PASS técnico / 2026-09-19

Evidência:
- `evidence/g560-environmental-review-20260919T134816Z.json`;
- upload SHA-256 `9906bbecbd1d2c056a2a1e87d2b50d8d4885adc904621a8b1120ff49c40b1527`.

Resultados:
- 21/21 automated checks PASS;
- 4/4 live Search states PASS;
- Projection ready;
- fingerprint editorial equal;
- errors=[];
- runtime 3674.3619 ms;
- g560_technical_ready=true.

Estado:
- T560 PASS;
- T561 PASS técnico;
- T562 PASS;
- T563 PASS;
- T564 NOT_RUN — único item restante;
- T565 NOT_PASS por dependência explícita de T564;
- gate atual: G-560-HUMAN.

Aceite visual mínimo:
- evidência renderizada da Knowledge List Search;
- revisar desktop e pelo menos um narrow breakpoint crítico;
- confirmar hierarquia visual, legibilidade, foco/feedback e ausência de overflow/regressão;
- sem teste artigo-a-artigo.


## G-560 humano — PASS/CLOSED — 2026-09-19

Evidência:
- `evidence/g560-human-visual-acceptance-20260919.md`;
- captura desktop real 1726x412;
- query observada: `windows 11`.

Decisão:
- desktop: PASS humano;
- mobile visual: DEFERRED/NON-BLOCKING por decisão explícita do Product Owner;
- source/runtime checks 782/520 permanecem PASS;
- Visual Contract v2 não foi alterado;
- dívida mobile fica para iniciativa futura em que mobile seja requisito de release.

Gate:
- T560–T565 PASS/CLOSED;
- G-560 CLOSED;
- gate atual G-570.

G-570 OBJETIVO:
1. scope/capability;
2. SQL/bounds;
3. abuso/queries longas;
4. benchmark p50/p95 no ambiente real;
5. fechar G-570 sem alterar ranking.


## Continuidade G-570 — pacote 2026-09-19

Build:
- `0.5.0-g570.1`;
- contrato: `g570-security-performance-contract-v1.md`;
- evidence local: `evidence/g570-local-package-validation-20260919.json`;
- ZIP SHA-256: `4cd915fc3be58166a354434bf9688f0a1509ca36d570697540dff9eace82ab62`;
- 61 arquivos / 54 PHP;
- lint 54/54;
- extracted lint 54/54;
- active requires 44/44;
- local suite 37/37;
- deterministic build 2/2.

Garantias:
- Search ranker/retrieval core não alterados;
- G530 engine ON;
- G560 runner OFF;
- G570 runner ON;
- runner G570 não escreve Projection nem editorial;
- zero ASI/FULLTEXT/network/query logging.

Teste ambiental esperado:
1. scope/capability e negativas;
2. candidate/result/query bounds;
3. abuse/malformed input;
4. 30 amostras benchmark;
5. p95 <=750 ms e max <=1500 ms;
6. zero fallback/error;
7. fingerprint editorial igual.

Estado:
- G-560 CLOSED;
- G-570 package/local PASS;
- G-570 environmental NOT_RUN;
- T570–T574 permanecem abertos.


## G-570 ambiental — PASS/CLOSED — 2026-09-20

Evidence:
- `evidence/g570-environmental-review-20260920T164828Z.json`;
- upload SHA-256 `38d399cab1f466c1b919513b8cf3650dd3ae7b6a55d94b715efe5597c3b89ffd`.

Security:
- 45/45 checks PASS;
- capability fail-closed PASS;
- object-level denial PASS;
- SQL/bounds PASS;
- abuse PASS;
- zero network/query log/ASI/FULLTEXT;
- fingerprint editorial equal.

Performance:
- 30 samples;
- p50 178.4739 ms;
- p95 207.7448 ms;
- max 212.9128 ms;
- fallback 0;
- technical errors 0;
- budget comfortably met.

Gate:
- T570 PASS;
- T571 PASS;
- T572 PASS;
- T573 PASS;
- T574 PASS;
- G-570 CLOSED;
- gate atual: G-580.

G-580 objetivo:
1. activation/update;
2. explicit rebuild + fallback;
3. disable Search module safely;
4. uninstall/retention behavior;
5. no editorial loss and no ASI dependency.

## Continuidade G-580 — pacote 2026-09-20

Build:
- `0.5.0-g580.1`;
- contrato: `g580-lifecycle-rebuild-contract-v1.md`;
- evidence local: `evidence/g580-local-package-validation-20260920.json`;
- ZIP SHA-256: `b2a2d55793a3835ff06151d2c1cc4e301292df51bc35c5b5bdf166546a4f166e`;
- 65 arquivos / 58 PHP;
- lint 58/58;
- active requires 46/46;
- suíte G-580 33/33;
- deterministic build 2/2.

Garantias:
- base G-570 validada por checksum canônico;
- delta package G-570 -> G-580 = 4 added / 2 modified / 0 deleted;
- ranker SHA-256 inalterado: `47787ee0fcf6845c264fcc77b8ae0d5d5657e1e3f66e0d0939fe1940fecaad78`;
- activation/update sem rebuild implícito;
- explicit rebuild com pass2 NO_CHANGE obrigatório;
- fallback WordPress quando Projection não-ready;
- disable operacional sem nova Option persistente;
- deactivation/uninstall não destrutivos;
- zero ASI/FULLTEXT/query logging.

Estado:
- G-570 CLOSED;
- G-580 package/local PASS;
- G-580 environmental NOT_RUN;
- T580–T584 OPEN;
- G-580 ainda OPEN.

Próxima ação humana:
1. instalar o ZIP `0.5.0-g580.1` sobre a versão atual em homologação;
2. acessar **Base de Conhecimento → Lifecycle G-580**;
3. executar **Executar G-580 e baixar JSON**;
4. anexar o JSON para decisão T580–T584;
5. somente após G-580 CLOSED avançar para G-585.

## Continuidade G-580.2 — validator false-negative patch

Primeira execução ambiental em `0.5.0-g580.1`:
- T580=true;
- T581=true;
- T582=true;
- T583=true;
- T584=false;
- único blocker: `runtime_source.no_query_logging=false`;
- `persists_query_log=false`;
- errors/throwables=0;
- Projection final ready;
- fingerprint editorial igual.

Root cause:
- o runner incluía seu próprio source na varredura;
- procurava literalmente `bdc_kb_search_query_log`;
- a própria linha de asserção continha esse literal;
- falso negativo determinístico.

Patch:
- build `0.5.0-g580.2`;
- somente bootstrap/version + runner;
- marker montado por concatenação para evitar self-match;
- detecção real continua válida;
- local/package PASS;
- SHA-256 `47f3d2c641863eca5048bcb4ed469ebe243c1262ef4aaada582f60ceec84cf29`.

Não fechar G-580 ainda. Reexecutar runner em homologação e exigir:
- `no_query_logging=true`;
- T580/T581/T582/T583/T584=true;
- `next_gate=G-585`;
- errors=[] / throwables=[];
- fingerprint editorial equal.

## Continuidade G-580 CLOSED / G-585 package ready — 2026-09-20

### Gate anterior

G-580 PASS/CLOSED.

Evidência:
- `evidence/g580-environmental-review-20260920T173016Z.json`;
- SHA-256 `f778789c8067d4ced3272fce02054a1a696d321c5b6b1bdd5cfea0f6b16b53f6`.

Resultado:
- T580–T584=true;
- Projection ready 623/623;
- pass1/pass2 623 NO_CHANGE;
- mismatch=0;
- fingerprint editorial equal;
- no network/query logging/ASI;
- next_gate=G-585.

### Gate atual

G-585 — ASI Independence / Decommission Readiness.

Contrato:
- `g585-asi-independence-contract-v1.md`;
- ADR: `adr-005-001-zero-runtime-dependency-asi.md`.

Build:
- `0.5.0-g585.1`;
- ZIP SHA-256 `cae86ef93572e9320efe89d8fed891e90be032e5d46e4aaccf6dca647bf62880`;
- 66 arquivos / 59 PHP;
- lint 59/59;
- active requires 48/48;
- local suite 29/29;
- deterministic build 2/2.

Delta vs g580.2:
- added: `includes/class-search-independence-runner-g585.php`;
- modified: bootstrap/version/flags only;
- deleted: none;
- Search Service/rebuild/lifecycle/ranker unchanged.

Invariantes:
- runner nunca desativa ASI automaticamente;
- se ASI estiver ativo -> `BLOCKED_LEGACY_ACTIVE` antes de rebuild/Golden;
- nenhuma remoção física de tabela/option ASI;
- static scan cobre runtime PHP carregado, símbolos e hooks;
- Search + Golden + rebuild + lifecycle devem passar com ASI ausente;
- ranker permanece `lexical-ranker-v1.0.0`;
- G-590 permanece bloqueado até G-585 ambiental PASS.

### Próxima ação humana exata

1. desativar manualmente o plugin **Advanced Search Intelligence** em homologação;
2. manter arquivos/tabelas/options legados intactos;
3. instalar `0.5.0-g585.1` por atualização sobre o BDC atual;
4. abrir **Base de Conhecimento → Independência G-585**;
5. executar **Executar G-585 e baixar JSON**;
6. anexar o JSON.

Aceite esperado:
- `status=PASS`;
- static_runtime_scan.matches=[];
- legacy_environment.active_plugins=[];
- legacy_environment.loaded_symbols=[];
- legacy_environment.loaded_hooks=[];
- Search probes projection_like;
- Golden status=PASS / blocking_failed=0 / technical_failed=0;
- rebuild PASS / pass2 written=0 / mismatch=0;
- lifecycle rollback PASS;
- T585/T586/T587/T588/T589/T589.1/T589.2=true;
- `next_gate=G-590`;
- errors=[] / throwables=[].

Se qualquer item falhar, manter G-585 OPEN e diagnosticar antes de RC.

## Continuidade G-585.2 — atribuição do símbolo residual

Primeira execução G-585 em `0.5.0-g585.1`:
- ASI desativado;
- static BDC runtime scan limpo: 48 arquivos / 0 matches;
- legacy active plugins=[];
- loaded legacy hooks=[];
- loaded symbol: `BDC_KX_ASI_Adapter`;
- T585=false / T586=true;
- status `FAIL_DEPENDENCY_FOUND`;
- T587/T588/T589 não executados por fail-fast.

Não inferir de onde `BDC_KX_ASI_Adapter` vem. O g585.1 não gravou file origin.

Patch `0.5.0-g585.2`:
- usa ReflectionClass/ReflectionFunction;
- reporta `type`, `symbol`, `source_scope`, `source_path`;
- scopes possíveis: bdc_plugin, mu_plugin, plugin, theme, wordpress_core, external_or_unknown, internal_or_unknown;
- nunca exporta caminho absoluto;
- regra de gate inalterada: qualquer símbolo legacy ainda bloqueia T585;
- Search/rebuild/lifecycle/ranker inalterados.

Package:
- 59/59 PHP lint;
- 48/48 active requires;
- 31/31 checks;
- deterministic 2/2;
- SHA-256 `0db339e50e997bbc4211972173809ea71517fc242696b17345bf8d095146c915`.

Próxima ação humana:
1. manter ASI desativado;
2. instalar g585.2;
3. executar G-585;
4. anexar JSON.

Se o símbolo vier de outro plugin/MU-plugin, identificar o componente exato antes de qualquer ação. Não remover tabelas ASI e não criar whitelist sem evidência.

## Continuidade — Rebaseline ASI Functional Parity / UX-004

Product Owner explicitou:
- não aceitar perda de função existente do ASI;
- reconstruir/melhorar capacidades dentro do BDC;
- Word Cloud deve continuar funcional;
- BDC deve possuir Home pública própria;
- Home atual é baseline funcional/visual;
- Header/Body isolados são dívida de manutenção e devem ser consolidados.

ASI 4.6.8 inventory inicial confirmou:
- Public Search;
- post + item/section retrieval;
- QueryContext/vocabulary/bindings/relevance rules;
- Word Cloud completa;
- Search Events/Interactions/Outcomes/Quality Signals;
- Search Intelligence;
- Knowledge Curation/Diagnostics/Suggestions;
- Ranking Simulation;
- Golden Queries;
- queue/indexing/migrations/reconciliation/post-install;
- Quality Diagnostics.

Novos documentos:
- `specs/005-search-lexical-golden-queries/asi-functional-parity-rebaseline-v1.md`;
- `specs/005-search-lexical-golden-queries/asi-functional-inventory-v1.md`;
- `ux/004-public-home-portal/spec.md`;
- `ux/004-public-home-portal/tasks.md`.

Gate state:
- G-580 CLOSED;
- P-580A OPEN;
- UX-004 DISCOVERY/CONTRACT;
- G-585 PAUSED;
- G-590 BLOCKED.

Não instalar g585.2 neste momento como próxima ação. Preservar o pacote/evidência para retomada posterior.

Próximo passo técnico:
1. localizar ownership de `bc_home_config`, `bc_ultimas`, `bc_populares`, `bdc_home_filter_v270`, `bdc_entra_login`;
2. inventariar public Search/Word Cloud ASI com contratos;
3. decidir entry-point único da Home BDC;
4. implementar somente após H-001/H-010 fechados;
5. retomar G-585 apenas quando Home e matriz funcional não tiverem MISSING bloqueante sem plano.

## Continuidade — UX-005 Public Article Reader / GRE parity

O plugin BDC passa a controlar também a experiência pública dos artigos.

Baseline externa observada:
- GRE 0.6.0: 8 fields, editor/coverage e painel lateral automático;
- Astra Custom CSS atual: Home + ASI + Single Post + Elementor compatibility na mesma folha;
- screenshot real: hero documental, conteúdo principal, Dicas úteis e Resumo Executivo persistente à direita.

Decisão:
- UX-004 fica responsável pela Home;
- UX-005 fica responsável pelo Article Reader;
- P-580B inventaria GRE integralmente;
- não aposentar GRE por já existirem 3 metas Summary no BDC.

Executive Summary Rail futuro:
- read model composto;
- WP_Post title;
- Summary: objective/escalation/important;
- Classification: audience/responsible_team/catalog_item;
- affected_service/systems_involved: owners pendentes;
- preferir sticky dentro da grid;
- read-only e reflow mobile.

Dicas úteis:
- structured repeatable content;
- ordered;
- automatic top-of-article rendering;
- Workspace editor;
- ausência => zero markup;
- search/KD integration precisa de contrato;
- storage ainda não congelado.

Theme/CSS:
- retirar Astra Custom CSS da posição de owner;
- migrar hardening Elementor útil para CSS escopado do BDC;
- Article Reader deve provar funcionamento com Custom CSS desligado antes do cutover.

Próximo passo:
1. A001-A006 inventory;
2. localizar source/data de Dicas úteis;
3. fechar owners Serviço Afetado/Sistemas Envolvidos;
4. fechar A010-A015;
5. só depois implementar reader/package.

## Continuidade — P-580 Public Experience Inventory

A discovery build `0.5.0-p580a.1` is ready.

Purpose:
- identify runtime ownership of current Home/article behavior before rebuilding UX-004/UX-005;
- no product behavior changes;
- G-585 remains paused.

Expected environmental artifact:
- `front_page_settings`;
- `theme.custom_css` hash/bytes only;
- `target_shortcodes` with callback source;
- `public_hooks` with callback source;
- `public_surfaces.candidates`;
- `corpus.source_kind_counts`;
- `corpus.structured_tips_discovery`;
- `corpus.gre_meta_coverage`;
- safety flags all true/false as declared;
- errors/throwables empty.

Interpretation rules:
- callback source is evidence of ownership, not authorization to copy code;
- Custom CSS content is intentionally not exported;
- post content/GRE values are intentionally not exported;
- source-kind samples are IDs only;
- if Code Snippets or another generic host owns a callback, further targeted discovery may be required;
- no implementation starts until H-001/A-001 findings are classified.

Package SHA-256:
`39a0c8ba819d46713d1584fbd36c0efc6ab635c0929b63e21e1683d145109277`.

## Continuidade — P-580A.2 deep inventory

Environmental p580a.1 established:
- active Home ID 41395 is Elementor page;
- Home shortcodes/filter are hosted by Code Snippets;
- Entra login owner is external gateway;
- Search/Word Cloud disappear when ASI is disabled;
- published corpus is predominantly legacy_html (528/606);
- GRE environment injects Helpful Tips + Summary Rail through the_content;
- Tips meta is `_bdc_es_helpful_tips` on 7 posts;
- GAC and WP Unified Indexer also alter the public article pipeline.

Do NOT implement Home/Reader from appearance alone.

p580a.2 is the final targeted discovery pass before H-010/A-010:
1. identify exact Code Snippet metadata/hash containing Home v2.7.0 functions;
2. report behavioral signals for latest/popular/filter without exporting source;
3. report installed plugin versions and hashes of GRE Tips/Frontend files;
4. profile only the structural shape of Helpful Tips values.

Package:
- version `0.5.0-p580a.2`;
- SHA-256 `319b97c4f5ee0a6f9f0679cb478d113b91413660bf19a00b83ec56eca2b6d0b4`;
- 60/60 PHP lint;
- 46/46 active requires;
- 25/25 checks;
- deterministic 2/2;
- G-585 runner OFF.

Next:
- install p580a.2 over p580a.1;
- Base de Conhecimento → Inventário Público;
- execute and attach JSON;
- then close H-001/A-001 and freeze H-010/A-010 contracts.

## Continuidade — Public Experience preview 0.5.0-ux004005.1

Discovery is closed for Home/Article ownership:
- H-001 CLOSED;
- A-001 CLOSED.

Contracts:
- H-010 CLOSED/FROZEN;
- A-010 CLOSED/FROZEN.

Preview package:
- version `0.5.0-ux004005.1`;
- SHA-256 `d61acada9230f454efc7ecb2c4580cf4c612d4a7724edaa84d7e8fd1314e8fe7`;
- 77 files / 66 PHP;
- 66/66 lint + extracted lint;
- 49/49 active requires;
- 26/26 checks;
- deterministic build 2/2.

Safety:
- admin-only preview;
- manage_options + nonce;
- no default template takeover;
- no page_on_front/editorial/meta/Elementor writes;
- G-585 runner OFF;
- Search core/ranker unchanged.

Home:
- preview shell/Header;
- latest/categories/popular compatibility;
- admin-only lexical Search;
- Word Cloud provisional ONLY — not parity/cutover ready.

Article:
- preview shell;
- canonical the_content remains;
- GAC/WPUI preserved;
- GRE Helpful Tips/Rail replaced only in preview;
- BDC Tips Store reads existing physical key;
- composed sticky Summary Rail.

Human next:
- install over p580a.2;
- Base de Conhecimento → Prévia Pública;
- capture Home preview + at least one article with Tips/Summary;
- do not change legacy plugins/snippets/theme during this review.

After visual review:
- iterate H-020/A-020;
- build real public Search facade;
- implement full Word Cloud parity module;
- add Workspace Helpful Tips writer;
- execute source-kind regression before any cutover.

## Continuidade — redesign public preview 0.5.0-ux004005.2

Previous preview:
- `0.5.0-ux004005.1` = architecture/functional baseline;
- human visual direction REJECTED;
- never use .1 as final visual reference.

Redesign candidate:
- version `0.5.0-ux004005.2`;
- SHA-256 `24e96812189a7fdd1252714030a4b0341f9357170721c2aa94dbcc2d18c4181b`;
- 80 files / 69 PHP;
- 69/69 lint + extracted ZIP lint;
- 52/52 active requires;
- 33/33 checks;
- deterministic build 2/2.

Critical changes:
- Auth Bridge -> `[bdc_entra_login]` preferred;
- WP account/logout fallback;
- custom logo first;
- menu-first navigation resolver;
- complete Home visual recomposition;
- no technical cloud copy in consumer UI;
- read-only Article Content compatibility stage;
- duplicate legacy chrome removed only with title + >=2 chrome marker evidence;
- Tips auto-fit;
- 360px sticky Summary Rail;
- canonical content pipeline/GAC/WPUI preserved.

Safety:
- admin preview only;
- manage_options + nonce;
- no page_on_front/content/meta/Elementor write;
- no ASI/GRE decommission;
- Search core/ranker unchanged;
- G-585 PAUSED / G-590 BLOCKED.

Next homologation:
- install .2 over .1;
- Base de Conhecimento → Prévia Pública;
- Home screenshot;
- article screenshot with Tips + Summary;
- confirm login/profile action;
- confirm no duplicated title/header inside article.

## Continuidade — 0.5.0-ux004005.3 Search-first

Design decision:
- Home is a Search product;
- secondary discovery is progressive disclosure;
- Search is available inside Article Reader;
- candidate navigation must not fall back to legacy articles.

Research:
- Material persistent Search;
- Zendesk search on all Help Center pages;
- Intercom Modern Help Center quick Search / clean docs reading;
- GitBook Ctrl/Cmd+K global Search;
- Algolia DocSearch documentation-search UX.

Package:
- version `0.5.0-ux004005.3`;
- SHA-256 `19bebb7b55015dac8b356f66390915c8a21659b362df2315f64a9f69905a2cea`;
- 81 files / 69 PHP / 2 JS;
- 69/69 lint + extracted lint;
- 52/52 active requires;
- 40/40 checks;
- deterministic 2/2.

Home:
- centered primary Search;
- compact suggestions;
- “Explorar a Base” disclosure contains Categories + Latest + Popular;
- no metrics dashboard;
- no large marketing hero.

Article:
- persistent global Search in header;
- Ctrl/Cmd+K;
- query result panel stays in article;
- result links use candidate Reader;
- clean article heading/content;
- Tips + Summary Rail preserved;
- legacy chrome sanitizer remains.

Next homologation:
1. install .3 over .2;
2. open Home preview;
3. test Ctrl+K;
4. search from Home and open a result — it must stay in new Reader;
5. search from inside Reader — no Home return required;
6. test Latest/Popular links — must open new Reader;
7. capture Home + Reader screenshots;
8. do not cut over or disable ASI/GRE yet.

## Continuidade — 0.5.0-ux004005.4

Keep Home search-first direction.

v4 focus:
1. restore complete Header behavior without reverting visual direction;
2. preserve exact Entra profile-menu attributes;
3. keep global article Search without replacing quick links;
4. move Executive Summary to a true right-side sticky context rail;
5. protect article reading width;
6. add restrained visual separation to Reader canvas.

Header parity:
- Home;
- Consulta Avançada;
- Telefones;
- Links Úteis;
- POSTI;
- Entra profile menu;
- mobile quick-link toggle.

Reader:
- content ~980px;
- rail ~350px;
- sticky;
- header-aware top offset;
- independent internal scroll only when required;
- reflow below article on narrower viewport.

Package:
- `0.5.0-ux004005.4`;
- SHA-256 `a07a518708b9ad92afbffacb6cd6527e944247e7084112851bc1e84c2c6ea300`;
- 69/69 lint + extracted lint;
- 19/19 v4 checks;
- deterministic 2/2.

Next homologation:
- install .4 over .3;
- verify all five Header links;
- open Entra profile menu and confirm department/job title/admin/logout;
- verify mobile quick-link toggle if possible;
- open a long article and scroll: Summary must follow until end of article region;
- confirm article width remains comfortable;
- capture Home + long Reader screenshot.

No cutover / no plugin decommission.

## Continuidade — 0.5.0-ux004005.5

Focus:
1. incremental Search like current ASI interaction model;
2. restore GAC in candidate Reader;
3. make Summary follow scroll reliably;
4. reduce title chrome and optimize article width;
5. emphasize Search inside article without changing Header parity.

Search v5:
- typing triggers results;
- no Enter required;
- min 2 chars;
- debounce 180ms;
- AbortController cancels stale requests;
- current BDC Search Service remains the retrieval engine;
- live results include title/category/excerpt/rank and candidate Reader link;
- ASI section/trecho result parity remains pending.

Reader v5:
- real WordPress loop + the_content pipeline restores integration conditions for GAC/WPUI;
- duplicate legacy chrome sanitizer remains read-only;
- Summary rail = 300px follow-scroll context rail;
- article target = 1060px;
- no-Summary articles use single centered column;
- reduced heading card;
- Search row visually stronger.

Package:
- `0.5.0-ux004005.5`;
- SHA-256 `9b011563b24345139350fb0c7477050e7803ea51ef7aa912bf983b98e2b89cd9`;
- 69/69 lint + extracted ZIP lint;
- 25/25 checks;
- deterministic 2/2.

Next homologation:
- install .5 over .4;
- type in Home Search without Enter and observe results changing;
- repeat inside Reader;
- open long article and confirm Summary follows scroll and stops at article end;
- confirm GAC “Ações do conteúdo” is restored;
- verify an article with no Summary uses full-width single column;
- capture screenshots.

No cutover / no ASI-GRE-GAC decommission.

## Continuidade — 0.5.0-ux004005.6 Premium Polish

Do not reopen architecture unless homologation exposes a regression.

Preserve:
- live Search;
- GAC;
- Summary follow-scroll;
- Header parity;
- Home search-first;
- candidate-to-candidate navigation.

v6 polish:
- premium Search result hierarchy;
- loading feedback;
- internal Search result scroll;
- quieter Header;
- document heading without card treatment;
- integrated Helpful Tips callout;
- refined article typography/line length;
- quieter Summary presentation.

Package:
- `0.5.0-ux004005.6`;
- SHA-256 `68b916b6eaf1281bc3b6208694dbaf7733831970bbcc970ae69de58fce5a1ad4`;
- 69/69 lint + extracted lint;
- JS syntax PASS;
- CSS 4/4 parse PASS;
- 24/24 checks;
- source parity 9/9;
- deterministic 2/2.

Next homologation:
1. install .6 over .5;
2. compare Home live Search visually with .5;
3. verify loading/typing/scroll in result panel;
4. inspect Autran Reader: GAC and Summary behavior must be unchanged;
5. judge only visual hierarchy, density and readability;
6. capture Home result state + Reader top + Reader mid-scroll.

No cutover / no dependency decommission.

## Continuidade — visual paused / P-580WC.1

Visual:
- `0.5.0-ux004005.6` is the approved current baseline;
- stop active UI polish;
- resume only as new maturity/capabilities justify it.

Next functional candidate:
- `0.5.0-p580wc.1`;
- SHA-256 `0b68e3eefa19bb68dce74de208e37bb128e6de1190162fbfc326ac67670f3a8d`;
- Word Cloud BDC-owned v1;
- no ASI runtime/storage reads;
- no new table;
- no public-request generation;
- manual + hourly generation;
- quality/snapshot/lock/health/history/admin;
- telemetry/vocabulary sources are explicit pending, not simulated.

Homologation:
1. install p580wc.1 over v6;
2. open Base de Conhecimento → Nuvem de Conhecimento;
3. click Gerar snapshot agora;
4. capture state/term count/source statuses;
5. open Public Home preview and verify suggested terms;
6. click a term and verify Search receives it;
7. verify cron is scheduled;
8. later repeat Home smoke with ASI manually disabled.

Do not execute G-585 yet until Word Cloud/Home technical acceptance closes the blocking parity condition.


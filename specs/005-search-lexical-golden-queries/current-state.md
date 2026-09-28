# Estado atual — SPEC-005

**Status:** ATIVA — PREMIUM CONSOLIDATION / G-590 SECTION RETRIEVAL & DEEP-LINK  
**Branch:** `spec005-section-retrieval-deeplink`  
**Premium baseline:** `01508379f91a336b26b17268fb119458bd077f7e`

## Baselines protegidas

R-500, R-510, G-520, G-530, G-540, G-550, G-560, G-570 e G-580 permanecem PASS/CLOSED e funcionam como contratos obrigatórios de regressão.

O parent ranker permanece `lexical-ranker-v1.0.0`. G-590 não pode alterar seus pesos, sinais ou tie-break.

## G-590 — runtime candidato

Implementado:
- Section Projector determinístico;
- Search Document `search-document-v1.1.0`;
- Search Projection schema `1.1.0` na mesma tabela post-level;
- schema contract físico de colunas + índices;
- lifecycle degrade-safe para schema/version mismatch;
- Section Ranker `lexical-section-ranker-v1.1.0`;
- Section Result `search-section-result-v1.1.0`;
- fachada `Search_Service::search_sections()`;
- Anchor Manager efêmero/fail-closed;
- G-550 addendum para document v1.1 sem rebaseline de rank/result post-level;
- runner ambiental G-590;
- machine evidence validator;
- performance budget G-590 p95 <= 900 ms / max <= 1500 ms.

## Decisões v1.1

Encontrabilidade não depende de navegabilidade.

Uma Section `unresolved`:
- continua elegível ao rank lexical;
- mantém `section_key`;
- retorna `deep_link_available=false`;
- retorna `deep_link_url=''`;
- usa `parent_url` como `url`.

Uma Section `generated` pode retornar deep-link somente quando o destino HTML é comprovável.

## Gate local atual

Static structural gate sobre blobs GitHub atuais:
- **38/38 PASS**;
- tabela única preservada;
- candidate query não carrega `sections_json`;
- zero write editorial no runner/anchor;
- zero network;
- G-550 compatibility preservada;
- lifecycle/bounds/performance/source-kind probes presentes.

Este PASS é estrutural/local. Não equivale a G-590 PASS ambiental.

## Master Parity Ledger

- ASI-003 item/section retrieval = PARTIAL;
- ASI-004 stable item identity = PARTIAL;
- ASI-005 anchors/deep-links = PARTIAL.

PARTIAL continua blocker de cutover.

## Política de identidade do build

O Premium Product Standard é aplicado já no G-590:
- Product Version e engenharia são separados;
- o source não recebe Build ID persistente;
- o staging de homologação recebe `BDC_KB_BUILD_ID`;
- o validador ambiental rejeita evidência sem Product Version/Build ID esperados.

## Incidente G-590.1

O primeiro candidato ambiental `0.5.1-rc.1 / g590.1` retornou HTTP 504 antes da geração da evidência.

Classificação:
- FAIL CONTROLADO de orquestração;
- resultado funcional G-590 = NÃO AVALIADO;
- causa: runner monolítico síncrono;
- correção: `g590-resumable-v1.0.0`, com estado persistido, batches e retomada.

O core `Search_Rebuild_Service` permanece inalterado porque já foi homologado no G-580.

## Incidente G-590.2

O RC2 carregou a tela resumível, mas os botões não executaram ação.

Classificação:
- FAIL CONTROLADO de UI bootstrap;
- resultado funcional G-590 = NÃO EXECUTADO;
- causa: enqueue tardio dentro de `render_page()`;
- correção RC3: asset próprio via `admin_enqueue_scripts` + `wp_localize_script`.

## Incidente G-590.3

O RC3 executou o runner até **Concluído — JSON disponível**, porém o link de download transportou `&amp;` literal e o WordPress rejeitou o nonce.

Classificação:
- execução G-590 = CONCLUÍDA;
- relatório persistido = DISPONÍVEL;
- download = FAIL CONTROLADO;
- nenhuma reexecução funcional é necessária para a correção RC4.

A causa foi dupla codificação de URL ao usar `wp_nonce_url()` antes do boundary de renderização.

RC4 usa `add_query_arg()` + `wp_create_nonce()` e preserva o escaping somente no HTML.

## Evidência ambiental RC4

RC4 executou integralmente em WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2.

Resultado:
- T590-14 PASS;
- T590-15 FAIL;
- T590-16 FAIL;
- T590-17 PASS;
- T590-18 PASS;
- T590-19 FAIL / G-590 OPEN.

Review determinou dois gaps no **evidence contract**, não regressão comprovada do runtime:

1. T590-15 contava todo node não-heading de `Numbered_Hierarchy_Resolver`, inclusive listas `explicit_dom`, como strong hierarchy gap.
2. T590-16 descartava candidates de título repetido sem rare token; Gutenberg possuía 17 generated anchors, mas nenhum probe formulável pelo v1.2.

Evidence Contract v1.3 corrige somente runner/validator:
- blocker hierarchy = `numbering_inferred && depth > 1`;
- raw numbered nodes permanecem telemetria;
- strong gaps recebem amostras concretas;
- fallback `repeated_title_runtime_probe` não auto-aprova: a `section_key` esperada precisa ser recuperada pelo runtime.

SPEC-004 não é reaberta antes da evidência RC5.

## Evidência ambiental RC5

RC5 confirmou:
- T590-14 PASS;
- T590-15 FAIL;
- T590-16 FAIL;
- T590-17 PASS;
- T590-18 PASS;
- T590-19 FAIL / G-590 OPEN.

T590-15 agora é tratado como gap estrutural real: 1.294 strong numbered nodes sem heading contextual, com amostras legacy_html materializadas como paragraphs.

SPEC-004 foi reaberta em R-260 DISCOVERY/read-only. A baseline G-250 permanece válida.

T590-16 continha divergência no runner: candidates eram publish-only, enquanto Search canônico autoriza todos os statuses governados com `edit_post`.

## RC6 — discovery/evidence

- Product Version `0.5.1-rc.6`;
- Build ID `g590.6-27223c6ce89e`;
- source commit `27223c6ce89e7fb25453086d0fe6a60c0cdeec36`;
- runner blob `1a96246ed350ac9882836dfabcfc2437cb04a888`;
- ZIP SHA-256 `cd4cd53eea5ba140c60accfaeaaf58c2f99b049c542fc36d3d244588afd82842`;
- package: 82 arquivos;
- PHP 69/69;
- JS 3/3;
- JSON 2/2;
- active requires 65/65;
- inherited files 80/80 byte-identical ao RC5;
- deterministic build 2/2.

RC6 não altera Search Section Projector/Ranker/Service/Anchor Manager. É diagnóstico para fechar T590-16 e dimensionar R-260.

## RC6 — resultado ambiental

RC6 fechou T590-16:
- Elementor probe coverage PASS;
- Gutenberg probe coverage PASS;
- legacy_html probe coverage PASS;
- unprobed_source_kinds = [];
- section/deep-link/visible-text failures = 0.

Gate:
- T590-14 PASS;
- T590-15 FAIL;
- T590-16 PASS;
- T590-17 PASS;
- T590-18 PASS;
- T590-19 FAIL / OPEN.

Único blocker: T590-15 / R-260.

R-260 evidence:
- 1.294 strong gaps sem heading contextual;
- 189 posts afetados;
- 548 candidatos determinísticos em 94 posts;
- ~81,9% dos gaps concentrados em legacy_html.

## RC7 — R-260A discovery

- Product Version `0.5.1-rc.7`;
- Build ID `g590.7-4fd94fb372eb`;
- source commit `4fd94fb372ebfe5ca9da35a43f73310156c9d573`;
- runner blob `2485fa86808e9ea0b7420f63fc412b6cc3071c31`;
- R260 profiler blob `535ce3ccaae5325f17b3a59bd533470318e4cf3f`;
- ZIP SHA-256 `b826005d8ad777bfeac47e8b9a0743d42ef033b535ba59ae08f1bb1a4d8e8fbc`;
- 83 files;
- PHP 70/70;
- JS 3/3;
- JSON 2/2;
- deterministic build 2/2.

RC7 adiciona somente diagnóstico read-only para distinguir TOC duplicado de pseudo-heading com corpo.

## Evidência ambiental RC7

RC7:
- T590-14 PASS;
- T590-15 FAIL;
- T590-16 PASS;
- T590-17 PASS;
- T590-18 PASS;
- T590-19 OPEN.

R-260A classificou 548 deterministic candidates:
- 77 TOC-like;
- 289 body-bearing;
- 182 uncertain.

R-260A = PASS / DISCOVERY CLOSED.

O Anchor Manager atual é heading-only, portanto paragraph-derived Sections não podem receber deep-link comprovado sem novo contrato.

R-260B foi aberto em shadow-only para medir:
- heading collisions;
- duplicate candidate labels;
- MAX_SECTIONS overflow;
- paragraph anchor-contract blockers.

Option C — dedicated structural projection shared by consumers — permanece arquitetura preferida, porém ainda não congelada.

## RC8 — R-260B shadow package

- Product Version `0.5.1-rc.8`;
- Build ID `g590.8-5456389bc863`;
- source commit `5456389bc8632cb21cedfa5d758380fa7ca5e242`;
- runner blob `fd2f92f1146fb30aa4174eef9b6d37d18698cb56`;
- R260 profiler blob `e6157eb3cdcb103beff529aac92bece1dd88f025`;
- R260B shadow blob `a737c78264f6a52dfefd067e454dce997732647d`;
- ZIP SHA-256 `f237f2be921acc5bcdf5798a664f020d5b1ba0d24d1435f78cd638e0af61ad32`;
- files 84;
- PHP 71/71;
- JS 3/3;
- JSON 2/2;
- active requires 67/67;
- RC7 inherited unchanged 80/80;
- deterministic build 2/2.

RC8 é shadow-only. Não promove paragraph para Section e não altera Anchor Manager.

## Evidência ambiental RC9 / R-260C

RC9 executou integralmente em WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2.

Gate:
- T590-14 PASS;
- T590-15 FAIL;
- T590-16 PASS;
- T590-17 PASS;
- T590-18 PASS;
- T590-19 FAIL / OPEN.

Lifecycle/regression:
- corpus 623/623;
- extractor errors 0;
- explicit rebuild PASS;
- pass1/pass2 623 NO_CHANGE / 0 writes;
- determinism mismatch 0;
- Golden post-level PASS;
- p95 305.8531 ms / max 341.8281 ms;
- fingerprint editorial equal.

R-260C sobre 211 `promotable_shadow`:
- paragraph_unique: 131;
- paragraph_ambiguous: 80;
- block_unique_nonparagraph: 0;
- block_ambiguous: 0;
- not_rendered_exact: 0.

Disposition:
- **R-260C PASS / DISCOVERY CLOSED**;
- 100% dos targets existem no HTML renderizado;
- 62,0853% são inequívocos por título;
- 37,9147% exigem desambiguação;
- Deep-Link Contract v2 é viável somente fail-closed;
- first/nth occurrence, substring ou fuzzy matching permanecem proibidos.

Review: `g590-rc9-r260c-environmental-review-20260925.md`.

## Evidência ambiental RC10 / R-260D1

RC10 executou integralmente em WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2.

Gate:
- T590-14 PASS;
- T590-15 FAIL;
- T590-16 PASS;
- T590-17 PASS;
- T590-18 PASS;
- T590-19 FAIL / OPEN.

R-260D1:
- promotable_shadow: 211;
- title_unique: 131;
- context_unique: 66;
- context_ambiguous: 0;
- context_insufficient: 2;
- context_not_matched: 12;
- title_not_rendered: 0;
- effective_unique: 197/211 = 93,3649%;
- unresolved: 14.

Performance:
- p50 190.0358 ms;
- p95 341.861 ms;
- max 350.6229 ms;
- technical failures 0.

Safety:
- editorial fingerprint equal;
- corpus IDs equal;
- zero editorial write;
- zero external network;
- zero ASI runtime dependency;
- parent ranker frozen.

Disposition:
- **R-260D1 PASS / DISCOVERY CLOSED**;
- 66/80 ambiguidades do RC9 foram resolvidas deterministicamente;
- nenhum target ficou `context_ambiguous`;
- 14 permanecem fail-closed;
- R-260D2 autorizado.

Review: `g590-rc10-r260d1-environmental-review-20260928.md`.

## R-260D2 — runtime promotion

Arquitetura congelada:
**Option C — Dedicated Structural Projection shared by consumers**.

Contratos:
- `../004-content-extractor-knowledge-document/r260d2-structural-runtime-promotion-contract-v1.md`;
- `g590-deep-link-contract-v2.md`;
- `g590-evidence-contract-addendum-v1.5.md`.

Disposition estrutural:
- deterministic candidates: 548;
- TOC-like: 77 — suprimidos;
- body-bearing: 289 — projetados;
- uncertain: 182 — não promovidos;
- body-bearing unique-label: 211;
- body-bearing duplicate-label: 78 — continuam retrieval-eligible, com anchor fail-closed quando target não for comprovado.

A nova Structural Projection:
- versão `r260-structural-projection-v1.0.1`;
- não altera Content Extractor;
- não altera KD 2.1.0;
- usa identity estável baseada em post/tipo/depth/token/título/occurrence;
- não persiste offset DOM;
- não escreve conteúdo editorial.

Search:
- Section Projection promovida para `search-section-projection-v1.1.0`;
- Search Document permanece `search-document-v1.1.0`;
- schema físico Search permanece `1.1.0`;
- lexical-ranker-v1.0.0 permanece congelado.

Deep-Link v2:
- headings preservam resolução v1;
- paragraph structural usa `title_unique` ou `context_unique`;
- runtime revalida o target;
- qualquer divergência falha fechado;
- unresolved permanece pesquisável.

## T590-15 — Evidence Contract v1.5

`strong_numbered_without_heading_context` permanece telemetria obrigatória.

O blocker deixa de ser o raw counter universal e passa a ser **perda de estrutura determinística body-bearing comprovada**.

PASS exige, entre outros:
- candidate/disposition reconciliation integral;
- todo body-bearing projetado exatamente uma vez;
- duplicate-label não eliminado do retrieval;
- zero promoção unsafe de TOC/uncertain/ambiguous-confidence;
- zero projection gap/extra;
- zero overflow;
- zero identity collision;
- zero repository error;
- todo generated structural anchor materializável;
- texto visível inalterado.

Isso não é waiver do T590-15; qualquer gap concreto continua bloqueante.

## Evidência ambiental RC11 — FAIL CONTROLADO

RC11 executou integralmente em WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2.

Gate:
- T590-14 PASS;
- T590-15 FAIL;
- T590-16 PASS;
- T590-17 PASS;
- T590-18 PASS;
- T590-19 FAIL / OPEN.

Runtime promotion comprovado:
- 548 deterministic candidates;
- 289 body-bearing canônicos;
- 289 runtime Sections persistidas;
- 78 duplicate-body retidas;
- 263 anchors generated;
- 26 unresolved;
- projection gap 0;
- projection extra 0;
- unsafe promotion 0;
- overflow 0;
- identity collision 0;
- repository errors 0;
- generated anchor materialization failures 0;
- visible text changed 0.

Performance:
- p50 154.7291 ms;
- p95 231.3879 ms;
- max 238.5709 ms;
- technical failures 0.

Safety:
- editorial fingerprint equal;
- corpus IDs equal;
- zero ASI;
- zero network;
- zero editorial write;
- parent ranker frozen.

Único blocker:
- runtime dispositions = 62 TOC / 289 body / 197 uncertain;
- R-260A canônico = 77 TOC / 289 body / 182 uncertain.

Root cause:
- Structural Projection v1.0.0 calculava occurrence evidence somente após filtrar candidates;
- R-260A calcula occurrences sobre todos os hierarchy nodes antes do filtro;
- 15 candidates em 6 posts perderam `later_same_label` e foram classificados como uncertain;
- os 289 body-bearing/Sections não foram afetados.

Classificação:
**FAIL CONTROLADO / CLASSIFICATION PARITY DRIFT.**

Review: `g590-rc11-r260d2-environmental-review-20260928.md`.

## RC12 — parity correction

Structural Projection `r260-structural-projection-v1.0.1`:
- occurrence population = todos os hierarchy nodes válidos;
- candidate filtering ocorre depois;
- sem mudança no conjunto dos 289 body-bearing;
- regression R-260A ↔ runtime obrigatório para candidate/TOC/body/uncertain.

Source commit:
`61681200f1152d98caf949568cfd99810c83e57a`.

Package:
- Product Version `0.5.1-rc.12`;
- Build ID `g590.12-61681200f115`;
- ZIP SHA-256 `eeeb4222ed2e68ed6a488d088df8d3e61bd6313d87ace09f06f2cb8173b55026`;
- files 88;
- PHP 75/75;
- JS 3/3;
- JSON 2/2;
- active requires 71/71;
- R-260D2 behavior 23/23;
- R-260A ↔ runtime parity 4/4;
- Evidence Contract v1.5 fail-closed 13/13;
- deterministic build 2/2 byte-identical;
- delta RC11 → RC12: +0 / ~2 / -0;
- inherited byte-identical: 86/88.

Modified no ZIP:
- bootstrap — somente Product Version / Build ID;
- `class-r260-structural-projector.php` — occurrence parity fix.

Todo o restante do runtime RC11 permanece byte-idêntico.

## Próximo passo exato

1. instalar RC12 em homologação;
2. abrir **Base de Conhecimento → Section Retrieval G-590**;
3. selecionar **Reiniciar evidência**;
4. aguardar **Concluído — JSON disponível**;
5. baixar o JSON;
6. validar com `tools/homologation/spec005/validate-g590-evidence.php`;
7. somente `failed=0` com T590-14..18 PASS autoriza T590-15/T590-19 PASS/CLOSED.

Até a evidência RC12:
- G-590 permanece OPEN;
- PR #9 permanece DRAFT / NO MERGE;
- G-585 continua pausado;
- G-595 e SPEC-006 permanecem bloqueados.

## Limites de escopo

Não entram em G-590:
- Public Experience completa — SPEC-007;
- telemetry/vocabulary/relevance governance — SPEC-008;
- durable operations/queue — SPEC-009;
- semantic/vector — SPEC-010;
- AI/Foundry — SPEC-011+.

Histórico detalhado dos gates fechados permanece nos respectivos closeouts e em `evidence/`; este arquivo representa apenas o estado canônico atual.

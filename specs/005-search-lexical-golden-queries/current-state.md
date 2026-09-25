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

## Próximo passo exato

RC9 / R-260C é o candidato canônico para a próxima execução ambiental.

Pacote:
- Product Version `0.5.1-rc.9`;
- Build ID `g590.9-ea7203b6c2ab`;
- source commit `ea7203b6c2ab2e34438eb7b5d53c05ee877cac0a`;
- runner blob `9e360a019d067c8fb7e70c06175502c725fe495b`;
- R-260B shadow blob `19dd24055e5a9279b75ea7374a37f2063bfbf9b2`;
- R-260C anchor-feasibility blob `63e888e1e90a2154263f774b5a351eab119b68e0`;
- ZIP SHA-256 `8b56270a74b675d5601e93dc99553c11f0910017b3d2bf3a2c6aad3fe65c12f9`;
- package gates: 85 arquivos, PHP 72/72, JS 3/3, JSON 2/2, active requires 68/68, RC8 inherited 81/81, deterministic build 2/2.

Executar:
1. instalar RC9 em homologação;
2. abrir **Base de Conhecimento → Section Retrieval G-590**;
3. iniciar nova evidência RC9;
4. aguardar **Concluído — JSON disponível**;
5. baixar o JSON e validar com `tools/homologation/spec005/validate-g590-evidence.php`;
6. classificar R-260C por `paragraph_unique`, `paragraph_ambiguous`, `block_unique_nonparagraph`, `block_ambiguous` e `not_rendered_exact`;
7. somente com essa evidência decidir Deep-Link Contract v2 / R-260D runtime promotion.

Regras:
- `paragraph_unique` é o candidato primário para target navegável;
- `block_unique_nonparagraph` exige revisão adicional;
- ambiguous/no-match continuam fail-closed;
- Anchor Manager, Search Section Projector, Content Extractor e KD permanecem inalterados até a decisão R-260D;
- G-590 permanece OPEN.

Após evidência ambiental RC9:
1. decidir viabilidade do Deep-Link Contract v2;
2. congelar ou rejeitar R-260D runtime promotion;
3. somente depois reavaliar T590-15/T590-19;
4. G-585 continua pausado até fechamento real de G-590;
5. G-595 e SPEC-006 permanecem bloqueados.

## Limites de escopo

Não entram em G-590:
- Public Experience completa — SPEC-007;
- telemetry/vocabulary/relevance governance — SPEC-008;
- durable operations/queue — SPEC-009;
- semantic/vector — SPEC-010;
- AI/Foundry — SPEC-011+.

Histórico detalhado dos gates fechados permanece nos respectivos closeouts e em `evidence/`; este arquivo representa apenas o estado canônico atual.

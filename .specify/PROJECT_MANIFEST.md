# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada

**Tipo:** Plugin WordPress único, modular internamente

**Idioma:** Português do Brasil

**Estado:** SPEC-000/001/002/003/004 concluídas; SPEC-005 ATIVA/DISCOVERY; UX-001/UX-002/UX-003 concluídas; G-240/G-245/G-250 PASS/CLOSED

**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento sobre WordPress Core, com conteúdo editorial canônico em `WP_Post.post_content`/Core Blocks, evoluindo por vertical slices, com experiência coerente, encontrabilidade orientada à resposta confiável e dados derivados reconstruíveis.

Elementor é tratado como fonte legada durante a transição; não como arquitetura editorial futura.

## Estado consolidado

### SPEC-000 — Inventário Profundo e Contratos

**CONCLUÍDA.**

### SPEC-001 — Core mínimo + Summary narrativo

**CONCLUÍDA para desenvolvimento/homologação.**

Baseline histórica:

- package `0.1.0-rc.1`;
- SHA-256 `c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`;
- G-001/G-020/G-070/G-110/B-006/G-130 PASS.

### SPEC-002 — Classificação de Conhecimento

**CONCLUÍDA para desenvolvimento/homologação.**

Baseline funcional:

- package `0.2.0-rc.1`;
- SHA-256 `a5120299ea907d271bc39b318857ba033cf0cf340fe1836289f42a4c717b8fb5`;
- C-001/C-010/G-001/G-030/B-006/G-070/G-110/G-130 PASS;
- quatro taxonomias canônicas WordPress;
- legado read-only/advisory;
- sem migração automática ou dual-write.

### UX-001 — Product Experience & Knowledge Workspace

**CONCLUÍDA como baseline de produto.**

Resultado:

- arquitetura de informação fechada;
- Design System v1;
- Knowledge List + Knowledge Workspace;
- Summary/Classificação integrados ao Workspace;
- responsive/accessibility validados no protótipo;
- Heritage Pack KB2Ops;
- UI as Code v0.2 como artefato visual canônico.

### UX-002 — Mockup Visual Foundation

**CONCLUÍDA / PASS humano em 2026-09-17.**

Baseline visual homologada: `0.4.0-ux002.3`.

Resultado:

- `scr/` + Visual Contract v2 + Design System são autoridade visual operacional;
- WordPress permanece shell/plataforma, sem obrigar aparência genérica do wp-admin nas superfícies BDC;
- Knowledge List, Workspace, Summary, Classificação, Review, Histórico e vocabulários convergidos para a mesma identidade visual;
- toda nova UI ou alteração material deve seguir `ux/002-mockup-visual-foundation/visual-contract-v2.md` e mockups aplicáveis.

### SPEC-003 — Review & Governança

**CONCLUÍDA.**

Baseline funcional congelada:

- package `0.3.0-rc.1`;
- SHA-256 `7f681a3f62d792d30ccb016ae64b03e83d5cc46c4b2b1e2c2d96d41e3dfd0db5`;
- Review baseado em WordPress Comments API append-only;
- Histórico como projeção read-only do event log;
- `post_status` independente da governança.

### SPEC-004 — Content Extractor, Knowledge Document e Canonical Block Normalization

**CONCLUÍDA / CLOSED / main.**

G-240 foi promovido para `main` com KD 2.1.0 e aceite técnico/humano. G-245 foi promovido para `main` via PR #4 após G-250 e RC2 Final Smoke PASS.

#### Decisão arquitetural vigente

ADR-004-001 aceita em 2026-09-17:

- `WP_Post.post_content` + WordPress Core Blocks são o destino editorial canônico futuro;
- plugin Gutenberg não é dependência de produção;
- usar somente APIs estáveis do WordPress Core homologado;
- Elementor permanece source adapter legado temporário;
- `_elementor_data` é preservado até dependência zero e gate explícito de retirada;
- nenhum novo writer usa `_elementor_data` como destino;
- antigo T087C writer Elementor foi cancelado/superseded antes de implementação.

Constituição vigente nesta branch: **v1.3.0**.

#### Gates G-245 preservados

- T080: PASS WITH REVIEW ITEMS;
- T081 Elementor Projection: PASS ambiental histórico/read-only;
- T082–T086: gates defensivos concluídos local/contratualmente;
- T083B Durable Journal Storage: PASS ambiental;
- T087A/T087B-prep: readiness/lock PASS local/read-only;
- journal, stale-source, dry-run, batches, lock, canary/rollback permanecem investimentos válidos e serão generalizados para Block Migration.

#### Block Projection / diagnóstico

- T090 Block Projection v1.0: PASS LOCAL;
- T091 full-corpus: PASS AMBIENTAL;
- T092 Block Projection v1.1: PASS LOCAL, 25/25 assertions;
- T093 full-corpus v1.1 + diagnóstico KD: PASS AMBIENTAL.

Baseline ambiental atual: **623 posts**.

T093: 623/623 em duas passagens, zero errors/throwables/hash mismatches/safety violations e fingerprint editorial inalterado.

#### T094 — Editorial Fidelity — PASS AMBIENTAL

Evidência: `specs/004-content-extractor-knowledge-document/evidence/g245-editorial-fidelity-t094-20260917T180802Z.json`.

Resultado principal:

- `rich_html_source_required`: 467;
- `elementor_source_adapter_required`: 79 na classificação conservadora do inventário;
- `shortcode_resolution_required`: 37;
- `kd_structure_sufficient_candidate`: 33;
- `native_core_blocks`: 4;
- `not_applicable`: 3;
- errors/throwables: 0;
- `gate_result.t094_editorial_fidelity_pass=true`.

O corpus contém 6.874 links, 4.595 imagens, 25.764 ocorrências de inline formatting, 513 tabelas e 53 posts com shortcodes. Isso provou que o KD 2.1 não deve ser tratado como representação editorial lossless.

#### Arquitetura de duas projeções

**Conhecimento:**

`fonte editorial -> Content Extractor -> Knowledge Document`

Uso: busca, IA, hierarquia, qualidade e guardrail semântico.

**Migração:**

`fonte editorial -> Migration Fidelity Source -> Lossless Core Block Serializer`

Uso: preservar fielmente o material editorial durante a normalização.

#### T095 — Migration Fidelity Source v1

**PASS LOCAL / READ-ONLY.**

- legacy HTML/plain text preservados exatamente;
- Gutenberg existente preservado como `native_core_blocks`;
- Elementor `text-editor`/`shortcode` preservados em unidades lossless;
- mixed source sempre exige revisão humana;
- raw payload permanece somente em memória;
- `fidelity_hash` determinístico;
- zero writer/network/render.

Contrato: `migration-fidelity-source-contract-v1.md`.

#### T096 — Lossless Core Block Serialization

**PASS LOCAL / HOMOLOGAÇÃO PENDENTE.**

Primeira canonicalização lossless:

- legacy HTML/plain text -> `core/freeform`;
- Elementor text-editor -> `core/freeform`;
- Elementor shortcode -> `core/shortcode`;
- Gutenberg existente -> `native_noop`;
- mixed/unsupported -> fail-closed.

O objetivo é convergir para primitives do WordPress Core sem tentar reconstruir prematuramente milhares de links, imagens, spans e tabelas. O refinamento de `core/freeform` em blocos semânticos é uma etapa posterior, orientada pelo KD e por novos gates.

Validação local combinada T095/T096: **34/34 assertions PASS + PHP lint PASS**.

Pacote T096:

- `0.4.0-g245-lossless-t096.1`;
- SHA-256 `5a2fc4ac31bfbe2b68cfe5f06d07057310760f54c9f5ecfc9fbc55b3b07ad961`;
- 41 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- smoke T096 habilitado somente no pacote de homologação;
- writers continuam desabilitados.

T096 deve comprovar, usando `serialize_blocks()`/`parse_blocks()` reais do Core em duas passagens, zero mismatch de payload, zero mismatch parse/serialize, determinismo dos hashes e zero mutação editorial.

## Fonte da verdade e fronteiras

- Editorial canônico futuro: `WP_Post.post_content` + WordPress Core Blocks.
- Plugin Gutenberg: **não é dependência de produção**.
- Elementor: source adapter legado durante transição.
- Summary: Post Metadata API.
- Classificação: Taxonomy API.
- Review/Governança: Comments API append-only.
- Knowledge Document: projeção semântica derivada; nunca fonte editorial lossless.
- Migration Fidelity Source: projeção efêmera lossless; não fonte persistida.
- UX/UI: UX-001 + UX-002, `scr/` e Visual Contract v2.
- Nenhum write editorial está autorizado neste estágio.

## Estratégia de produto

1. Core + Summary — concluído;
2. Classificação — concluído;
3. UX-001 — concluído;
4. Review & Governança — concluído;
5. UX-002 — concluído / contrato permanente;
6. Content Extractor + KD + Canonical Block Normalization — **concluído / SPEC-004 CLOSED**;
7. Search lexical + Golden Queries;
8. Telemetria/Inteligência de Busca;
9. Operações/Indexação;
10. Semantic Search/Vetores;
11. IA/Foundry/RAG.

## Próximos gates

A sequência T096–G-250 descrita historicamente acima foi superada pelo fechamento da SPEC-004 registrado adiante. A frente atual é SPEC-005:

1. T513 — Auto Validator v2: AUTO_PASS / AMBIGUOUS_QUARANTINED / AUTO_FAIL;
2. T514 — Technical Challenge + Diversity/Robustness; typo/alias reais ficam PENDING_TELEMETRY;
3. T515/T516 — congelar versão/hash e fechar R-510;
4. G-520 — contratos/storage/security/rollback antes de runtime;
5. G-585 — comprovar independência operacional do ASI antes do RC.

## Regra de liberação

`FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

**GO de desenvolvimento/homologação != GO de produção.**

A ADR-004-001 e os PASS read-only não autorizam writer. Qualquer write em `post_content` exige stale guard, journal, dry-run, lock, rollback, canário e autorização específica.


## Fechamento SPEC-004 — 2026-09-18

- T100E-E6: PASS ambiental;
- T100E-E7: PASS/CLOSED;
- G-245: PASS/CLOSED;
- G-250 Lifecycle/RC1: PASS ambiental/CLOSED;
- RC final limpo: `0.4.0-spec004-rc2`;
- SHA-256 RC2: `ac25c2ffd4a0ae2250fa2ce1a07bf07b4cad8a24030e31f12c78189e61e7506b`;
- 39/39 PHP lint;
- 38/38 active requires;
- deterministic rebuild PASS;
- G250/E6/Preflight/T100D/Elementor writer OFF.

Dívida residual: 39 artigos relacionados a Elementor (34 Elementor + 5 mixed). `Elementor_Adapter` permanece.

Direção de produto: autorização continua obrigatória como decisão humana explícita/post-scoped, mas o download manual do Authorization Pack não é a UX-alvo definitiva. A integração futura deve ocorrer dentro da Workspace, sob novo gate/SPEC, sem migração global implícita.


## Promoção SPEC-004 para main

PR #4: **MERGED** em 2026-09-18.

Merge commit: `e08871557b2233bf1294b1e57752265d3fe68c0f`.

Release validada: `0.4.0-spec004-rc2`.

SHA-256: `ac25c2ffd4a0ae2250fa2ce1a07bf07b4cad8a24030e31f12c78189e61e7506b`.

Estado definitivo: **SPEC-004 CLOSED / main**.


### SPEC-005 — Search Lexical e Golden Queries

**ATIVA / DISCOVERY.**

Branch: `spec005-search-lexical-golden-queries`.

Base: `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`.

Objetivo: estabelecer retrieval lexical determinístico e Golden Queries antes de semantic search, vetores ou IA.

Gate atual: **G-560 — UX/Humano**.

R-500 e R-510 estão PASS/CLOSED. G-520 foi fechado em 2026-09-19 com 25/25 checks, uma Search Retrieval Projection BDC própria, sem FULLTEXT v1, e contratos versionados de normalização/documento/ranking/resultado/Golden runner. G-530 está PASS/CLOSED; G-540 está OPEN para full-corpus/determinismo/idempotência em homologação. Produção continua bloqueada pelos gates posteriores. Ver `specs/005-search-lexical-golden-queries/current-state.md` e `CONTINUIDADE.md`.

Regras de abertura:
- runtime de engine bloqueado até R-500 + R-510 + G-520;
- WordPress-first: medir `WP_Query` antes de projection/schema;
- Content Extractor é a representação semântica comum para derivados;
- Golden Suite vazia = NOT_CONFIGURED;
- blocking Golden failure = NO-GO;
- nenhuma telemetria detalhada, queue, vetor ou IA nesta fase;
- nenhuma tabela criada apenas por herança do ASI.

Referência histórica: ASI 4.6.8 @ `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, preservando contratos e não o runtime legado.


#### ADR-005-002 — Golden/Challenge/Quarantine

Aceita em 2026-09-18:
- Golden Relevance Set mantém apenas origem humana/curada/histórica;
- ambiguidade objetiva vira `AMBIGUOUS_QUARANTINED`, preservada mas fora do blocking set;
- Technical Challenge Set pode ser corpus-derived/synthetic e prova capacidade, não intenção de usuário;
- typo/alias reais são `PENDING_TELEMETRY` até a camada futura de Telemetria;
- nenhum algoritmo pode trocar expected_post_id automaticamente.


#### Fechamento R-510 — 2026-09-18

- T513: PASS AUTOMATED WITH QUARANTINE;
- T514: PASS AMBIENTAL;
- T515: PASS / datasets congelados;
- T516: PASS/CLOSED;
- Golden version: `golden-relevance-v1.0.0`;
- Golden set_hash: `e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4`;
- Challenge version: `technical-challenge-v1.0.0`;
- Challenge set_hash: `2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807`;
- source evidence SHA-256: `b461ff671177128958f8729208d1dcdb6a9635f93307879869b0908b8f252d89`;
- real-world typo/alias: `PENDING_TELEMETRY`;
- próximo gate: G-520.


#### G-520 Search Contract v1 — PASS/CLOSED — 2026-09-19

- normalizer: `search-normalizer-v1.0.0`;
- document: `search-document-v1.0.0`;
- ranker: `lexical-ranker-v1.0.0`;
- result: `search-result-v1.0.0`;
- Golden runner: `golden-runner-v1.0.0`;
- storage: uma tabela `{$wpdb->prefix}bdc_kb_search_documents`;
- state: Option `bdc_kb_search_projection_state`;
- retrieval: bounded LIKE + ranker PHP;
- fallback: WP_Query native relevance;
- FULLTEXT v1: não autorizado;
- validation: 25/25 PASS;
- runtime changes durante G-520: zero;
- próximo gate: G-530.


#### G-530 Lexical Engine Local — PASS/CLOSED — 2026-09-19

- build: `0.5.0-g530.1`;
- Query Normalizer / Search Document / Projection Repository / Ranker / Search Service implementados;
- fallback WordPress degradado;
- 17/17 testes PASS;
- 7/7 PHP lint;
- blob parity local/GitHub 7/7;
- zero write editorial;
- zero network;
- zero ASI runtime/storage;
- zero FULLTEXT;
- dois defects prevenidos por regressão automatizada;
- próximo gate: G-540.


#### G-540 Corpus / Projection Rebuild — PASS/CLOSED — 2026-09-19

- evidence: `evidence/g540-environmental-20260919T115727Z.json`;
- upload SHA-256: `c2f1195c35917bc1863f262a7c0a930d622e93733aaccbfba3325becf1b789de`;
- corpus: 623;
- Projection rows: 623;
- pass1: 623 WRITTEN;
- pass2: 623 NO_CHANGE / 0 WRITTEN;
- determinism mismatch: 0;
- DB snapshot mismatch: 0;
- document_state ready: 623;
- editorial changed posts: 0;
- errors/throwables: 0;
- Projection status: ready;
- próximo gate: G-550.


#### G-550 Golden Gate — pacote local pronto — 2026-09-19

- build: `0.5.0-g550.1`;
- Golden runtime resource próprio;
- Technical Challenge runtime resource próprio;
- runner: `golden-runner-v1.0.0`;
- stale guard para normalizer/document/ranker/result;
- Projection ready obrigatória;
- fallback WordPress proibido para PASS;
- 6/6 harness PASS;
- 52/52 PHP lint;
- 45/45 active requires;
- deterministic build 2/2;
- SHA-256 `5c0fb99476aab84149341c1f069f64bfb9d8bdb57daaeca2636fe518164739b5`;
- ambiental NOT_RUN;
- G-550 permanece OPEN.


#### G-550 Golden Gate — PASS/CLOSED — 2026-09-19

- evidence: `evidence/g550-environmental-20260919T121422Z.json`;
- upload SHA-256: `e6f83104d330129cd0bd1835f4c1fd21f8d7117b461dbb646527f408d56cd07b`;
- Projection ready;
- Golden/Challenge hashes current;
- 13 executions;
- blocking_failed=0;
- warning_failed=0;
- technical_failed=0;
- technical_error_count=0;
- status PASS;
- privacy/ASI independence fields all PASS;
- próximo gate: G-560.

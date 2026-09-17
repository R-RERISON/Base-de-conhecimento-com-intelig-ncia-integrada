# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000/001/002/003 concluídas; UX-001/UX-002 concluídas; SPEC-004 ativa; G-240 PASS/CLOSED; G-245 rebaselined para Canonical Block Normalization  
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

**ATIVA.**

G-240 foi promovido para `main` com KD 2.1.0 e aceite técnico/humano. G-245 permanece somente na branch `spec004-g245-production-readiness` / PR #4 DRAFT.

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
6. Content Extractor + KD + Canonical Block Normalization — **em execução**;
7. Search lexical + Golden Queries;
8. Telemetria/Inteligência de Busca;
9. Operações/Indexação;
10. Semantic Search/Vetores;
11. IA/Foundry/RAG.

## Próximos gates

1. T096 full-corpus lossless round-trip em homologação;
2. T097 paridade renderizada/editorial em cohort controlado;
3. T098 generalização dos gates defensivos para Block Migration;
4. T099 canário de 1 artigo + rollback real, somente com Authorization Pack específico;
5. T100 batches homologados;
6. T101 dependência residual Elementor / gate de retirada futura;
7. G-250 Lifecycle/RC.

## Regra de liberação

`FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

**GO de desenvolvimento/homologação != GO de produção.**

A ADR-004-001 e os PASS read-only não autorizam writer. Qualquer write em `post_content` exige stale guard, journal, dry-run, lock, rollback, canário e autorização específica.

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

- `scr/` + Visual Contract v2 + Design System passam a ser autoridade visual operacional;
- WordPress permanece shell/plataforma, sem obrigar aparência genérica do wp-admin nas superfícies BDC;
- Knowledge List, Workspace, Summary, Classificação, Review, Histórico e vocabulários convergidos para a mesma identidade visual;
- navegação contextual e iconografia discreta homologadas;
- contrato visual incorporado a AGENTS, DoD e instruções globais;
- toda nova UI ou alteração material deve seguir `ux/002-mockup-visual-foundation/visual-contract-v2.md` e mockups aplicáveis.

### SPEC-003 — Review & Governança

**CONCLUÍDA.**

Baseline funcional congelada:

- package `0.3.0-rc.1`;
- SHA-256 `7f681a3f62d792d30ccb016ae64b03e83d5cc46c4b2b1e2c2d96d41e3dfd0db5`;
- R-001/R-010/G-001/G-030/DS-010/G-070/G-110/G-130 PASS;
- Review baseado em WordPress Comments API append-only;
- Histórico como projeção read-only do event log;
- `post_status` independente da governança.

### SPEC-004 — Content Extractor e Knowledge Document

**ATIVA.**

Baseline promovida para `main` em 2026-09-16:

- merge G-240: `32a696386bf2ab5574d4d7725db78636fa51f36c`;
- build de aceite: `0.4.0-acceptance.12`;
- Knowledge Document: schema `2.1.0`;
- R-200/R-210/G-220/G-230/G-240: PASS;
- G-240 full-corpus: duas passagens 622/622, zero errors/throwables/hash mismatches/canonical mismatches, zero `structure_incomplete`, zero `not_ready`, zero mutação editorial;
- aceite humano G-240: 8/8 coverage, order, no invented text, structure preserved e gate PASS.

**G-245 está em andamento apenas na branch `spec004-g245-production-readiness` / PR #4, ainda DRAFT e não promovida para `main`.**

Estado G-245:

- T080: PASS WITH REVIEW ITEMS;
- T081: Projection Plan Elementor PASS ambiental como diagnóstico histórico/read-only;
- T082–T086: gates defensivos concluídos local/contratualmente;
- T083B Durable Journal Storage: PASS ambiental;
- T087A/T087B-prep: readiness/lock concluídos sem writer;
- ADR-004-001 aceita: WordPress Core Blocks são o destino editorial canônico futuro;
- Elementor writer/T087C antigo: SUPERSEDED antes de implementação;
- T090 Block Projection v1.0: PASS LOCAL / READ-ONLY;
- T091 Block Projection full-corpus: **PASS AMBIENTAL** sobre 623 posts;
- T092 Block Projection v1.1: **PASS LOCAL / READ-ONLY**, 25/25 assertions + lint;
- T093 full-corpus v1.1 + diagnóstico KD: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.

T091 comprovou duas passagens 623/623, zero errors/throwables/hash mismatches/safety violations, fingerprint editorial idêntico e `gate_result.t091_block_projection_pass=true`.

Gaps observados no T091:

- `KNOWLEDGE_DOCUMENT_REVIEW_REQUIRED`: 233;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:image`: 40;
- `BLOCK_PROJECTION_TABLE_SPAN_REVIEW`: 32;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:quote`: 8.

T092 resolveu apenas o gap comprovadamente seguro: `quote → core/quote`. Imagens continuam em review até existir proveniência de mídia suficiente; table spans continuam review.

## Fonte da verdade e fronteiras

- Editorial canônico futuro: `WP_Post.post_content` + WordPress Core Blocks.
- Plugin Gutenberg: **não é dependência de produção**; usar somente APIs estáveis do Core homologado.
- Elementor: source adapter legado durante transição; `_elementor_data` preservado até dependência zero e gate explícito de retirada.
- Summary: Post Metadata API do WordPress.
- Classificação: WordPress Taxonomy API.
- Review/Governança: Comments API append-only conforme SPEC-003.
- Knowledge Document: projeção derivada, determinística e reconstruível; nunca fonte editorial.
- UX/UI: UX-001 + UX-002, com `scr/` e `visual-contract-v2.md` como contrato vigente.
- WordPress Admin: shell e primitives; a aparência interna do produto pertence ao Design System BDC.
- O plugin não reescreve silenciosamente `post_content`.
- Migração administrativa para Blocks só pode ocorrer sob SPEC/gates/autorização/rollback explícitos.
- Nenhum novo writer deve usar `_elementor_data` como destino.
- Projeções/cache/índices nunca são fonte editorial.
- IA permanece assistiva e fora da autoridade editorial, orientada a reduzir esforço de leitura e tempo até resposta confiável.

## Estratégia de produto

1. Core + Summary — concluído;
2. Classificação de Conhecimento — concluído;
3. UX-001 Product Experience & Knowledge Workspace — concluído;
4. Review & Governança — concluído;
5. UX-002 Mockup Visual Foundation — concluído e contrato permanente;
6. Content Extractor + Knowledge Document + Canonical Block Normalization — **em execução; G-240 fechado, G-245 rebaselined**;
7. Search lexical + Golden Queries;
8. Telemetria/Inteligência de Busca;
9. Operações/Indexação;
10. Semantic Search/Vetores;
11. IA/Foundry/RAG.

## Regra de liberação

Compilar, passar unitário ou ter protótipo aprovado isoladamente não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

Nenhum mockup/protótipo transforma hipótese em contrato de domínio. Toda UI implementada deve respeitar o Visual Contract vigente; divergência exige decisão explícita.

**GO de desenvolvimento/homologação != GO de produção.**

**ADR-004-001 não autoriza writer. O novo destino Blocks deve repetir os gates de projection/dry-run/stale/journal/lock/canário antes de qualquer mutação editorial.**

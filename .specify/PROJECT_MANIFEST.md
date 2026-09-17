# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000/001/002/003 concluídas; SPEC-004 ativa; G-240 PASS/CLOSED; G-245 em andamento em branch dedicada  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento sem substituir WordPress/Elementor como fonte editorial, evoluindo por vertical slices, com experiência coerente e dados derivados reconstruíveis.

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

Production Preflight T080 já produziu baseline read-only com zero blockers e itens `review_required`, mantendo writer/migration desabilitados. Isso não autoriza persistência editorial.

## Fonte da verdade e fronteiras

- Editorial: `WP_Post` + Elementor.
- Summary: Post Metadata API do WordPress.
- Classificação: WordPress Taxonomy API.
- Review/Governança: Comments API append-only conforme SPEC-003.
- Knowledge Document: projeção derivada, determinística e reconstruível; nunca fonte editorial.
- UX/UI: baseline UX-001 + UI as Code versionado.
- O plugin não escreve `_elementor_data` como parte do knowledge plane.
- O plugin não reescreve silenciosamente `post_content`.
- Projeções/cache/índices nunca são fonte editorial.
- IA permanece assistiva e fora da autoridade editorial.

## Estratégia de produto

1. Core + Summary — concluído;
2. Classificação de Conhecimento — concluído;
3. UX-001 Product Experience & Knowledge Workspace — concluído;
4. Review & Governança — concluído;
5. Content Extractor + Knowledge Document — **em execução; G-240 fechado, G-245 em andamento**;
6. Search lexical + Golden Queries;
7. Telemetria/Inteligência de Busca;
8. Operações/Indexação;
9. Semantic Search/Vetores;
10. IA/Foundry/RAG.

## Regra de liberação

Compilar, passar unitário ou ter protótipo aprovado isoladamente não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

Nenhum mockup/protótipo transforma hipótese em contrato de domínio. Nenhuma implementação de nova feature pode ignorar o baseline UX congelado sem decisão explícita de mudança.

**GO de desenvolvimento/homologação != GO de produção.**

**G-240 em `main` não autoriza writer/migration Elementor. G-245 deve fechar seus subgates, rollback e autorização explícita antes de qualquer mutação editorial.**
# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000/001/002/003 concluídas; SPEC-004 ativa (G-245 em branch dedicada); UX-001 concluída; UX-002 ativa em branch dedicada  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento sem substituir WordPress/Elementor como fonte editorial, evoluindo por vertical slices, com experiência coerente, dados derivados reconstruíveis e IA orientada a reduzir o tempo para uma resposta confiável.

## Estado consolidado

### SPEC-000 — Inventário Profundo e Contratos
**CONCLUÍDA.**

### SPEC-001 — Core mínimo + Summary narrativo
**CONCLUÍDA para desenvolvimento/homologação.** Baseline `0.1.0-rc.1`.

### SPEC-002 — Classificação de Conhecimento
**CONCLUÍDA para desenvolvimento/homologação.** Baseline `0.2.0-rc.1`; quatro taxonomias canônicas WordPress; legado read-only/advisory.

### UX-001 — Product Experience & Knowledge Workspace
**CONCLUÍDA como baseline de produto.**

Resultado: arquitetura de informação, Design System v1, Knowledge List + Knowledge Workspace, responsive/accessibility e UI-as-Code v0.2.

### UX-002 — Mockup Visual Foundation
**ATIVA em `ux002-mockup-visual-foundation`.**

Objetivo: eliminar drift entre o runtime e os mockups/Design System sem criar framework administrativo paralelo. `scr/` + Design System UX-001 + protótipo UI-as-Code formam o contrato visual; WordPress permanece shell/plataforma.

Build de homologação prevista: `0.4.0-ux002.1`.

### SPEC-003 — Review & Governança
**CONCLUÍDA.** Baseline `0.3.0-rc.1`; Comments API append-only; histórico read-only.

### SPEC-004 — Content Extractor e Knowledge Document
**ATIVA.**

- G-240: PASS/CLOSED e promovido para `main`;
- KD 2.1.0: PASS técnico + aceite humano 8/8;
- G-245: em `spec004-g245-production-readiness` / PR #4 DRAFT;
- T080: PASS WITH REVIEW ITEMS;
- T081: PASS ambiental em `0.4.0-g245-projection.2`;
- T082: próximo subgate;
- writer/migration Elementor continuam não autorizados.

## Fonte da verdade e fronteiras

- Editorial: `WP_Post` + Elementor.
- Summary: Post Metadata API.
- Classificação: Taxonomy API.
- Review/Governança: Comments API append-only.
- Knowledge Document/Projection Plan: derivados determinísticos e reconstruíveis.
- UX/UI: `scr/` + contrato UX vigente + UI-as-Code versionado.
- WordPress Admin: shell e primitives; aparência interna do produto pertence ao Design System BDC.
- IA: assistiva, governada e orientada a reduzir esforço de escrita e principalmente esforço de leitura/tempo para resposta confiável.

## Estratégia de produto

1. Core + Summary — concluído;
2. Classificação — concluído;
3. UX-001 — concluído;
4. Review & Governança — concluído;
5. Content Extractor + Knowledge Document — em execução via G-245;
6. UX-002 — convergência visual progressiva paralela, sem mudança de contratos de dados;
7. Search lexical + Golden Queries;
8. Telemetria/Inteligência de Busca;
9. Operações/Indexação;
10. Semantic Search/Vetores;
11. IA/Foundry/RAG.

## Regra de liberação

Compilar, passar unitário ou ter protótipo aprovado isoladamente não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

Nenhum mockup altera contrato de domínio, segurança ou persistência. Nenhuma tela do produto pode ignorar o contrato visual vigente sem decisão explícita de mudança.

**GO de desenvolvimento/homologação != GO de produção.**

**G-240/T081 não autorizam writer/migration Elementor. UX-002 não altera essa regra.**

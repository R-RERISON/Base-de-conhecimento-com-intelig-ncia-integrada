# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000 concluída / SPEC-001 concluída / SPEC-002 concluída / UX-001 concluída / SPEC-003 planejamento ativo  
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

Baseline funcional atual:

- package `0.2.0-rc.1`;
- SHA-256 `a5120299ea907d271bc39b318857ba033cf0cf340fe1836289f42a4c717b8fb5`;
- C-001/C-010/G-001/G-030/B-006/G-070/G-110/G-130 PASS;
- regressão essencial da SPEC-001 PASS;
- quatro taxonomias canônicas WordPress;
- legado apenas read-only/advisory;
- sem migração automática ou dual-write.

### UX-001 — Product Experience & Knowledge Workspace

**CONCLUÍDA para baseline de produto.**

Resultado:

- arquitetura de informação fechada;
- Design System v1;
- Knowledge List + Knowledge Workspace;
- Summary/Classificação integrados ao Workspace;
- Review/Governança acomodado apenas como conceito futuro;
- responsive/accessibility validados no protótipo;
- Heritage Pack KB2Ops;
- UI as Code v0.2 como artefato visual canônico;
- Figma não é dependência operacional.

Gates UX-001/UX-005/UX-010/UX-030/UX-050: PASS.

### SPEC-003 — Review & Governança

**PLANEJAMENTO ATIVO / RUNTIME BLOQUEADO ATÉ R-001 + R-010.**

Caminho canônico:

`specs/003-review-governanca/`

Primeiro objetivo: inventariar stores, writers, consumers, capabilities e semântica histórica antes de definir estado canônico, reviewer, histórico ou qualquer score.

## Fonte da verdade e fronteiras

- Editorial: `WP_Post` + Elementor.
- Summary: Post Metadata API do WordPress.
- Classificação: WordPress Taxonomy API, quatro conceitos canônicos do slice atual.
- UX/UI: baseline UX-001 + protótipo UI as Code versionado.
- Review/Governança: sem owner canônico até R-010 da SPEC-003.
- O plugin não escreve `_elementor_data`.
- O plugin não reescreve silenciosamente `post_content`.
- Projeções/cache/índices nunca são fonte editorial.
- IA é assistiva e permanece fora das SPECs 001/002/003 enquanto não houver SPEC própria.

## Estratégia de produto

1. Core + Summary — concluído;
2. Classificação de Conhecimento — concluído;
3. UX-001 Product Experience & Knowledge Workspace — concluído;
4. **Review & Governança — planejamento ativo**;
5. Content Extractor;
6. Search lexical + qualidade/Golden Queries;
7. Telemetria/Inteligência de Busca;
8. Operações/Indexação;
9. Semantic Search/Vetores;
10. IA/Foundry/RAG.

## Regra de liberação

Compilar, passar unitário ou ter protótipo aprovado isoladamente não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

Nenhum mockup/protótipo transforma hipótese em contrato de domínio. Nenhuma implementação de nova feature pode ignorar o baseline UX congelado sem decisão explícita de mudança.

**GO de desenvolvimento/homologação != GO de produção.**

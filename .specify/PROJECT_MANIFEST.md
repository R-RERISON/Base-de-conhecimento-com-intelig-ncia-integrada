# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000 concluída / SPEC-001 concluída / SPEC-002 concluída / UX-001 ativa  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento sem substituir WordPress/Elementor como fonte editorial, evoluindo por vertical slices, com experiência de uso coerente e dados derivados reconstruíveis.

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

**ATIVA.**

Objetivo: estabelecer a baseline transversal de experiência antes da implementação da SPEC-003.

Entregáveis mínimos:

- princípios UX;
- arquitetura de informação;
- mapa de navegação;
- inventário de telas;
- Design System v1;
- Knowledge Workspace master mockup;
- Review & Governance mockup;
- estados responsivos/acessibilidade;
- mapa de herança visual KB2Ops: preservar / evoluir / descartar;
- arquivo Figma editável como referência visual canônica.

UX-001 não cria novas regras de negócio e não autoriza features futuras por antecipação. Cada SPEC funcional continua sendo autoridade do domínio que implementa.

## Fonte da verdade e fronteiras

- Editorial: `WP_Post` + Elementor.
- Summary: Post Metadata API do WordPress.
- Classificação: WordPress Taxonomy API, quatro conceitos canônicos do slice atual.
- UX/UI: baseline transversal definida por UX-001 e refinada por SPEC sem quebrar o Design System canônico.
- O plugin não escreve `_elementor_data`.
- O plugin não reescreve silenciosamente `post_content`.
- Projeções/cache/índices nunca são fonte editorial.
- IA é assistiva e continua fora das SPECs 001/002.

## Estratégia de produto

Ordem canônica de evolução:

1. Core + Summary — concluído;
2. Classificação de Conhecimento — concluído;
3. **UX-001 — Product Experience & Knowledge Workspace — ativo**;
4. Review & Governança — próximo após baseline UX;
5. Content Extractor;
6. Search lexical + qualidade/Golden Queries;
7. Telemetria/Inteligência de Busca;
8. Operações/Indexação;
9. Semantic Search/Vetores;
10. IA/Foundry/RAG.

A UX-001 é transversal e não substitui a numeração das SPECs funcionais.

## Regra de liberação

Compilar, passar unitário ou ter mockup aprovado isoladamente não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

Nenhum mockup pode transformar hipótese visual em contrato de domínio. Nenhuma implementação de nova feature pode ignorar o baseline UX congelado sem decisão explícita de mudança.

**GO de desenvolvimento/homologação != GO de produção.**

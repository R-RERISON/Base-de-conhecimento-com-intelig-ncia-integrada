# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000 concluída / SPEC-001 concluída para desenvolvimento-homologação / SPEC-002 aberta em profiling read-only  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento sem substituir WordPress/Elementor como fonte editorial, evoluindo por vertical slices e mantendo dados derivados reconstruíveis.

## Estado consolidado

### SPEC-000 — Inventário Profundo e Contratos

**CONCLUÍDA.**

### SPEC-001 — Core mínimo + Summary narrativo

**CONCLUÍDA para desenvolvimento/homologação.**

Baseline funcional congelada:

- package `0.1.0-rc.1`;
- SHA-256 `c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`;
- G-001 PASS;
- G-020 PASS;
- G-070 PASS;
- G-110 PASS;
- B-006 PASS;
- G-130 PASS.

A conclusão da SPEC-001 não é GO de produção/cutover. B-003/preflight retorna antes de qualquer coexistência produtiva de writers.

### SPEC-002 — Classificação de Conhecimento

**ABERTA EM PLANEJAMENTO / S001 PROFILING.**

Primeira regra: nenhum runtime permanente de classificação antes de profiling real e decisão Taxonomy vs Post Meta por conceito.

## Fonte da verdade e fronteiras

- Editorial: `WP_Post` + Elementor.
- Summary: Post Metadata API do WordPress.
- Classificação: owner lógico próprio; primitive física ainda em decisão na SPEC-002.
- O plugin não escreve `_elementor_data`.
- O plugin não reescreve silenciosamente `post_content`.
- Projeções/cache/índices nunca são fonte editorial.
- IA é assistiva e não participa das SPECs 001/002 nesta fase.

## Baseline de regressão

Toda evolução da SPEC-002 deve preservar o comportamento homologado do `0.1.0-rc.1`.

## Estratégia de produto

Ordem canônica de evolução:

1. Core + Summary — concluído;
2. Classificação de Conhecimento — ativo;
3. Review & Governança — futuro;
4. Content Extractor — futuro;
5. Search lexical + qualidade/Golden Queries — futuro;
6. Telemetria/Inteligência de Busca — futuro;
7. Operações/Indexação — futuro;
8. Semantic Search/Vetores — futuro;
9. IA/Foundry/RAG — futuro.

A numeração histórica de placeholders não tem precedência sobre as decisões canônicas mais recentes.

## Regra de liberação

Compilar ou passar unitário não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

**GO de desenvolvimento/homologação != GO de produção.**

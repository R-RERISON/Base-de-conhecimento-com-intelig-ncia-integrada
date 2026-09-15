# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000 concluída / SPEC-001 concluída / SPEC-002 em fechamento de lifecycle RC  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento sem substituir WordPress/Elementor como fonte editorial, evoluindo por vertical slices e mantendo dados derivados reconstruíveis.

## Estado consolidado

### SPEC-000 — Inventário Profundo e Contratos

**CONCLUÍDA.**

### SPEC-001 — Core mínimo + Summary narrativo

**CONCLUÍDA para desenvolvimento/homologação.** Baseline `0.1.0-rc.1`, com G-001/G-020/G-070/G-110/B-006/G-130 PASS.

### SPEC-002 — Classificação de Conhecimento

**EM FECHAMENTO.**

Primeiro slice implementado com WordPress Taxonomy API:

- audiência — multi;
- equipe responsável — multi;
- tipo de conhecimento — single;
- item de catálogo — multi.

Gates atuais:

- C-001 PASS;
- C-010 PASS;
- G-001 PASS;
- G-030 PASS;
- B-006 PASS;
- G-070 PASS;
- G-110 PASS;
- regressão SPEC-001 PASS;
- G-130 pendente apenas do lifecycle real do package limpo `0.2.0-rc.1`.

O legado permanece read-only/advisory; não existe migração automática, auto-map ou dual-write.

## Fonte da verdade e fronteiras

- Editorial: `WP_Post` + Elementor.
- Summary: Post Metadata API do WordPress.
- Classificação: WordPress Taxonomy API, owner lógico do plugin para os quatro conceitos autorizados.
- O plugin não escreve `_elementor_data`.
- O plugin não reescreve silenciosamente `post_content`.
- Projeções/cache/índices nunca são fonte editorial.
- IA é assistiva e não participa das SPECs 001/002.

## Baseline de regressão

Até G-130 da SPEC-002, `0.1.0-rc.1` segue como baseline concluída e `0.2.0-rc.1` é candidata à nova baseline.

## Estratégia de produto

1. Core + Summary — concluído;
2. Classificação de Conhecimento — fechamento;
3. Review & Governança — próximo candidato;
4. Content Extractor;
5. Search lexical + Golden Queries;
6. Telemetria/Inteligência de Busca;
7. Operações/Indexação;
8. Semantic Search/Vetores;
9. IA/Foundry/RAG.

## Regra de liberação

Compilar ou passar unitário não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

**GO de desenvolvimento/homologação != GO de produção.**

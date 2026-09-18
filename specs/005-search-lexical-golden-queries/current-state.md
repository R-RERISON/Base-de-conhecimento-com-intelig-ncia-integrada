# Current State — SPEC-005

## Estado

**ATIVA — DISCOVERY / DoR.**

Branch: `spec005-search-lexical-golden-queries`.

Base: `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`.

## O que existe

- SPEC-004 CLOSED/main;
- Content Extractor + KD 2.1.0;
- Workspace e Knowledge List;
- pesquisa simples na Knowledge List por `WP_Query s`;
- post type `post`;
- Visual Contract v2;
- agente/skill especializados de Search/Golden.

## O que NÃO existe

- Search engine próprio;
- Search Retrieval Projection no novo plugin;
- FULLTEXT próprio;
- Golden runtime;
- query logging;
- semantic/vector;
- IA no retrieval;
- public search surface do novo plugin.

## Baseline ASI

ASI 4.6.8 serve como referência comportamental. Nenhum código ou schema é copiado automaticamente.

## Gate atual

**R-500 — Search Baseline / Definition of Ready.**

Trabalho autorizado agora:
- diagnóstico read-only;
- benchmark;
- inventário;
- Golden dataset;
- contratos.

Runtime de engine permanece bloqueado até R-500 + R-510 + G-520.

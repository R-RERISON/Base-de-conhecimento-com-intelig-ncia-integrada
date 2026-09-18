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


## T502 — Search Baseline Diagnostic

Status: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**.

Build: `0.5.0-r500-t502.1`.  
SHA-256: `0b92d355be79c23e6837fa4b11cd6982674b643795f3e8de4f4949b9c47f5b86`.

Validação local:
- 40/40 PHP lint pré/pós ZIP;
- 39/39 active requires;
- deterministic rebuild PASS;
- forbidden write/network calls: 0;
- source/package Git blob parity PASS.

O diagnóstico compara:
1. Knowledge List atual: `WP_Query s + modified DESC`;
2. WordPress native search sem essa ordenação explícita;
3. Content Extractor semantic coverage;
4. Summary e taxonomias como sinais não nativamente pesquisáveis;
5. p50/p95 de probes;
6. fingerprint editorial before/after.

Guardrail adicional: `asi-quality-parity-contract-v1.md` formaliza que simplificação arquitetural não pode regredir a qualidade funcional comprovada do ASI.

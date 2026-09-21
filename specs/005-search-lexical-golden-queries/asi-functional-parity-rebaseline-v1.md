# Rebaseline — Paridade Funcional ASI → BDC v1

**Status:** FROZEN PARA INVENTÁRIO / IMPLEMENTAÇÃO BLOQUEADA POR CAPACIDADE  
**Data:** 2026-09-20  
**Baseline ASI:** Advanced Search Intelligence 4.6.8 @ `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`  
**BDC:** SPEC-005 / branch `spec005-search-lexical-golden-queries`

## 1. Correção de princípio

**Zero runtime dependency on ASI não significa zero funcionalidades do ASI.**

O ASI é tratado como baseline funcional e memória institucional. Nenhuma capacidade relevante em uso pode ser considerada substituída apenas porque o novo BDC não depende mais do plugin ASI.

Uma capacidade ASI só pode ser retirada quando ocorrer uma destas condições:

1. **PARITY** — comportamento equivalente comprovado no BDC;
2. **IMPROVED** — comportamento reconstruído no BDC com melhoria comprovada;
3. **SUPERSEDED BY WORDPRESS/BDC** — mesma necessidade atendida por primitive canônica existente, com evidência;
4. **EXPLICITLY RETIRED** — Product Owner decide explicitamente que a capacidade não deve continuar.

Ausência de implementação, simplificação arquitetural ou preferência técnica **não** são justificativa para perda funcional.

## 2. Descoberta que motivou a rebaseline

A desativação do ASI em homologação quebrou imediatamente a Home pública porque a página editorial ativa contém:

- `[asi_search_form]`;
- `[bdc_word_cloud]`.

O HTML/JS da Home também possui normalização específica do DOM produzido pelo ASI.

Logo, o G-585 anterior comprovava independência de source/runtime PHP, mas não era suficiente para comprovar **Decommission Readiness de produto**.

## 3. Home pública é superfície canônica do BDC

O BDC deve possuir sua própria página pública/portal de entrada.

Requisitos mínimos preservados da Home existente:

- Header BDC;
- identidade/marca;
- perfil/SSO quando disponível;
- links rápidos;
- Search pública;
- Word Cloud funcional;
- filtros/categorias;
- Últimas Atualizações;
- Instruções Populares;
- loading/error/empty states;
- responsividade e acessibilidade.

A implementação atual é referência funcional/visual, não arquitetura obrigatória.

O objetivo de evolução é consolidar Header + Body + scripts + assets em componentes mantidos pelo plugin BDC, reduzindo HTML isolado/editorial e dependências implícitas.

## 4. Word Cloud é funcionalidade obrigatória

Word Cloud NÃO está aposentada.

A versão BDC deve preservar ou melhorar:

- geração de termos;
- qualidade/normalização;
- click-to-search;
- integração com Search pública;
- atualização/scheduling;
- estados de saúde;
- configuração administrativa quando necessária;
- uso de dados BDC próprios;
- zero storage/runtime ASI após cutover.

Redesign visual é permitido; remoção funcional não é.

## 5. Matriz de capacidades ASI — baseline inicial

| Domínio | Capacidade ASI 4.6.8 | Estado BDC atual | Regra |
|---|---|---|---|
| Public UX | Search pública / shortcode / AJAX | AUSENTE como substituto público | MUST REBUILD |
| Public UX | Search-as-you-type + Enter/submit + cancel stale | AUSENTE público | MUST REBUILD/IMPROVE |
| Public UX | Estados idle/loading/zero/error/rate-limit | PARCIAL no admin Search | MUST PORT to public |
| Public UX | Progressive disclosure mostrar mais/menos | AUSENTE público | EVALUATE/PRESERVE |
| Public UX | Página/full-page search | NÃO COMPROVADO | INVENTORY |
| Home | Portal/landing BDC | HTML editorial fragmentado | MUST REBUILD |
| Home | Categorias/filtros | EXISTE legado Home | MUST PRESERVE/IMPROVE |
| Home | Últimas Atualizações | EXISTE legado Home | MUST PRESERVE |
| Home | Instruções Populares | EXISTE legado Home | MUST PRESERVE |
| Word Cloud | Shortcode/public rendering | ASI dependency | MUST REBUILD |
| Word Cloud | Generator/quality | ASI dependency | MUST REBUILD/IMPROVE |
| Word Cloud | Click intent → Search | ASI dependency | MUST REBUILD |
| Word Cloud | Cron/health/admin | ASI dependency | MUST INVENTORY/PRESERVE |
| Retrieval | Post index/ranker | PARITY parcial comprovada SPEC-005 | CONTINUE |
| Retrieval | Item/section knowledge index | NÃO implementado no BDC Search v1 | MUST INVENTORY/FUTURE GATE |
| Retrieval | Item ranker / related sections | NÃO substituído | MUST PRESERVE OR IMPROVE |
| Retrieval | Deep-link/anchor contract | NÃO substituído | MUST INVENTORY |
| Query | QueryContext / bounded NL understanding | PARCIAL | COMPARE |
| Query | Vocabulary/aliases | NÃO implementado no v1 | MUST REBUILD when evidence/gate permits |
| Query | Term bindings | NÃO implementado | INVENTORY |
| Ranking | Explicit relevance rules | NÃO implementado | INVENTORY |
| Analytics | Search Events | AUSENTE por design SPEC-005 | MUST REBUILD in telemetry phase |
| Analytics | Interactions / click correlation | AUSENTE | MUST REBUILD |
| Analytics | Outcomes | AUSENTE | MUST REBUILD |
| Analytics | Quality Signals | AUSENTE | MUST REBUILD |
| Intelligence | Frequent/emerging terms | AUSENTE | MUST REBUILD |
| Intelligence | Zero-result gaps | AUSENTE | MUST REBUILD |
| Intelligence | Query/ranking/cache/p95 analytics | PARCIAL técnico only | MUST REBUILD product analytics |
| Curation | Knowledge diagnostics | PARCIAL em outros domínios | INVENTORY/PARITY |
| Curation | Evidence-based suggestions | AUSENTE | MUST REBUILD/IMPROVE |
| Curation | Ranking simulation before Apply | AUSENTE | MUST REBUILD before mutable relevance controls |
| Regression | Golden Queries | PARITY/IMPROVED SPEC-005 | PRESERVE |
| Operations | Durable indexing queue | AUSENTE na Search v1 | MUST REBUILD when operational indexing enters scope |
| Operations | Safe rebuild | PARITY mínima SPEC-005 | PRESERVE/EVOLVE |
| Operations | Migrations/resume | PARCIAL project lifecycle | COMPARE |
| Operations | Post-install assistant | AUSENTE equivalente | EVALUATE/REBUILD |
| Quality | Structural audit | PARCIAL via Content Extractor/KD | MAP/PARITY |
| Quality | Quality/diagnostic exports | PARCIAL gates/evidence | MUST CONSOLIDATE |
| Objective | Executive Summary Objective | SUPERSEDED pelo BDC Summary domain | PROVE PARITY |
| Privacy | privacy-preserving search telemetry modes | FUTURE | MUST PRESERVE WHEN TELEMETRY RETURNS |

## 6. Sequência revisada

Antes de G-585 decommission:

1. **P-580A — ASI Functional Inventory**
   - inventário completo de runtime, public UX, admin UX, storage, jobs, reports, shortcodes, hooks e fluxos;
   - classificar PARITY / IMPROVED / MISSING / RETIRED-BY-PO.

2. **UX-004 — BDC Public Home**
   - Home pública canônica mantida pelo plugin BDC;
   - Header/body/componentização;
   - Search pública BDC;
   - Word Cloud BDC;
   - categorias, últimas e populares;
   - Visual Contract v2 + referência da Home existente.

3. Evoluções funcionais restantes são distribuídas nas SPECs já planejadas quando arquiteturalmente corretas:
   - telemetry/search intelligence;
   - indexing/operations;
   - semantic/vector;
   - AI/RAG.

4. **G-585 — Decommission Readiness**
   só pode fechar quando:
   - zero runtime/storage dependency on ASI;
   - zero shortcode/template/page dependency on ASI;
   - Home funciona com ASI desativado;
   - matriz funcional não possui MISSING bloqueante;
   - toda capacidade não migrada está explicitamente planejada ou aposentada pelo Product Owner;
   - Search/Golden/rebuild/lifecycle passam sem ASI.

## 7. Regra anti-regressão ampliada

Uma função existente não será descartada por ser complexa.

Aplicar:
`entender → medir → redesenhar → implementar → provar paridade/melhoria → somente então retirar legado`.

Complexidade histórica deve ser reduzida quando possível, mas redução de código não pode significar redução de capacidade.

## 8. Decisão atual

- G-580 continua CLOSED;
- G-585 fica **PAUSED / BLOCKED BY FUNCTIONAL PARITY REBASELINE**;
- g585.2 permanece artefato diagnóstico válido, mas não é a próxima ação de homologação;
- G-590 permanece bloqueado;
- próxima ação técnica: P-580A + UX-004 discovery/contract antes de novo ZIP de decommission.

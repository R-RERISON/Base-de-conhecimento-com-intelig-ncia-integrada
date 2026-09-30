# SPEC-006 → SPEC-007 — Handoff Plan

**Status:** PREPARED / SPEC-007 NOT ACTIVE  
**Data:** 2026-09-30

## Objetivo

Preparar a transição para a SPEC-007 — Public Knowledge Experience sem iniciar runtime novo antes do closeout formal da SPEC-006.

## Pré-condição de ativação

SPEC-007 só pode passar de NEXT para ACTIVE quando:

- P640 fechado;
- P650 fechado;
- P660 fechado;
- P670 com closeout aprovado;
- Master Functional Parity Ledger atualizado conforme evidência;
- `CONTINUIDADE.md` final da SPEC-006 produzido;
- branch/base da SPEC-007 definida após fechamento da SPEC-006.

## Baseline herdada

A SPEC-007 deve partir do artefato/source homologado da foundation:

- product version: `0.6.0-dev`;
- package baseline: `p650.3`;
- SHA-256: `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`;
- Search lexical/G-590 preservados;
- Public Experience Preview preservada;
- Word Cloud preservada como capability atual, mas intelligence/governance permanece SPEC-008;
- nenhuma dependência runtime ASI pode ser reintroduzida.

## Escopo esperado SPEC-007

Do Premium Rebaseline:

- Home pública BDC;
- Article Reader;
- Header/Auth;
- Summary Rail / Helpful Tips;
- public live search / ASI-018;
- independência de Astra Additional CSS;
- independência de Home Code Snippet;
- integração ambiental Entra quando aplicável;
- Visual Contract premium;
- acessibilidade/responsividade reais.

## Fora de escopo

Continuam fora da SPEC-007:

- vocabulary/aliases/bindings/relevance rules — SPEC-008;
- Search Events/Interactions/Outcomes — SPEC-008;
- Word Cloud intelligence/governance convergida — SPEC-008;
- durable queue/indexing/reconciliation — SPEC-009;
- semantic/vector — SPEC-010;
- AI/Foundry — SPEC-011+;
- retirement/cutover global — SPEC-014.

## Master Ledger relevante

SPEC-007 deverá trabalhar principalmente sobre:

- GRE-006 — rail público;
- GRE-007 — shortcode/fallback;
- KB2-008 — Search/Portal aliases quando houver consumer público;
- ASI-018 — public live search;
- ENV-001 — Home Code Snippet;
- ENV-002 — Astra Additional CSS;
- ENV-003 — Entra integration;
- ENV-004 — GAC/WP Unified Indexer somente se a experiência pública depender dele.

Nenhum estado deve ser promovido antes de consumer/runtime/environmental evidence.

## Primeira atividade após ativação

A primeira atividade da SPEC-007 deve ser discovery/contract, não implementação:

1. inventariar a Home pública atual e todos os consumers;
2. mapear ownership de Header/Auth/Home/Reader;
3. registrar dependências de Astra/Code Snippets/Entra;
4. comparar a Public Experience Preview atual com a produção real;
5. congelar Visual Contract/acceptance matrix;
6. definir cutover-safe preview/route contract;
7. somente então abrir o primeiro vertical slice runtime.

## Invariantes herdados

- WordPress-first;
- Search lexical funciona sem IA/vetor;
- nenhuma regressão de Search/Golden;
- nenhuma escrita editorial implícita;
- nenhuma escrita em `_elementor_data`;
- nenhuma retirada de legado por inferência;
- nenhum cutover antes do Ledger;
- versão 1.0.0 continua reservada à SPEC-014.

## Não fazer ainda

Enquanto SPEC-006 estiver aberta:

- não criar branch runtime SPEC-007;
- não alterar templates públicos em produção;
- não mudar Home oficial;
- não remover Astra CSS/Code Snippet;
- não alterar integração Entra;
- não promover ASI-018/ENV-* no Ledger.

Este documento é somente handoff preparado.

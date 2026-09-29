# ASI Capability Closure Map — pós G-595

**Data:** 2026-09-29  
**Baseline legado:** Advanced Search Intelligence 4.6.8  
**Estado:** SPEC-005 boundary PASS; ASI global parity NÃO concluída.

## Regra

Fechar a SPEC-005 significa fechar a ownership de Search lexical/retrieval.  
Não significa que o produto BDC já substitui toda a superfície funcional do ASI.

O Master Functional Parity Ledger contém 22 capabilities ASI.

### Cobertura atual

**7/22 capabilities ASI estão verificadas na fronteira já concluída:**
- ASI-001 post lexical retrieval — IMPROVED_VERIFIED;
- ASI-002 query normalization — IMPROVED_VERIFIED;
- ASI-003 item/section retrieval — PARITY_VERIFIED;
- ASI-004 stable item identity — PARITY_VERIFIED;
- ASI-005 anchors/deep links — PARITY_VERIFIED;
- ASI-006 Golden Queries — IMPROVED_VERIFIED;
- ASI-019 rebuild/lifecycle — PARITY_VERIFIED.

**15/22 ainda não estão em estado de retirement:**
- 12 GAP;
- 3 PARTIAL.

## Capabilities remanescentes

### SPEC-007 — Public Knowledge Experience

| ID | Capability | Estado atual | Necessidade |
|---|---|---|---|
| ASI-018 | public live search | PARTIAL | completar UX pública Search-as-you-type, estados, integração com Home e jornada pública |

O cutover público também depende de ENV-001..004 e capacidades GRE/KB2Ops relacionadas à experiência pública.

### SPEC-008 — Search Intelligence, Telemetry, Privacy & Governed Relevance

| ID | Capability | Estado atual | Necessidade |
|---|---|---|---|
| ASI-007 | vocabulary/aliases | GAP | reconstruir governado |
| ASI-008 | term bindings | GAP | reconstruir |
| ASI-009 | relevance rules | GAP | reconstruir |
| ASI-010 | diagnostics/suggestions | GAP | reconstruir |
| ASI-011 | ranking simulation/apply | GAP | reconstruir com governança |
| ASI-012 | Search Events | GAP | telemetria |
| ASI-013 | Interactions | GAP | correlação click/journey |
| ASI-014 | Outcomes | GAP | medir resolução |
| ASI-015 | Search Intelligence | GAP | frequent/emerging/zero-result/unresolved |
| ASI-016 | privacy/retention modes | GAP | contrato privacy/retention |
| ASI-017 | Word Cloud | PARTIAL | convergir para sinais canônicos BDC |

Esta SPEC é a maior parte da funcionalidade de inteligência/tuning que o ASI entregava além do retrieval básico.

### SPEC-009 — Operations, Indexing & Reliability

| ID | Capability | Estado atual | Necessidade |
|---|---|---|---|
| ASI-020 | durable queue/retry/dead | GAP | decisão/implementação baseada em workload |
| ASI-021 | reconciliation/post-install | GAP | reconstruir |
| ASI-022 | diagnostics/Site Health | PARTIAL | consolidar observabilidade operacional |

## Interpretação funcional

### O BDC já substitui o ASI para
- busca lexical canônica;
- normalização de consulta;
- busca por post;
- busca por item/seção;
- identidade estável de resultado;
- deep-link/anchors;
- Golden Queries;
- rebuild/lifecycle básico;
- funcionamento sem runtime ASI.

### O BDC ainda NÃO substitui integralmente o ASI para
- experiência pública live-search completa;
- vocabulário/aliases/bindings;
- regras mutáveis de relevância;
- simulação e Apply governado;
- diagnóstico/sugestões de tuning;
- eventos/interações/outcomes;
- Search Intelligence;
- privacy/retention da telemetria;
- Word Cloud convergida;
- durable queue/retry/dead-letter;
- reconciliation/post-install;
- Site Health/diagnósticos operacionais completos.

## Decisão de arquitetura

Não reabrir a SPEC-005 para absorver essas capacidades.

Motivo:
- Search core está estável e comprovado;
- misturar telemetry, public UX, governed relevance e operations na SPEC-005 aumentaria acoplamento e risco;
- as SPECs 007/008/009 possuem ownership e gates próprios;
- o mesmo Search Service / Lexical Ranker permanece canônico; nenhuma SPEC futura deve criar segundo ranker.

## Regra de retirement

ASI só pode ser retirado após:
1. SPEC-007 resolver as dependências públicas aplicáveis;
2. SPEC-008 resolver ou aposentar explicitamente as capabilities 007–017 aplicáveis;
3. SPEC-009 resolver ou aposentar explicitamente 020–022;
4. Master Functional Parity Ledger ficar sem PARTIAL/GAP/UNKNOWN_ENVIRONMENTAL aplicável;
5. SPEC-014 CUT-1410+ comprovar preflight/cutover/rollback.

Até lá:
- SPEC-005 = CLOSED;
- ASI = LEGACY STILL REQUIRED FOR GLOBAL PARITY / RETIREMENT NOT AUTHORIZED.

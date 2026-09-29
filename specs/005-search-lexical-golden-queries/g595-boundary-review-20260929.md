# G-595 — SPEC-005 Boundary Review

**Status:** READY FOR ENVIRONMENTAL BOUNDARY SMOKE  
**Data:** 2026-09-29  
**Escopo:** SPEC-005 Search Lexical + Golden + Section Retrieval + Independence  
**Não autoriza:** ASI retirement, cutover público, merge para main ou release 1.0.

## 1. Baselines fechadas

### G-590
- PASS/CLOSED em RC12;
- item/section retrieval = PARITY_VERIFIED;
- stable item identity = PARITY_VERIFIED;
- anchors/deep links = PARITY_VERIFIED;
- 623/623 corpus;
- 0 mismatch;
- Golden PASS;
- p95 255.923 ms;
- zero runtime ASI dependency.

### G-585
- INDEPENDENCE PASS/CLOSED em RC15;
- T585/T585.1/T586/T587/T588/T589/T589.1/T589.2 = PASS;
- candidate public surface isolada da Home legada;
- Search/Golden/rebuild/lifecycle passam com ASI desativado;
- cutover_authorized=false;
- next gate = SPEC005_BOUNDARY_REVIEW.

## 2. Escopo de fechamento da SPEC-005

A SPEC-005 pode fechar apenas as capacidades de Search sob sua ownership:

| Capability | Ledger | Estado |
|---|---|---|
| Post lexical retrieval | ASI-001 | IMPROVED_VERIFIED |
| Query normalization | ASI-002 | IMPROVED_VERIFIED |
| Item/section retrieval | ASI-003 | PARITY_VERIFIED |
| Stable item identity | ASI-004 | PARITY_VERIFIED |
| Anchors/deep links | ASI-005 | PARITY_VERIFIED |
| Golden Queries | ASI-006 | IMPROVED_VERIFIED |
| Rebuild/lifecycle | ASI-019 | PARITY_VERIFIED |

Esses estados são suficientes para fechar a **fronteira Search da SPEC-005**.

## 3. O que NÃO pertence ao fechamento G-595

O Master Functional Parity Ledger ainda contém blockers aplicáveis ao retirement do ASI e/ou do ambiente legado:

### SPEC-007 — Public Knowledge Experience
- ASI-018 public live search = PARTIAL;
- ENV-001 Home Code Snippet = PARTIAL;
- ENV-002 Astra Additional CSS = PARTIAL;
- ENV-003 Entra integration = PARTIAL;
- ENV-004 GAC/WP Unified Indexer = UNKNOWN_ENVIRONMENTAL;
- GRE-006 public rail = PARTIAL;
- aliases/shortcodes públicos condicionais continuam dependentes de preflight.

### SPEC-008 — Search Intelligence / Telemetry / Governed Relevance
- ASI-007 vocabulary/aliases = GAP;
- ASI-008 term bindings = GAP;
- ASI-009 relevance rules = GAP;
- ASI-010 diagnostics/suggestions = GAP;
- ASI-011 ranking simulation/apply = GAP;
- ASI-012 Search Events = GAP;
- ASI-013 Interactions = GAP;
- ASI-014 Outcomes = GAP;
- ASI-015 Search Intelligence = GAP;
- ASI-016 privacy/retention modes = GAP;
- ASI-017 Word Cloud = PARTIAL.

### SPEC-009 — Operations / Reliability
- ASI-020 durable queue/retry/dead = GAP;
- ASI-021 reconciliation/post-install = GAP;
- ASI-022 diagnostics/Site Health = PARTIAL.

## 4. Boundary disposition

**SPEC-005 Search boundary:** elegível para fechamento após G-595 técnico.  
**ASI decommission:** BLOQUEADO.  
**Public cutover:** BLOQUEADO.  
**Release 1.0:** BLOQUEADO.

Isso não é contradição: uma SPEC pode fechar sua ownership sem declarar paridade global do produto.

## 5. G-595 acceptance

### T595-01 — deterministic build
Gerar artefato boundary a partir das baselines fechadas G-590/G-585.

### T595-02 — manifest/checksum
ZIP e manifest devem ser determinísticos e versionados.

### T595-03 — regressão SPEC-001–005
Executar regressões locais consolidadas; nenhuma baseline fechada pode regredir.

### T595-04 — environmental boundary smoke
Em homologação:
- ASI desativado;
- candidate Public Experience acessível;
- Search lexical operacional;
- Section retrieval/deep-link operacional;
- Golden PASS;
- rebuild/lifecycle PASS;
- nenhuma escrita editorial;
- nenhuma dependência ASI no runtime BDC.

### T595-05 — final boundary report
Registrar PASS/FAIL e blockers externos ao boundary.

### T595-06 — human merge gate
Mesmo com T595-01..05 PASS, merge permanece decisão humana explícita.

## 6. Relação com SPEC-014

SPEC-014 continua sendo o único gate de retirement global.

CUT-1410 exige Master Ledger sem PARTIAL/GAP/UNKNOWN_ENVIRONMENTAL aplicável. Esse requisito não está atendido hoje.

Portanto:
- G-595 pode fechar SPEC-005;
- SPEC-014 não pode autorizar retirement;
- nenhuma remoção física do ASI deve ocorrer neste estágio.

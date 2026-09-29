# Master Functional Parity Ledger v1

**Data:** 2026-09-21  
**Regra:** PLANNED = GAP para cutover.

Estados:
PARITY_VERIFIED | IMPROVED_VERIFIED | SUPERSEDED_WITH_EVIDENCE | PARTIAL | GAP | RETIRED_BY_PRODUCT_DECISION | UNKNOWN_ENVIRONMENTAL.

| ID | Referência | Capability | Estado BDC | Destino | Blocker |
|---|---|---|---|---|---|
| GRE-001 | GRE 0.8 | oito campos estruturados | PARITY_VERIFIED | SPEC-006 / P-630 | NÃO |
| GRE-002 | GRE 0.8 | Objective Provider/evento | SUPERSEDED_WITH_EVIDENCE | serviços internos | NÃO |
| GRE-003 | GRE 0.8 | Summary Provider | PARTIAL | SPEC-006 | SIM |
| GRE-004 | GRE 0.8 | Helpful Tips read/write | PARITY_VERIFIED | SPEC-006 / P-630 | NÃO |
| GRE-005 | GRE 0.8 | Coverage Dashboard | GAP | SPEC-006/007 | SIM |
| GRE-006 | GRE 0.8 | rail público | PARTIAL | SPEC-007 | SIM |
| GRE-007 | GRE 0.8 | shortcode/fallback | UNKNOWN_ENVIRONMENTAL | SPEC-007/014 | CONDICIONAL |
| KB2-001 | KB2Ops 0.2.1 | Content Extractor | SUPERSEDED_WITH_EVIDENCE | SPEC-004 | NÃO |
| KB2-002 | KB2Ops 0.2.1 | Knowledge Studio | PARTIAL | SPEC-006/007 | SIM |
| KB2-003 | KB2Ops 0.2.1 | Review/Governança | IMPROVED_VERIFIED | SPEC-003 | NÃO |
| KB2-004 | KB2Ops 0.2.1 | AI READY/include_ai | GAP | SPEC-012 | CONDICIONAL |
| KB2-005 | KB2Ops 0.2.1 | service/technologies/keywords/versions | SUPERSEDED_WITH_EVIDENCE | SPEC-006 / P-630 | NÃO |
| KB2-006 | KB2Ops 0.2.1 | pre-analysis/checklist | PARTIAL | SPEC-006/012 | SIM |
| KB2-007 | KB2Ops 0.2.1 | reports/top viewed/searches | PARTIAL | SPEC-008 | CONDICIONAL |
| KB2-008 | KB2Ops 0.2.1 | Search/Portal aliases | UNKNOWN_ENVIRONMENTAL | SPEC-007/014 | CONDICIONAL |
| ASI-001 | ASI 4.6.8 | post lexical retrieval | IMPROVED_VERIFIED | SPEC-005 | NÃO |
| ASI-002 | ASI 4.6.8 | query normalization | IMPROVED_VERIFIED | SPEC-005 | NÃO |
| ASI-003 | ASI 4.6.8 | item/section retrieval | PARITY_VERIFIED | SPEC-005 / G-590 | NÃO |
| ASI-004 | ASI 4.6.8 | stable item identity | PARITY_VERIFIED | SPEC-005 / G-590 | NÃO |
| ASI-005 | ASI 4.6.8 | anchors/deep links | PARITY_VERIFIED | SPEC-005 / G-590 | NÃO |
| ASI-006 | ASI 4.6.8 | Golden Queries | IMPROVED_VERIFIED | SPEC-005 | NÃO |
| ASI-007 | ASI 4.6.8 | vocabulary/aliases | GAP | SPEC-008 | SIM |
| ASI-008 | ASI 4.6.8 | term bindings | GAP | SPEC-008 | SIM |
| ASI-009 | ASI 4.6.8 | relevance rules | GAP | SPEC-008 | SIM |
| ASI-010 | ASI 4.6.8 | diagnostics/suggestions | GAP | SPEC-008 | SIM |
| ASI-011 | ASI 4.6.8 | ranking simulation/apply | GAP | SPEC-008 | SIM |
| ASI-012 | ASI 4.6.8 | Search Events | GAP | SPEC-008 | SIM |
| ASI-013 | ASI 4.6.8 | Interactions | GAP | SPEC-008 | SIM |
| ASI-014 | ASI 4.6.8 | Outcomes | GAP | SPEC-008 | SIM |
| ASI-015 | ASI 4.6.8 | Search Intelligence | GAP | SPEC-008 | SIM |
| ASI-016 | ASI 4.6.8 | privacy/retention modes | GAP | SPEC-008 | SIM |
| ASI-017 | ASI 4.6.8 | Word Cloud | PARTIAL | SPEC-008 | SIM |
| ASI-018 | ASI 4.6.8 | public live search | PARTIAL | SPEC-007 | SIM |
| ASI-019 | ASI 4.6.8 | rebuild/lifecycle | PARITY_VERIFIED | SPEC-005 | NÃO |
| ASI-020 | ASI 4.6.8 | durable queue/retry/dead | GAP | SPEC-009 | SIM |
| ASI-021 | ASI 4.6.8 | reconciliation/post-install | GAP | SPEC-009 | SIM |
| ASI-022 | ASI 4.6.8 | diagnostics/Site Health | PARTIAL | SPEC-006/009 | SIM |
| ENV-001 | ambiente | Home Code Snippet | PARTIAL | SPEC-007 | SIM |
| ENV-002 | ambiente | Astra Additional CSS | PARTIAL | SPEC-007 | SIM |
| ENV-003 | ambiente | Entra integration | PARTIAL | SPEC-007 | SIM |
| ENV-004 | ambiente | GAC/WP Unified Indexer | UNKNOWN_ENVIRONMENTAL | SPEC-007/014 | SIM |
| PROD-001 | BDC | metadata/license/update | IMPROVED_VERIFIED | SPEC-006 / P-600 | NÃO |
| PROD-002 | BDC | readme/license/changelog/security/contributing | IMPROVED_VERIFIED | SPEC-006 / P-600 | NÃO |
| PROD-003 | BDC | Composer/WPCS/PHPUnit/static | PARTIAL | SPEC-006 / P-610/P-670 | SIM 1.0 |
| PROD-004 | BDC | Plugin Check | PARTIAL | SPEC-006 / P-620/P-650/P-670 | SIM RC |
| PROD-005 | BDC | modular production bootstrap | PARTIAL | SPEC-006 | SIM 1.0 |
| PROD-006 | BDC | ZIP sem laboratório indevido | PARTIAL | SPEC-006 | SIM 1.0 |

## Atualização obrigatória

Toda SPEC:
1. lista IDs afetados;
2. só altera estado com evidência;
3. referencia contrato/teste/ambiente;
4. nunca fecha capability apenas como PLANNED;
5. registra RETIRED_BY_PRODUCT_DECISION quando uma função comprovada não fará parte do produto.

## Gate final

SPEC-014 só autoriza retirada quando todas as linhas aplicáveis estiverem em:
PARITY_VERIFIED, IMPROVED_VERIFIED, SUPERSEDED_WITH_EVIDENCE ou RETIRED_BY_PRODUCT_DECISION com preflight.

PARTIAL, GAP e UNKNOWN_ENVIRONMENTAL aplicáveis bloqueiam.


### G-590 final status — 2026-09-28

RC12 environmental evidence fechou G-590:
- Product `0.5.1-rc.12`;
- Build `g590.12-61681200f115`;
- T590-14..19 PASS;
- 548 deterministic candidates reconciliados em 77 TOC / 289 body / 182 uncertain;
- 289/289 structural Sections persistidas;
- projection gap/extra/unsafe/overflow/collision/repository error = 0;
- generated anchor materialization failures = 0;
- Golden post-level PASS;
- p95 255.923 ms / max 299.911 ms;
- editorial fingerprint equal;
- zero runtime ASI dependency.

Evidência versionada:
- `evidence/g590-rc12-environmental-pass-review-20260928.json`;
- `specs/005-search-lexical-golden-queries/g590-closeout-20260928.md`.

ASI-003/004/005 foram promovidos de `PARTIAL` para `PARITY_VERIFIED`.

Isso remove os três blockers de retrieval/identity/deep-link, mas **não autoriza ASI decommission**. G-585 e os demais blockers aplicáveis no Master Ledger continuam mandatórios.



### SPEC-006 P-600 / P-610 / P-620 status — 2026-09-29

P-600:
- product source version `0.6.0-dev`;
- metadata/license/update identity established;
- readme/license/changelog/upgrade/security/contributing established;
- PROD-001 and PROD-002 promoted to `IMPROVED_VERIFIED`.

P-610:
- Composer/PHPUnit/WPCS/PHPStan toolchain operational on PHP 8.1;
- bounded quality baseline PASS;
- full-plugin WPCS debt remains material;
- PROD-003 promoted only to `PARTIAL`.

P-620:
- official WordPress Plugin Check executed;
- identity/readme blockers remediated;
- remaining findings explicitly routed to P-640/P-650/P-660;
- production package is not Plugin Check clean;
- PROD-004 promoted only to `PARTIAL`.

Evidence:
- `evidence/spec006-p600-metadata-license-pass-20260929.json`;
- `evidence/spec006-p610-tooling-pass-20260929.json`;
- `evidence/spec006-p620-plugin-check-disposition-20260929.json`.

PARTIAL remains a cutover/release blocker.

### SPEC-006 P-630 Domain Closure status — 2026-09-29

Environmental acceptance:
- WordPress 6.9.4;
- PHP 8.5.10;
- plugin 0.6.0-dev;
- 606 posts;
- 20 sampled posts;
- store errors = 0;
- facts/tips noop writers PASS;
- public reader canonical alignment PASS;
- domain state unchanged.

Corpus observations:
- affected_service canonical = 7;
- affected_service legacy fallback = 0;
- systems_involved = 6;
- technologies/keywords/versions = 0;
- Helpful Tips = 7 posts / 14 items;
- Coverage = 590 EMPTY / 16 PARTIAL / 0 COMPLETE.

Ledger disposition:
- GRE-001 -> `PARITY_VERIFIED`: eight-field Coverage read model is implemented over canonical owners. Corpus completeness is tracked separately from capability parity.
- GRE-004 -> `PARITY_VERIFIED`: canonical Helpful Tips read/write, validation, read-after-write and rollback are established.
- KB2-005 -> `SUPERSEDED_WITH_EVIDENCE`: BDC owns affected_service and adopts technologies/keywords/versions physical keys without runtime KB2Ops dependency; `_kb2ops_service` remains read-only fallback only.
- GRE-003, GRE-005, KB2-002 and KB2-006 remain open by scope and continue to block their applicable cutover paths.

Evidence:
- `evidence/spec006-p630-local-domain-closure-pass-20260929.json`;
- `evidence/spec006-p630-environmental-package-20260929.json`;
- `evidence/spec006-p630-domain-closure-pass-20260929.json`;
- `specs/006-premium-product-foundation/p630-closeout-20260929.md`.

P-630 does **not** authorize public cutover or GRE/KB2Ops retirement. Those decisions remain gated by the remaining Ledger rows and SPEC-014.


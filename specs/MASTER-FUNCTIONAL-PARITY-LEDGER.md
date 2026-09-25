# Master Functional Parity Ledger v1

**Data:** 2026-09-21  
**Regra:** PLANNED = GAP para cutover.

Estados:
PARITY_VERIFIED | IMPROVED_VERIFIED | SUPERSEDED_WITH_EVIDENCE | PARTIAL | GAP | RETIRED_BY_PRODUCT_DECISION | UNKNOWN_ENVIRONMENTAL.

| ID | Referência | Capability | Estado BDC | Destino | Blocker |
|---|---|---|---|---|---|
| GRE-001 | GRE 0.8 | oito campos estruturados | PARTIAL | SPEC-006 | SIM |
| GRE-002 | GRE 0.8 | Objective Provider/evento | SUPERSEDED_WITH_EVIDENCE | serviços internos | NÃO |
| GRE-003 | GRE 0.8 | Summary Provider | PARTIAL | SPEC-006 | SIM |
| GRE-004 | GRE 0.8 | Helpful Tips read/write | PARTIAL | SPEC-006 | SIM |
| GRE-005 | GRE 0.8 | Coverage Dashboard | GAP | SPEC-006/007 | SIM |
| GRE-006 | GRE 0.8 | rail público | PARTIAL | SPEC-007 | SIM |
| GRE-007 | GRE 0.8 | shortcode/fallback | UNKNOWN_ENVIRONMENTAL | SPEC-007/014 | CONDICIONAL |
| KB2-001 | KB2Ops 0.2.1 | Content Extractor | SUPERSEDED_WITH_EVIDENCE | SPEC-004 | NÃO |
| KB2-002 | KB2Ops 0.2.1 | Knowledge Studio | PARTIAL | SPEC-006/007 | SIM |
| KB2-003 | KB2Ops 0.2.1 | Review/Governança | IMPROVED_VERIFIED | SPEC-003 | NÃO |
| KB2-004 | KB2Ops 0.2.1 | AI READY/include_ai | GAP | SPEC-012 | CONDICIONAL |
| KB2-005 | KB2Ops 0.2.1 | service/technologies/keywords/versions | PARTIAL | SPEC-006 | SIM |
| KB2-006 | KB2Ops 0.2.1 | pre-analysis/checklist | PARTIAL | SPEC-006/012 | SIM |
| KB2-007 | KB2Ops 0.2.1 | reports/top viewed/searches | PARTIAL | SPEC-008 | CONDICIONAL |
| KB2-008 | KB2Ops 0.2.1 | Search/Portal aliases | UNKNOWN_ENVIRONMENTAL | SPEC-007/014 | CONDICIONAL |
| ASI-001 | ASI 4.6.8 | post lexical retrieval | IMPROVED_VERIFIED | SPEC-005 | NÃO |
| ASI-002 | ASI 4.6.8 | query normalization | IMPROVED_VERIFIED | SPEC-005 | NÃO |
| ASI-003 | ASI 4.6.8 | item/section retrieval | PARTIAL | SPEC-005 / G-590 | SIM |
| ASI-004 | ASI 4.6.8 | stable item identity | PARTIAL | SPEC-005 / G-590 | SIM |
| ASI-005 | ASI 4.6.8 | anchors/deep links | PARTIAL | SPEC-005 / G-590 | SIM |
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
| PROD-001 | BDC | metadata/license/update | GAP | SPEC-006 | SIM 1.0 |
| PROD-002 | BDC | readme/license/changelog/security/contributing | GAP | SPEC-006 | SIM 1.0 |
| PROD-003 | BDC | Composer/WPCS/PHPUnit/static | GAP | SPEC-006 | SIM 1.0 |
| PROD-004 | BDC | Plugin Check | GAP | SPEC-006 | SIM RC |
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


### G-590 candidate status — 2026-09-21

ASI-003/004/005 foram promovidos de GAP para PARTIAL somente porque há runtime candidato versionado:
- Search Section Projection na mesma tabela post-level;
- section identity determinística;
- Section Ranker/Service;
- Search_Service::search_sections();
- anchor efêmero fail-closed;
- lifecycle schema 1.1 com verificação física;
- runner ambiental + machine evidence validator.

**PARTIAL não é paridade.** Os três itens continuam blockers até:
1. G-590 ambiental PASS;
2. coverage audit sem gap bloqueante;
3. Golden post-level permanecer PASS;
4. section/deep-link probes passarem;
5. performance/security budgets passarem;
6. evidência ser versionada no repositório.

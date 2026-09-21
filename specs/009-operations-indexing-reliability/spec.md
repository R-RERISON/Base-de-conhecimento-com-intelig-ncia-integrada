# SPEC-009 — Operations, Indexing & Reliability

**Status:** PLANEJADA  
**Pré-requisito:** Search data model estável e workload mensurável.

## Problema

Full rebuild manual não basta para crescimento, atualizações concorrentes e falhas parciais.

## Escopo

- change detection;
- stale projection;
- incremental indexing;
- idempotency;
- queue somente se necessária;
- lease/claim;
- retry/backoff;
- dead-letter;
- resumability;
- reconciliation;
- orphan repair;
- schema migrations;
- maintenance windows;
- Site Health;
- sanitized diagnostics;
- disaster/rebuild drills;
- upgrade/deactivate/reactivate/uninstall.

WP-Cron é trigger, não fila.

## Gates

OP-900 Workload Evidence  
OP-910 Incremental Projection  
OP-920 Durability Decision  
OP-930 Queue/Lease/Retry se autorizada  
OP-940 Reconciliation  
OP-950 Site Health  
OP-960 Upgrade/Migration  
OP-970 Recovery Drill  
OP-980 Performance/Scale  
OP-990 Operations Acceptance

# Matriz de Evidência — SPEC-005

| Gate | Evidência principal | Estado |
|---|---|---|
| R-500 | `r500-search-baseline-decision.md` | PASS/CLOSED |
| R-510/T510 | `evidence/r510-t510-environmental-20260918T211840Z.json` | PASS AMBIENTAL |
| R-510/T511.2 | `r510-t5112-environmental-findings-v1.md` | PASS AMBIENTAL |
| R-510/T513 | `evidence/r510-t5142-environmental-20260918T235252Z.json` | PASS AUTOMATED WITH QUARANTINE |
| R-510/T514 | mesma evidência + ADR-005-002 | PASS AMBIENTAL |
| R-510/T515 | frozen fixtures + `r510-suite-hash-contract-v1.md` | PASS |
| R-510/T516 | `r510-closeout-20260918.md` | PASS/CLOSED |
| G-520 | `g520-closeout-20260919.md` + `evidence/g520-contract-validation-20260919.json` | PASS/CLOSED |
| G-530 | `g530-closeout-20260919.md` + `evidence/g530-local-validation-20260919.json` | PASS/CLOSED |
| G-540 | `evidence/g540-environmental-20260919T115727Z.json` | PASS/CLOSED |
| G-550 | `evidence/g550-environmental-20260919T121422Z.json` | PASS/CLOSED |
| G-560 | `evidence/g560-local-package-validation-20260919.json` + `evidence/g560-environmental-review-20260919T134816Z.json` + `evidence/g560-human-visual-acceptance-20260919.md` | PASS/CLOSED |
| G-570 | segurança + p50/p95 + bounds | NOT_RUN |
| G-580 | lifecycle/rebuild/fallback | NOT_RUN |
| G-585 | ASI ausente + Search/Golden + rebuild próprio | NOT_RUN |
| G-590 | package + manifest + checksum + final smoke | NOT_RUN |

## Freeze R-510

Golden Relevance:
- `golden-relevance-v1.0.0`;
- `e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4`;
- 5 blocking ativos;
- 1 quarantine.

Technical Challenge:
- `technical-challenge-v1.0.0`;
- `2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807`;
- 7 casos;
- não blocking.

Fonte ambiental:
- SHA-256 `b461ff671177128958f8729208d1dcdb6a9635f93307879869b0908b8f252d89`;
- `r510_ready=true`.

## Semântica

`PASS | FAIL | NOT_RUN | NOT_CONFIGURED | STALE | N/A | POSTERGADO | WAIVED`

- Golden vazia = NOT_CONFIGURED.
- AUTO_FAIL = NO-GO.
- AMBIGUOUS_QUARANTINED não é blocking PASS; é expectativa preservada fora do blocking set.
- Technical Challenge não é consulta real.
- real-world typo/alias=PENDING_TELEMETRY não bloqueia o bootstrap lexical.
- G-585 continua separado de R-510.

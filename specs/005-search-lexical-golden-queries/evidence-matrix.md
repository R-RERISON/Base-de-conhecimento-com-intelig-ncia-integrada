# Matriz de Evidência — SPEC-005

| Gate | Evidência mínima | Estado inicial |
|---|---|---|
| R-500 | baseline corpus + WP_Query benchmark + gap analysis | NOT_RUN |
| R-510 | Golden Suite v1 não vazia + revisão humana + hash | NOT_RUN |
| G-520 | contratos + decisão WordPress-first + security/rollback | NOT_RUN |
| G-530 | unit/integration lexical + determinismo | NOT_RUN |
| G-540 | full-corpus + coverage + duas passagens | NOT_RUN |
| G-550 | Golden report current, blocking=0 | NOT_RUN |
| G-560 | human relevance + UX/accessibility | NOT_RUN |
| G-570 | security + p50/p95 + bounds | NOT_RUN |
| G-580 | lifecycle/rebuild/fallback | NOT_RUN |
| G-590 | package + manifest + checksum + final smoke | NOT_RUN |

## Semântica de estado

`PASS | FAIL | NOT_RUN | NOT_CONFIGURED | STALE | N/A | POSTERGADO | WAIVED`

Regras:
- suite Golden vazia = NOT_CONFIGURED;
- evidence stale = não conta como PASS;
- blocking Golden FAIL = NO-GO;
- UI material exige evidência humana;
- release usa o mesmo artefato testado.

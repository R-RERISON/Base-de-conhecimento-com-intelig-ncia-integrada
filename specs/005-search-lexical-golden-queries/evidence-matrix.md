# Matriz de Evidência — SPEC-005

Atualizada após a revisão do JSON T511.2 em 2026-09-18. O estado de um diagnóstico não promove automaticamente o gate de produto.

| Gate | Evidência mínima / referência | Estado atual |
|---|---|---|
| R-500 | [Benchmark e decisão](r500-search-baseline-decision.md) | PASS/CLOSED |
| R-510/T510 | [Discovery histórica](evidence/r510-t510-environmental-20260918T211840Z.json) | PASS AMBIENTAL |
| R-510/T511.2 | [Baseline independente](r510-t5112-environmental-findings-v1.md) | PASS AMBIENTAL |
| R-510/T513 | [Expected/max_rank/severity/rationale humanos](r510-golden-candidate-review-v1.md) | PENDING |
| R-510/T514 | [Diversidade real revisada](r510-golden-candidate-review-v1.md) | PENDING |
| R-510 | Suite aprovada não vazia + revisão + diversidade + versão/hash | OPEN; T515/T516 NOT_RUN |
| G-520 | Contratos + decisão WordPress-first + security/rollback | NOT_RUN |
| G-530 | Unit/integration lexical + determinismo | NOT_RUN |
| G-540 | Full-corpus + coverage + duas passagens | NOT_RUN |
| G-550 | Golden report current, blocking=0 | NOT_RUN |
| G-560 | Relevância humana + UX/accessibility | NOT_RUN |
| G-570 | Segurança + p50/p95 + bounds | NOT_RUN |
| G-580 | Lifecycle/rebuild/fallback | NOT_RUN |
| G-585 | ASI ausente + scan produtivo + Search/Golden + rebuild próprio | NOT_RUN |
| G-590 | Package + manifest + checksum + final smoke | NOT_RUN |

## Semântica de estado

`PASS | FAIL | NOT_RUN | NOT_CONFIGURED | STALE | N/A | POSTERGADO | WAIVED`

`OPEN`/`PENDING` indicam fluxo ainda não concluído; não equivalem a PASS.

- Suite Golden vazia = NOT_CONFIGURED.
- Candidates medidos não são suite aprovada.
- Evidence stale não conta como PASS.
- Blocking Golden FAIL = NO-GO.
- UI material exige evidência humana.
- Release usa o mesmo artefato testado.
- T511.2 não substitui G-550 nem prova G-585; o JSON não registra ASI desativado.

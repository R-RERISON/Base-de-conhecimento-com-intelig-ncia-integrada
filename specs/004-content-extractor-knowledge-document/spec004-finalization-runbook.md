# SPEC-004 — Finalization Runbook

**Estado:** EXECUTADO / FECHADO em 2026-09-18.

## Entrada obrigatória

G-250 deve retornar:

`gate_result.g250_lifecycle_rc_pass=true`

com:
- upgrade RC1 confirmado;
- deactivate/activate confirmado;
- rollback/downgrade controlado confirmado;
- SPEC-001/002/003 read paths PASS;
- Content Extractor PASS;
- Knowledge Document 2.1.0 PASS;
- Workspace Context PASS;
- post 358 ainda em Gutenberg/Core Blocks;
- journal latest `applied`;
- lock `free`;
- fingerprint before == after.

## Após G-250 PASS

### F1 — fechar gates

Atualizar:
- T100E-E7 → CLOSED/PASS;
- T100E → CLOSED;
- G-245 → PASS/CLOSED;
- G-250 → PASS/CLOSED.

### F2 — limpar runtime final

No bootstrap final:
- `BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD=false`;
- `BDC_KB_SPEC004_G250_LIFECYCLE_BUILD=false`;
- `BDC_KB_SPEC004_G245_PREFLIGHT_BUILD=false`;
- `BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD=false`;
- `BDC_KB_ELEMENTOR_WRITER_ENABLED=false`.

O artefato instalável final não deve conter:
- E6 runner;
- G-250 runner;
- T100D executor;
- Production Preflight;
- smokes/diagnósticos históricos desligados.

O source tree pode preservar evidências e ferramentas históricas.

### F3 — gerar RC final limpo

Usar builder determinístico.

Exigir:
- single plugin root;
- active requires completos;
- PHP lint 100%;
- source/artifact manifest;
- rebuild SHA-256 idêntico;
- UX-003 assets preservados;
- nenhum writer inesperado;
- nenhum network/shortcode/dynamic render inesperado.

### F4 — regression final estática

Rodar `tools/t100e/regression_runner.py` contra source + ZIP final.

PASS exige:
- failures=[];
- T100D OFF;
- Elementor writer OFF;
- G250/E6/Preflight não ativos;
- Workspace invariants PASS;
- T100C preparation read-only;
- nenhum active runtime dependency ausente.

### F5 — documentação final

Gerar:
- `spec004-final-report.md`;
- `spec004-residual-debt.md`;
- manifest final;
- checksum final;
- release evidence JSON.

Dívida residual obrigatória:
- 39 artigos relacionados a Elementor no baseline E6:
  - 34 Elementor;
  - 5 mixed;
- Elementor Adapter permanece;
- retirada do Elementor é trabalho futuro e exige dependência zero.

### F6 — aceite final

O fechamento da SPEC-004 declara:
- Content Extractor/KD estabilizados;
- Core Blocks como destino editorial canônico;
- um canário real com rollback comprovado;
- uma migração persistente real comprovada;
- Workspace post-scoped;
- UX-003 aprovada;
- E6 full-corpus PASS;
- G-250 lifecycle PASS;
- artefato final reproduzível.

## Fora do escopo do fechamento

Não iniciar antes do encerramento:
- IA;
- embeddings;
- busca vetorial;
- migração em massa;
- remoção de Elementor;
- novas features editoriais.


## Resultado da execução

- G-250 RC1: PASS ambiental;
- evidence: `evidence/g250-lifecycle-rc-pass-20260918T123514Z.json`;
- T100E-E7: CLOSED/PASS;
- G-245: CLOSED/PASS;
- G-250: CLOSED/PASS;
- RC final: `0.4.0-spec004-rc2`;
- SHA-256: `ac25c2ffd4a0ae2250fa2ce1a07bf07b4cad8a24030e31f12c78189e61e7506b`;
- final static validation: PASS;
- PR #4 continua DRAFT até revisão final.

# SPEC-000 — Premium Rebaseline v2

**Status:** REABERTURA DOCUMENTAL / ZERO RUNTIME  
**Data:** 2026-09-21

## Razão

A SPEC-000 original cumpriu seu papel, porém:
- GRE evoluiu de 0.6.0 para 0.8.0;
- capabilities reais também vivem no ambiente;
- SPEC-005 provou que zero dependência não significa zero perda funcional;
- o produto agora adota padrão Premium Product.

## Baselines correntes

- KB2Ops 0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94;
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1;
- GRE 0.8.0 @ c58a403a29f275789fde7e65452358f307adc927;
- BDC integration @ 76d2617c43a5347002ef846c32b8ea00ad556953.

## Nova interpretação

1. Git é baseline de código, não a única baseline funcional.
2. Runtime ambiental pode estabelecer capability real quando versionado/evidenciado.
3. Paridade é ledger viva.
4. PLANNED continua GAP.
5. SPEC final de cutover não pode descobrir blocker crítico pela primeira vez.

## Artefatos normativos

- docs/PREMIUM-PLUGIN-PRODUCT-STANDARD.md;
- specs/MASTER-FUNCTIONAL-PARITY-LEDGER.md;
- specs/PREMIUM-REBASELINE-SPEC-REVIEW.md;
- specs/ROADMAP.md v2.

Este addendum não apaga a SPEC-000 histórica e não autoriza runtime.

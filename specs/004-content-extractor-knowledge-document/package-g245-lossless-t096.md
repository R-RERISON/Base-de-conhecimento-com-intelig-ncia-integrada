# Package — G-245 T096 Lossless Core Blocks

**Build:** `0.4.0-g245-lossless-t096.1`  
**Purpose:** homologation-only diagnostic smoke for T096  
**Status:** package validated / environmental gate pending

## Artifact

- ZIP: `base-conhecimento-inteligencia-integrada-0.4.0-g245-lossless-t096.1.zip`
- SHA-256: `5a2fc4ac31bfbe2b68cfe5f06d07057310760f54c9f5ecfc9fbc55b3b07ad961`

## Local validation

- T095/T096 combined assertions: **34/34 PASS**;
- PHP lint before ZIP: **41/41 PASS**;
- PHP lint after ZIP re-extraction: **41/41 PASS**;
- single plugin root: PASS;
- UX-002 approved visual byte parity: PASS;
- T094 smoke: OFF;
- T096 smoke: ON only in this homologation package;
- Elementor Projection/Journal smokes: OFF;
- `BDC_KB_ELEMENTOR_WRITER_ENABLED=false`;
- no `wp_update_post`, `update_post_meta` or `delete_post_meta` in new T095/T096 runtime;
- no external network;
- no shortcode/block rendering;
- Gutenberg plugin dependency: false.

## Important packaging note

This ZIP is a **diagnostic homologation build**, not a byte-identical export of the development branch. The Git branch remains the canonical source of implementation and retains all previously built defensive G-245 classes/gates. The package contains the runtime needed for the T096 environmental smoke while preserving the approved product UI.

## T096 gate

Menu:

`Base de Conhecimento > Lossless Blocks G-245`

Action:

`Executar T096 e baixar JSON`

Expected:

```json
{
  "gate_result": {
    "t096_lossless_roundtrip_pass": true
  }
}
```

The smoke must prove:

- complete corpus in two passes;
- zero errors/throwables/safety violations;
- zero raw-payload round-trip mismatches;
- zero parse/serialize mismatches;
- zero fidelity/serialization hash mismatches;
- native Gutenberg content preserved exactly;
- corpus unchanged;
- editorial fingerprint before/after equal;
- no editorial payload, URL or post ID exported;
- no persistence.

## Promotion rule

T096 PASS does **not** authorize any writer. After T096, T097 must validate rendered/editorial parity in a controlled cohort before the Block Migration defensive gates and canary are activated.

# T100E Engineering Tools

## Static regression runner v1.1

Run from repository root:

```bash
python tools/t100e/regression_runner.py
```

Persist evidence:

```bash
python tools/t100e/regression_runner.py \
  --json specs/004-content-extractor-knowledge-document/evidence/t100e-static-regression.json
```

Compare source tree with an installable ZIP:

```bash
python tools/t100e/regression_runner.py \
  --artifact-zip /path/base-conhecimento-inteligencia-integrada.zip \
  --json specs/004-content-extractor-knowledge-document/evidence/t100e-artifact-regression.json
```

The runner does not bootstrap WordPress, access the database, call the network or perform editorial writes.

Current responsibilities:
- PHP lint when PHP CLI is available;
- all source `require_once` integrity;
- unconditional/conditional runtime require inventory;
- build-flag invariants;
- consumed one-shot T100D executor must remain OFF;
- Post Management Workspace invariants;
- T100C read-only enforcement;
- product vs engineering writer inventory;
- network/shortcode/dynamic-block-render inventory;
- engineering/test source inventory;
- exact Block/Elementor defensive-family pairing;
- optional source-vs-ZIP file-manifest comparison;
- failure when an installable artifact omits any **active** runtime dependency.

Warnings represent consolidation debt. Failures represent contract/regression violations.

## T100E interpretation

A PASS does **not** mean the runtime is fully consolidated. It means the current implementation still satisfies the frozen safety contracts.

Warnings are intentionally carried forward until the matching T100E work item removes or formally accepts them.

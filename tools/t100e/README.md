# T100E Engineering Tools

## Static regression runner

Run from repository root:

```bash
python tools/t100e/regression_runner.py
```

Persist evidence:

```bash
python tools/t100e/regression_runner.py --json specs/004-content-extractor-knowledge-document/evidence/t100e-static-regression.json
```

The runner does not bootstrap WordPress, access the database, call the network or perform editorial writes.

Initial responsibilities:
- PHP lint when the PHP CLI is available;
- unconditional require integrity;
- build-flag invariants;
- Post Management Workspace invariants;
- T100C read-only enforcement;
- writer/network/shortcode/block-render inventories;
- engineering/test file inventory;
- duplicate Block/Elementor defensive-family signal.

Warnings are inventory findings; failures are contract/regression violations.

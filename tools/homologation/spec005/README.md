# SPEC-005 Homologation Tools

## G-590

Build the deterministic homologation artifact from repository root:

```bash
python tools/homologation/spec005/build-g590.py
```

Outputs:

- `dist/base-conhecimento-inteligencia-integrada-0.5.1-rc.1-g590.1.zip`;
- matching manifest JSON;
- `dist/g590-local-package-validation.json`.

The builder:

1. runs the G-590 functional/unit contract;
2. runs the G-590 structural safety gate;
3. runs the existing T100E regression runner;
4. copies the plugin to temporary staging;
5. sets Product Version `0.5.1-rc.1` and injects Build ID `g590.1-<commit>` only in staging;
6. enables only the G-590 engineering runner required for this gate;
7. disables unrelated engineering runners;
8. keeps product runtime capabilities from the current baseline;
9. PHP-lints the staged source;
10. produces the installable ZIP twice;
11. requires byte-identical SHA-256;
12. PHP-lints the extracted ZIP;
13. writes machine-readable local evidence.

No source bootstrap is modified by the builder.

After installation in homologation:

1. open **Base de Conhecimento → Section Retrieval G-590**;
2. run the gate;
3. download the JSON;
4. validate:

```bash
php tools/homologation/spec005/validate-g590-evidence.php /path/evidence.json
```

Do not mark G-590 PASS from package validation alone. Environmental evidence remains mandatory.


### Product Version and Build ID

G-590 follows the Premium Product Standard:

- plugin Product Version: `0.5.1-rc.1`;
- engineering Build ID: `g590.1-<12-char-git-sha>`;
- artifact filename carries both for operator clarity;
- runtime evidence must report both;
- source bootstrap never persists the engineering Build ID.

The local evidence remains under `dist/` until the package is accepted. Only accepted evidence is promoted to the repository `evidence/` tree.

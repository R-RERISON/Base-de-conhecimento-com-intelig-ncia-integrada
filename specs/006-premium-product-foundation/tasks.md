# SPEC-006 — Tasks

## Estado

- [x] Pré-requisito: SPEC-005 CLOSED/main.
- [x] P-600 Plugin Metadata/License — PASS
- [x] P-610 Tooling/WPCS/PHPUnit — PASS / legacy debt inventoried
- [ ] P-620 Plugin Check
- [ ] P-630 Domain Closure
- [ ] P-640 Modular Runtime
- [ ] P-650 Packaging/Install/Upgrade
- [ ] P-660 Security/Privacy baseline
- [ ] P-670 Premium Foundation Acceptance

## P-600

- [x] P600-01 criar contrato metadata/license.
- [x] P600-02 estabelecer versão source `0.6.0-dev`.
- [x] P600-03 completar plugin header.
- [x] P600-04 adicionar readme/license/changelog/upgrade/security/contributing.
- [x] P600-05 adicionar teste estático.
- [x] P600-06 executar validação local consolidada — PASS.
- [x] P600-07 registrar evidência e fechar P-600 — `evidence/spec006-p600-metadata-license-pass-20260929.json`.

## P-610 closeout

- [x] Composer validate/install on PHP 8.1.
- [x] PHPUnit 10.5 baseline — 2 tests / 6 assertions PASS.
- [x] WPCS 3.4 bounded baseline PASS.
- [x] PHPStan 2.2 level 5 bounded baseline PASS.
- [x] full-plugin WPCS debt measured — 11,718 errors / 7,118 warnings / 121 files.
- [x] bulk PHPCBF explicitly forbidden.
- [x] evidence: `evidence/spec006-p610-tooling-pass-20260929.json`.
- [ ] P-620 Plugin Check — next active gate.

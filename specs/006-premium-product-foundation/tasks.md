# SPEC-006 — Tasks

## Estado

- [x] Pré-requisito: SPEC-005 CLOSED/main.
- [x] P-600 Plugin Metadata/License — PASS
- [x] P-610 Tooling/WPCS/PHPUnit — PASS / legacy debt inventoried
- [x] P-620 Plugin Check — PASS inventory/disposition; production package still not clean
- [x] P-630 Domain Closure — PASS / environmental acceptance + Master Ledger disposition
- [ ] P-640 Modular Runtime — next active gate
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
- [x] P-620 Plugin Check — completed after P-610.

## P-620 closeout

- [x] Official Plugin Check executed with no global ignores.
- [x] Initial: 743 findings / 449 errors / 294 warnings.
- [x] Canonical distribution slug fixed to `bdc-knowledge-base`.
- [x] Readme blockers corrected.
- [x] Re-run: 343 findings / 52 errors / 291 warnings.
- [x] 191 findings routed to P-640/P-650 engineering surface.
- [x] 150 findings routed to P-660 production remediation.
- [x] Update URI documented as private-distribution waiver.
- [x] UPGRADE.md routed to P-650 package pruning.
- [x] Evidence: `evidence/spec006-p620-plugin-check-disposition-20260929.json`.
- [x] P-630 Domain Closure — completed after P-620.

## P-630 closeout

- [x] P630-01 contract/ownership.
- [x] P630-02 Knowledge Facts canonical store.
- [x] P630-03 Helpful Tips canonical read/write.
- [x] P630-04 eight-field Coverage read model.
- [x] P630-05 Public Article Reader consumes canonical facts owner.
- [x] P630-06 Knowledge Workspace editing surface.
- [x] P630-07 local regression/security — static contract + full PHP lint + WPCS new production classes + PHPUnit PASS.
- [x] P630-08 environmental acceptance — PASS on WordPress 6.9.4 / PHP 8.5.10 / 606 posts.
- [x] P630-09 Master Ledger disposition after environmental evidence.

Evidence:
- `evidence/spec006-p630-local-domain-closure-pass-20260929.json`;
- `evidence/spec006-p630-environmental-package-20260929.json`;
- `evidence/spec006-p630-domain-closure-pass-20260929.json`;
- `specs/006-premium-product-foundation/p630-closeout-20260929.md`.

Disposition:
- GRE-001 -> PARITY_VERIFIED;
- GRE-004 -> PARITY_VERIFIED;
- KB2-005 -> SUPERSEDED_WITH_EVIDENCE;
- GRE-003/GRE-005/KB2-002/KB2-006 remain open by scope.

Next active gate:
- [ ] P-640 Modular Runtime.

## P-640 modular runtime

- [x] P640-01 baseline e contrato — `p640-modular-runtime-contract-v1.md`.
- [x] P640-02 implementar `Runtime_Module_Registry` mínimo.
- [x] P640-03 extrair módulos de produto do bootstrap.
- [x] P640-04 separar loaders de engenharia/homologação.
- [x] P640-05 reduzir bootstrap ao composition root.
- [ ] P640-06 static contract + PHP lint + WPCS afetado — REABERTO.
- [ ] P640-07 regressão/environmental acceptance — resultado funcional recebido; fechamento bloqueado até package local conforme.
- [ ] P640-08 package/runtime inventory para P-650.


Implementation notes P640-02..05:
- bootstrap reduzido para 89 linhas;
- 4 requires diretos: Core_Runtime_Loader, Runtime_Module_Registry, Engineering_Module_Loader e Plugin;
- Search/Public Preview/Word Cloud roteados pelo Runtime_Module_Registry;
- 71 blocos condicionais de laboratório removidos do bootstrap e centralizados no Engineering_Module_Loader;
- flags funcionais preservadas;
- sem mudança de persistência, ranking, endpoint, cutover ou retirement;
- contratos estáticos: `tests/unit/spec006-p640-runtime-module-registry.php` e `tests/unit/spec006-p640-modular-runtime.php`.

P640-06 evidence — INVALIDADA:
- execução remota não é válida para fechamento do gate;
- static registry contract: 14/14 PASS;
- modular runtime contract: 15/15 PASS;
- full plugin PHP lint: 131/131 PASS;
- WPCS affected runtime: PASS;
- PHPUnit foundation: PASS;
- deterministic package: PASS;
- package SHA-256: `e22e599ae8ae212c8e9c87a522e4f04a63fe187344798191c7f2fe8051f2ef9a`;
- evidence marcada como `INVALIDATED`: `evidence/spec006-p640-local-validation-pass-20260929.json`.

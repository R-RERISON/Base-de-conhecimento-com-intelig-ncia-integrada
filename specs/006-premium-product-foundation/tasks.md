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
- [ ] P640-07 regressão/environmental acceptance — runner de reconciliação implementado para provar o runtime modular diretamente no p650.3 congelado; execução WP-CLI pendente.
- [ ] P640-08 package/runtime inventory para P-650 — ferramenta implementada; fechamento aguarda P640-06/P640-07 conformes.


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


P640 local-only remediation:
- canonical executor: `tools/homologation/spec006/validate-p640-local.py`;
- executor fails closed when `vendor/bin/phpcs` or `vendor/bin/phpunit` is unavailable;
- current session local diagnostics: static registry 14/14 PASS; modular runtime 15/15 PASS; PHP lint 131/131 PASS;
- current session tooling blocker: Composer/PHPCS/PHPUnit unavailable, therefore P640-06 remains OPEN;
- P640-08 inventory tool implemented: `tools/homologation/spec006/inventory-p640-package-runtime.py`;
- observed diagnostic inventory on current artifact: 150 plugin files / 131 PHP / 42 core / 25 product / 46 engineering; zero missing engineering declarations and zero core/product/engineering overlaps;
- 15 PHP remain outside declared runtime ownership and require P-650 disposition, including loader infrastructure, public templates and historical Elementor migration classes.


## P-650 packaging / install / upgrade

- [x] P650-01 contrato — `p650-packaging-install-upgrade-contract-v1.md`.
- [x] P650-02 builder local determinístico — `tools/homologation/spec006/build-p650-production.py`.
- [x] P650-03 contrato estático do package — `tests/unit/spec006-p650-package-contract.php`.
- [x] P650-04 validador local fail-closed — `tools/homologation/spec006/validate-p650-local.py`.
- [ ] P650-05 build local e package integrity — package p650.1 preparado para homologação; fechamento do gate ainda bloqueado pelo P640-06 local completo.
- [ ] P650-06 install/upgrade/rollback environmental — p650.3 install/runtime PASS; rollback runner preparado, execução pendente.
- [ ] P650-07 Plugin Check do ZIP final + closeout/Ledger.

P650 decisions:
- source checkout continua contendo engenharia/homologação;
- ZIP de produção candidato exclui `Engineering_Module_Loader` e todos os arquivos explicitamente declarados por ele;
- bootstrap do ZIP é transformado somente na distribuição para remover flags/require/calls de engenharia;
- Core/Search/Public Experience/Word Cloud são preservados;
- classes históricas Elementor não são removidas por inferência;
- builder gera raiz única, manifest, SHA-256 e prova deterministicidade;
- `validate-p650-local.py` exige evidência P640 `LOCAL_ONLY PASS` antes de permitir packaging;
- sem GitHub Actions como executor de gate.


P650 homologation package — 2026-09-30:
- artifact: `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.1.zip`;
- SHA-256: `503234ece2620d01bb556e5810a374f07e2153221171f21755bbe7c1b4f5b926`;
- source plugin tree SHA verified against HEAD: `ddb30b150b78c08e9163f7324c5f911c4ead13d0`;
- deterministic build: PASS;
- single root: PASS;
- engineering files in package: 0;
- forbidden repository paths: 0;
- declared Core/Product runtime files missing: 0;
- PHP lint inside ZIP: 84/84 PASS;
- authorized for environmental homologation only, not production release;
- evidence: `evidence/spec006-p650-homologation-package-20260930.json`.


P650 environmental acceptance — 2026-09-30:
- user installed exactly `p650.1`;
- reported result: sem erros, tudo funcionando;
- install/runtime smoke: PASS;
- rollback: NOT EXECUTED;
- official Plugin Check on same ZIP: PENDING;
- evidence: `evidence/spec006-p650-environmental-install-runtime-pass-20260930.json`.


## P-660 security / privacy baseline

- [x] P660-01 contrato — `p660-security-privacy-baseline-contract-v1.md`.
- [x] P660-02 inventory tool — `tools/homologation/spec006/inventory-p660-security-privacy.py`.
- [x] P660-03 static baseline contract — `tests/unit/spec006-p660-security-baseline.php`.
- [x] P660-04 first remediation — removed Host header trust from public login redirect.
- [x] P660-05 package hygiene correction — orphan G-240 acceptance harness removed from distribution.
- [x] P660-06 environmental smoke on hardened package — p650.2 PASS.
- [ ] P660-07 official Plugin Check / residual security disposition — residual disposition concluída; runner oficial preso ao SHA do p650.3, execução pendente.
- [ ] P660-08 closeout.

P660 baseline inventory on p650.1:
- 84 PHP scanned;
- 25 files flagged for review;
- SECRET_CRITICAL = 0;
- NETWORK_HIGH = 0;
- MUTATION_HIGH = 0;
- DB_HIGH = 0;
- remaining signals are review-level, not proven vulnerabilities.

Hardened package:
- build: `p650.2`;
- SHA-256: `1d48ecc25d2f1bbe173ab83e6368f46306c5de054cdd19b411945c328b6051f7`;
- 102 files / 83 PHP;
- PHP lint: 83/83 PASS;
- deterministic build: PASS;
- `class-real-content-acceptance.php`: excluded;
- HTTP_HOST trust: removed;
- evidence: `evidence/spec006-p650-p660-hardened-package-p6502-20260930.json`.


P650.2 / P660 environmental smoke — 2026-09-30:
- exact artifact SHA-256: `1d48ecc25d2f1bbe173ab83e6368f46306c5de054cdd19b411945c328b6051f7`;
- user report: testado, tudo funcionando aparentemente sem problemas;
- hardened runtime smoke: PASS;
- P660-06: PASS;
- P650 rollback: still NOT EXECUTED;
- official Plugin Check: still PENDING;
- evidence: `evidence/spec006-p6502-environmental-smoke-pass-20260930.json`;
- residual security disposition: `p660-residual-security-disposition-20260930.md`;
- local official Plugin Check runner: `tools/homologation/spec006/run-p650-p660-plugin-check-local.py`.


## P-670 premium foundation acceptance

- [x] P670-01 acceptance contract preparado — `p670-premium-foundation-acceptance-contract-v1.md`.
- [x] P670-02 preflight fail-closed implementado — `tools/homologation/spec006/validate-p670-preflight.py`; execução permanece bloqueada até as evidências finais existirem.
- [x] P670-03 contrato estático do preflight — `tests/unit/spec006-p670-preflight-contract.php`.
- [x] P670-04 plano de Master Ledger preparado — `p670-master-ledger-disposition-plan.md`; NÃO aplicado.
- [x] P670-05 gerador de closeout candidate preparado — `generate-p670-closeout-candidate.py`.
- [ ] P670-06 executar preflight real — depende de P640/P650/P660/Plugin Check/rollback PASS.
- [ ] P670-07 revisão humana do Ledger e closeout SPEC-006.

P670 não autoriza 1.0.0, cutover, retirement ou bulk migration.


P650.3 hardening — 2026-09-30:
- artifact: `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip`;
- SHA-256: `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`;
- request boundary hardening applied to admin/public handlers;
- PHP lint: 83/83 PASS;
- deterministic build: PASS;
- engineering/lab files: 0;
- P660 heuristic inventory: SECRET_CRITICAL=0 / NETWORK_HIGH=0 / MUTATION_HIGH=0 / DB_HIGH=0;
- environmental smoke: PENDING because runtime code changed from p650.2;
- official Plugin Check must target p650.3 after environmental PASS;
- evidence: `evidence/spec006-p650-p660-hardened-package-p6503-20260930.json`.


P650.3 environmental acceptance — 2026-09-30:
- exact artifact SHA-256: `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`;
- user report: tudo continua funcionando aparentemente;
- runtime smoke: PASS;
- p650.3 is now FROZEN for Plugin Check/rollback;
- Plugin Check runner rejects SHA mismatch;
- rollback runner compares post_content and BDC meta SHA-256 fingerprints without exporting content;
- runbook: `p650-p660-final-local-gates-runbook.md`.


Final local gate hardening — 2026-09-30:
- P640 admissible local package advanced to `p640.2`; historical p640.1 remains invalidated/non-admissible;
- P640 evidence now records `plugin_tree_sha`;
- P650 local validator rejects stale P640 evidence when plugin tree differs;
- P650 local validator enforces frozen p650.3 SHA-256;
- P660 canonical local validator added: `tools/homologation/spec006/validate-p660-local.py`;
- P660 local gate executes static security contract, affected PHP lint, affected WPCS, security inventory and high-risk contract;
- Windows final orchestrator added: `tools/homologation/spec006/run-spec006-final-gates.ps1`;
- current assistant runtime remains BLOCKED_LOCAL_TOOLING (Composer/PHPCS/PHPUnit/WP-CLI absent);
- `composer.lock` is not versioned; automatic dependency bootstrap is therefore explicit, not silent;
- evidence: `evidence/spec006-final-local-gates-preflight-blocked-runtime-20260930.json`.


P640 environmental reconciliation decision — 2026-09-30:
- não exigir instalação de um package técnico p640.2 somente para runtime proof;
- local quality continua gerando p640.2 para proveniência/gate;
- runtime ambiental é reconciliado no próprio p650.3 já homologado;
- runner: `tools/homologation/spec006/run-p640-environmental-reconciliation-local.py`;
- valida classes/módulos/hooks e ausência de Engineering_Module_Loader;
- read-only probe; nenhuma mutation/migration;
- evidence esperada: `evidence/spec006-p640-environmental-reconciliation-current.json`.


P670 final closeout artifacts prepared — 2026-09-30:
- `p670-final-closeout-template.md`;
- `p670-final-evidence-template.json`;
- both are TEMPLATE/NOT EVIDENCE until final gates PASS;
- SPEC007 handoff plan prepared but SPEC007 remains NOT ACTIVE.


Final gate execution attempt — 2026-09-30:
- current runtime inspected and execution attempted;
- p650.3 SHA-256 MATCH;
- single root PASS;
- 102 files / 83 PHP;
- PHP lint 83/83 PASS;
- forbidden repo paths = 0;
- engineering/lab named files = 0;
- Engineering_Module_Loader absent from package/bootstrap;
- Search/Public Experience/Word Cloud production flags present/enabled;
- plugin runtime tree still `2a205aa5ef1804f4f3da30f3f9832709d25bc800`;
- Composer/PHPCS/PHPUnit/WP-CLI unavailable in the current assistant runtime;
- no WordPress runtime/project checkout is exposed here;
- external Git DNS unavailable;
- therefore P640 WPCS/PHPUnit, P640 environmental reconciliation, official Plugin Check, rollback and P670 remain BLOCKED_ENVIRONMENTAL, not failed;
- evidence: `evidence/spec006-final-gates-execution-attempt-20260930.json`.


WordPress click-to-run companion — 2026-09-30:
- artifact: `bdc-spec006-final-gates-runner-1.0.0.zip`;
- SHA-256: `3e34569d1eb60eae5c2fd89e14dd07b5b683d5c744bbff9cff664c4d6e5ba531`;
- separate temporary plugin; p650.3 remains unchanged;
- native package/runtime identity + fingerprint;
- official Plugin Check via WP Admin AJAX;
- resumable rollback p650.3 -> p650.2 -> p650.3;
- final JSON download;
- PHPCS/WPCS + PHPUnit remain external tooling;
- contract: `spec006-wordpress-click-runner-contract-v1.md`;
- evidence: `evidence/spec006-wordpress-click-runner-package-20260930.json`.


Click-runner 1.0.1 — Plugin Check transport diagnostics:
- first full execution reached native integrity/runtime PASS then failed with `Unexpected end of JSON input`;
- classified as controlled transport/server-completion incident, not BDC regression;
- runner 1.0.1 captures endpoint/stage/check, HTTP status, Content-Type, body length/preview and parse error;
- partial JSON can now be downloaded even when blocked;
- package: `bdc-spec006-final-gates-runner-1.0.1.zip`;
- SHA-256: `e497465f1d4dbea2bd5ac548b8c008f357cad173501a9b15dc5b04033cac5982`;
- p650.3 remains unchanged.


Click-runner 1.0.2 — Runtime_Environment_Setup probe:
- Plugin Check setup-runtime returned HTTP 200 with empty body in homologation;
- official code path confirmed: temporary WordPress tables + object-cache drop-in preparation;
- runner 1.0.2 probes `can_set_up()`, `is_set_up()`, filesystem method, custom user-table constants, object-cache/drop-in state and temporary `pc_` tables;
- automatic continuation is allowed only when runtime isolation is objectively complete (temporary tables + active Plugin Check drop-in);
- no blind bypass of setup-runtime;
- post-cleanup probe added;
- artifact SHA-256: `3fb06eee21b7b01d791486c968e37569bf3a114912682ec9a5b4f5a3d211f43c`.


Click-runner 1.0.3 — safe Plugin Check runtime recovery:
- observed 1.0.2: Plugin Check 2.1.0 setup created 12 temp tables but did not leave official object-cache drop-in;
- recovery does not bypass runtime isolation;
- requires complete expected temp table set;
- requires temp active_plugins option with BDC + Plugin Check;
- requires FS direct, no CUSTOM_USER_TABLE/META, no DISALLOW_FILE_MODS, no existing object-cache;
- copies only installed Plugin Check official `drop-ins/object-cache.copy.php`;
- requires byte-exact SHA equality after copy;
- new request must observe Plugin Check drop-in constant before runtime checks continue;
- companion emergency cleanup uses official `Runtime_Environment_Setup::clean_up()`;
- residual drop-in may be removed only when byte-exact to official source;
- package: `bdc-spec006-final-gates-runner-1.0.3.zip`;
- SHA-256: `9693a83970c46fbb88201c3f835ea651b99bc489011f8806708ac597f65a56e9`;
- static contract 21/21 PASS.

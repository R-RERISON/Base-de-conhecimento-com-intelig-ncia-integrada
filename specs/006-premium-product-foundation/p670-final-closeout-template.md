# P-670 — Premium Foundation Closeout

**Status:** TEMPLATE / NÃO USAR COMO PASS SEM EVIDÊNCIA  
**Data:** YYYY-MM-DD  
**Artifact:** `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip`  
**SHA-256:** `a76addbace25a8b00d8a0646ba7034952dfa941c3636e21c5162644a5b4636c2`

## 1. Preconditions

Preencher somente a partir das evidências finais:

- P640 local: PASS / FAIL;
- P640 environmental reconciliation: PASS / FAIL;
- P650 local package quality: PASS / FAIL;
- P650 environmental smoke: PASS / FAIL;
- P650 rollback: PASS / FAIL;
- P660 local security/privacy: PASS / FAIL;
- Plugin Check official: PASS / FAIL;
- P670 preflight: PASS_PRECONDITIONS / BLOCKED.

## 2. Source and artifact identity

- source commit:
- plugin_tree_sha:
- package name:
- package SHA-256:
- deterministic build:
- package single root:
- engineering/lab files:
- forbidden repo-only paths:

PASS exige identidade coerente entre source/plugin tree/evidências e o p650.4 congelado.

## 3. Quality

Registrar:

- PHP lint;
- WPCS P640 affected runtime;
- PHPUnit;
- WPCS P660 affected runtime;
- static contracts P640/P650/P660/P670;
- package checksum;
- Plugin Check.

Não afirmar full-plugin WPCS-clean enquanto a dívida histórica global não tiver sido eliminada.

## 4. Environmental runtime

Registrar:

- WordPress version;
- PHP version;
- p650.4 smoke;
- Workspace;
- Search;
- Public Experience;
- Word Cloud;
- P640 runtime module reconciliation;
- fatal/errors.

## 5. Security/privacy

Registrar:

- critical secrets;
- network/SSRF high risk;
- mutation high risk;
- DB high risk;
- residual Plugin Check findings e disposition;
- qualquer waiver localizado, se houver.

Nenhum waiver global é permitido.

## 6. Rollback/data preservation

Registrar:

- previous package;
- candidate package;
- rollback install;
- restore candidate;
- `post_content` deterministic SHA equality;
- BDC meta deterministic SHA equality;
- `data_preserved=true`.

## 7. Master Ledger disposition

Aplicar somente se sustentado por P670 preflight + revisão humana:

| ID | Before | Final | Evidence |
|---|---|---|---|
| PROD-003 | PARTIAL |  |  |
| PROD-004 | PARTIAL |  |  |
| PROD-005 | PARTIAL |  |  |
| PROD-006 | PARTIAL |  |  |

Não alterar linhas GRE/KB2/ASI/ENV por inferência.

## 8. Explicit boundaries

O closeout da SPEC-006 deve manter:

- `version_1_0_authorized=false`;
- `cutover_authorized=false`;
- `retirement_authorized=false`;
- `bulk_migration_authorized=false`;
- nenhuma deleção de storage legado;
- nenhuma ativação de semantic/vector/AI.

## 9. Known debt routed forward

Registrar explicitamente:

- ausência atual de `composer.lock`, se ainda aplicável;
- dívida histórica full-plugin WPCS, sem confundir com files changed quality;
- gaps do Master Ledger pertencentes a SPEC-007+;
- qualquer finding Plugin Check aceito com justification.

## 10. Decision

Somente um dos estados:

- `PASS / SPEC-006 CLOSED`;
- `BLOCKED / REMEDIATION REQUIRED`.

Se PASS:

- atualizar Master Ledger;
- atualizar PROJECT_MANIFEST/ROADMAP;
- finalizar CONTINUIDADE;
- preparar branch/base da SPEC-007;
- manter public cutover/retirement bloqueados até SPEC-014.

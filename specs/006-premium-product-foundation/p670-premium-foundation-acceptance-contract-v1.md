# P-670 — Premium Foundation Acceptance Contract v1

**Status:** PREPARED  
**Data:** 2026-09-30  
**Pré-condições de fechamento:** P-640, P-650 e P-660 fechados com evidência admissível.

## Objetivo

Consolidar a SPEC-006 como Premium Product Foundation comprovada, sem antecipar cutover, retirement ou versão 1.0.0.

## Fontes normativas

- `docs/PREMIUM-PLUGIN-PRODUCT-STANDARD.md`;
- `docs/DEFINITION-OF-DONE.md`;
- `specs/MASTER-FUNCTIONAL-PARITY-LEDGER.md`;
- contratos P-600..P-660;
- evidências locais e ambientais da SPEC-006.

## P670-01 — Preflight de evidência

Exigir:

- P-600 PASS;
- P-610 PASS;
- P-620 disposition PASS;
- P-630 PASS;
- P-640 PASS;
- P-650 PASS;
- P-660 PASS;
- `CONTINUIDADE.md` atualizado;
- Master Ledger coerente.

Qualquer PASS baseado em evidência invalidada, remota não admitida ou package diferente do homologado bloqueia P-670.

## P670-02 — Quality gate consolidado

Verificar:

- PHP lint;
- WPCS no runtime afetado;
- PHPUnit aplicável;
- análise estática/baseline;
- integração WordPress real;
- regressão;
- Plugin Check oficial;
- build determinístico;
- package integrity;
- checksum;
- environmental smoke;
- install/upgrade/rollback;
- nenhuma dívida de laboratório no ZIP.

## P670-03 — Security/privacy

Exigir:

- capability/nonce nos mutations;
- input sanitization/unslash;
- output escaping/disposition localizada;
- SQL user-controlled não preparado = 0;
- secrets no package = 0;
- SSRF/network high = 0 ou waiver explícito;
- nenhuma mutation destrutiva por GET.

## P670-04 — Runtime/product foundation

Verificar:

- composition root pequeno;
- product module ownership explícito;
- engenharia fora do ZIP production;
- Search/Public Experience/Word Cloud preservados;
- nenhum framework/container desnecessário;
- metadata/license/readme/changelog/upgrade coerentes.

## P670-05 — Editorial/data invariants

Exigir:

- nenhuma escrita silenciosa em `post_content`;
- nenhuma escrita em `_elementor_data` sem gate;
- nenhuma remoção automática de legado;
- nenhuma migração destrutiva;
- dados canônicos preservados;
- rollback conhecido.

## P670-06 — Ledger disposition

P-670 pode promover somente capabilities comprovadas pela SPEC-006.

Candidatos:
- `PROD-003` — somente se tooling final admissível comprovar;
- `PROD-004` — somente após Plugin Check do mesmo artefato;
- `PROD-005` — somente após P-640 local PASS + runtime ambiental;
- `PROD-006` — somente após package pruning + environmental/Plugin Check.

Nenhuma linha funcional GRE/KB2/ASI deve ser promovida por inferência de foundation.

## P670-07 — Limites

P-670 NÃO autoriza:

- versão 1.0.0;
- public cutover;
- ASI/GRE/KB2 retirement;
- deleção de storage legado;
- bulk migration;
- semantic/vector/AI activation.

Esses itens permanecem nos gates posteriores, especialmente SPEC-014 para cutover/retirement.

## Critério PASS

P-670 fecha somente quando todos os blockers aplicáveis da SPEC-006 estiverem resolvidos ou formalmente roteados para SPEC posterior sem violar o DoD da foundation.

## Artefato de closeout

Gerar:
- `evidence/spec006-p670-premium-foundation-pass-YYYYMMDD.json`;
- `specs/006-premium-product-foundation/p670-closeout-YYYYMMDD.md`;
- atualização do Master Ledger;
- atualização final do `CONTINUIDADE.md`.



## Automação fail-closed preparada

Ferramentas:

- `tools/homologation/spec006/validate-p670-preflight.py`;
- `tests/unit/spec006-p670-preflight-contract.php`;
- `tools/homologation/spec006/generate-p670-closeout-candidate.py`;
- `specs/006-premium-product-foundation/p670-master-ledger-disposition-plan.md`.

### Política de evidência histórica

P600/P610/P620 permanecem como histórico da evolução da SPEC, inclusive com execuções remotas antigas.

O P670 não usa essas execuções remotas para fechar os gates atuais. As propriedades materiais são reprovadas localmente por:

- P640: WPCS/PHPUnit/static/build/proveniência;
- P650: deterministic package/package integrity/checksum;
- P660: WPCS/security inventory/static;
- Plugin Check oficial local sobre o p650.3 congelado;
- rollback local com fingerprints de dados.

Assim, a evidência histórica é preservada sem transformar GitHub Actions em executor admissível dos gates finais.

### Resultado automático permitido

O validator P670 pode produzir somente:

- `PASS_PRECONDITIONS`; ou
- `BLOCKED`.

`PASS_PRECONDITIONS` não é fechamento automático da SPEC.

O gerador posterior pode produzir somente:

- `READY_FOR_HUMAN_LEDGER_REVIEW`.

Nem validator nem generator:

- alteram o Master Ledger;
- fecham SPEC-006;
- autorizam 1.0.0;
- autorizam cutover;
- autorizam retirement;
- autorizam bulk migration.

### Artefato congelado

P670 exige:

- `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip`;
- SHA-256 `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`.

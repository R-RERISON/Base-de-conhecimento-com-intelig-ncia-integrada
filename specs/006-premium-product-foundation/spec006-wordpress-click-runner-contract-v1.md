# SPEC-006 — WordPress Click-to-Run Final Gates Contract v1

**Status:** IMPLEMENTED / HOMOLOGATION TOOL  
**Data:** 2026-09-30  
**Produto BDC sob teste:** `p650.3` — não modificado.

## Decisão arquitetural

Os gates ambientais finais passam a ter um companion plugin temporário, separado do BDC:

`BDC SPEC-006 Final Gates Runner`

Motivo:
- preservar o `p650.3` byte a byte;
- executar os gates no mesmo WordPress usado pelo Product Owner;
- manter o runner ativo durante o ciclo de rollback;
- não levar engenharia para o ZIP de produção.

## Artefatos embarcados

O companion contém cópias locais congeladas de:
- `p650.2` — SHA-256 `1d48ecc25d2f1bbe173ab83e6368f46306c5de054cdd19b411945c328b6051f7`;
- `p650.3` — SHA-256 `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`.

Também contém manifests SHA-256 arquivo-a-arquivo de ambos.

## Click-to-run

A tela WordPress executa:

1. identidade byte-a-byte do BDC instalado contra o manifest do p650.3;
2. runtime modular Search/Public Experience/Word Cloud;
3. fingerprint editorial inicial;
4. Plugin Check oficial via os mesmos endpoints AJAX da UI oficial do Plugin Check;
5. rollback resumível `p650.3 -> p650.2 -> p650.3`;
6. fingerprint no p650.2 e após restauração;
7. identidade byte-a-byte final do p650.3;
8. JSON consolidado para download.

## Plugin Check

O companion:
- detecta o plugin oficial `plugin-check`;
- pode instalar/ativar explicitamente após clique do administrador;
- usa o fluxo Admin AJAX oficial;
- não habilita experimental;
- não usa AI;
- não usa PCP Ignore;
- captura errors/warnings para disposition.

Resultado:
- `PASS_CLEAN` quando errors=0 e warnings=0;
- `REVIEW_REQUIRED` quando existe finding a revisar.

## Rollback

O rollback é stateful/resumível:
- fingerprint antes;
- instalar p650.2 via `Plugin_Upgrader` com overwrite explícito;
- verificar manifest p650.2;
- fingerprint;
- restaurar p650.3;
- verificar manifest p650.3;
- fingerprint;
- exigir igualdade de `post_content` e metas BDC.

Se houver interrupção, o companion permanece ativo e o fluxo pode ser retomado.

## Segurança

- somente usuários `manage_options`;
- install/activate Plugin Check exige `install_plugins` + `activate_plugins`;
- rollback exige `update_plugins`;
- nonce em todo AJAX;
- zero AJAX nopriv;
- nenhuma mutation por GET.

## Limite

Este companion NÃO simula:
- PHPCS/WPCS;
- PHPUnit.

Esses gates continuam `EXTERNAL_TOOLING_REQUIRED`.

Portanto um PASS do companion fecha a parcela WordPress/environmental/Plugin Check/rollback, mas não fecha sozinho todo o P640/P670.

## Uso

Somente homologação. O companion deve ser removido após gerar e versionar o JSON final.

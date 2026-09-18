# Package — SPEC-005 R-510/T511

Build: `0.5.0-r510-t511.1`  
SHA-256: `5cbcb79c4332e32d4b4e6fcbc334e6044df712073b374b3094ded182470f1d99`

## Objetivo

Medir as Golden Queries ativas do ASI contra três baselines WordPress, sem aceitar ou alterar as expectativas.

Modos:
1. `admin_current` — scope administrativo + `modified DESC`;
2. `admin_relevance` — mesmo scope administrativo, sem override de ordenação;
3. `publish_native` — publish-only, busca nativa.

## Validação local

- 45 arquivos;
- 40 PHP;
- 40/40 PHP lint;
- 39/39 active requires;
- missing active requires = 0;
- ZIP integrity PASS;
- deterministic rebuild PASS;
- T510 OFF/fora do artefato;
- T511 ON/presente;
- class/bootstrap Git blob parity PASS;
- forbidden write/network calls = 0.

## Execução ambiental

Instalar sobre T510 e acessar:

`Base de Conhecimento -> Golden Baseline R-510`

ou:

`/wp-admin/admin.php?page=bdc-kb-spec005-r510-golden-baseline`

PASS de segurança esperado:

`gate_result.t511_read_only_safety_pass=true`.

R-510 continuará OPEN após T511; T513 human review e T514 diversidade ainda são obrigatórios.

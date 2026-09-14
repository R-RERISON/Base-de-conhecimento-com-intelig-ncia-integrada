# Matriz de Evidência — SPEC-001

> Estado atual: **G-001, G-020, G-070, G-110 e B-006 PASS no ambiente WordPress real. Package limpo `0.1.0-rc.1` preparado; G-130 aguarda lifecycle real do RC.**

| Gate | Aplicabilidade | Evidência exigida | Estado atual | Gate de saída |
|---|---|---|---|---|
| T040 Unitário determinístico | MUST | contrato, validação, diff, no-op e máquina B-006 isolada | PASS — 15/15 | pré-requisito de integração |
| G-001 Editorial/Elementor | MUST | before/after `_elementor_data`, `post_content`, `post_title`; HTTP/browser | **PASS** — onclick, G-110 e G070-HTTP preservaram editorial | Homologação |
| G-020 Summary | MUST | read/save/omit/empty/delete/no-op/allowlist/limite/sanitização/read-after-write | **PASS** — onclick real + persistência/releitura G110-A01 | Homologação |
| G-070 Segurança/scope | MUST | capability, nonce, GET, IDOR, tipo inválido, mass assignment, XSS, handler HTTP | **PASS** — runner in-process + `dev.6` HTTP 11/11 | Homologação |
| G-110 UI/UX | MUST | wp-admin, feedback, labels, foco, teclado, viewport estreito, persistência/PRG | **PASS** — `dev.5`, 2/2 humanos + todos automáticos, viewport 671x660 | Homologação |
| G-130 Lifecycle/release | MUST quando houver pacote | package/checksum, retirada de testes, activation/deactivation/upgrade e preservação | **PREPARADO/PENDENTE** — RC1 limpo criado; execução WordPress real ainda necessária | Release candidate |
| B-006 Write composto | MUST | fault injection determinístico + Metadata API real | **PASS** — `FAIL_SAFE` e `PARTIAL_FAILURE_CRITICAL` reais | Homologação |
| Golden Queries | N/A | Search fora de escopo | N/A | — |
| IA/custo | N/A | IA fora de escopo | N/A | — |
| Analytics/privacy logging | N/A | fora de escopo | N/A | — |

## Evidências raw

- `evidencias/bdc-kb-diagnostics-20260914-182034.json` — onclick técnico: 16 PASS / 0 FAIL / 0 resíduos;
- `evidencias/bdc-kb-browser-acceptance-20260914-184551.json` — tentativa intermediária; automáticos PASS, manual não concluído;
- `evidencias/bdc-kb-browser-acceptance-20260914-193151.json` — G-110 PASS completo;
- `evidencias/bdc-kb-http-security-20260914-194454.json` — G-070 HTTP PASS 11/11.

## G-070 HTTP `dev.6`

Confirmado em WordPress 6.9.4 / PHP 8.5.10:

- GET -> 405;
- nonce ausente/inválido -> bloqueio;
- payload ausente -> bloqueio;
- mass assignment -> bloqueio;
- `page` e ID inexistente -> bloqueio seguro;
- XSS via handler -> sanitizado;
- requests rejeitados -> zero alteração dos campos canônicos;
- editorial preservado;
- duas fixtures hard-deleted;
- zero resíduos.

## T044/G-130 RC1

Package `0.1.0-rc.1` preparado com somente seis arquivos de produto. Nenhuma classe, flag, hook ou marker de homologação está presente. PHP lint 5/5 e scan de strings temporárias PASS.

SHA-256: `c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`.

Ainda é obrigatório executar o lifecycle real no WordPress antes de declarar G-130 PASS.

# Matriz de Evidência — SPEC-001

> Estado final: **todos os gates MUST da SPEC-001 estão PASS no escopo de desenvolvimento/homologação. `0.1.0-rc.1` foi instalado e exercitado no WordPress real, incluindo lifecycle de desativação/reativação.**

| Gate | Aplicabilidade | Evidência exigida | Estado final | Observação |
|---|---|---|---|---|
| T040 Unitário determinístico | MUST | contrato, validação, diff, no-op e máquina B-006 isolada | **PASS — 15/15** | pré-requisito atendido |
| G-001 Editorial/Elementor | MUST | before/after `_elementor_data`, `post_content`, `post_title`; HTTP/browser | **PASS** | onclick, browser e HTTP preservaram editorial |
| G-020 Summary | MUST | read/save/omit/empty/delete/no-op/allowlist/limite/sanitização/read-after-write | **PASS** | Metadata API real + browser save/reload |
| G-070 Segurança/scope | MUST | capability, nonce, GET, IDOR, tipo inválido, mass assignment, XSS, handler HTTP | **PASS** | runner in-process + HTTP `dev.6` 11/11 |
| G-110 UI/UX | MUST | wp-admin, feedback, labels, foco, teclado, viewport estreito, persistência/PRG | **PASS** | `dev.5`, viewport mínimo 671x660 |
| G-130 Lifecycle/release | MUST quando houver pacote | package/checksum, retirada de testes, activation/deactivation e preservação | **PASS** | `0.1.0-rc.1` substituído, ativado, desativado, reativado e dados preservados |
| B-006 Write composto | MUST | fault injection determinístico + Metadata API real | **PASS** | `FAIL_SAFE` e `PARTIAL_FAILURE_CRITICAL` reais |
| Golden Queries | N/A | Search fora de escopo | N/A | — |
| IA/custo | N/A | IA fora de escopo | N/A | — |
| Analytics/privacy logging | N/A | fora de escopo | N/A | — |

## Evidências raw

- `evidencias/bdc-kb-diagnostics-20260914-182034.json` — onclick técnico: 16 PASS / 0 FAIL / 0 resíduos;
- `evidencias/bdc-kb-browser-acceptance-20260914-184551.json` — tentativa intermediária preservada para rastreabilidade;
- `evidencias/bdc-kb-browser-acceptance-20260914-193151.json` — G-110 PASS completo;
- `evidencias/bdc-kb-http-security-20260914-194454.json` — G-070 HTTP PASS 11/11;
- `evidencia-g130-rc1-s003.md` — confirmação manual estruturada do lifecycle do RC limpo.

## Package homologado

`base-conhecimento-inteligencia-integrada-0.1.0-rc.1.zip`

SHA-256:
`c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`

O pacote contém somente:

- `base-conhecimento-inteligencia-integrada.php`;
- `includes/class-plugin.php`;
- `includes/class-meta-contract.php`;
- `includes/class-summary-store.php`;
- `includes/class-admin-page.php`;
- `assets/css/admin.css`.

Nenhuma classe, flag, hook, fixture marker ou painel de homologação permanece no runtime de produto.

## Decisão

A evidência é suficiente para declarar **SPEC-001 CONCLUÍDA no ciclo de desenvolvimento/homologação**.

Não há autorização implícita para produção. Antes de cutover devem retornar B-003/preflight, identificação de writers/consumers e decisão explícita de single-writer/coexistência.

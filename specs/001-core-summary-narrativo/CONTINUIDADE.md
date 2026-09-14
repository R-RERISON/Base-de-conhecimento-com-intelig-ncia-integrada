# Prompt de Continuidade — SPEC-001 em fechamento de homologação

## Referência

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- SPEC: `SPEC-001 — Core mínimo + Summary narrativo`
- Estado: **Homologação funcional PASS; lifecycle/package G-130 pendente no RC limpo**.
- Confirme sempre o HEAD antes de alterar.

## Estado comprovado

- SPEC-000 concluída; T097 autorizou somente SPEC-001.
- S001 DoR: PASS.
- S002 runtime mínimo: implementado.
- Unitário: 15/15 PASS.
- Instalação inicial e leitura de metas GRE existentes: PASS.
- Onclick técnico real: 16/16 PASS, 0 resíduos.
- G-001: PASS.
- G-020: PASS.
- B-006: PASS real (`FAIL_SAFE` + `PARTIAL_FAILURE_CRITICAL`).
- G-110: PASS no `0.1.0-dev.5`, viewport mínimo 671x660, 0 resíduos.
- G-070 HTTP: PASS no `0.1.0-dev.6`, 11/11, 0 resíduos.
- Nenhum conteúdo real foi modificado pelos runners de homologação.

## Evidências principais

- `evidencias/bdc-kb-diagnostics-20260914-182034.json`
- `evidencias/bdc-kb-browser-acceptance-20260914-193151.json`
- `evidencias/bdc-kb-http-security-20260914-194454.json`

## RC limpo

Foi preparado `base-conhecimento-inteligencia-integrada-0.1.0-rc.1.zip`.

SHA-256:
`c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`

O RC contém somente:

- `base-conhecimento-inteligencia-integrada.php`;
- `includes/class-plugin.php`;
- `includes/class-meta-contract.php`;
- `includes/class-summary-store.php`;
- `includes/class-admin-page.php`;
- `assets/css/admin.css`.

Foram removidos integralmente:

- `BDC_KB_HOMOLOGATION_BUILD`;
- `BDC_KB_ENABLE_DIAGNOSTICS`;
- `class-diagnostics-runner.php`;
- `class-browser-acceptance.php`;
- `class-http-security-diagnostics.php`;
- hooks/painéis/markers de teste.

Localmente: PHP lint 5/5 PASS e scan de instrumentação temporária PASS.

## Política de lifecycle

O plugin não cria schema próprio, option persistente, cron ou usuários. Os três post metas são canônicos e preexistentes/compartilhados; **uninstall não deve apagá-los**. Deactivation é não destrutiva.

## Próximo passo exato — T044B/G-130

No WordPress de homologação:

1. substituir `0.1.0-dev.6` por `0.1.0-rc.1`;
2. confirmar versão `0.1.0-rc.1` e ausência de fatal error;
3. abrir **Base de Conhecimento** e confirmar que todos os painéis/botões de diagnóstico desapareceram;
4. confirmar listagem normal e abrir um Summary existente apenas para leitura;
5. desativar o plugin;
6. ativar novamente;
7. confirmar que o menu/listagem/leituras continuam funcionando e os dados existentes permanecem intactos;
8. somente então promover G-130 para PASS.

Após G-130 PASS: executar T045 DoD final e T046 encerramento/decisão da próxima SPEC.

## Restrições

Sem Classificação, Review, Search, Analytics, IA, queue, schema, REST/AJAX/SPA ou cutover nesta SPEC. GO de homologação não é GO de produção; B-003/preflight retorna antes de qualquer produção/cutover.

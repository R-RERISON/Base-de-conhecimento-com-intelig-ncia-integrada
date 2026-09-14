# Evidência G-130 — Lifecycle `0.1.0-rc.1`

## Contexto

- Data de confirmação: 2026-09-14.
- Ambiente: WordPress de homologação já utilizado nos gates anteriores.
- Package: `base-conhecimento-inteligencia-integrada-0.1.0-rc.1.zip`.
- SHA-256: `c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`.
- Natureza da evidência: **operator assertion estruturada**.

## Procedimento solicitado

1. substituir o build `0.1.0-dev.6` pelo `0.1.0-rc.1`;
2. confirmar versão e ausência de fatal error;
3. confirmar ausência de botões/painéis de diagnóstico;
4. abrir Base de Conhecimento e confirmar listagem;
5. abrir Summary existente somente para leitura e confirmar dados;
6. desativar plugin;
7. ativar novamente;
8. confirmar menu/listagem/leitura e preservação dos dados.

## Resultado informado pelo operador

**PASS — “Funcionou conforme orientado.”**

A confirmação é aceita para G-130 porque o roteiro era fechado e binário, sem mudança de conteúdo real, e sucede evidências automatizadas já PASS para integridade, segurança e browser.

## Promoção

- package limpo: PASS;
- ausência de instrumentação temporária: PASS;
- activation/smoke: PASS;
- deactivation não destrutiva: PASS;
- reactivation: PASS;
- preservação de dados: PASS;
- G-130: **PASS**.

## Limitação

Esta evidência não autoriza produção/cutover nem prova coexistência de writers. B-003 e preflight produtivo permanecem obrigatórios.

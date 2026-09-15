# Evidência G-130 — Lifecycle real `0.2.0-rc.1`

**Data:** 2026-09-15  
**Package:** `base-conhecimento-inteligencia-integrada-0.2.0-rc.1.zip`  
**SHA-256:** `a5120299ea907d271bc39b318857ba033cf0cf340fe1836289f42a4c717b8fb5`

## Evidência do operador

O operador confirmou em WordPress real que o package RC1 funcionou corretamente conforme o checklist de lifecycle:

- substituição/instalação do RC1 sem fatal;
- versão `0.2.0-rc.1` ativa;
- ausência dos painéis/runners de homologação;
- listagem da Base de Conhecimento funcional;
- abertura de artigo funcional;
- Summary preservado;
- Classificação preservada;
- quatro vocabulários acessíveis;
- desativação do plugin sem perda dos dados;
- reativação do plugin sem fatal;
- releitura após reativação sem regressão;
- nenhuma fixture/instrumentação temporária reapareceu.

## Resultado

**G-130: PASS.**

A evidência é uma confirmação operacional humana do lifecycle real, complementando as evidências automatizadas anteriores de package, segurança, persistência e browser acceptance.

## Limite da decisão

G-130 conclui desenvolvimento/homologação da SPEC-002. Não constitui GO de produção/cutover. Coexistência produtiva de writers, preflight operacional e estratégia de rollout permanecem decisões separadas.

# Evidência G-130 — lifecycle ambiental do 0.3.0-rc.1

Data: 2026-09-15

## Contexto

Após o fechamento do G-110 e a remoção de toda a instrumentação temporária de G-070/G-110, foi produzido o package limpo `0.3.0-rc.1`.

## Evidência ambiental

O operador confirmou no ambiente real que o RC permaneceu funcionando corretamente após o smoke de lifecycle solicitado, incluindo instalação/substituição, abertura da Base de Conhecimento/Workspace e ciclo de desativação/ativação do plugin.

Esta evidência é deliberadamente registrada como **confirmação operacional**, e não como JSON automatizado: o G-130 valida lifecycle do package limpo, enquanto os gates funcionais e de segurança já possuem evidências automatizadas próprias.

## Baseline preservada

- package: `0.3.0-rc.1`;
- SHA-256 do ZIP: `7f681a3f62d792d30ccb016ae64b03e83d5cc46c4b2b1e2c2d96d41e3dfd0db5`;
- G-070: PASS 22/22, cleanup zero;
- G-110: PASS 22/22 browser + 7/7 server, cleanup zero;
- runners/flags temporários ausentes do RC;
- Stores/Contracts permanentes não foram refatorados no cleanup;
- fonte editorial permanece fora do domínio de escrita do plugin.

## Resultado

**G-130: PASS ambiental.**

A SPEC-003 está concluída e `0.3.0-rc.1` passa a ser a baseline funcional para a SPEC-004.

# SPEC-004 — Relatório Final

**Data:** 2026-09-18  
**Status:** PASS / CLOSED na branch de fechamento  
**PR:** #4 permanece DRAFT até revisão final

## Resultado

A SPEC-004 estabilizou o Content Extractor e Knowledge Document 2.1.0 e estabeleceu WordPress Core Blocks como destino editorial canônico, preservando WordPress como plataforma e Elementor como fonte legada temporária.

## Evidências principais

- G-240: PASS/CLOSED;
- UX-003: PASS ambiental;
- T100D: migração persistente real do post 358 — PASS;
- T100E-E6: 623 artigos × 2 passagens, zero errors/throwables/safety violations, determinismo e fingerprint PASS;
- G-250: lifecycle upgrade/deactivate/activate/downgrade/reinstall — PASS;
- post 358 no G-250: Gutenberg, no_action_required, journal applied, lock free, fingerprint inalterado.

## Release final candidate

Build: `0.4.0-spec004-rc2`  
SHA-256: `ac25c2ffd4a0ae2250fa2ce1a07bf07b4cad8a24030e31f12c78189e61e7506b`

Validação:
- 44 arquivos no ZIP;
- 39 PHP / 39 lint PASS;
- 38/38 active requires;
- single plugin root;
- deterministic rebuild PASS;
- G250 OFF;
- E6 OFF;
- Preflight OFF;
- T100D OFF;
- Elementor writer OFF.

## Guardrails preservados

- nenhuma migração em massa implícita;
- writes futuros permanecem post-scoped;
- mixed exige humano;
- Elementor não será removido antes de dependência zero;
- `_elementor_data` permanece preservado;
- autorização editorial continua explícita.

## Autorização e UX

O download de Authorization Pack não é a UX definitiva. Ele comprovou o contrato de autorização durante engenharia/homologação. Uma futura migração integrada deve ocorrer dentro da Workspace, com confirmação humana explícita e todos os guardrails defensivos. Essa integração não foi introduzida no fechamento para evitar regressão após G-250.


## RC2 Final Smoke

**PASS AMBIENTAL / HUMAN ACCEPTANCE — 2026-09-18.**

O usuário confirmou a instalação e teste do `0.4.0-spec004-rc2` em homologação. Nenhuma regressão foi reportada na confirmação.

Com este aceite, os gates técnicos e o smoke final de homologação da SPEC-004 estão completos. O único passo restante é a revisão/autorizaçao humana do merge do PR #4 para `main`.

# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 aprovado como referência executável.
- SPEC-003: **planejamento ativo**.
- Implementação de runtime ainda **bloqueada**.

## Próximo passo exato

Executar **S001 — Descoberta / Current State**.

Ordem:

1. inventário de código histórico disponível;
2. writer/consumer map;
3. seleção de stores candidatos;
4. profiler temporário read-only;
5. evidência JSON real no ambiente;
6. decisão R-001;
7. somente depois fechar Domain Contract R-010.

## O que NÃO fazer agora

- não criar meta canônica de review;
- não criar estado `approved/reviewed/...`;
- não criar reviewer/responsável;
- não criar histórico persistente;
- não criar score;
- não criar `AI Ready`;
- não modificar plugin RC durante descoberta além de tooling temporário explicitamente identificado.

## Hipóteses sob avaliação

- post meta para estado atual;
- user ID para reviewer quando necessário;
- custom comment type para eventos append-only;
- estado inicial neutro para artigos sem decisão.

Todas permanecem **NÃO CONTRATUAIS** até R-010 PASS.

## Gate atual

- R-001: NOT_RUN.
- R-010: BLOCKED por R-001.
- G-001/G-030/G-070/G-110/G-130: BLOCKED.

## Regra

A primeira entrega da SPEC-003 deve aumentar conhecimento sobre o estado real do ambiente, não aumentar quantidade de código permanente.

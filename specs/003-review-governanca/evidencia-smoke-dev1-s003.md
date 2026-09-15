# SPEC-003 — Evidência de Smoke Ambiental `0.3.0-dev.1`

## Objetivo

Comprovar que a entrada do runtime permanente mínimo de Review & Governança não causa regressão ambiental antes de abrir tooling de integração, handler HTTP ou UI de Review.

## Build

- versão: `0.3.0-dev.1`;
- runtime novo: `Review_Contract` + `Review_Store`;
- sem handler HTTP de Review;
- sem UI de Review;
- sem profiler S001.

## Critérios solicitados ao operador

1. plugin ativa sem fatal;
2. Base de Conhecimento abre normalmente;
3. listagem de artigos permanece funcional;
4. artigo existente abre normalmente;
5. Summary permanece íntegro;
6. Classificação permanece íntegra;
7. painel do profiler não aparece;
8. nenhuma UI de Review aparece nesta build.

## Evidência do operador

Em 15/09/2026, após executar o smoke no ambiente de homologação, o operador respondeu:

> `funcionou, pode seguir`

Esta é evidência humana de smoke, não substitui os unitários determinísticos nem o diagnóstico de integração da Comments API.

## Decisão

**G-001 ambiental: PASS.**

A SPEC-003 pode avançar para integração real do `Review_Store` com a WordPress Comments API usando exclusivamente fixtures temporárias e cleanup obrigatório.

# Plano de Mockups — UX-001

## Estratégia

Não produzir todas as telas futuras em alta fidelidade. Produzir primeiro as superfícies que definem a estrutura do produto e os componentes que as próximas SPECs reutilizarão.

## Wave 1 — estrutura

### M01 — Knowledge List

Objetivo: validar densidade, ações, filtros futuros e transição para o Workspace.

Variações:

- desktop 1440;
- estreito 782;
- empty/search-empty conceitual.

### M02 — Knowledge Workspace / Overview

Objetivo: definir o shell do artigo e a navegação interna.

Conteúdo:

- breadcrumb;
- título;
- metadata essencial;
- área de ações;
- tabs/subnav;
- resumo dos domínios implementados;
- slots visuais futuros claramente identificados.

### M03 — Knowledge Workspace / Summary

Objetivo: transportar a funcionalidade homologada da SPEC-001 para o shell definitivo sem mudar contrato.

### M04 — Knowledge Workspace / Classification

Objetivo: transportar a funcionalidade homologada da SPEC-002 para o shell definitivo sem mudar contrato.

## Wave 2 — governança

### M05 — Review & Governance

Objetivo: testar o encaixe da futura SPEC-003 no Workspace.

Regra: usar rótulos de hipótese quando estados/transições ainda não estiverem formalmente definidos.

### M06 — Activity / History

Objetivo: testar densidade e espaço do histórico, sem definir persistência.

## Wave 3 — estados transversais

### M07 — Empty states
### M08 — Permission / read-only
### M09 — Validation / system error
### M10 — Loading / stale/conflict placeholder

## Fidelity

1. Wireframe estrutural.
2. Review de arquitetura de informação.
3. Componentização.
4. Alta fidelidade apenas após aprovação estrutural.
5. Responsive variants.
6. Anotação de comportamento e handoff.

## Critérios de aprovação do Knowledge Workspace master

- não depende de página vertical infinita;
- Summary/Classificação cabem sem perder clareza;
- próxima feature pode entrar sem novo shell;
- ações primárias permanecem óbvias;
- contexto do artigo não se perde durante navegação interna;
- funciona em 1440 e 782;
- teclado/foco podem ser implementados de forma linear;
- nenhuma feature futura é apresentada como disponível;
- componentes usados pertencem ao Design System v1.

## Relação com implementação

UX-001 termina com mockups e contratos de experiência. A implementação estrutural necessária para adotar o novo Workspace deve ser planejada na SPEC funcional apropriada ou em uma SPEC técnica explicitamente autorizada; não será introduzida silenciosamente por UX-001.

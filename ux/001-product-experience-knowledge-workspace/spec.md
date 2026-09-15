# UX-001 — Product Experience & Knowledge Workspace

**Status:** ATIVA  
**Natureza:** trilha transversal de UX/UI, não substitui SPEC funcional  
**Baseline funcional:** `0.2.0-rc.1`  
**Bloqueia:** início da implementação da SPEC-003 até Gate UX-010 PASS

## Problema

O produto possui Summary e Classificação funcionais e homologados, mas a continuidade por vertical slices pode gerar uma interface longa e fragmentada se cada nova SPEC apenas acrescentar formulários à página existente.

Antes de Review & Governança, é necessário congelar uma arquitetura de informação e uma linguagem visual que permitam crescimento sem regressão de experiência.

## Objetivo

Criar uma baseline visual e de interação canônica para o produto, preservando WordPress/wp-admin como contexto operacional quando adequado, aproveitando os melhores padrões do KB2Ops e projetando um **Knowledge Workspace** escalável.

## Princípios

1. Conteúdo primeiro.
2. Progressive disclosure; evitar página vertical infinita.
3. Um workspace, vários domínios.
4. WordPress-aware, não WordPress-limited.
5. Acessibilidade por contrato.
6. Hipótese visual nunca vira regra de negócio automaticamente.
7. Legado KB2Ops com curadoria: PRESERVAR / EVOLUIR / DESCARTAR.
8. Design System antes de multiplicar telas.
9. Desktop administrativo primário; responsividade real obrigatória.
10. Sem regressão funcional de Summary/Classificação.
11. **Ferramentas externas não podem bloquear o projeto.**

## Estratégia de prototipação

A abordagem canônica é **UI as Code**:

- HTML/CSS/JS executável e versionado no Git;
- zero dependências externas para abrir o protótipo;
- sem npm/build obrigatório;
- CSS Custom Properties para tokens;
- componentes visuais reutilizáveis;
- screenshots são evidência derivada, não source of truth;
- Figma fica arquivado como referência histórica e não participa de gates.

Detalhes: `ui-as-code-strategy.md`.

## Escopo obrigatório

- arquitetura de informação;
- mapa de navegação;
- inventário de telas;
- Design System v1;
- Knowledge List;
- Knowledge Workspace master;
- Summary + Classificação dentro do Workspace;
- Review & Governance como antecipação visual controlada;
- estados empty/loading/error/success/permission;
- responsividade e acessibilidade;
- protótipo UI as Code navegável;
- matriz KB2Ops PRESERVAR/EVOLUIR/DESCARTAR.

## Fora de escopo

- implementar Review & Governança;
- alterar schema de dados;
- alterar contratos Summary/Classificação;
- construir Search/Extractor/AI;
- criar dashboard sem caso de uso aprovado;
- substituir WordPress/Elementor;
- SPA por preferência estética;
- definir AI READY.

## Tela âncora — Knowledge Workspace

A tela de artigo evolui para um workspace com:

- cabeçalho contextual;
- navegação interna por domínios;
- área principal de trabalho;
- painel contextual somente quando houver dado real;
- Summary e Classificação como domínios existentes;
- Review/Histórico apenas como reserva arquitetural até SPEC própria.

## Gates

- **UX-001:** inventário de telas e jornadas fechado.
- **UX-005:** arquitetura de informação/navegação aprovada.
- **UX-010:** Design System v1 + protótipo executável do Knowledge Workspace e Knowledge List aprovados.
- **UX-020:** Review & Governance encaixado visualmente sem criar contrato de domínio.
- **UX-030:** responsividade/acessibilidade validadas no protótipo.
- **UX-040:** benchmark KB2Ops classificado.
- **UX-050:** handoff UI as Code versionado e rastreável no Git.

## Definition of Ready para SPEC-003

SPEC-003 só pode iniciar implementação quando:

- UX-010 PASS;
- Review/Governança couber no Workspace sem novo shell;
- componentes necessários estiverem no Design System;
- responsive/accessibility rules estiverem verificadas;
- nenhuma decisão visual depender de schema ainda não decidido;
- protótipo executável estiver versionado.

## Regra de mudança

Após UX-001 congelada, mudanças estruturais de navegação/workspace exigem decisão explícita registrada. Refinamentos visuais locais podem ocorrer nas SPECs desde que não quebrem o contrato de experiência.

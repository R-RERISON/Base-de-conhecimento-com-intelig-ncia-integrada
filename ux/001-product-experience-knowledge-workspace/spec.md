# UX-001 — Product Experience & Knowledge Workspace

**Status:** ATIVA  
**Natureza:** trilha transversal de UX/UI, não substitui SPEC funcional  
**Baseline funcional:** `0.2.0-rc.1`  
**Bloqueia:** início da implementação da SPEC-003 até Gate UX-010 PASS

## Problema

O produto já possui Summary e Classificação funcionais e homologados, mas a continuidade por vertical slices pode gerar uma interface incremental, longa e fragmentada se cada nova SPEC apenas acrescentar novos formulários à página existente.

Review & Governança introduzirá estados, ações, responsáveis, histórico, qualidade e elegibilidade futura. Antes disso, é necessário congelar uma arquitetura de informação e uma linguagem visual que permita o crescimento sem regressão de experiência.

## Objetivo

Criar uma baseline visual e de interação canônica para o produto, preservando WordPress/wp-admin como contexto operacional quando adequado, aproveitando os melhores padrões do legado KB2Ops e projetando um **Knowledge Workspace** escalável para as próximas SPECs.

## Princípios

1. **Conteúdo primeiro.** A informação do artigo e sua governança devem dominar a hierarquia visual.
2. **Progressive disclosure.** Complexidade aparece quando necessária; evitar página vertical infinita.
3. **Um workspace, vários domínios.** Summary, Classificação, Review, Qualidade e Histórico devem parecer partes do mesmo produto.
4. **WordPress-aware, não WordPress-limited.** Respeitar o shell administrativo sem aceitar automaticamente padrões pobres de UX.
5. **Acessibilidade por contrato.** Teclado, foco, contraste, labels, estados e largura estreita são requisitos.
6. **Sem hipótese virando regra de negócio.** Mockup pode reservar espaço para feature futura, mas não definir comportamento de domínio não aprovado.
7. **Legado com curadoria.** Padrões KB2Ops serão classificados como PRESERVAR, EVOLUIR ou DESCARTAR.
8. **Design System antes de multiplicação.** Componentes recorrentes devem nascer do sistema, não ser redesenhados por tela.
9. **Desktop administrativo como primário, responsivo real como obrigatório.**
10. **Sem regressão funcional.** UX-001 não altera contratos Summary/Classificação homologados.

## Escopo

### Obrigatório

- arquitetura de informação;
- mapa de navegação;
- inventário de telas;
- taxonomia de estados visuais;
- Design System v1;
- Knowledge List;
- Knowledge Workspace master;
- Summary + Classification dentro do workspace;
- Review & Governance mockup de antecipação controlada;
- busca e filtros como arquitetura futura, sem implementar domínio;
- estados vazio/loading/error/success/permission;
- responsividade;
- acessibilidade;
- Figma editável;
- matriz KB2Ops PRESERVAR/EVOLUIR/DESCARTAR.

### Fora de escopo

- implementar Review & Governança;
- alterar schema de dados;
- alterar Summary/Classificação;
- construir Search/Extractor/AI;
- criar dashboard sem caso de uso aprovado;
- substituir WordPress/Elementor;
- SPA por preferência estética;
- definir AI READY.

## Tela âncora — Knowledge Workspace

A tela de artigo deve evoluir para um workspace com:

- cabeçalho contextual: breadcrumb, título, identificadores, estado e ações;
- navegação interna por domínios;
- área principal de trabalho;
- painel contextual de qualidade/governança quando aplicável;
- Summary e Classificação como domínios existentes;
- espaços reservados para Review e Histórico, explicitamente marcados como futuros enquanto não implementados.

A solução não deve resultar em uma sequência ilimitada de blocos verticais.

## Gates

### UX-001 Baseline

- **UX-001:** inventário de telas e jornadas fechado.
- **UX-005:** arquitetura de informação/navegação aprovada.
- **UX-010:** Design System v1 + Knowledge Workspace master mockup aprovados.
- **UX-020:** Review & Governance mockup coerente com a futura SPEC-003, sem criar contrato de domínio.
- **UX-030:** responsividade/acessibilidade documentadas.
- **UX-040:** benchmark KB2Ops classificado por preservar/evoluir/descartar.
- **UX-050:** artefato Figma editável e rastreável.

## Definition of Ready para SPEC-003

SPEC-003 só pode iniciar implementação quando:

- UX-010 PASS;
- estrutura de estados/ações da SPEC-003 puder ser encaixada no workspace sem novo shell;
- componentes necessários estiverem no Design System ou explicitamente previstos;
- responsive/accessibility rules estiverem fechadas;
- nenhuma decisão visual depender de schema ainda não decidido.

## Regra de mudança

Após UX-001 congelada, mudanças estruturais de navegação/workspace exigem decisão explícita registrada. Refinamentos visuais locais podem ocorrer dentro das SPECs desde que não quebrem o contrato de experiência.

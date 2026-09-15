# Design System v1 — Diretrizes Iniciais

**Status:** draft para UX-001  
**Objetivo:** impedir divergência visual entre SPECs e criar componentes reutilizáveis antes de multiplicar telas.

## Foundations

### Grid e largura

- conteúdo administrativo com largura máxima legível, evitando full-width indiscriminado;
- grid de 12 colunas para layouts complexos;
- espaçamento baseado em múltiplos de 4px;
- densidade administrativa moderada: alta eficiência sem compactação excessiva;
- breakpoint crítico operacional: 782px, alinhado ao comportamento WordPress já homologado;
- mobile abaixo disso deve preservar funções essenciais e legibilidade.

### Tipografia

- usar a stack nativa do ambiente administrativo sempre que possível;
- hierarquia limitada e previsível: page title, section title, subsection, body, helper, metadata;
- labels sempre explícitos; placeholder nunca substitui label;
- evitar excesso de caixa alta.

### Cores

- foundations devem partir da paleta administrativa existente, com tokens semânticos próprios;
- tokens obrigatórios: `surface`, `surface-muted`, `text-primary`, `text-secondary`, `border`, `primary`, `success`, `warning`, `danger`, `info`, `focus`;
- estado nunca depende exclusivamente de cor;
- contraste mínimo deve atender WCAG AA para texto e controles relevantes.

### Espaçamento

Escala proposta: 4, 8, 12, 16, 24, 32, 40, 48.

Não usar margens arbitrárias por tela quando existir token equivalente.

## Componentes essenciais

### Navigation

- breadcrumb;
- tabs/subnav de workspace;
- pagination;
- back/context link.

### Actions

- primary button;
- secondary button;
- tertiary/text action;
- destructive action;
- icon-only apenas com accessible name.

### Data entry

- text field;
- textarea;
- single select;
- multi select;
- checkbox/radio quando domínio exigir;
- helper/error text;
- field group.

### Data display

- badge/status;
- metadata pair;
- table/list row;
- card/panel;
- callout;
- legacy-reference block;
- activity item.

### Feedback

- success notice;
- warning notice;
- error notice;
- inline validation;
- loading/skeleton;
- empty state;
- permission/read-only state.

## Workspace patterns

### Header

Deve manter identidade do artigo durante toda a jornada. Campos mínimos visuais:

- breadcrumb/contexto;
- título;
- metadata essencial;
- status quando existir contrato;
- ações relacionadas ao artigo.

### Internal navigation

Preferência inicial: tabs horizontais responsivas. Alternativa: subnav lateral somente se testes de densidade mostrarem ganho claro.

### Main + context panel

Permitido em desktop quando painel contextual tiver informação realmente acionável. Em largura estreita, painel deve reflow para o fluxo principal; não usar coluna fixa que reduza controles.

## Estados obrigatórios por componente

- default;
- hover;
- focus-visible;
- active/selected;
- disabled;
- validation error;
- read-only quando aplicável.

## Critérios de componentização

Um padrão vira componente quando:

1. aparece em duas ou mais telas; ou
2. carrega comportamento de estado/feedback crítico; ou
3. precisa de consistência para acessibilidade.

## Anti-padrões proibidos

- página vertical infinita criada pela soma de SPECs;
- segundo writer só para “ficar bonito”;
- modal para fluxo principal longo;
- placeholder como label;
- cor como único indicador de estado;
- ações destrutivas próximas/visualmente equivalentes à ação principal;
- cards para toda informação sem necessidade hierárquica;
- dashboard ornamental sem pergunta operacional concreta;
- mockup que mostre feature futura como se estivesse disponível.

## Figma

O arquivo canônico deverá conter páginas:

1. `00 Foundations`
2. `01 Components`
3. `02 IA & Flows`
4. `03 Knowledge List`
5. `04 Knowledge Workspace`
6. `05 Review & Governance`
7. `06 Responsive`
8. `99 Archive`

Componentes e tokens devem ser usados nos mockups, não copiados manualmente tela a tela.

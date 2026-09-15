# Design System v1 — UX-001

**Status:** ESPECIFICAÇÃO FECHADA / componentização Figma em andamento  
**Origem:** contratos atuais + Heritage Pack KB2Ops.

## 1. Foundations

### Grid e largura

- grid lógico de 12 colunas em desktop;
- largura de conteúdo administrativo legível; evitar full-width sem necessidade;
- gutter base 24px;
- spacing scale: `4, 8, 12, 16, 24, 32, 40, 48`;
- breakpoint crítico WordPress: `782px`;
- Context Panel opcional apenas em desktop largo.

### Radius

- `radius-sm`: 10px;
- `radius-md`: 16px;
- `radius-lg`: 22px.

### Elevação

Sombras discretas e raras. Bordas definem estrutura; sombra só reforça sobreposição ou agrupamento importante.

## 2. Tokens de cor

### Brand

- `brand-navy`: `#0B1F4D`;
- `brand-blue-500`: `#1677FF`;
- `brand-blue-600`: `#0B63E5`;
- `brand-blue-700`: `#084FB8`.

### Surface

- `bg-app`: `#F6F8FB`;
- `surface`: `#FFFFFF`;
- `surface-soft`: `#FBFCFD`;
- `border`: `#DFE5EC`.

### Text

- `text-primary`: `#172033`;
- `text-secondary`: `#475467`;
- `text-muted`: `#667085`.

### Semantic

- `success`: `#14AE6C`;
- `success-bg`: `#E8F8F0`;
- `warning`: `#C98500`;
- `warning-bg`: `#FFF5DC`;
- `danger`: `#D92D20`;
- `danger-bg`: `#FEECEB`;
- `info`: `#0B63E5`;
- `focus`: `#1677FF`.

## 3. Tipografia

No runtime WordPress, usar stack administrativa compatível com o ambiente. No Figma, usar Inter como proxy visual consistente.

Hierarquia:

- Display: 40/48 bold;
- H1: 32/40 bold;
- H2: 24/32 bold;
- H3: 18–20 semibold;
- Body: 14–16 regular;
- Label: 13–14 semibold;
- Meta/helper: 12–13 regular.

Regras:

- label explícito sempre;
- placeholder não substitui label;
- caixa alta apenas para eyebrow/overline curto;
- texto operacional prioriza legibilidade sobre branding.

## 4. Componentes canônicos

### Navegação

- Breadcrumb;
- Workspace Tabs;
- Back link;
- Pagination;
- Filter bar.

### Ações

- Button Primary;
- Button Secondary;
- Button Tertiary/Text;
- Button Danger;
- Icon Button com accessible name;
- Overflow Menu.

### Dados / estado

- Status Badge;
- Metadata Pair;
- Dense Table Row;
- Card/Panel;
- Callout;
- Activity Item;
- Legacy Reference Block.

### Entrada

- Text Field;
- Textarea;
- Single Select;
- Multi Select;
- Checkbox/Radio quando houver contrato;
- Helper Text;
- Validation Message;
- Field Group.

### Feedback

- Success Notice;
- Warning Notice;
- Error Notice;
- Inline Validation;
- Empty State;
- Loading/Skeleton;
- Permission State;
- Read-only State.

## 5. Estados obrigatórios

Todo componente interativo relevante deve prever:

- default;
- hover;
- focus-visible;
- active/selected;
- disabled;
- validation error;
- success quando aplicável;
- read-only quando aplicável.

## 6. Data density

### Curadoria

Preferência por densidade média-alta:

- tabelas/listas para conjuntos grandes;
- cards apenas para agrupamento ou resumo;
- metadata secundária não deve competir com título/ação;
- linha de tabela deve suportar leitura rápida e navegação por teclado.

### Workspace

- uma tarefa principal por domínio ativo;
- até duas colunas em formulários desktop;
- uma coluna <=782px;
- painel lateral nunca reduz o formulário abaixo de largura confortável.

## 7. Semântica de status

Status sempre combina pelo menos dois sinais entre:

- texto;
- ícone;
- forma/badge;
- cor.

Não usar vermelho/verde como única diferenciação.

## 8. Acessibilidade

- WCAG AA para texto/controles relevantes;
- foco perceptível;
- targets adequados;
- ordem DOM acompanha ordem visual;
- tabs operáveis por teclado;
- mensagens de erro associadas ao controle;
- icon-only com nome acessível;
- helper text não contém informação crítica exclusivamente visual.

## 9. Padrões do Knowledge Workspace

### Context Header

Título, ID, contexto editorial e ações secundárias.

### Tabs

Decisão v1: horizontais. Runtime atual: Visão geral, Summary e Classificação. Futuro Review/Histórico não aparecem como disponíveis antes do contrato.

### Main Work Area

Domínio ativo e writer canônico correspondente.

### Context Panel

Opcional e apenas com informação real. Reflow em largura estreita.

## 10. Anti-padrões bloqueados

- página vertical infinita;
- segundo writer por estética;
- modal para fluxo principal longo;
- placeholder como label;
- cor como único estado;
- ação destrutiva equivalente à primária;
- cards para tudo;
- dashboard ornamental;
- feature futura parecendo disponível;
- reintrodução de schema legado via mockup.

## 11. Figma canônico

Arquivo: `UX-001 — Product Experience & Knowledge Workspace`  
File key: `myCK7Aq0ih8C55ejcRZFVz`  
URL: `https://www.figma.com/design/myCK7Aq0ih8C55ejcRZFVz`

O Master Board já contém Foundations, Knowledge List, Knowledge Workspace, conceito de Review, responsive 782px e anti-padrões. A próxima wave é componentizar e elevar Workspace/List para alta fidelidade.

## Resultado

A especificação textual de Design System v1 está fechada. O Gate UX-010 permanece dependente da componentização e aprovação dos Master Mockups em Figma.
# Design System v1 — UX-001

**Status:** ESPECIFICAÇÃO FECHADA / implementação UI as Code ativa  
**Origem:** contratos atuais + Heritage Pack KB2Ops.

## 1. Foundations

### Grid e largura

- grid lógico de 12 colunas em desktop;
- largura de conteúdo administrativo legível;
- gutter base 24px;
- spacing: `4, 8, 12, 16, 24, 32, 40, 48`;
- breakpoint crítico WordPress: `782px`;
- Context Panel opcional apenas em desktop largo.

### Radius

- `radius-sm`: 10px;
- `radius-md`: 16px;
- `radius-lg`: 22px.

### Elevação

Bordas definem estrutura; sombra discreta só reforça agrupamento relevante.

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

No runtime WordPress, usar stack administrativa compatível com o ambiente. No protótipo UI as Code, usar `Inter, Segoe UI, Roboto, Arial, sans-serif`, sem exigir webfont externa.

Hierarquia:

- Display: 40/48 bold;
- H1: 32/40 bold;
- H2: 24/32 bold;
- H3: 18–20 semibold;
- Body: 14–16 regular;
- Label: 13–14 semibold;
- Meta/helper: 12–13 regular.

## 4. Design System como código

Os tokens devem existir como CSS Custom Properties no protótipo e posteriormente ser traduzidos para o CSS do plugin.

Exemplos canônicos:

- `--brand-navy`;
- `--blue`;
- `--bg`;
- `--surface`;
- `--surface-soft`;
- `--text`;
- `--muted`;
- `--border`;
- `--success`, `--warning`, `--danger`;
- `--radius`;
- `--focus`.

Não há dependência de Tailwind, Bootstrap, npm ou biblioteca externa.

## 5. Componentes canônicos

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

## 6. Estados obrigatórios

- default;
- hover;
- focus-visible;
- active/selected;
- disabled;
- validation error;
- success quando aplicável;
- read-only quando aplicável.

## 7. Data density

### Curadoria

- tabelas/listas para conjuntos grandes;
- cards apenas para agrupamento ou resumo;
- metadata secundária não compete com título/ação;
- linha de tabela suporta leitura rápida e teclado.

### Workspace

- uma tarefa principal por domínio ativo;
- até duas colunas em desktop;
- uma coluna <=782px;
- painel lateral nunca reduz o formulário abaixo de largura confortável.

## 8. Semântica de status

Status combina pelo menos dois sinais entre texto, ícone, forma/badge e cor. Vermelho/verde nunca são a única diferenciação.

## 9. Acessibilidade

- WCAG AA para texto/controles relevantes;
- foco perceptível;
- ordem DOM acompanha ordem visual;
- tabs operáveis por teclado;
- mensagens de erro associadas ao controle;
- icon-only com nome acessível.

## 10. Padrões do Knowledge Workspace

### Context Header

Título, ID, contexto editorial e ações secundárias.

### Tabs

Horizontais. Runtime atual: Visão geral, Summary e Classificação. Review/Histórico não aparecem como disponíveis antes do contrato.

### Main Work Area

Domínio ativo e writer canônico correspondente.

### Context Panel

Opcional e apenas com informação real. Reflow em largura estreita.

## 11. Anti-padrões bloqueados

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

## 12. Artefato canônico

O artefato executável passa a ser `prototype/index.html`, descrito em `ui-as-code-strategy.md`.

O arquivo Figma criado durante a exploração fica arquivado como referência histórica e não participa de gate nem continuidade.

## Resultado

A especificação textual do Design System v1 está fechada. UX-010 depende agora do QA do protótipo UI as Code, e não de componentização em ferramenta externa.

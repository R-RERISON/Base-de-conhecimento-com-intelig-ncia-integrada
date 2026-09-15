# UX-001 — Responsive & Accessibility Contract v1

**Status:** ESPECIFICAÇÃO FECHADA / validação visual pendente  
**Baseline técnica:** browser acceptance da SPEC-002 já comprovou operação real até 492×660; este documento define o contrato da nova arquitetura visual.

## 1. Breakpoints operacionais

### Desktop largo — >=1200px

- Context Header em uma linha estrutural;
- tabs horizontais completas;
- Main Work Area + Context Panel opcional;
- formulários podem usar duas colunas;
- Knowledge List usa tabela densa.

### Desktop estreito / tablet — 783–1199px

- Context Panel deixa de ser coluna fixa e reflowa abaixo do conteúdo;
- ações podem quebrar em duas linhas;
- tabs mantêm ordem lógica e podem usar scroll horizontal controlado;
- tabela reduz colunas secundárias antes de reduzir legibilidade.

### WordPress narrow — <=782px

- uma coluna;
- título e metadata quebram naturalmente;
- ações ficam empilhadas ou full-width quando necessário;
- fields ocupam 100%;
- multi-selects não criam overflow horizontal;
- contexto permanece antes do formulário;
- nenhuma ação depende de hover.

### Mobile de contingência

Objetivo: consulta, revisão básica e ações administrativas essenciais. UX-001 não promete paridade de edição editorial com Elementor.

## 2. Ordem de foco

Workspace:

1. Voltar / breadcrumb acionável;
2. ação editorial secundária;
3. tabs na ordem visual;
4. heading do domínio ativo;
5. fields na ordem DOM;
6. helper/error associado;
7. ação primária;
8. ações secundárias do domínio;
9. painel contextual quando reflowado.

A ordem visual nunca deve divergir da ordem DOM apenas por CSS.

## 3. Tabs

- papel semântico de tablist/tab quando implementadas como tabs reais;
- estado selecionado não depende só de cor;
- foco visível;
- navegação por teclado coerente;
- em largura estreita, scroll horizontal não pode esconder foco ativo.

## 4. Formulários

- label explícito;
- helper associado ao campo;
- mensagem de erro textual;
- required somente quando domínio realmente exigir;
- placeholder é exemplo, nunca label;
- validação não remove valores já digitados;
- save mantém contexto e informa resultado.

## 5. Status e badges

Sempre combinar texto com cor/ícone/forma. Não usar apenas verde/vermelho. Labels devem permanecer compreensíveis em grayscale.

## 6. Contraste

Alvo mínimo: WCAG AA para texto e controles. Tokens semânticos devem ser testados no par foreground/background usado no componente, não isoladamente.

## 7. Alvos e ações

- botão principal com área confortável de clique;
- icon-only requer accessible name;
- overflow menu deve ser alcançável por teclado;
- ações destrutivas exigem separação visual e confirmação adequada.

## 8. Tabela / Knowledge List

Desktop:

- título é o principal link/ação da linha;
- colunas secundárias podem ser ocultadas por prioridade em largura estreita;
- nunca transformar todas as linhas em cards por padrão sem teste de eficiência;
- cabeçalho e conteúdo precisam manter associação semântica.

Narrow:

- preservar título, sinais essenciais e ação;
- metadata secundária pode reflowar sob o título;
- paginação permanece operável por teclado.

## 9. Context Panel

- não recebe foco antes do Main Work Area;
- quando reflowado, entra depois do domínio ativo;
- informação futura/hipotética nunca aparece como status ativo;
- não deve conter writer concorrente.

## 10. Estados transversais

Cada master screen deverá ter pelo menos evidência de:

- default;
- empty;
- saved/success;
- validation error;
- forbidden/read-only;
- system error.

Loading/skeleton apenas quando existir fluxo assíncrono real.

## 11. Gate

A especificação para U040–U045 está definida. **UX-030 permanece NOT_RUN** até os Master Mockups serem verificados visualmente em desktop largo, 782px e largura mobile de contingência com checklist de teclado/contraste.
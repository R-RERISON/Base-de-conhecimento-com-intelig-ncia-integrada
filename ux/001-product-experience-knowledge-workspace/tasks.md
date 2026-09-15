# Tarefas — UX-001 Product Experience & Knowledge Workspace

## U001 — Baseline e evidência

- [x] U001 Congelar `0.2.0-rc.1` como baseline funcional.
- [x] U002 Registrar SPEC-002 concluída e SPEC-003 bloqueada por UX-001.
- [x] U003 Inventariar telas reais atuais do plugin.
- [x] U004 Inventariar referências KB2Ops — 10 telas do designer + benchmark histórico.
- [x] U005 Classificar padrões KB2Ops: PRESERVAR / EVOLUIR / DESCARTAR.
- [x] U006 Identificar jornadas primárias e secundárias.
- [x] U007 Fechar screen inventory v1.

**Gate UX-001: PASS.**

## U010 — Arquitetura de informação

- [x] U010 Definir mapa global de navegação.
- [x] U011 Definir arquitetura Knowledge List → Knowledge Workspace.
- [x] U012 Definir navegação interna do Workspace — tabs horizontais v1.
- [x] U013 Definir hierarquia de ações.
- [x] U014 Definir padrões de estado/feedback.
- [x] U015 Definir wp-admin para Curadoria e shell separado apenas para Resolvedor futuro quando justificado.

**Gate UX-005: PASS.**

## U020 — Design System v1

- [x] U020 Foundations: grid, spacing, radius, typography e cores semânticas.
- [x] U021 Inventário de componentes canônicos.
- [x] U022 Data density para admin desktop.
- [x] U023 Estados default/hover/focus/disabled/error/success/loading/empty.
- [x] U024 Tokens e nomenclatura canônica.
- [x] U025 Migrar Design System para CSS Custom Properties e classes reutilizáveis no protótipo.

**Design System v1: PASS.**

## U030 — Master prototype executável

- [x] U030 Knowledge List — UI as Code v0.2.
- [x] U031 Knowledge Workspace — Summary no mesmo shell.
- [x] U032 Knowledge Workspace — Classificação no mesmo shell.
- [x] U033 Knowledge Workspace — Overview.
- [x] U034 Review & Governança — conceito controlado, explicitamente não contratual.
- [x] U035 Histórico/atividade — encaixe arquitetural representado sem persistência contratada.
- [x] U036 Estados base: empty/loading/success/error/permission-read-only.

**Gate UX-010: PASS.**

## U040 — Responsividade e acessibilidade

- [x] U040 Desktop largo 1440px — PASS.
- [x] U041 1024px — PASS.
- [x] U042 782px e 492px — PASS, sem overflow horizontal.
- [x] U043 Tabs por teclado + foco visível — PASS no protótipo.
- [x] U044 Pares principais de contraste WCAG AA — PASS.
- [x] U045 Labels/ARIA/feedback — PASS no QA automatizado.

Evidência: `prototype/QA-v0.2.md`.

**Gate UX-030: PASS para protótipo. Browser acceptance real será repetido quando a nova shell for implementada no plugin.**

## U050 — Handoff UI as Code

- [x] U050 UI as Code definido como artefato canônico; Figma arquivado.
- [x] U051 Diretório `prototype/` com zero dependências externas.
- [x] U052 Tokens em CSS Custom Properties.
- [x] U053 HTML/CSS/JS separados e reutilizáveis.
- [x] U054 Checklist/QA visual versionado.
- [x] U055 Protótipo navegável versionado no Git.

**Gate UX-050: PASS.**

## U060 — Fechamento

- [x] U060 Review técnico com contratos SPEC-001/002 — PASS.
- [x] U061 Review de aderência ao legado KB2Ops — PASS.
- [x] U062 Fechar decisões pendentes de shell/tabs/responsividade/tooling.
- [x] U063 Congelar baseline UX v1 em `ux-baseline-v1.md`.
- [x] U064 Autorizar planejamento da SPEC-003 Review & Governança.

## Resultado

**UX-001: CONCLUÍDA PARA BASELINE DE PRODUTO.**

Próxima frente: `SPEC-003 — Review & Governança`.

## Regra

Nenhum protótipo autoriza schema, writer, estado de negócio ou integração futura. A implementação da SPEC-003 precisa definir seus próprios owners, estados, transições, capabilities, persistência e gates antes de alterar runtime.

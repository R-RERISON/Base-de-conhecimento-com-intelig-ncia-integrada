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

**Design System v1: PASS textual / execução UI as Code ativa.**

## U030 — Master mockups → protótipos executáveis

- [x] U030 Knowledge List — primeira versão executável criada localmente.
- [x] U031 Knowledge Workspace — Summary executável no mesmo shell.
- [x] U032 Knowledge Workspace — Classificação executável no mesmo shell.
- [x] U033 Knowledge Workspace — Overview executável.
- [ ] U034 Review & Governance — mockup controlado, sem domínio contratado.
- [ ] U035 Histórico/atividade — placeholder arquitetural.
- [x] U036 Estados base: empty/loading/success/error/permission-read-only.

**Gate UX-010: IN_PROGRESS — depende de QA visual/responsive e versionamento final do protótipo.**

## U040 — Responsividade e acessibilidade

- [ ] U040 Validar desktop largo.
- [ ] U041 Validar 783–1199px.
- [ ] U042 Validar <=782px/mobile de contingência.
- [ ] U043 Validar ordem de foco e teclado.
- [ ] U044 Validar contraste e semântica de status.
- [ ] U045 Validar labels/descriptions/aria patterns.

Especificação: `responsive-accessibility-v1.md`.

**Gate UX-030: NOT_RUN.**

## U050 — Handoff UI as Code

- [x] U050 Decidir UI as Code como artefato canônico; Figma arquivado.
- [x] U051 Definir diretório `prototype/` e contrato de zero dependências.
- [x] U052 Traduzir tokens para CSS Custom Properties.
- [x] U053 Criar navegação entre List / Overview / Summary / Classificação / States.
- [ ] U054 Versionar QA checklist e evidências visuais locais.
- [ ] U055 Fechar pacote de handoff no Git.

**Gate UX-050: IN_PROGRESS.**

## U060 — Fechamento

- [ ] U060 Review técnico com contratos SPEC-001/002.
- [ ] U061 Review de aderência ao legado KB2Ops.
- [ ] U062 Fechar decisões pendentes.
- [ ] U063 Congelar baseline UX v1.
- [ ] U064 Autorizar planejamento/implementação da SPEC-003.

## Próximo bloco de execução

1. versionar o protótipo UI as Code no repositório;
2. executar QA em 1440px, 1024px, 782px e ~492px;
3. validar teclado/foco e semântica;
4. corrigir gaps visuais;
5. fechar UX-010 + UX-030 + UX-050;
6. somente então autorizar SPEC-003.

## Regra

Nenhum mockup/protótipo autoriza schema, writer, estado de negócio ou integração futura. Ferramentas externas podem ajudar, mas nunca podem bloquear a continuidade.

# UX-001 — Baseline UX v1

**Status:** CONGELADA PARA PLANEJAMENTO DA SPEC-003  
**Baseline funcional:** `0.2.0-rc.1`  
**Artefato visual canônico:** `prototype/` — UI as Code v0.2

## 1. Resultado

A UX-001 fecha a arquitetura de experiência necessária para impedir que Summary, Classificação e as próximas capacidades cresçam como uma página vertical incremental.

North Stars preservados do KB2Ops:

1. Knowledge Workspace;
2. Knowledge List;
3. Resolvedor futuro separado conceitualmente da curadoria.

## 2. Knowledge Workspace v1

Estrutura congelada:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Context Panel opcional;
- Visão geral;
- Summary;
- Classificação.

Review & Governança e Histórico estão comprovados apenas como encaixe arquitetural futuro; não são runtime autorizado pela UX-001.

## 3. Contratos funcionais preservados

### SPEC-001

- objetivo;
- escalonamento;
- importante;
- Post Metadata API;
- writer canônico existente.

### SPEC-002

- audiência — multi;
- equipe responsável — multi;
- tipo de conhecimento — single;
- item de catálogo — multi;
- WordPress Taxonomy API;
- legado advisory/read-only;
- sem seed automático, dual-write ou fallback legado.

A UX-001 não alterou nenhum desses contratos.

## 4. Design System v1

Congelados:

- tokens de cor/spacing/radius;
- hierarquia tipográfica;
- botões e ações;
- tabs;
- panels/cards;
- dense list/table;
- campos e helpers;
- badges semânticos;
- notices;
- empty/error/permission/loading states;
- foco visível e regra de contraste.

## 5. QA UI as Code v0.2

Resultado automatizado: **PASS**.

- 1440px: PASS;
- 1024px: PASS;
- 782px: PASS;
- 492px: PASS;
- zero overflow horizontal nos quatro viewports;
- feedback Summary/Classificação: PASS;
- tabs por teclado com setas: PASS;
- labels associados: PASS;
- IDs únicos: PASS;
- semântica `aria-expanded`: PASS;
- pares principais de contraste WCAG AA: PASS.

Evidência: `prototype/QA-v0.2.md`.

## 6. Review técnico

### Aderência SPEC-001/002

**PASS.** O protótipo não introduz writer, schema, campo, estado ou integração nova nos domínios já implementados.

### Aderência KB2Ops Heritage

**PASS.** Foram preservados cabeçalho contextual, tabs por domínio, lista densa, painel contextual e progressive disclosure. Foram rejeitados AI Ready implícito, scores sem owner, campos históricos não canônicos, sidebar duplicada dentro do produto e dashboards ornamentais.

## 7. Decisões congeladas

- Curadoria permanece no contexto wp-admin.
- Não haverá segunda sidebar própria dentro do wp-admin.
- Knowledge Workspace é o recipiente canônico dos domínios de artigo.
- Tabs horizontais são o padrão v1.
- Context Panel existe apenas quando houver informação real/acionável.
- <=782px usa fluxo de uma coluna.
- UI as Code é o artefato visual canônico; Figma não é dependência.
- Feature futura deve ser explicitamente marcada como hipótese até possuir SPEC.

## 8. DoR para SPEC-003

**ATENDIDA PARA PLANEJAMENTO.**

A SPEC-003 pode agora definir, de forma independente e sem pressupostos visuais escondidos:

- owner de Review/Governança;
- estado canônico do conhecimento;
- transições;
- capabilities;
- responsável/revisor;
- histórico/auditoria;
- critérios de qualidade somente se houver fórmula, owner e ação operacional.

A implementação da SPEC-003 continua sujeita aos próprios gates técnicos e de evidência.

## Regra de mudança

Mudanças estruturais em List/Workspace/tabs/responsividade exigem decisão explícita registrada. Refinamentos locais são permitidos se não violarem os contratos acima.

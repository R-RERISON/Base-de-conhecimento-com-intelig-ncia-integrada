# Visual Contract v2 — Mockups → Runtime

## Regra principal

**WordPress-first não significa WordPress-looking.**

O WordPress fornece shell, autenticação, capabilities, APIs e navegação administrativa. A superfície interna da Base de Conhecimento deve comunicar o produto BDC e seguir os mockups vigentes.

## Autoridade visual

- `scr/*.png`: referência visual humana primária;
- `ux/001-product-experience-knowledge-workspace/design-system-v1.md`: tokens e componentes;
- `ux/001-product-experience-knowledge-workspace/prototype/`: comportamento visual executável;
- este contrato: ponte normativa entre referência e runtime.

## Foundations

- largura máxima de produto: 1320px;
- gutter desktop: 24–28px;
- breakpoints críticos: 782px e 520px;
- radius: 10 / 16 / 22px;
- sombra: discreta, estrutura prioritariamente por borda;
- font stack local: Inter quando disponível, Segoe UI, Roboto, Arial, sans-serif;
- sem download de webfont.

## Tokens canônicos

- navy `#0B1F4D`;
- blue `#1677FF` / `#0B63E5` / `#084FB8`;
- bg `#F6F8FB`;
- surface `#FFFFFF`;
- soft `#FBFCFD`;
- text `#172033`;
- text-secondary `#475467`;
- muted `#667085`;
- border `#DFE5EC`;
- success `#126B4A` / bg `#E8F8F0`;
- warning `#8A5A00` / bg `#FFF5DC`;
- danger `#B42318` / bg `#FEECEB`;
- info `#175CD3` / bg `#EEF4FF`.

## Componentes obrigatórios

Quando aplicável, novas telas devem reutilizar:

- product hero/context hero;
- tabs horizontais;
- panel/card;
- dense data table;
- status badge;
- primary/secondary/text button;
- field + label + helper + validation;
- callout/notice;
- empty state;
- activity/history item;
- legacy reference disclosure;
- pagination;
- iconografia discreta com Dashicons nativos quando melhorar reconhecimento e navegação.

## Regras de aparência

- não usar `widefat`, `.button`, notices ou inputs como aparência final sem override BDC;
- não usar cards para tudo;
- ação primária deve ser inequívoca;
- metadata não compete com título;
- status deve combinar texto + forma/dot/cor;
- futuro não pode parecer clicável/ativo;
- espaços e títulos devem seguir hierarquia do produto, não defaults do wp-admin.

## Regras de implementação

- CSS sempre escopado à superfície BDC ou à página BDC;
- WordPress sidebar/topbar não são substituídos;
- zero dependência de biblioteca visual externa;
- CSS do runtime deve usar tokens canônicos;
- novas variantes exigem razão real; não criar micro-design por feature;
- alterações funcionais e visuais permanecem separáveis em revisão.

## Regra permanente de desenvolvimento

A partir do fechamento da UX-002, **toda e qualquer nova superfície do produto, formulário, tabela, estado, componente ou alteração material de UI deve seguir este contrato visual**.

Isso é requisito de arquitetura de produto, não acabamento opcional.

Toda implementação com UI deve, antes de ser considerada Done:

1. identificar o mockup em `scr/` aplicável ou declarar explicitamente que não existe referência direta;
2. reutilizar tokens e componentes canônicos antes de criar qualquer variante;
3. manter WordPress como shell/plataforma sem aceitar aparência genérica do wp-admin como resultado final da superfície BDC;
4. validar desktop e os breakpoints aplicáveis de 782px e 520px;
5. validar foco, teclado, contraste, estados, feedback e legibilidade;
6. registrar evidência humana quando a mudança visual for material.

Divergência intencional só é permitida com justificativa explícita, impacto de regressão, evidência e decisão registrada na SPEC/UX-SPEC correspondente.

## Baseline homologada

A fundação visual `0.4.0-ux002.3` foi **APROVADA em homologação humana em 2026-09-17** e passa a ser a baseline mínima para evolução visual do produto.

Foram aprovados como conjunto coerente: Knowledge List, Knowledge Workspace, Summary, Classificação, Review & Governança, Histórico, telas dos quatro vocabulários, navegação contextual e iconografia discreta.

## Definition of Visual Done

Uma tela só está visualmente concluída quando:

1. possui referência em mockup/contrato;
2. usa tokens canônicos;
3. desktop e mobile foram revisados;
4. hover/focus/disabled/error/read-only aplicáveis estão representados;
5. não depende apenas de cor;
6. não reintroduz aparência genérica do wp-admin na superfície do produto;
7. passou revisão humana contra o mockup aplicável.

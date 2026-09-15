# Evidência Visual — Benchmark KB2Ops

**Status:** OBSERVADO  
**Uso:** referência de UX/UI para UX-001; não constitui contrato funcional nem prova de comportamento em produção.

## Fonte visual principal

Artefato localizado na biblioteca histórica do projeto:

- arquivo: `Painel SaaS de Base de Conhecimento em Português.png`;
- dimensão: 1312 × 1199 px;
- SHA-256: `75b0a64de6e231be2391a8beba53bf043ff9a6d44e490b75de4944bce5240d62`;
- composição: board com 12 superfícies de produto KB2Ops/Knowledge Studio.

A imagem é tratada como **mockup/benchmark visual**. Rótulos, números e features exibidos não autorizam schema, métricas, IA, estados ou writers no novo produto.

## Fontes complementares

Também foram localizados artefatos históricos de linguagem visual:

- `bdc-astra-kb2ops-visual-contract-v3.0-preview.css`;
- `bdc-kb2ops-astra-child-2.1.0-validation.txt`.

O contrato visual preview declara explicitamente intenção de unificar Astra/BdC com KB2Ops por tokens, raios, superfícies, sombras e estados, sem importar seletores `.kb2ops-*`. A validação histórica registra 37 tokens em paridade e checkpoints de contraste aprovados.

## Inventário observado — 12 superfícies

| ID | Superfície | Intenção visual observada | Decisão UX-001 |
|---|---|---|---|
| K01 | Visão Geral / Knowledge Studio | KPIs, progresso de revisão, cobertura e prontidão | EVOLUIR — padrão bom; dashboard só existe quando métricas tiverem owner e pergunta operacional real |
| K02 | Lista de Posts | busca, filtros, tabs de status, tabela densa, ações por linha | PRESERVAR — forte referência para Knowledge List |
| K03 | Revisão de Post / Editor Assistido | contexto do artigo + badges + tabs `Resumo / Classificação / Análise / Histórico` | PRESERVAR PADRÃO / EVOLUIR CONTEÚDO — base direta para Knowledge Workspace |
| K04 | IA Assistente de Revisão | elementos identificados + sugestões acionáveis | EVOLUIR FUTURO — não implementar antes do domínio/IA ser autorizado |
| K05 | Knowledge Search / Resolvedor | busca central, atalhos e proposta de resolução rápida | PRESERVAR COMO REFERÊNCIA FUTURA — superfície do resolvedor separada da curadoria |
| K06 | Resultados da Busca | query persistente, filtros/tabs, relevância e cards/lista | PRESERVAR COMO REFERÊNCIA FUTURA |
| K07 | Visualização de Resultado | resolução rápida, situações, escalonamento e detalhes expansíveis | PRESERVAR/EVOLUIR — referência forte para experiência operacional do resolvedor |
| K08 | Classificação e Metadados | formulário em grid, tags, status e save explícito | EVOLUIR — adaptar aos quatro conceitos canônicos; descartar writers/conceitos não autorizados |
| K09 | Relatórios e Métricas | KPIs, séries, distribuição e rankings | EVOLUIR FUTURO — depende de telemetria canônica |
| K10 | Configurações do Plugin | tabs e toggles de capacidades | EVOLUIR — somente configurações com contrato; evitar central de toggles especulativa |
| K11 | Migração / Limpeza | onboarding/lifecycle de primeira instalação | DESCARTAR DA NAVEGAÇÃO NORMAL — usar apenas quando lifecycle real justificar |
| K12 | Página Pública / Shortcode | busca simples e categorias, sem shell administrativo | EVOLUIR FUTURO — potencial superfície de acesso do resolvedor |

## Padrões visuais com valor comprovado como referência

### Preservar a intenção

- sidebar/nav escura com conteúdo claro, quando houver shell próprio fora do wp-admin;
- cabeçalho contextual forte;
- tabs para domínios do mesmo artigo;
- tabela/lista com alta densidade informacional sem excesso de cards;
- badges semânticos curtos;
- progress/coverage somente quando o indicador tiver significado real;
- formulários em grid de duas colunas no desktop;
- separação clara entre experiência de **curadoria** e de **resolvedor**;
- blocos de resolução rápida e escalonamento como padrões futuros do resolvedor.

### Evoluir

- não duplicar sidebar própria dentro do wp-admin atual;
- usar o WordPress como shell administrativo enquanto ele for suficiente;
- substituir conceitos históricos por owners canônicos atuais;
- status/AI Ready não aparecem até existir contrato de domínio;
- charts não entram como decoração;
- reduzir dependência de cor por labels/ícones/texto;
- responsividade e foco passam a ser contratos, não refinamentos finais.

### Descartar

- migração/limpeza como módulo permanente de navegação;
- writers paralelos criados apenas para uniformidade visual;
- toggle “incluir na base IA” sem política e owner aprovados;
- qualquer métrica ou número do mockup como dado real;
- qualquer campo histórico não aprovado sendo reintroduzido por nostalgia visual.

## Paleta/tokens históricos observados

O visual contract preview oferece uma base objetiva para o Design System v1:

- navy `#0B1F4D`;
- blue 500 `#1677FF`;
- blue 600 `#0B63E5`;
- blue 700 `#084FB8`;
- background `#F6F8FB`;
- surface `#FFFFFF`;
- surface soft `#FBFCFD`;
- text `#172033`;
- text soft `#475467`;
- text muted `#667085`;
- border `#DFE5EC`;
- success `#14AE6C` / bg `#E8F8F0`;
- warning `#C98500` / bg `#FFF5DC`;
- danger `#D92D20` / bg `#FEECEB`;
- radius: 10 / 16 / 22 px;
- font stack: system / Segoe UI / Roboto.

Esses valores entram como **baseline candidata de mockup**, com escopo visual limitado ao produto e sem alteração do runtime atual até gate UX correspondente.

## Decisão arquitetural extraída do benchmark

O benchmark revela duas experiências distintas que devem permanecer conceitualmente separadas:

1. **Curadoria / Knowledge Studio** — administradores, analistas, governança; atualmente dentro do wp-admin.
2. **Resolvedor / Knowledge Search** — busca, resultados e artigo operacional; futuro domínio de Search.

O novo produto deve compartilhar Design System e dados canônicos entre as duas experiências, mas não precisa forçar o mesmo shell de navegação.

## Resultado

A evidência é suficiente para fechar o inventário visual de referência da UX-001 e substituir a classificação por memória por uma matriz baseada em artefato real. A próxima etapa passa a ser componentização + Master Mockups, não nova coleta genérica de referências.
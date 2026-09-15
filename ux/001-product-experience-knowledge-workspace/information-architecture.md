# Arquitetura de Informação v1 — UX-001

**Status:** FECHADA para Gate UX-005  
**Baseline funcional:** `0.2.0-rc.1`

## Objetivo

Organizar o produto antes da alta fidelidade, preservando WordPress/Elementor como fonte editorial e impedindo que cada SPEC crie um novo shell ou uma nova página vertical independente.

## 1. Experiência de Curadoria

Permanece dentro do **wp-admin** enquanto esse shell for suficiente.

### 1.1 Base de Conhecimento

Entrada operacional principal:

`Knowledge List -> Knowledge Workspace -> domínio ativo -> ação -> feedback -> permanência no contexto`

### 1.2 Knowledge List

Decisão: lista/tabela densa como padrão, inspirada na tela de Posts do KB2Ops.

Responsabilidades:

- localizar artigos;
- abrir Workspace;
- filtrar quando houver contrato correspondente;
- apresentar metadados reais, não métricas inventadas.

### 1.3 Knowledge Workspace

Recipiente canônico dos domínios associados ao artigo.

Estrutura:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Context Panel opcional em desktop quando existir informação acionável.

Domínios atuais:

- Visão geral;
- Summary;
- Classificação.

Domínios reservados para SPEC futura:

- Review & Governança;
- Histórico.

Nenhuma reserva arquitetural autoriza runtime ou schema.

### 1.4 Vocabulários

As quatro taxonomias canônicas continuam usando UI nativa WordPress enquanto não houver evidência de inadequação:

- Audiências;
- Equipes responsáveis;
- Tipos de conhecimento;
- Itens de catálogo.

Não criar segundo writer por uniformidade estética.

## 2. Experiência de Governança

Domínio futuro da SPEC-003.

A UX-001 reserva encaixe para:

- estado do conhecimento;
- responsável/revisor;
- decisão de revisão;
- critérios de qualidade;
- histórico de decisão.

Esses itens são apenas estrutura de experiência até a SPEC-003 definir owner, estados, transições, permissões e persistência.

## 3. Experiência do Resolvedor

O benchmark KB2Ops mostrou que Search Home, Results e Knowledge Result formam uma experiência diferente da curadoria.

Decisão: **separação conceitual Curador x Resolvedor**.

Fluxo futuro:

`Search Home -> Search Results -> Knowledge Result -> resolução / detalhe / escalonamento`

Pode reutilizar Design System e dados canônicos, mas não é obrigado a reutilizar o shell wp-admin.

## 4. Operações / Inteligência

Dashboard, Relatórios, Telemetria, Indexação e Configurações só ganham superfície permanente quando existir caso de uso e owner funcional.

Migração/Limpeza não pertence à navegação normal; é lifecycle temporário quando necessário.

## 5. Decisões de navegação

### Global

- não duplicar a sidebar do WordPress com uma segunda sidebar própria dentro do wp-admin;
- `Base de Conhecimento` é a entrada do domínio de curadoria;
- novas entradas globais exigem caso de uso, não estética.

### Workspace

- **tabs horizontais** são o padrão v1;
- o artigo permanece identificável durante toda a jornada;
- save não retorna automaticamente à lista;
- ações secundárias ficam visualmente separadas da ação primária;
- ações destrutivas não competem com save.

## 6. Context Panel

Decisão v1:

- opcional >=1200px;
- só existe com informação real/acionável;
- reflowa abaixo do conteúdo entre 783–1199px;
- fluxo único <=782px;
- não vira segundo writer.

## 7. Feedback

Padrão:

- sucesso: confirmação textual próxima ao contexto;
- validação: junto ao campo/grupo;
- forbidden: sem mutação e com mensagem clara;
- erro sistêmico: preserva contexto e não promete sucesso;
- estado nunca depende só de cor.

## 8. Jornada primária

`Lista -> artigo -> Workspace -> Summary ou Classificação -> salvar -> feedback -> continuar no mesmo artigo`

## 9. Jornada de vocabulário

`Workspace/Classificação -> Gerenciar vocabulário -> UI nativa da taxonomia -> retornar ao artigo`

## 10. Regra de autoridade

1. contratos funcionais atuais;
2. UX-001 congelada;
3. evidência visual/usabilidade KB2Ops;
4. preferência estética.

## Gate

**UX-005 — PASS.**

A arquitetura global, o fluxo List -> Workspace, a navegação interna, a hierarquia de ações, feedback e divisão wp-admin/UI futura estão definidos o suficiente para avançar para Design System v1 + Master Mockups.
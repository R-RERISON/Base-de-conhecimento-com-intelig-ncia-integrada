# UX-001 — Knowledge Workspace Blueprint v1

**Status:** ARQUITETURA VISUAL FECHADA / alta fidelidade pendente  
**Baseline funcional:** `0.2.0-rc.1`

## 1. Objetivo

Substituir o crescimento incremental da página administrativa por um workspace coerente, no qual cada domínio do conhecimento mantém o artigo como contexto e reutiliza o mesmo shell.

## 2. Shell do Workspace

### Zona A — Context Header

Sempre visível no início da página:

- breadcrumb `Base de Conhecimento / Artigo`;
- título do artigo;
- ID do post;
- metadata editorial mínima e confiável;
- ação secundária `Abrir no WordPress` quando aplicável;
- menu secundário para ações não primárias.

Não exibir badge de Review, qualidade, AI Ready ou responsável até existir contrato correspondente.

### Zona B — Navegação interna

Decisão v1: **tabs horizontais**.

Runtime atual:

1. Visão geral;
2. Summary;
3. Classificação.

Reserva arquitetural, não runtime atual:

4. Review & Governança;
5. Histórico.

Features futuras não devem parecer habilitadas na UI produtiva antes da SPEC responsável.

### Zona C — Main Work Area

Contém somente o domínio ativo. Regras:

- ação primária única;
- helper text próximo do controle;
- feedback de save inline/notice sem retirar o usuário do artigo;
- formulários longos segmentados por grupos, não por modais;
- sem duplicar writers.

### Zona D — Context Panel

Permitido em desktop largo apenas quando houver conteúdo real e acionável.

Na baseline atual pode ser omitido. Review/Governança poderá utilizá-lo futuramente para contexto, não para criar um segundo formulário paralelo.

Em largura <= 1100px o painel deve reflowar abaixo da área principal. Em <= 782px o fluxo é uma única coluna.

## 3. Visão geral do artigo

A visão geral não cria métricas novas. Ela agrega leitura dos domínios existentes:

### Card Summary

- presença dos três campos canônicos;
- prévia curta;
- ação `Editar Summary`.

### Card Classificação

- audiência;
- equipe responsável;
- tipo de conhecimento;
- item de catálogo;
- ação `Editar Classificação`.

A overview é uma projeção de leitura. Writers continuam pertencendo aos handlers canônicos de cada domínio.

## 4. Summary

Contrato visual:

- título `Resumo Executivo`;
- campos Objetivo, Escalonamento e Importante;
- textarea com label explícito;
- helper indicando semântica de vazio quando necessário;
- botão `Salvar Summary`;
- feedback PRG preservado.

Não adicionar score de completude na UX-001.

## 5. Classificação

Contrato visual:

- layout de duas colunas em desktop quando houver largura;
- uma coluna <= 782px;
- Tipo de conhecimento como single;
- Audiência, Equipe responsável e Item de catálogo como multi;
- link de gestão de vocabulário como ação secundária para perfil autorizado;
- referência legada em progressive disclosure, rotulada `Referência legada — não canônica`;
- save explícito.

Nenhum campo histórico extra entra por estética.

## 6. Ações

### Primárias

Uma por domínio ativo: `Salvar Summary`, `Salvar Classificação`.

### Secundárias

- Abrir no WordPress;
- Gerenciar vocabulário;
- Voltar para lista.

### Destrutivas

Não existem na baseline atual do Workspace. Futuras ações destrutivas exigem confirmação e separação visual.

## 7. Feedback e estados

Todo domínio deve prever:

- default;
- empty;
- saved/success;
- validation error;
- forbidden;
- system error;
- read-only quando aplicável;
- loading apenas se uma operação assíncrona real for introduzida.

## 8. Responsividade

### >= 1200px

Header amplo, tabs completas, Main + Context Panel opcional.

### 783–1199px

Main prioritário; Context Panel reflowa; tabs podem reduzir spacing ou permitir scroll horizontal controlado.

### <= 782px

Uma coluna; título quebra naturalmente; ações empilham; tabs preservam foco/teclado; campos ocupam 100%.

### Mobile

UX-001 garante leitura e ações administrativas essenciais, mas não promete paridade editorial completa com Elementor.

## 9. Acessibilidade

- heading hierarchy previsível;
- labels nunca substituídos por placeholder;
- `:focus-visible` perceptível;
- tabs operáveis por teclado;
- status com texto/ícone além de cor;
- targets de ação adequados;
- mensagens de erro associadas ao campo;
- ordem DOM acompanha ordem visual.

## 10. Relação com KB2Ops

Preserva de H02: contexto do artigo + tabs + domínio ativo + informação lateral.  
Preserva de H10: densidade e grid de classificação.  
Descarta: AI Ready, checklist/score não contratados e qualquer campo histórico não autorizado.

## 11. Gate

Este blueprint fecha a arquitetura do Workspace para **UX-005**. Alta fidelidade, componentização Figma e aprovação visual ainda pertencem ao **UX-010**.
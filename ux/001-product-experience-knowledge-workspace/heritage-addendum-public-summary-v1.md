# UX-001 — Addendum de Herança — Resumo Executivo no artigo

**Status:** ADENDO APROVADO À BASELINE UX v1  
**Origem:** tela real/legada fornecida em 15/09/2026  
**Objetivo:** preservar a proposta de valor visível do Resumo Executivo na experiência de consumo do artigo.

## 1. Evidência visual observada

A tela fornecida mostra um artigo operacional publicado com:

- cabeçalho contextual do artigo;
- chips de contexto;
- responsável, data de publicação e atualização;
- conteúdo principal Elementor;
- painel lateral persistente **Resumo Executivo**;
- campos estruturados exibidos ao leitor;
- ações de contribuição sem alterar automaticamente o post.

O painel lateral contém, na referência histórica, itens como título, objetivo, equipe responsável, item de catálogo, serviço afetado, sistemas envolvidos, público-alvo, escalonamento e importante.

## 2. Valor de produto

Este padrão é mais importante do que uma simples escolha estética: ele mostra ao usuário final o retorno concreto do trabalho de curadoria realizado no admin.

A governança deixa de ser apenas metadata interna e passa a melhorar a leitura do artigo no ponto de uso.

Decisão: **PRESERVAR FORTEMENTE A INTENÇÃO**.

## 3. Arquitetura correta no produto novo

O painel futuro não será owner e não terá writer próprio. Ele será uma **projection/read model composta**.

Fontes autorizadas hoje:

| Informação | Owner |
|---|---|
| título | `WP_Post` |
| objetivo | Summary / SPEC-001 |
| escalonamento | Summary / SPEC-001 |
| importante | Summary / SPEC-001 |
| audiência | Classificação / SPEC-002 |
| equipe responsável | Classificação / SPEC-002 |
| item de catálogo | Classificação / SPEC-002 |
| tipo de conhecimento | Classificação / SPEC-002, se útil à experiência |

Itens históricos ainda não contratados, como `serviço afetado` e `sistemas envolvidos`, **não podem reaparecer apenas para reproduzir a tela antiga**. Eles entram somente quando uma SPEC posterior autorizar seus owners/primitives.

## 4. Superfície de produto

Nome arquitetural provisório:

**Knowledge Result — Executive Summary Rail**

Pertence à experiência do **Resolvedor**, não ao Knowledge Workspace de curadoria.

Fluxo futuro:

`Search/entrada -> Knowledge Result -> artigo oficial + Executive Summary Rail -> resolução / escalonamento`

## 5. Regras

- read-only para o leitor;
- nunca reescrever `post_content` ou `_elementor_data`;
- nunca duplicar valores canônicos em novo store só para renderização;
- ocultar graciosamente campos sem valor;
- em viewport estreito, o rail reflowa para bloco contextual no corpo;
- informação `Importante` preserva prioridade visual sem depender só de cor;
- responsável/publicado/atualizado só entram quando a fonte semântica estiver claramente definida;
- nenhuma informação de Review/AI aparece antes de existir contrato correspondente.

## 6. Relação com a tela vendida

A tela histórica é tratada como **evidência de proposta de valor já apresentada aos stakeholders**. Por isso, a experiência pública/operacional de consumo passa a ser um requisito de continuidade do produto, ainda que sua implementação esteja numa SPEC posterior.

Isso não obriga cópia pixel-perfect. Obriga preservar a capacidade: **o conhecimento estruturado deve ficar visível e útil dentro do artigo oficial**.

## 7. Decisão de roadmap

Este addendum não altera a ordem atual da SPEC-003.

Ele deve ser consumido quando abrirmos Content Extractor/Search/Resolvedor, evitando que o produto novo entregue somente curadoria administrativa e perca a face de consumo que já demonstrou valor.

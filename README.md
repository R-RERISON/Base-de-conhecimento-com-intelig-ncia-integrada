# Base de Conhecimento com Inteligência Integrada

Plataforma WordPress de **gestão, curadoria, busca e inteligência aplicada à Base de Conhecimento**.

> **Mantra do projeto:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Estado atual

**Baseline Zero — fundação e inventário.**

Ainda não existe runtime do novo plugin. Esta fase prepara o terreno para uma reconstrução greenfield, limpa e WordPress-first, usando três projetos existentes como fontes de comportamento comprovado — nunca como dependências de runtime.

## Regra de produto mais importante

O plugin **não faz manutenção editorial dos posts**.

- O post continua sendo criado, editado e publicado no WordPress/Elementor.
- Elementor continua sendo a superfície editorial oficial.
- O novo plugin não substitui o editor, não reescreve `_elementor_data` e não publica conteúdo em nome do autor.
- O plugin administra a **camada de conhecimento ao redor do post**: resumo executivo, classificação, revisão, qualidade, indexação, busca, telemetria, IA, vetores, governança e operações.

## Projetos de referência

Os três repositórios abaixo são laboratórios anteriores e referências obrigatórias de comportamento:

1. **KB2Ops — Operational Knowledge Engine**  
   https://github.com/R-RERISON/KB2Ops-Operational-Knowledge-Engine  
   Referência principal de produto, fluxos, Design System, Knowledge Studio e Knowledge Search.

2. **Advanced Search Intelligence (ASI)**  
   https://github.com/R-RERISON/Advanced-search-Intelligence  
   Referência de busca, ranking, índice, itens, vocabulário, Golden Queries, telemetria, fila, diagnóstico, privacidade e operações.

3. **Gerenciador de Resumo Executivo da Base de Conhecimento**  
   https://github.com/R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento  
   Referência de clean code, WordPress-first, metadados canônicos, editor administrativo e renderer.

### Baseline observado em 2026-09-14

- ASI `main`: árvore `c0ddff89caad529ce1bcdc645eb795e4a9b187a1` — versão 4.6.8.
- KB2Ops `main`: árvore `f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94` — versão 0.2.1 hardened.
- Resumo Executivo `main`: árvore `1120a534d8eb2288460c2c675730deef0d67c365` — versão 0.6.0.

Esses identificadores existem para rastreabilidade do inventário. O novo produto não deve copiar código cegamente nem depender desses repositórios em execução.

## Visão

Construir um único plugin WordPress, modular internamente, capaz de oferecer uma jornada contínua:

```text
Home da Base
   ↓
Busca / descoberta
   ↓
Resultado operacional
   ↓
Artigo oficial no Elementor
   ↓
Gestão e curadoria no Knowledge Studio
   ↓
Qualidade / Analytics / IA / Operações
```

Visualmente e sistemicamente, tudo deve pertencer ao mesmo produto.

## Princípios fundamentais

1. **WordPress-first.** Antes de criar infraestrutura própria, verificar APIs e primitives nativas do WordPress.
2. **Princípio de negação.** Toda solução proposta deve ser desafiada por uma alternativa mais simples.
3. **Greenfield consciente.** Reescrever por domínio e comportamento, não traduzir linha antiga por linha nova.
4. **Errar barato.** Evolução por vertical slices pequenos, homologáveis e reversíveis.
5. **Humano como autoridade editorial.** IA sugere; humano decide; WordPress persiste.
6. **Fonte editorial única.** O conteúdo oficial continua no `WP_Post`/Elementor.
7. **Sem regressão silenciosa.** Funcionalidade comprovada só é substituída depois de existir contrato e teste equivalente.
8. **Design System desde o primeiro pixel.** A linguagem visual do KB2Ops é a base do novo produto.
9. **Português do Brasil obrigatório.** Código de domínio, documentação, telas, Specs, agentes, skills e mensagens do projeto devem usar pt-BR, exceto nomes técnicos impostos por APIs externas.
10. **Custo e observabilidade desde o início.** IA, embeddings e processamento assíncrono precisam de telemetria e limites.

## Estratégia de reconstrução

```text
G0  Inventário + constituição + contratos
G1  Core WordPress + Design System + navegação
G2  Resumo Executivo integrado
G3  Knowledge Studio / revisão / classificação
G4  Search lexical + Golden Queries
G5  Telemetria + qualidade + operações
G6  Semantic Search / vetores / hybrid retrieval
G7  IA assistida / Foundry / custo
G8  paridade integral e desativação controlada dos plugins antigos
```

Nenhum gate é avançado sem baseline, critérios de aceite e regressão.

## Governança

Leia nesta ordem antes de implementar qualquer código:

1. `AGENTS.md`
2. `.specify/PROJECT_MANIFEST.md`
3. `.specify/memory/constitution.md`
4. `docs/REFERENCIAS-E-INVENTARIO.md`
5. `specs/ROADMAP.md`
6. SPEC ativa em `specs/`
7. `docs/DEFINITION-OF-DONE.md`

## Status

🟡 **PREPARAÇÃO / INVENTÁRIO** — nenhum runtime do novo plugin deve ser criado até a SPEC-000 encerrar o inventário funcional dos três produtos de referência.

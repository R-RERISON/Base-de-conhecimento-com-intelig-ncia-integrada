# AGENTS — Orquestração do Projeto

> **Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

Este arquivo define como agentes humanos e de IA devem atuar no projeto **Base de Conhecimento com Inteligência Integrada**.

## Papel do Orquestrador

O Orquestrador é responsável por manter coerência sistêmica, decidir a sequência de trabalho, convocar especialistas, impedir regressões e garantir que cada implementação respeite a Constituição.

Nenhum agente especialista trabalha isoladamente em uma mudança estrutural. Toda proposta relevante deve ser confrontada por pelo menos:

1. **Arquiteto WordPress** — verifica se o Core já oferece uma solução adequada;
2. **Arquiteto de Produto/Conhecimento** — verifica aderência ao fluxo real dos usuários;
3. **Crítico de Simplicidade** — aplica o princípio de negação;
4. **Especialista de Segurança/Regressão** — verifica risco, compatibilidade e rollback.

## Regra de experiência mínima

Todo agente especializado deste repositório representa um profissional de **nível especialista/sênior**, com experiência mínima explícita em sua área. A experiência declarada é um padrão de raciocínio esperado, não uma alegação sobre identidade humana real.

## Princípio de negação

Para cada solução proposta, perguntar obrigatoriamente:

- O WordPress já faz isso nativamente?
- Podemos remover uma camada?
- Podemos evitar uma tabela?
- Podemos evitar uma API?
- Podemos evitar JavaScript?
- Podemos evitar um job assíncrono?
- Podemos evitar IA?
- Podemos evitar vetor?
- Podemos resolver com metadata, taxonomy, options, capabilities, hooks ou HTTP API?
- Qual é a solução mais simples que atende o requisito sem comprometer o futuro?

Se a resposta mais simples for suficiente, a solução mais complexa deve ser rejeitada.

## Fonte editorial e fronteira do plugin

Invariante absoluta:

> **O plugin não cria, edita, reescreve ou publica o conteúdo editorial do post.**

O conteúdo oficial continua sendo produzido em WordPress/Elementor.

O plugin pode:

- ler o post e sua estrutura;
- gerenciar metadados e taxonomias próprias;
- classificar, revisar e governar conhecimento;
- criar projeções e índices derivados;
- gerar sugestões de IA;
- indexar chunks e embeddings;
- medir uso, qualidade e lacunas;
- oferecer busca e resolução operacional.

O plugin não pode escrever em `_elementor_data`, substituir Elementor nem se tornar editor de posts.

## Idioma

Todo o projeto é conduzido em **português do Brasil**:

- documentação;
- Specs;
- decisões arquiteturais;
- nomes de domínio quando tecnicamente viável;
- mensagens administrativas;
- comentários relevantes;
- relatórios;
- agentes e skills.

Exceções: nomes de APIs, classes do WordPress, padrões externos, protocolos e termos técnicos cuja tradução reduza clareza ou compatibilidade.

## Ordem de trabalho obrigatória

Antes de alterar runtime:

1. Ler a Constituição.
2. Ler o Manifesto.
3. Ler a SPEC ativa completa.
4. Ler os artefatos dos projetos de referência relacionados.
5. Registrar baseline atual.
6. Definir comportamento esperado.
7. Definir testes/gates antes de implementar.
8. Aplicar princípio de negação.
9. Implementar o menor vertical slice funcional.
10. Executar regressão e validar visualmente.
11. Atualizar documentação e estado da SPEC.

## Regra anti-regressão

Nenhuma funcionalidade comprovada dos projetos de referência é considerada migrada apenas porque existe código equivalente.

Ela só é considerada substituída quando houver:

- contrato funcional explícito;
- evidência de paridade ou evolução deliberada;
- teste automatizado quando aplicável;
- teste manual quando UI/browser for necessário;
- rollback definido;
- impacto em dados documentado;
- impacto visual validado.

## Mudanças destrutivas

São proibidas por padrão durante instalação/ativação:

- DROP TABLE;
- exclusão automática de plugins;
- remoção irreversível de metadata;
- renomeação de dados sem ponte de compatibilidade;
- reconstrução total silenciosa;
- chamadas de IA em massa sem estimativa de custo.

Qualquer limpeza definitiva deve ser explícita, autorizada, auditável e reversível quando possível.

## Definition of Ready resumida

Uma SPEC só entra em implementação quando:

- problema está definido;
- baseline existe;
- usuário/fluxo afetado está claro;
- WordPress-first foi avaliado;
- solução simples foi considerada;
- dados envolvidos estão mapeados;
- critérios de aceite existem;
- regressões possíveis foram identificadas;
- rollback foi pensado;
- impacto visual foi descrito.

## Definition of Done resumida

Uma SPEC só termina quando:

- comportamento aprovado funciona;
- testes passam;
- segurança passa;
- compatibilidade WordPress passa;
- UI segue o Design System;
- acessibilidade básica passa;
- documentação está atualizada;
- nenhum dado editorial foi indevidamente alterado;
- regressões dos contratos relevantes passam;
- custo/telemetria foi tratado quando houver IA ou processamento intensivo.

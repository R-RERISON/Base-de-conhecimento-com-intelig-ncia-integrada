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
- Podemos evitar uma dependência externa?
- Podemos evitar uma tabela?
- Podemos evitar uma API?
- Podemos evitar JavaScript?
- Podemos evitar um job assíncrono?
- Podemos evitar IA?
- Podemos evitar vetor?
- Podemos resolver com Blocks, metadata, taxonomy, options, capabilities, hooks ou HTTP API?
- Qual é a solução mais simples que atende o requisito sem comprometer o futuro?

Se a resposta mais simples for suficiente, a solução mais complexa deve ser rejeitada.

## Fonte editorial e fronteira do plugin

A fonte editorial canônica futura é:

> **`WP_Post.post_content` + WordPress Core Blocks.**

O produto usa APIs estáveis do WordPress Core. O plugin Gutenberg não é dependência de produção. APIs experimentais/plugin-only exigem ADR própria.

Elementor é tratado como **fonte legada temporária**, não destino editorial futuro. Enquanto houver dependência comprovada:

- `Elementor_Adapter` pode ler a fonte de forma read-only;
- `_elementor_data` deve ser preservado;
- o plugin Elementor não deve ser removido automaticamente;
- nenhum novo writer deve usar `_elementor_data` como destino.

O plugin pode:

- ler o post e suas estruturas legadas/nativas;
- gerenciar metadados e taxonomias próprias;
- classificar, revisar e governar conhecimento;
- criar projeções e índices derivados;
- gerar sugestões de IA;
- indexar chunks e embeddings;
- medir uso, qualidade e lacunas;
- oferecer busca e resolução operacional;
- executar migrações administrativas governadas para Core Blocks quando houver SPEC/gate/autorização explícitos.

O plugin não pode:

- tornar-se editor paralelo;
- reescrever silenciosamente `post_content`;
- escrever em `_elementor_data` como arquitetura-alvo;
- publicar posts em nome do autor sem fluxo autorizado;
- permitir que IA exerça autoridade editorial autônoma.

Fluxo editorial de IA:

`IA sugere → humano revisa → humano decide → WordPress persiste`.

## Regra específica de Blocks

Antes de criar abstração própria para conteúdo estruturado, avaliar primeiro:

- `parse_blocks()`;
- `serialize_blocks()`;
- Block API estável;
- `block.json`/registro nativo quando um bloco `bdc/*` for realmente necessário;
- Block Patterns/templates/locking quando padronização editorial exigir.

Core Blocks devem ser preferidos a blocos customizados. Bloco `bdc/*` só é permitido quando nenhum Core Block representa adequadamente o domínio sem perda relevante.

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
4. Ler ADRs vigentes da SPEC.
5. Ler os artefatos dos projetos de referência relacionados.
6. Registrar baseline atual.
7. Definir comportamento esperado.
8. Definir testes/gates antes de implementar.
9. Aplicar princípio de negação.
10. Implementar o menor vertical slice funcional.
11. Executar regressão e validar visualmente.
12. Atualizar documentação e estado da SPEC.
13. Criar/atualizar `CONTINUIDADE.md` da SPEC com prompt autossuficiente para novo chat.
14. Conferir o handoff contra `docs/DEFINITION-OF-DONE.md`.

## Contrato visual obrigatório

A UX-002 foi homologada e fechada em `0.4.0-ux002.3`. A partir dessa baseline, **toda e qualquer nova tela, formulário, tabela, estado, componente ou alteração material de UI do produto deve seguir o contrato visual vigente**.

Antes de implementar ou revisar UI, todo agente deve ler:

1. `ux/002-mockup-visual-foundation/visual-contract-v2.md`;
2. os mockups aplicáveis em `scr/`;
3. quando necessário, o Design System e o protótipo da UX-001.

Regras obrigatórias:

- WordPress permanece shell/plataforma; aparência genérica do wp-admin não é resultado final aceitável para a superfície BDC;
- reutilizar tokens/componentes canônicos antes de criar variantes;
- usar mockup aplicável como referência de hierarquia, densidade, navegação, estados e iconografia;
- novas features não podem criar um “micro-design” isolado;
- validar desktop e breakpoints aplicáveis, foco, teclado, contraste e feedback;
- alteração material de UI exige evidência visual humana antes de Done/merge;
- divergência intencional do contrato exige justificativa explícita, risco de regressão e decisão documentada na SPEC/UX-SPEC.

**Gate:** UI funcionalmente correta, mas visualmente divergente do contrato vigente, **não está concluída e não deve ser promovida**.

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

## Continuidade entre chats

O contexto de conversa não é considerado armazenamento confiável do projeto.

Toda implementação material deve terminar com um `CONTINUIDADE.md` atualizado na pasta da SPEC ativa, baseado em `.specify/templates/continuity-prompt-template.md`.

O Prompt de Continuidade deve permitir que um novo chat retome o trabalho sem conhecimento prévio da conversa anterior e deve conter, no mínimo:

- repositório, branch e commit;
- SPEC e estado;
- baseline comprovado;
- implementações concluídas;
- decisões e invariantes;
- arquivos/dados/contratos afetados;
- testes e gates com resultados;
- gaps, riscos e blockers;
- itens fora de escopo;
- próximo passo exato;
- critério de conclusão do próximo passo.

O novo chat deve ser instruído a reler AGENTS, Manifesto, Constituição, SPEC, ADRs vigentes, DoD e confirmar o estado do GitHub antes de modificar qualquer coisa.

Frases vagas como “continue de onde paramos” não são handoff aceitável.

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
- dependências editoriais externas foram justificadas ou eliminadas;
- UI segue o Visual Contract vigente e os mockups aplicáveis;
- acessibilidade básica passa;
- documentação está atualizada;
- nenhum dado editorial foi indevidamente alterado;
- regressões dos contratos relevantes passam;
- custo/telemetria foi tratado quando houver IA ou processamento intensivo;
- `CONTINUIDADE.md` está atualizado e pronto para um novo chat.

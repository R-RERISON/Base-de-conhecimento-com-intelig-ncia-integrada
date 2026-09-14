# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** Baseline Zero / Inventário  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento, integrando curadoria, resumo executivo, classificação, busca lexical, busca semântica, telemetria, qualidade, operações e inteligência artificial sem substituir o WordPress ou o Elementor como fonte editorial.

## Usuários principais

### Analista de Conhecimento
Revisa, classifica, estrutura e aprova conteúdos para consumo operacional e futuro uso de IA.

### Resolvedor / Analista de Atendimento
Precisa encontrar rapidamente a próxima ação, procedimento, erro conhecido, validação e escalonamento corretos.

### Gestor de Conhecimento
Acompanha cobertura, qualidade, lacunas, comportamento de busca, risco e evolução da base.

### Administrador WordPress / Operações
Administra configuração, saúde, indexação, filas, migrações, IA e diagnósticos.

## Fonte da verdade

### Editorial
`WP_Post` + Elementor.

### Metadados e classificação
APIs nativas de metadata e taxonomias do WordPress sempre que suficientes.

### Projeções de busca
Índices derivados e reconstruíveis, nunca fonte editorial.

### IA
Saída assistiva e rastreável. IA não é fonte editorial nem autoridade de aprovação.

## Fronteiras não negociáveis

1. O plugin não mantém conteúdo editorial de posts.
2. O plugin não escreve em `_elementor_data`.
3. Elementor continua sendo o editor dos posts.
4. O plugin pode ler e interpretar Elementor para gestão, busca e IA.
5. A publicação oficial continua seguindo o fluxo WordPress.
6. Nenhum conteúdo não aprovado entra no índice produtivo de IA quando o gate de confiança estiver ativo.
7. Dados derivados devem ser reconstruíveis a partir da fonte da verdade.

## Hierarquia tecnológica

### Nível 0 — WordPress Core
Preferir:

- Posts;
- Post Meta;
- Taxonomies;
- Options / Settings API;
- Roles & Capabilities;
- Nonces;
- Hooks / Filters;
- Shortcodes;
- admin-post;
- WP-Cron;
- Transients / Object Cache;
- WordPress HTTP API;
- Site Health;
- REST API apenas quando houver consumidor real.

### Nível 1 — Tabelas próprias
Aceitas quando há motivo técnico comprovado, como:

- FULLTEXT dedicado;
- telemetria volumosa;
- fila durável;
- chunks;
- embeddings;
- vetores;
- Golden Queries e evidência de regressão, se o modelo nativo não atender.

### Nível 2 — Serviços externos
Apenas para capacidades que o ambiente WordPress não deve executar sozinho:

- embeddings;
- LLM;
- agentes;
- Microsoft Foundry;
- integrações corporativas.

## Princípio de negação

Nenhuma arquitetura é aceita sem a pergunta:

> “Qual parte desta solução pode ser removida sem perder o resultado?”

Uma decisão mais simples vence quando entrega o mesmo requisito com menor custo, menor acoplamento e menor superfície de falha.

## Referências históricas obrigatórias

### KB2Ops
Fonte de referência para produto, fluxo, Design System e experiência operacional.

### ASI
Fonte de referência para comportamento de busca, índice, ranking, privacidade, Golden Queries, telemetria e operações.

### Gerenciador de Resumo Executivo
Fonte de referência para WordPress-first, clean code, contrato de metadata e implementação sucinta.

## Estratégia de produto

A plataforma deve entregar uma experiência integrada desde a Home até o detalhe de uma revisão administrativa.

A UI não pode parecer um conjunto de plugins agregados.

## Estratégia de desenvolvimento

- Greenfield.
- Vertical slices.
- Baseline antes de mudança.
- Testes antes de substituição.
- Reversibilidade.
- Sem big-bang.
- Sem refatoração simultânea de todos os domínios.
- Sem dependência de GitHub Actions.
- Build e verificação devem poder rodar localmente.
- Toda implementação material termina com Prompt de Continuidade versionado na SPEC ativa.

## Continuidade entre chats

O projeto não depende da memória implícita de uma conversa do ChatGPT.

Cada SPEC em implementação deve possuir `CONTINUIDADE.md`, baseado no template canônico em `.specify/templates/continuity-prompt-template.md`.

Esse artefato deve ser suficiente para que um novo chat:

1. descubra o estado atual comprovado;
2. saiba quais decisões já estão vigentes;
3. conheça testes, gaps, riscos e blockers;
4. saiba exatamente qual é o próximo passo;
5. reler o repositório antes de alterar qualquer coisa.

O repositório e a Constituição prevalecem sobre o texto do handoff caso exista divergência.

## Diretriz de IA

Fluxo de curadoria:

`IA sugere → humano revisa → humano aprova → WordPress persiste`.

Fluxo de resposta:

`query → retrieval confiável → evidências → síntese opcional → fontes`.

## Diretriz de custo

Toda operação de IA deve poder ser atribuída a:

- operação;
- modelo;
- post/objeto;
- quantidade processada;
- tokens quando disponíveis;
- custo estimado;
- data/hora;
- usuário ou automação responsável quando aplicável.

## Diretriz visual

O Design System do KB2Ops é a referência visual inicial. O novo projeto deve reconstruí-lo como contrato próprio, sem depender do plugin KB2Ops.

## Regra de liberação

Uma versão não é liberada apenas porque compila. Deve possuir evidência suficiente de:

- integridade do pacote;
- ativação segura;
- rotas funcionais;
- handlers funcionais;
- permissões e nonces;
- filtros e paginação;
- regressão funcional;
- compatibilidade visual;
- integridade de dados;
- rollback;
- Prompt de Continuidade atualizado.

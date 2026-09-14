# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000 concluída / SPEC-001 autorizada para Definition of Ready e implementação posterior aos seus gates  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento, integrando curadoria, resumo executivo, classificação, busca lexical, busca semântica, telemetria, qualidade, operações e inteligência artificial sem substituir o WordPress ou o Elementor como fonte editorial.

## Estado de execução após T097

A SPEC-000 — Inventário Profundo e Contratos — foi concluída documentalmente.

A decisão canônica `specs/000-inventario-profundo-e-contratos/decisao-t097.md` autoriza a abertura da **SPEC-001 — Core mínimo + Summary narrativo**, sob escopo estrito.

A autorização significa:

- criar e detalhar a SPEC-001;
- provar seu próprio Definition of Ready;
- somente depois implementar o vertical slice autorizado.

A autorização **não** significa release, cutover produtivo ou permissão implícita para Search, Classificação, Review, IA, Analytics, queue, vetor ou outras capacidades fora do escopo da SPEC-001.

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

### Nível 1 — Estruturas próprias
Aceitas somente quando há motivo técnico comprovado. A SPEC-000 aprovou documentalmente apenas uma família futura no baseline: **Search Retrieval Projection reconstruível**. Outras estruturas próprias continuam condicionadas a suas SPECs e gates.

### Nível 2 — Serviços externos
Apenas para capacidades que o ambiente WordPress não deve executar sozinho:

- embeddings;
- LLM;
- agentes;
- Microsoft Foundry;
- integrações corporativas.

Nenhum serviço externo é necessário para a SPEC-001 autorizada.

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

As referências preservam comportamento e aprendizado; não obrigam reprodução de código, schema, menus ou dívida histórica.

## Estratégia de produto

A plataforma deve entregar uma experiência integrada desde a Home até o detalhe de uma revisão administrativa.

A UI não pode parecer um conjunto de plugins agregados.

A integração será construída por vertical slices; produto único não significa big-bang.

## Estratégia de desenvolvimento

- Greenfield com memória institucional.
- Vertical slices.
- Baseline antes de mudança.
- Testes antes de substituição.
- Reversibilidade.
- Sem big-bang.
- Sem refatoração simultânea de todos os domínios.
- Sem dependência de GitHub Actions.
- Build e verificação devem poder rodar localmente.
- Toda implementação material termina com Prompt de Continuidade versionado na SPEC ativa.

## SPEC-001 autorizada — fronteira inicial

A primeira SPEC autorizada deve começar por **Core mínimo + Summary narrativo** para o Analista de Conhecimento.

Conceitos:

- `objective`;
- `escalation`;
- `important`.

Storage inicial preservado:

- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

Antes de código, a SPEC-001 deve comprovar os post types reais suportados, Matriz de Mutação, Matriz de Evidência, contratos de campo, B-006, UI mínima, aceite/não aceite e rollback.

## Continuidade entre chats

O projeto não depende da memória implícita de uma conversa do ChatGPT.

Cada SPEC em implementação deve possuir `CONTINUIDADE.md`, baseado no template canônico em `.specify/templates/continuity-prompt-template.md`.

O repositório, a Constituição, este Manifesto, a SPEC ativa e suas evidências prevalecem sobre memória do chat.

## Diretriz de IA

Fluxo de curadoria:

`IA sugere → humano revisa → humano aprova → WordPress persiste`.

Fluxo de resposta:

`query → retrieval confiável → evidências → síntese opcional → fontes`.

A SPEC-001 autorizada não inclui IA.

## Diretriz de custo

Toda operação de IA futura deve poder ser atribuída a operação, modelo/provider, objeto, quantidade processada, tokens/unidades quando disponíveis, custo estimado/real, data/hora e responsável quando aplicável.

## Diretriz visual

O Design System do KB2Ops é a referência visual inicial. O novo projeto deve reconstruí-lo como contrato próprio, incrementalmente, sem depender do plugin KB2Ops.

## Regra de liberação

Uma versão não é liberada apenas porque compila. Deve possuir evidência suficiente de:

- integridade do pacote;
- ativação segura;
- rotas/handlers aplicáveis funcionais;
- permissões e nonces;
- regressão funcional;
- compatibilidade visual;
- integridade de dados;
- rollback;
- Matriz de Evidência da SPEC;
- Prompt de Continuidade atualizado.

A decisão T097 autoriza trabalho na SPEC-001; cada release futuro continua sujeito ao DoD e aos gates da própria SPEC.
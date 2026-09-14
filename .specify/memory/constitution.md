# Constituição — Base de Conhecimento com Inteligência Integrada

**Versão:** 1.1.0  
**Ratificação:** 2026-09-14  
**Idioma oficial:** Português do Brasil  
**Mantra constitucional:** **“Quem não sabe onde está, não sabe para onde quer ir”.**

Esta Constituição prevalece sobre conveniência técnica, pressa, preferência individual, frameworks, tendências e decisões anteriores. Qualquer exceção exige ADR explícita, motivação, alternativas rejeitadas, risco, rollback e aprovação do Orquestrador.

---

## Artigo I — O WordPress é a plataforma

### I.1 — WordPress-first
Antes de construir qualquer infraestrutura própria, a equipe deve demonstrar que as APIs nativas do WordPress não atendem adequadamente ao requisito.

A ordem preferencial de decisão é:

1. WordPress Core;
2. extensão simples via hooks/APIs do Core;
3. estrutura própria mínima;
4. serviço externo.

### I.2 — Primitives prioritivas
Devem ser avaliados primeiro:

- `WP_Post`;
- Post Metadata API;
- Taxonomy API;
- Options / Settings API;
- Roles & Capabilities;
- Nonces;
- Hooks / Filters;
- Shortcodes;
- `admin-post.php`;
- AJAX nativo quando interação dinâmica realmente exigir;
- WP-Cron como disparador;
- Transients / Object Cache;
- WordPress HTTP API;
- REST API quando houver consumidor real;
- Site Health para saúde técnica apropriada.

### I.3 — Não reconstruir o WordPress
É vedado construir um CMS paralelo, sistema próprio de usuários, editor próprio de posts, roteador próprio ou framework administrativo quando o WordPress já entrega a capacidade necessária.

---

## Artigo II — O conteúdo editorial pertence ao WordPress/Elementor

### II.1 — Fonte editorial canônica
O conteúdo oficial da Base de Conhecimento permanece no `WP_Post` e na estrutura editorial produzida pelo Elementor.

### II.2 — Proibição absoluta
O plugin não deve:

- substituir Elementor;
- tornar-se editor de posts;
- escrever em `_elementor_data`;
- reescrever silenciosamente `post_content`;
- publicar posts em nome do autor;
- alterar conteúdo editorial a partir de IA sem ação editorial explícita no fluxo oficial.

### II.3 — O que o plugin pode gerenciar
O plugin pode gerenciar a camada sistêmica ao redor do post:

- Resumo Executivo;
- classificação;
- revisão;
- aprovação;
- qualidade;
- conhecimento operacional derivado;
- índices;
- chunks;
- embeddings;
- busca;
- telemetria;
- inteligência artificial;
- lacunas;
- saúde;
- operações;
- migrações.

### II.4 — Projeções não são fonte da verdade
Índices, chunks, embeddings e Knowledge Records são projeções reconstruíveis. Nunca substituem o post oficial.

---

## Artigo III — Princípio de negação e simplicidade

### III.1 — Pergunta obrigatória
Toda proposta deve responder:

> **“Qual parte desta solução pode ser removida sem perder o resultado necessário?”**

### III.2 — Checklist de negação
Antes de aprovar uma solução, questionar:

- precisamos mesmo desta classe?
- precisamos desta tabela?
- precisamos deste endpoint?
- precisamos deste JavaScript?
- precisamos de React?
- precisamos de REST?
- precisamos de job assíncrono?
- precisamos de cache próprio?
- precisamos de IA?
- precisamos de embedding?
- precisamos de vetor?
- podemos usar Metadata, Taxonomy, Settings, Hooks ou APIs nativas?

### III.3 — Complexidade precisa ser comprada
Toda complexidade adicional deve demonstrar benefício mensurável em confiabilidade, desempenho, escala, segurança ou produto.

---

## Artigo IV — Greenfield com memória institucional

### IV.1 — Novo código
O plugin é uma reconstrução greenfield. Código dos três projetos anteriores não deve ser copiado de forma indiscriminada.

### IV.2 — Projetos de referência
São fontes obrigatórias de aprendizado:

- KB2Ops;
- Advanced Search Intelligence;
- Gerenciador de Resumo Executivo.

### IV.3 — Comportamento antes de código
Antes de substituir uma função comprovada, registrar:

- comportamento atual;
- razão de existência;
- dados envolvidos;
- casos de erro;
- dependências;
- teste de regressão;
- comportamento pretendido no novo produto.

### IV.4 — Não reproduzir dívida histórica
Compatibilidade só é incorporada quando existe necessidade real e evidência de uso.

---

## Artigo V — Desenvolvimento por vertical slices

### V.1 — Errar barato
Nenhuma fase deve acumular meses de infraestrutura antes de entregar um fluxo homologável.

### V.2 — Slice mínimo
Cada SPEC deve entregar uma jornada verificável de ponta a ponta.

Exemplo válido:

`abrir post → visualizar resumo → editar metadado → salvar → reler → mostrar estado`.

### V.3 — Proibido big-bang
É vedada a reconstrução simultânea de todos os domínios.

---

## Artigo VI — Sem regressão silenciosa

### VI.1 — Baseline obrigatório
Antes da implementação deve existir estado atual documentado.

### VI.2 — Paridade explícita
Funcionalidade anterior só é considerada substituída após evidência de paridade ou decisão consciente de mudança.

### VI.3 — Golden Queries
Busca deve ter consultas de referência com expectativas versionadas antes de mudanças de ranking, semantic search, embeddings, reranking ou IA.

### VI.4 — UI também regride
Fluxos visuais críticos devem possuir critérios de validação responsiva, acessível e funcional.

---

## Artigo VII — Clean Code pragmático

### VII.1 — Classes pequenas e coesas
Classes extensas são sinal de inspeção obrigatória. Como orientação, ultrapassar aproximadamente 400–500 linhas exige justificar coesão ou dividir responsabilidades.

### VII.2 — Nomes por domínio
Código deve refletir conceitos do produto, não detalhes acidentais de implementação.

### VII.3 — Dependências explícitas
Módulos devem possuir fronteiras claras e evitar acesso lateral a detalhes internos de outros módulos.

### VII.4 — Sem abstração antecipada
Não criar interfaces, factories, repositories ou camadas genéricas sem pelo menos um problema concreto que justifique a abstração.

---

## Artigo VIII — Arquitetura modular em um único plugin

### VIII.1 — Um plugin distribuível
O produto final é um único plugin WordPress.

### VIII.2 — Modularidade interna
Domínios devem permanecer separáveis conceitualmente:

- Core;
- Conhecimento;
- Resumo Executivo;
- Revisão;
- Classificação;
- Busca;
- Inteligência de Busca;
- IA;
- Analytics;
- Operações;
- UI.

### VIII.3 — Um shell visual
Todas as telas pertencem ao mesmo Design System e à mesma navegação de produto.

---

## Artigo IX — Design System é contrato

### IX.1 — Origem
A linguagem visual do KB2Ops é referência inicial.

### IX.2 — Implementação própria
O novo plugin deve possuir seus próprios tokens/componentes, sem dependência de runtime do KB2Ops.

### IX.3 — Pixel faz parte do requisito
Hierarquia, espaçamento, estados, responsividade, foco, feedback e consistência são critérios funcionais, não acabamento opcional.

### IX.4 — WordPress Admin continua shell
Não criar uma segunda sidebar administrativa dentro do wp-admin.

---

## Artigo X — Inteligência Artificial é assistiva e governada

### X.1 — Autoridade humana
Fluxo editorial:

`IA sugere → humano revisa → humano decide → WordPress persiste`.

### X.2 — Retrieval antes de geração
Respostas devem ser construídas sobre recuperação confiável e evidências rastreáveis.

### X.3 — Nada de automação destrutiva silenciosa
IA não pode modificar em massa metadata, taxonomias ou conteúdo sem ação aprovada e auditável.

### X.4 — Provider desacoplado
Microsoft Foundry pode ser o primeiro provedor, mas o domínio não deve depender diretamente de SDK/provedor específico.

### X.5 — WordPress HTTP API primeiro
Integrações HTTP devem preferir `wp_remote_get`, `wp_remote_post` e APIs relacionadas.

---

## Artigo XI — Vetores e semantic search são capacidades opcionais

### XI.1 — Busca deve degradar graciosamente
Ausência de suporte vetorial não pode quebrar o plugin.

### XI.2 — Lexical permanece relevante
Semantic search complementa, não elimina automaticamente, busca lexical determinística.

### XI.3 — Hybrid retrieval
Combinação lexical + semântica deve ser validada por Golden Queries e métricas antes de produção.

### XI.4 — Nunca vetorizar fonte bruta inadequada
Elementor bruto, JSON estrutural, shortcodes e marcação de apresentação devem passar por Content Extractor antes de chunking/embedding.

---

## Artigo XII — Custo, hashes e idempotência

### XII.1 — Não recalcular sem necessidade
Usar `content_hash`, `chunk_hash`, versão de modelo e equivalentes para identificar `NO_CHANGE`.

### XII.2 — Custos observáveis
Chamadas de IA devem registrar consumo/custo quando a API permitir e estimativa quando necessário.

### XII.3 — Operações em massa exigem orçamento
Nenhum processamento de toda a base pode ser disparado sem previsão de volume, impacto e custo.

---

## Artigo XIII — Segurança WordPress

Toda mutação deve considerar:

- capability;
- nonce;
- sanitização;
- validação;
- escaping na saída;
- método HTTP esperado;
- princípio do menor privilégio.

Nonce não substitui capability.

---

## Artigo XIV — Instalação, migração e rollback

### XIV.1 — Ativação mínima
Ativação não deve executar trabalho pesado nem destrutivo.

### XIV.2 — Dados antigos são preservados
Durante transição, meta keys e tabelas comprovadamente utilizadas devem ser preservadas até gate explícito de migração.

### XIV.3 — Limpeza explícita
Exclusões definitivas exigem confirmação administrativa, nonce, capability, evidência e plano de rollback quando aplicável.

### XIV.4 — Fallback
Até paridade integral, plugins antigos podem ser reativados em homologação se o novo produto falhar.

---

## Artigo XV — Idioma oficial

Todo artefato humano do projeto deve ser pt-BR.

Código pode usar termos técnicos em inglês quando isso for padrão da plataforma, API ou aumentar interoperabilidade. Textos de domínio e documentação permanecem em português.

---

## Artigo XVI — Governança de Specs

Nenhuma implementação sem SPEC ativa.

Cada SPEC deve conter no mínimo:

- problema;
- baseline;
- usuários;
- fluxo atual;
- resultado esperado;
- alternativas;
- avaliação WordPress-first;
- princípio de negação;
- dados;
- segurança;
- UI/UX;
- testes;
- aceite;
- regressão;
- rollback;
- fora de escopo;
- Prompt de Continuidade atualizado ao final de cada implementação material.

---

## Artigo XVII — Emenda constitucional

Mudanças nesta Constituição devem:

1. explicar problema;
2. citar princípio afetado;
3. apresentar alternativa mais simples;
4. mapear risco de regressão;
5. receber nova versão semântica;
6. atualizar data de ratificação.

---

## Artigo XVIII — Continuidade entre chats e preservação de contexto

### XVIII.1 — Problema reconhecido
O ChatGPT e outros agentes conversacionais possuem contexto finito. O projeto não pode depender da continuidade implícita de uma conversa para preservar decisões, estado, testes, riscos ou próximos passos.

### XVIII.2 — Prompt de Continuidade obrigatório
Toda implementação material, ao ser concluída, deve produzir ou atualizar um **Prompt de Continuidade** autossuficiente, pronto para ser utilizado em um novo chat.

Uma SPEC não pode ser marcada como `Concluída`, `Homologação` ou entregue como implementação final sem esse artefato atualizado.

### XVIII.3 — Local canônico
Cada SPEC que entrar em implementação deve possuir um arquivo `CONTINUIDADE.md` em sua própria pasta, baseado em `.specify/templates/continuity-prompt-template.md`.

### XVIII.4 — Conteúdo mínimo obrigatório
O Prompt de Continuidade deve registrar, no mínimo:

- repositório, branch e commit de referência;
- SPEC ativa e estado;
- objetivo exato da continuidade;
- baseline comprovado;
- implementações concluídas;
- decisões arquiteturais vigentes;
- invariantes que não podem ser violados;
- arquivos e áreas modificados;
- dados, hooks, rotas, eventos e contratos envolvidos;
- testes e gates executados com resultados;
- gaps, blockers, riscos e dívidas conhecidas;
- itens explicitamente fora de escopo;
- próximo passo exato;
- critério objetivo para concluir o próximo passo.

### XVIII.5 — Repositório prevalece sobre memória de chat
O Prompt de Continuidade é um mecanismo de handoff, não uma nova fonte da verdade. Constituição, Manifesto, SPEC, código e evidências versionadas no repositório prevalecem sobre memória do chat ou texto desatualizado do prompt.

Se houver divergência entre o prompt e o repositório, a continuidade deve parar para investigar a divergência antes de qualquer alteração.

### XVIII.6 — O novo chat deve revalidar o estado
O Prompt de Continuidade deve instruir explicitamente o novo chat a:

1. ler `AGENTS.md`;
2. ler `.specify/PROJECT_MANIFEST.md`;
3. ler esta Constituição;
4. ler a SPEC ativa e seus artefatos;
5. ler `docs/DEFINITION-OF-DONE.md`;
6. confirmar branch/commit/estado atual no GitHub;
7. somente então continuar a implementação.

### XVIII.7 — Estado comprovado, não intenção
O handoff deve distinguir claramente:

- o que foi implementado e testado;
- o que foi apenas decidido;
- o que está planejado;
- o que falhou;
- o que permanece desconhecido.

É proibido registrar como concluído algo que não possua evidência.

### XVIII.8 — Saída obrigatória do Orquestrador
Ao encerrar uma implementação material em chat, o Orquestrador deve informar que o `CONTINUIDADE.md` foi atualizado e disponibilizar o Prompt de Continuidade ou indicar seu caminho canônico no repositório.

# Instruções Globais — Copilot / Agentes

Você está trabalhando no projeto **Base de Conhecimento com Inteligência Integrada**.

## Idioma

Responda, documente e produza artefatos do projeto em **português do Brasil**. Preserve em inglês apenas nomes técnicos impostos por APIs, bibliotecas, protocolos, classes do WordPress e padrões externos.

## Mantra

> **“Quem não sabe onde está, não sabe para onde quer ir”.**

Antes de modificar algo, descubra e registre o estado atual.

## Constituição obrigatória

Leia antes de implementar:

1. `AGENTS.md`
2. `.specify/PROJECT_MANIFEST.md`
3. `.specify/memory/constitution.md`
4. SPEC ativa em `specs/`
5. `docs/DEFINITION-OF-DONE.md`

## Princípio de negação

Toda solução deve ser questionada em termos mais simples. Pergunte se o WordPress já entrega o requisito e qual camada pode ser removida.

## WordPress-first

Prefira APIs nativas: posts, metadata, taxonomias, options/settings, capabilities, nonces, hooks, admin-post, AJAX quando necessário, WP-Cron como disparador, transients/object cache, HTTP API, REST apenas com consumidor real e Site Health.

## Elementor

Regra absoluta: o plugin **não faz manutenção editorial do post**.

- não escrever em `_elementor_data`;
- não substituir Elementor;
- não publicar silenciosamente;
- não reescrever conteúdo editorial;
- leitura e projeções derivadas são permitidas.

## Greenfield

Os repositórios KB2Ops, ASI e Resumo Executivo são referências comportamentais. Não copie código sem análise. Preserve contratos úteis, não dívida histórica.

## Sem regressão

Nenhuma funcionalidade existente é considerada substituída sem contrato e evidência de regressão/paridade.

## IA

IA sugere; humano decide; WordPress persiste. Retrieval e fontes precedem síntese. Custo e uso devem ser observáveis.

## Vetores

Semantic search é opcional e deve degradar para lexical. Nunca vetorize `_elementor_data` bruto; use conteúdo canônico extraído.

## Desenvolvimento

Trabalhe por vertical slices pequenos, reversíveis, testáveis e homologáveis. Evite criar infraestrutura futura sem uma SPEC que a justifique.

## Continuidade entre chats

O contexto de conversa é temporário; o repositório é permanente.

Ao concluir qualquer implementação material:

1. crie ou atualize `CONTINUIDADE.md` dentro da pasta da SPEC ativa;
2. use `.specify/templates/continuity-prompt-template.md`;
3. registre branch, commit, estado comprovado, decisões, invariantes, arquivos/dados/contratos, testes, gaps e riscos;
4. defina próximo passo exato e critério objetivo de conclusão;
5. produza um prompt autossuficiente pronto para colar em um novo chat;
6. instrua o novo chat a reler AGENTS, Manifesto, Constituição, SPEC, DoD e confirmar o estado do GitHub antes de alterar qualquer coisa.

Uma implementação não deve ser declarada concluída, em homologação ou pronta para transferência sem esse handoff atualizado. Nunca dependa da frase “continue de onde paramos” como mecanismo de continuidade.

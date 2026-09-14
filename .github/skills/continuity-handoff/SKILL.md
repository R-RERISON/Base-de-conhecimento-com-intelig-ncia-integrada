# Skill — Continuidade entre Chats / Handoff de Contexto

**Nível:** Especialista / Principal  
**Experiência mínima representada:** 15 anos em engenharia de software, gestão de configuração, handoff técnico e continuidade operacional.

## Objetivo

Garantir que qualquer implementação possa ser retomada em um novo chat sem depender da memória da conversa anterior.

## Quando aplicar

- ao concluir qualquer implementação material;
- antes de declarar uma SPEC concluída ou em homologação;
- quando houver risco de estouro de contexto;
- antes de interromper trabalho relevante;
- ao mudar de chat, agente principal ou responsável técnico.

## Artefato obrigatório

Atualizar/criar `CONTINUIDADE.md` dentro da pasta da SPEC ativa usando `.specify/templates/continuity-prompt-template.md`.

## Procedimento

1. Ler Constituição, Manifesto, SPEC ativa e DoD.
2. Confirmar branch e commit exatos no GitHub.
3. Registrar somente estado comprovado, não intenção.
4. Listar implementações concluídas.
5. Registrar decisões arquiteturais vigentes.
6. Registrar invariantes que não podem ser quebrados.
7. Listar arquivos, dados, hooks, rotas e contratos afetados.
8. Registrar testes/gates executados com resultado.
9. Registrar blockers, riscos e dívidas conhecidas.
10. Definir o próximo passo exato e seu critério de conclusão.
11. Gerar um prompt autossuficiente, pronto para ser colado em um novo chat.
12. Conferir que o prompt manda o novo chat reler o repositório antes de agir.

## Regra de verdade

O prompt de continuidade não é a fonte da verdade. O repositório e a Constituição prevalecem. Se houver divergência, o próximo chat deve investigar antes de modificar qualquer arquivo.

## Proibições

- escrever “continue de onde paramos” sem contexto verificável;
- depender de memória implícita de chat;
- omitir blockers conhecidos;
- inventar testes ou estado não confirmado;
- copiar resumos antigos sem atualizar SHA/branch/gates;
- deixar próximos passos vagos.

## Gate

Uma implementação material não está concluída se não existir um Prompt de Continuidade atualizado e utilizável por um novo chat.

# Skill — Execução de SPEC

**Nível:** Especialista / Principal  
**Experiência mínima representada:** 15 anos em engenharia de software e entrega incremental.

## Objetivo

Executar uma SPEC sem perder baseline, simplicidade, regressão, rastreabilidade e continuidade entre chats.

## Procedimento

1. Ler Constituição/Manifesto/SPEC.
2. Confirmar status “Pronta”.
3. Convocar agentes necessários.
4. Revalidar baseline.
5. Definir testes antes do runtime.
6. Aplicar WordPress-first.
7. Aplicar princípio de negação.
8. Implementar menor vertical slice.
9. Executar testes e inspeção de segurança.
10. Homologar fluxo real.
11. Registrar evidências.
12. Atualizar tarefas/status.
13. Criar ou atualizar `CONTINUIDADE.md` na pasta da SPEC usando o template canônico.
14. Confirmar branch/commit e registrar somente estado comprovado.
15. Gerar o Prompt de Continuidade autossuficiente com próximo passo exato e critério de conclusão.
16. Validar o handoff contra o DoD antes de declarar a implementação concluída.

## Proibições

- implementar requisito futuro fora da SPEC;
- fazer refatoração oportunista não relacionada;
- declarar PASS sem evidência;
- avançar gate com blocker conhecido;
- depender de memória implícita do chat;
- encerrar implementação material sem Prompt de Continuidade atualizado;
- produzir handoff vago como “continue de onde paramos”.

## Saída

SPEC implementada com evidências e `CONTINUIDADE.md` atualizado, ou estado Bloqueada com causa explícita e handoff suficiente para retomada em novo chat.

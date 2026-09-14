# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## 1. Prompt pronto para colar em um novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".

Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

Antes de qualquer alteração:
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md.
4. Leia todos os artefatos de specs/000-inventario-profundo-e-contratos/.
5. Leia docs/DEFINITION-OF-DONE.md.
6. Leia este CONTINUIDADE.md inteiro.
7. Confirme o estado atual no GitHub antes de implementar ou documentar novas conclusões.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- Commit de referência do estado de governança: 7df13b4bf0e50f32e21ab0e471aea0cdbff182c9
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: Pronta / início de execução do inventário profundo

OBJETIVO DA CONTINUIDADE
Executar a SPEC-000 profundamente antes de qualquer runtime novo. Ler e decompor KB2Ops, Advanced Search Intelligence e Gerenciador de Resumo Executivo, classificando comportamentos, persistência, hooks, rotas, segurança, UI, testes e complexidade. O próximo foco deve começar pelo ASI, por ser o componente mais complexo e crítico de busca.

ESTADO ATUAL COMPROVADO
- O novo repositório nasceu greenfield e não possui runtime do plugin.
- Constituição 1.1.0 ratificada em 2026-09-14.
- WordPress-first é obrigatório.
- Princípio de negação é obrigatório.
- O plugin não faz manutenção editorial de posts.
- Elementor continua sendo editor/publicador canônico.
- É proibido escrever em _elementor_data.
- KB2Ops é referência de produto/UI/Design System.
- ASI é referência de search/index/ranking/Golden Queries/telemetria/operações.
- Gerenciador de Resumo Executivo é referência de clean code/WordPress-first/metadata.
- Roadmap SPEC-000 até SPEC-012 está materializado.
- 14 agentes especialistas e 19 skills especializadas estão versionados.
- Nenhum runtime novo foi criado.

IMPLEMENTAÇÕES CONCLUÍDAS NESTA ETAPA
- README e visão do produto.
- AGENTS.md e regras de orquestração.
- Manifesto do Projeto.
- Constituição 1.1.0.
- Contrato editorial Elementor.
- Definition of Done.
- SpecKit completo com templates de SPEC, plano, tarefas, checklist, ADR e continuidade.
- Roadmap e pastas SPEC-000 a SPEC-012.
- SPEC-000 com spec.md, plan.md, tasks.md, research.md, data-model.md e checklist.
- Catálogo de agentes especialistas.
- Catálogo de skills especializadas.
- Skill continuity-handoff.
- Cláusula constitucional obrigando Prompt de Continuidade ao fim de toda implementação material.

DECISÕES ARQUITETURAIS VIGENTES
- O produto final será um único plugin WordPress, modular internamente.
- O projeto é greenfield: comportamentos dos legados são referência; código não tem direito automático de ser copiado.
- WordPress Core deve ser avaliado antes de infraestrutura própria.
- Toda complexidade adicional deve ser justificada.
- UI deve derivar do contrato visual do KB2Ops, reconstruído de forma própria.
- IA é assistiva: IA sugere, humano decide, WordPress persiste.
- Retrieval precede síntese.
- Vetores/semantic search são opcionais e não podem derrubar o core.
- Desenvolvimento acontece por vertical slices e não por big-bang.

INVARIANTES QUE NÃO PODEM SER VIOLADOS
- WordPress-first.
- Aplicar princípio de negação antes de adicionar complexidade.
- O plugin não faz manutenção editorial dos posts.
- Elementor continua sendo o editor/publicador canônico.
- Não escrever em _elementor_data.
- Não reescrever post_content silenciosamente.
- IA sugere; humano decide; WordPress persiste.
- Sem regressão silenciosa.
- Todo o projeto humano/documental em pt-BR.
- Nenhuma implementação sem SPEC ativa.
- Nenhuma implementação material termina sem CONTINUIDADE.md atualizado.

ARQUIVOS/ÁREAS PRINCIPAIS JÁ MATERIALIZADOS
- AGENTS.md — regras dos agentes.
- .specify/PROJECT_MANIFEST.md — identidade e missão.
- .specify/memory/constitution.md — Constituição 1.1.0.
- .specify/templates/* — templates SpecKit e Prompt de Continuidade.
- .github/agents/* — agentes especialistas.
- .github/skills/* — skills especializadas.
- docs/DEFINITION-OF-DONE.md — gate de conclusão.
- docs/CONTRATO-EDITORIAL-ELEMENTOR.md — fronteira editorial.
- docs/REFERENCIAS-E-INVENTARIO.md — referências iniciais.
- specs/000-inventario-profundo-e-contratos/* — SPEC ativa.
- specs/001-* até specs/012-* — roadmap planejado.

DADOS E CONTRATOS PERSISTENTES ENVOLVIDOS
Ainda não há dados próprios do novo plugin. A SPEC-000 deve inventariar os contratos persistentes existentes nos três plugins de referência: post meta, taxonomias, options, transients, cron hooks, tabelas, shortcodes, AJAX, REST, capabilities, hooks/events e artefatos de migração.

TESTES E GATES EXECUTADOS
- Estrutura do repositório relida no GitHub após bootstrap de governança.
- Confirmado que nenhum runtime foi criado por acidente.
- Constituição, Manifesto, DoD, agentes, skills e SPEC-000 estão versionados.
- Nenhum teste de runtime é aplicável ainda porque a SPEC-000 é documental/inventário.

GAPS / RISCOS / DÍVIDAS CONHECIDAS
- O inventário profundo dos três plugins ainda não foi concluído.
- ASI possui alta complexidade e deve ser lido arquivo a arquivo, não apenas via README.
- Já foi observado drift potencial: ASI espera BDC\\ExecutiveSummary\\Objective_Provider, enquanto o runtime observado do Gerenciador de Resumo Executivo não expõe esse provider no bootstrap atual. Isso deve ser confirmado e registrado formalmente na SPEC-000.
- Ainda não existe matriz completa de persistência, hooks, rotas, testes e paridade.
- Nenhuma decisão sobre schema novo, taxonomias novas, MariaDB Vector ou Foundry deve ser tratada como definitiva antes do inventário.

O QUE NÃO DEVE SER FEITO AGORA
- Não criar bootstrap do novo plugin.
- Não criar tabelas novas.
- Não copiar classes dos legados.
- Não implementar Design System runtime.
- Não integrar Foundry.
- Não criar embeddings/vetores.
- Não iniciar SPEC-001.
- Não alterar os três plugins de referência durante o inventário.

PRÓXIMO PASSO EXATO
Começar a execução real da SPEC-000 pelo Advanced Search Intelligence:
1. Fixar SHA/versionamento atual usado como referência.
2. Ler o bootstrap completo.
3. Mapear árvore de runtime.
4. Inventariar Schema.php e todas as tabelas/índices.
5. Inventariar Settings/options/transients/cron/capabilities.
6. Mapear Search Index, Item Knowledge, QueryContext, Relevance, Vocabulary, Bindings e Rules.
7. Mapear Queue, Migration Runner e PostInstallOrchestrator.
8. Mapear Search Events, Interactions, Outcomes, Quality e Golden Queries.
9. Mapear Admin, rotas, AJAX, shortcodes e frontend.
10. Mapear testes para cada comportamento.
11. Classificar cada componente como MANTER, REDESENHAR, SUBSTITUIR POR WORDPRESS, EVOLUIR COM IA/VETOR, DESCARTAR ou AINDA NÃO SABEMOS.
12. Atualizar os artefatos da SPEC-000 e este CONTINUIDADE.md ao final do bloco ASI.

CRITÉRIO PARA CONSIDERAR O PRÓXIMO PASSO CONCLUÍDO
O bloco ASI só termina quando os arquivos de runtime relevantes tiverem sido lidos, sua persistência/hooks/rotas/testes estiverem mapeados, comportamentos críticos e razões de existência estiverem documentados, complexidades questionadas pelo princípio de negação e existir uma matriz preliminar de decisão/paridade específica do ASI.

REGRA DE CONTINUIDADE
Não assuma contexto de chats anteriores além do que estiver no repositório e neste prompt. Não recomece o projeto, não recrie decisões já comprovadas e não avance para a próxima SPEC sem fechar os gates da SPEC atual. Se houver divergência entre este prompt e o repositório, o repositório e a Constituição prevalecem; investigue a divergência antes de modificar qualquer coisa.
```

## 2. Estado resumido para humanos

- **Commit de referência do estado:** `7df13b4bf0e50f32e21ab0e471aea0cdbff182c9`
- **SPEC:** SPEC-000 — Inventário Profundo e Contratos
- **Último gate concluído:** fundação de governança + cláusula de continuidade
- **Próximo gate:** inventário profundo do ASI
- **Blockers conhecidos:** nenhum blocker para iniciar o inventário; runtime novo permanece deliberadamente bloqueado

## 3. Evidências disponíveis

- Constituição 1.1.0 versionada.
- Manifesto atualizado com política de continuidade.
- DoD com gate específico de continuidade.
- Template canônico de Prompt de Continuidade.
- Skill `continuity-handoff`.
- AGENTS e Copilot Instructions atualizados.
- SPEC-000 atualizada para exigir handoff.

## 4. Regra de atualização

Atualizar este arquivo após cada bloco material da SPEC-000, especialmente após finalizar o inventário de ASI, KB2Ops e Gerenciador de Resumo Executivo. Substituir informações obsoletas; não acumular estado contraditório.

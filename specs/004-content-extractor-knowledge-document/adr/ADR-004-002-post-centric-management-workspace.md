# ADR-004-002 — Post-Centric Management Workspace

- **Status:** ACEITA
- **Data:** 2026-09-17
- **SPEC:** SPEC-004
- **Gate de origem:** T099C PASS → T100 rebaseline
- **Supersede:** T100A Batch Authorization Pack antes de qualquer execução

## Contexto

A trilha G-245 comprovou tecnicamente a migração editorial segura para WordPress Core Blocks, incluindo fidelity inventory, lossless serialization, parity, stale-source, journal, lock, authorization pack e um canário real T099C com apply, verificação e rollback imediato.

Ao preparar expansão para múltiplos artigos, foi identificado um requisito de produto mais importante: o artigo não deve ser tratado como item disperso entre telas técnicas. O **post é a unidade primária de gerenciamento**. Summary, Classificação, IA, Conteúdo, Core Blocks, Governança e Histórico pertencem ao contexto do mesmo post.

O baseline UX-002 já contém a estrutura correta: lista de artigos → ação **Gerenciar** → Knowledge Workspace vinculada a `post_id`. Portanto, não será criada uma segunda workspace.

## Decisão

1. **Post é a unidade primária de gerenciamento do produto.**
2. A rota canônica permanece a existente: `Base de Conhecimento → Gerenciar → Workspace do post`.
3. Operações específicas do artigo devem convergir para atividades dentro desta Workspace.
4. Telas globais servem somente para busca, visão agregada, inventário, administração e processamento read-only.
5. Processamentos massivos de leitura/análise podem existir, mas seus resultados devem ser projetados de volta no contexto individual de cada post.
6. Writes editoriais permanecem **post-scoped**, com journal, lock, stale check, verificação, rollback e autorização conforme o risco.
7. Gates T09x continuam como instrumentos de engenharia/homologação; não são a UX final.
8. O antigo T100A Batch Authorization Pack é **SUPERSEDED BEFORE EXECUTION**. O ZIP produzido não foi instalado nem executado.
9. A integração IA seguirá a regra: **IA sugere → humano revisa → humano decide → WordPress persiste**.
10. Novas atividades serão adicionadas por um `Post_Activity_Registry` versionado, evitando menus/telas paralelas.

## Atividades canônicas

Ordem inicial:

1. Visão geral
2. Conteúdo
3. Summary
4. Classificação
5. Inteligência
6. Core Blocks
7. Review & Governança
8. Histórico

As atividades novas de T100A — Conteúdo, Inteligência e Core Blocks — começam em **read-only**.

## Invariantes

- Um Workspace sempre representa exatamente um `post_id`.
- Troca de atividade nunca troca implicitamente o post.
- Nenhuma atividade read-only pode persistir estado.
- Nenhuma atividade IA pode executar write editorial diretamente.
- Core Blocks não habilita writer no T100A.
- Source `mixed` continua exigindo revisão humana.
- Elementor continua como source adapter legado enquanto houver dependência.
- Plugin Gutenberg continua não sendo dependência.
- UX-002 permanece linguagem visual de base; mudanças deliberadas exigem evidência anti-regressão.
- PR #4 permanece DRAFT até o fechamento de G-245.

## Consequências

### Positivas

- contexto único por artigo;
- menor dispersão cognitiva;
- melhor rastreabilidade;
- novas capacidades podem crescer sem multiplicar menus;
- leitura em escala continua possível;
- writes continuam isoláveis por artigo.

### Trade-offs

- o Workspace passa a ser um componente arquitetural central e precisa de contrato estável;
- atividades deverão declarar capacidade, modo e segurança;
- migração das telas diagnósticas para UX final será gradual, com paridade antes de remoção.

## Próximo gate

**T100A — Post Management Workspace Shell + Post Context Contract**, read-only para as novas atividades e anti-regressão dos fluxos existentes.

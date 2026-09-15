# Continuidade — UX-001 Product Experience & Knowledge Workspace

## Estado atual

- SPEC-002 encerrada; baseline funcional `0.2.0-rc.1`.
- UX-001 é a trilha ativa.
- SPEC-003 Review & Governança continua bloqueada para implementação até UX-010 PASS.
- UX-001 não autorizou runtime, schema ou writer novo.

## Gates

- UX-001 Inventário/Jornadas: **PASS**.
- UX-005 Arquitetura de Informação: **PASS**.
- Design System v1 textual: **PASS**.
- UX-010 Master Mockups: **NOT_RUN / próxima frente**.
- UX-030 Responsive/A11y: **NOT_RUN**.
- UX-050 Figma/Handoff: **IN_PROGRESS**.

## Evidência KB2Ops incorporada

As 10 telas fornecidas pelo designer foram formalizadas em `heritage-pack-v1.md` e reconciliadas com `kb2ops-heritage.md` / `evidence-kb2ops-visual-benchmark.md`.

North Stars:

1. Knowledge Workspace — herança principal da Revisão de Post;
2. Knowledge List — herança principal da Lista de Posts;
3. Resolvedor futuro — combinação Search Home + Results + Artigo operacional.

Não foram transportados automaticamente AI Ready, scores, campos históricos, métricas ou writers legados.

## Arquitetura congelada v1

### Curadoria

`Knowledge List -> Knowledge Workspace -> domínio ativo -> salvar -> feedback -> permanecer no artigo`

Workspace:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Context Panel opcional quando existir informação acionável.

Runtime atual nas tabs: Visão geral, Summary e Classificação.

Review & Governança / Histórico continuam apenas como reserva arquitetural até SPEC própria.

### Resolvedor

Permanece como experiência futura separada conceitualmente do wp-admin.

## Design System v1

Tokens, tipografia, spacing, radius, semântica de cor, componentes, estados e data density estão definidos em `design-system-v1.md`.

## Figma

Arquivo canônico: `UX-001 — Product Experience & Knowledge Workspace`  
URL: `https://www.figma.com/design/myCK7Aq0ih8C55ejcRZFVz`  
File key: `myCK7Aq0ih8C55ejcRZFVz`

O Master Board existente contém:

- foundations iniciais;
- Knowledge List draft;
- Knowledge Workspace draft;
- Review & Governance conceitual;
- responsive 782px;
- anti-padrões.

## Próximo passo exato

1. estruturar páginas Figma por domínio;
2. componentizar Design System v1;
3. elevar Knowledge Workspace Overview para alta fidelidade;
4. produzir Summary e Classificação em alta fidelidade dentro do mesmo Workspace;
5. elevar Knowledge List usando H04 como benchmark;
6. construir estados empty/error/permission e responsive;
7. executar review visual e técnico para Gate UX-010.

## Critério de avanço para SPEC-003

- UX-010 PASS;
- Workspace master aprovado;
- Summary/Classificação encaixados sem regressão;
- Review/Governança visualmente acomodado sem congelar domínio;
- responsive/accessibility definidos;
- componentes reutilizáveis suficientes para implementação.

## Regra

“Quem não sabe onde está, não sabe para onde quer ir.” A baseline visual agora está mapeada; a próxima decisão depende de protótipo de alta fidelidade, não de nova coleta genérica de referências.
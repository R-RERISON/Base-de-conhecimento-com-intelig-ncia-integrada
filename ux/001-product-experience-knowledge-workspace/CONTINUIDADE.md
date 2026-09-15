# Continuidade — UX-001 Product Experience & Knowledge Workspace

## Estado atual

- SPEC-002 encerrada; baseline funcional `0.2.0-rc.1`.
- UX-001 é a trilha ativa.
- SPEC-003 Review & Governança continua bloqueada até UX-010 PASS.
- UX-001 não autorizou runtime, schema ou writer novo.
- **Figma foi removido como dependência operacional.**

## Gates

- UX-001 Inventário/Jornadas: **PASS**.
- UX-005 Arquitetura de Informação: **PASS**.
- Design System v1 textual: **PASS**.
- UX-010 Master Prototype: **IN_PROGRESS**.
- UX-030 Responsive/A11y: **NOT_RUN**.
- UX-050 UI-as-Code/Handoff: **IN_PROGRESS**.

## Evidência KB2Ops incorporada

As 10 telas do designer estão formalizadas em `heritage-pack-v1.md`. North Stars:

1. Knowledge Workspace;
2. Knowledge List;
3. Resolvedor futuro.

Não foram transportados automaticamente AI Ready, scores, campos históricos, métricas ou writers legados.

## Arquitetura congelada v1

Curadoria:

`Knowledge List -> Knowledge Workspace -> domínio ativo -> salvar -> feedback -> permanecer no artigo`

Workspace:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Context Panel opcional quando houver informação real.

Runtime atual representado: Visão geral, Summary e Classificação.

Review & Governança / Histórico permanecem reservas arquiteturais até SPEC própria.

## Nova abordagem — UI as Code

Documento: `ui-as-code-strategy.md`.

Artefato canônico passa a ser um protótipo HTML/CSS/JS versionado em `prototype/`, executável diretamente no navegador e sem npm, build, conta externa ou licença.

Figma permanece apenas como artefato histórico. Nenhum gate atual ou futuro da UX-001 depende dele.

## Próximo passo exato

1. versionar/fechar protótipo executável List + Workspace + Summary + Classificação + States;
2. executar QA em desktop largo, 1024px, 782px e mobile de contingência;
3. verificar teclado/foco, labels e semântica de estado;
4. corrigir gaps do protótipo;
5. fechar UX-010, UX-030 e UX-050;
6. autorizar planejamento da SPEC-003.

## Critério de avanço para SPEC-003

- UX-010 PASS;
- Workspace master aprovado;
- Summary/Classificação encaixados sem regressão;
- Review/Governança acomodado sem congelar domínio;
- responsive/accessibility verificados;
- protótipo UI as Code versionado e reproduzível.

## Regra

“Quem não sabe onde está, não sabe para onde quer ir.” A UX agora também segue uma regra de independência: **ferramenta externa não pode ser caminho crítico do projeto**.

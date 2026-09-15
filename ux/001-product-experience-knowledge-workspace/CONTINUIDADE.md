# Continuidade — UX-001 Product Experience & Knowledge Workspace

## Estado final

- SPEC-002 encerrada; baseline funcional `0.2.0-rc.1`.
- UX-001 concluída para baseline de produto.
- Figma removido como dependência operacional.
- UI as Code v0.2 é o artefato visual executável canônico.
- SPEC-003 Review & Governança está autorizada para planejamento, não para implementação antecipada.

## Gates

- UX-001 Inventário/Jornadas: **PASS**.
- UX-005 Arquitetura de Informação: **PASS**.
- Design System v1: **PASS**.
- UX-010 Master Prototype: **PASS**.
- UX-030 Responsive/A11y do protótipo: **PASS**.
- UX-050 UI-as-Code/Handoff: **PASS**.

## Artefatos principais

- `heritage-pack-v1.md`;
- `information-architecture.md`;
- `design-system-v1.md`;
- `responsive-accessibility-v1.md`;
- `ux-baseline-v1.md`;
- `prototype/index.html`;
- `prototype/prototype.css`;
- `prototype/prototype.js`;
- `prototype/QA-v0.2.md`.

## Arquitetura congelada v1

Curadoria:

`Knowledge List -> Knowledge Workspace -> domínio ativo -> salvar -> feedback -> permanecer no artigo`

Workspace:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Context Panel opcional;
- Visão geral;
- Summary;
- Classificação.

Review & Governança / Histórico possuem apenas encaixe arquitetural; passam a ser responsabilidade da SPEC-003.

## Próxima frente

Canônica:

`specs/003-review-governanca/`

A SPEC-003 deve começar por R-001 Current State / profiling read-only. Nenhum estado de governança, reviewer, score, histórico ou AI Ready está autorizado antes do Domain Contract R-010.

## Regra

Ferramenta externa nunca é caminho crítico. A baseline visual agora é reproduzível com arquivos versionados e navegador comum.

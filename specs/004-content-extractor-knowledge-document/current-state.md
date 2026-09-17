# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: Core Blocks como destino editorial canônico.
- ADR-004-002: Post-Centric Management Workspace.
- T091/T093/T094/T096/T097/T098.2/T099A/T099B/T099C: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- antigo T100A Batch Authorization Pack: **SUPERSEDED BEFORE EXECUTION**.
- novo T100A Post Management Workspace: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**.

## T099C — último write comprovado

Evidência: `evidence/g245-t099c-canary-pass-20260917T212107Z.json`.  
SHA-256 bruto: `3ddb5f55053675699c9269acfdcb81a03e440be03236389777ddb4144b04ba3a`.

Post 358 terminou restaurado byte-a-byte, journal `rolled_back`, lock livre e sem erros.

## Decisão T100

O post passa a ser a unidade central do produto. Não haverá uma UX orientada a batches/jobs para atividades editoriais.

Rota canônica:

`Base de Conhecimento → Gerenciar → post_id`

Dentro da mesma Workspace ficam:
- Visão geral;
- Conteúdo;
- Summary;
- Classificação;
- Inteligência;
- Core Blocks;
- Review & Governança;
- Histórico.

Processamentos globais read-only continuam permitidos, mas seus resultados devem aparecer na Workspace do post correspondente.

## T100A — implementação

Novas classes:
- `includes/class-post-activity-registry.php`;
- `includes/class-post-management-context.php`;
- `includes/class-post-management-activities.php`.

Integração deliberada em `class-admin-page.php`:
- preserva lista e botão **Gerenciar**;
- preserva Summary/Classificação/Review/Histórico;
- preserva o mesmo `post_id` ao trocar de atividade;
- adiciona Conteúdo/Inteligência/Core Blocks;
- substitui o rótulo genérico WordPress/Elementor pela fonte efetivamente detectada.

As três atividades novas são read-only. Nenhum novo writer, persistência, execução IA ou rede externa foi habilitado.

## Próximos passos

1. instalar T100A na homologação;
2. revisar lista → Gerenciar em artigos representativos, incluindo post 358;
3. validar visual e navegação sem regressão;
4. confirmar na aba Core Blocks do post 358: journal `rolled_back` e lock `free`;
5. somente após T100B PASS evoluir ações post-scoped.

## Guardrails

- UX-002 permanece baseline.
- Gutenberg plugin não é dependência.
- Elementor não é removido antes de dependência zero.
- source mixed continua humano.
- novas atividades T100A não escrevem.
- antigo ZIP de batch está invalidado.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

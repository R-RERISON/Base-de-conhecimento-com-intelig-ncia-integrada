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
- novo T100A Post Management Workspace: **PASS LOCAL**.
- T100B Workspace Human/Environmental Acceptance: **PASS CONFIRMADO PELO USUÁRIO**.
- T100C Core Blocks Post Activity: **PASS AMBIENTAL / READ-ONLY**.
- T100D Persistent Single-Post Migration: **PASS AMBIENTAL**.

## T099C — canário com rollback comprovado

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

## T100C — PASS ambiental

A aba Core Blocks agora possui preparação operacional por artigo, ainda sem writer:
- estado operacional por `post_id`;
- blocos esperados;
- journal/lock;
- `authorization_id` determinístico sob `core_blocks_migrate_v1`;
- download de Authorization Pack individual, `authorized=false`;
- botão futuro de migração visível, porém desabilitado.

Evidência ambiental: `evidence/g245-t100c-core-blocks-auth-post-358-20260917T235220Z.json`.\n\nPost 358: `ready_for_authorization`, dry-run `ready`, journal `rolled_back`, lock `free`, `core/freeform`, authorization_id `17c002d3ccc770c6ef154fdbed28cbd0c8d198411c84e168fe9aadb1b7a41af0`.

## T100D — PASS ambiental

Evidência: `evidence/g245-t100d-persistent-migration-pass-post-358-20260918T100755Z.json`.

SHA-256 bruto: `0d8072056fe6653bc6595c8b41c28c1445d3d5bea8b637e182d7a1d5871aa94f`.

Post 358 terminou persistido em Core Blocks: apply verificado, hash final igual ao esperado, `_elementor_data` inalterado, journal final `applied`, rollback não executado e zero erros.

## Gate atual — T100E Engineering Consolidation

Antes de ampliar IA ou migração para outros artigos, consolidar runtime, testes, boundary produto/engenharia, build/release e serviços defensivos. Contrato: `t100e-engineering-consolidation-contract-v1.md`.

## Guardrails

- UX-002 permanece baseline.
- Gutenberg plugin não é dependência.
- Elementor não é removido antes de dependência zero.
- source mixed continua humano.
- novas atividades T100A não escrevem.
- antigo ZIP de batch está invalidado.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

## T100E — Engineering Consolidation

Status: **IN PROGRESS / ZERO EDITORIAL WRITE**.

Concluído nesta rodada:
- runtime baseline congelado;
- runner estático único v1.1;
- T100D one-shot retirado do runtime ativo e mantido somente como fonte histórica;
- classificação `product / defensive_product / legacy_compat / engineering_test_only`;
- builder determinístico de ZIP por dependências ativas;
- candidate build `0.4.0-g245-consolidation-t100e.1` com 54 arquivos totais / 49 PHP / 0 lint failures;
- `Production_Preflight` mantido ativo até existir substituto ambiental equivalente.

Achados ainda abertos:
- famílias defensivas Block/Elementor paralelas;
- ferramentas de engenharia permanecem no source tree;
- boundary source/artifact precisa ser formalizado por manifesto de release;
- matriz ambiental da Workspace ainda precisa ser consolidada.

T100E-E5 concluído: a família histórica de migração Elementor foi classificada como ilha sem consumidor de produto e saiu do runtime ativo; `Elementor_Adapter` permanece para leitura legada.

HE5-001 concluído localmente: Block Journal/Store recebeu validação de identidade/estado, transição durável, envelope e readback. Teste local 15/15 PASS.

Candidate: `0.4.0-g245-consolidation-t100e.3`, SHA-256 `8cdb59c46fa80a8a642fd555e7d87b896ae757358149a11e71978e9df9b717f3`, 40 PHP, 38 requires, 0 lint failures, build determinístico reproduzido.

HE5-001: **PASS AMBIENTAL**. O journal `applied` do post 358 permaneceu legível sob o hardening, com `noop`, `no_action_required`, lock livre e sem novo write.

Próximo passo: **T100E-E6 — Workspace Regression Matrix**.


## UX-003 — Consolidação visual

Status: **PASS AMBIENTAL**.

Build: `0.4.0-g245-ux003.1`.  
SHA-256: `758118f5bf9f0a06f05b05a73992cb47ab4c03ea8c32f18fa26f0e2c3ba88e2f`.

Mudanças:
- Preflight removido do menu visível;
- navegação superior única;
- Visão geral sem menu duplicado;
- interface principal padronizada em pt-BR;
- jargão de desenvolvimento removido da superfície de produto;
- Workspace full-width;
- estados Core Blocks traduzidos para linguagem de produto;
- nenhum novo write.

Contrato: `ux003-workspace-visual-contract-v1.md`.

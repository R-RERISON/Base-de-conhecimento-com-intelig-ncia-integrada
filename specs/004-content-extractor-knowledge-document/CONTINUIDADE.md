# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

## Estado atual

- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- ADR-004-001: Core Blocks como destino editorial canônico.
- ADR-004-002: Post-Centric Management Workspace.
- T091/T093/T094/T096/T097/T098.2/T099A/T099B/T099C: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T100 batch: **SUPERSEDED BEFORE EXECUTION**.
- T100A Workspace: **PASS LOCAL**.
- T100B Workspace Human/Environmental Acceptance: **PASS CONFIRMADO PELO USUÁRIO**.
- T100C Core Blocks Post Activity: **PASS AMBIENTAL / READ-ONLY**.
- T100D Persistent Single-Post Migration: **PASS AMBIENTAL**.

## Evidência T099C

Arquivo: `evidence/g245-t099c-canary-pass-20260917T212107Z.json`.  
SHA-256 bruto: `3ddb5f55053675699c9269acfdcb81a03e440be03236389777ddb4144b04ba3a`.

Post 358:
- apply Core Blocks verificado;
- rollback imediato verificado;
- conteúdo final igual ao original;
- `_elementor_data` igual ao original;
- journal final `rolled_back`;
- lock livre;
- errors=[];
- `t099c_canary_pass=true`.

## Arquitetura T100

**Post é a unidade central de gerenciamento.**

A UX final deve convergir para a Workspace existente:

`lista → Gerenciar → Workspace do post`

Atividades canônicas:
1. Visão geral
2. Conteúdo
3. Summary
4. Classificação
5. Inteligência
6. Core Blocks
7. Review & Governança
8. Histórico

Telas T09x permanecem ferramentas de engenharia/homologação durante a transição e só poderão ser removidas após paridade.

## T100A

Contrato: `t100-post-management-workspace-contract-v1.md`.

Runtime:
- `class-post-activity-registry.php`;
- `class-post-management-context.php`;
- `class-post-management-activities.php`;
- integração deliberada em `class-admin-page.php`.

Conteúdo, Inteligência e Core Blocks começam read-only.

No post 358 a aba Core Blocks deve refletir o audit trail já comprovado: journal `rolled_back`, lock `free`.

## T100D — PASS ambiental

Post 358 foi migrado persistentemente para `core/freeform` com hash final esperado, `_elementor_data` intacto, journal `applied`, rollback não executado e errors=[]. Evidência: `evidence/g245-t100d-persistent-migration-pass-post-358-20260918T100755Z.json`.

## Próximo gate exato — T100E Engineering Consolidation

Sem novas features neste gate. Inventariar runtime, consolidar runner anti-regressão, separar produto de engenharia, formalizar build/release reproduzível e avaliar consolidação de journal/lock/stale/auth/snapshot/rollback antes de escalar IA ou migração.

## Guardrails absolutos

- não instalar/executar o antigo T100 Batch Authorization Pack;
- novas atividades T100A não escrevem;
- nenhuma ação global pode alterar vários posts implicitamente;
- writes futuros são explicitamente post-scoped;
- mixed exige humano;
- Elementor permanece até dependência zero;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

## T100E — progresso

- E1 runtime inventory: concluído;
- E2 static regression runner v1.1: concluído;
- E3 runtime classification: concluído;
- E4 deterministic release builder candidate: concluído;
- T100D executor consumido: flag OFF / require condicional;
- candidate build: `0.4.0-g245-consolidation-t100e.1`;
- candidate SHA-256: `34ed3902e4514054b6db75b0a44951dab2b6ccbf358f902108155b81ef8c701b`;
- 49 PHP no candidate, 0 lint failures;
- nenhuma mutação editorial em T100E.

### T100E-E5 — concluído localmente

- mapa de equivalência congelado;
- família histórica de migração Elementor retirada do runtime ativo;
- `Elementor_Adapter` preservado;
- Block Journal/Store endurecido (HE5-001);
- teste HE5-001: 15/15 PASS;
- candidate `0.4.0-g245-consolidation-t100e.3`;
- SHA-256 `8cdb59c46fa80a8a642fd555e7d87b896ae757358149a11e71978e9df9b717f3`;
- 40 PHP / 38 requires / 0 lint failures;
- T100D OFF;
- Elementor writer OFF;
- zero write editorial durante T100E.

### Próximo passo exato

**HE5-001 environmental compatibility: PASS**. Post 358 permaneceu `applied`, `noop/no_action_required`, lock livre e sem novo write.


## UX-003 — próximo checkpoint visual

Instalar `0.4.0-g245-ux003.1` em homologação e validar:
- menu lateral sem Preflight G-245;
- largura total do wp-admin;
- navegação superior única;
- Visão geral apenas informativa;
- pt-BR consistente;
- Blocos do WordPress com estados humanizados;
- ausência de regressão funcional.

UX-003: **PASS AMBIENTAL**.

### Próximo passo exato

**T100E-E6 — Workspace Regression Matrix**, seguido por **E7 Production Readiness Exit** e **G-250 Lifecycle/RC**.


### T100E-E6 — ação ambiental

Instalar `0.4.0-g245-e6.1` e acessar diretamente:

`/wp-admin/admin.php?page=bdc-kb-t100e-e6`

Executar a matriz e baixar o JSON. PASS esperado:

`gate_result.t100e_e6_workspace_regression_pass=true`.

Após PASS: T100E-E7 Production Readiness Exit.

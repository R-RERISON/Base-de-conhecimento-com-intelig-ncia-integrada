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
- T100C Core Blocks Post Activity: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**.

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

## Próximo gate exato — validação ambiental T100C

Na Workspace de um post elegível, validar:
- `Core Blocks` permanece no mesmo `post_id`;
- `Estado operacional = ready_for_authorization` quando aplicável;
- blocos esperados corretos;
- journal/lock coerentes;
- `authorization_id` presente e estável sem drift;
- botão **Baixar Authorization Pack deste post** funciona;
- pack retorna `authorized=false` e exige autorização humana explícita;
- botão **Migrar para Core Blocks** permanece desabilitado;
- nenhuma mutação editorial ocorre.

Após PASS, o próximo gate é **T100D — executor unitário Core Blocks**, nunca em lote e nunca sem `post_id + authorization_id` específicos.

## Guardrails absolutos

- não instalar/executar o antigo T100 Batch Authorization Pack;
- novas atividades T100A não escrevem;
- nenhuma ação global pode alterar vários posts implicitamente;
- writes futuros são explicitamente post-scoped;
- mixed exige humano;
- Elementor permanece até dependência zero;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

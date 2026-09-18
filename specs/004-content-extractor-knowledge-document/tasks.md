# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: PASS/CLOSED/main com KD 2.1.0.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — WordPress Core Blocks como destino editorial canônico.
- ADR-004-002: ACEITA — Post-Centric Management Workspace.
- T091/T093/T094/T096/T097/T098.2/T099A/T099B/T099C: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T100A Batch Authorization Pack: **SUPERSEDED BEFORE EXECUTION**; ZIP não instalado/executado.
- T100A Post Management Workspace: **PASS LOCAL**.
- T100B Workspace Human/Environmental Acceptance: **PASS CONFIRMADO PELO USUÁRIO**.
- T100C Core Blocks Post Activity: **PASS AMBIENTAL / READ-ONLY**.
- T100D Persistent Single-Post Migration: **PASS AMBIENTAL**.
- T100E Engineering Consolidation: **IN PROGRESS / ZERO EDITORIAL WRITE**.
- G-250: NOT_RUN.

## T099C — PASS AMBIENTAL

Evidência: `evidence/g245-t099c-canary-pass-20260917T212107Z.json`.  
SHA-256 bruto: `3ddb5f55053675699c9269acfdcb81a03e440be03236389777ddb4144b04ba3a`.

Comprovado no post 358:
- authorization_id revalidado;
- journal durável + lock exclusivo;
- `legacy_html -> core/freeform` temporário;
- apply SHA-256 esperado = observado;
- `_elementor_data` preservado;
- rollback imediato verificado;
- `post_content` final igual ao original byte-a-byte;
- latest journal `rolled_back`;
- lock livre;
- errors=[];
- `t099c_canary_pass=true`.

## Rebaseline T100

A expansão por batch foi interrompida antes de qualquer execução. A arquitetura passa a tratar o **post como unidade primária de gerenciamento**.

A superfície canônica já existente é preservada:

`Base de Conhecimento → lista de artigos → Gerenciar → Workspace do post`

Telas/gates T09x continuam como ferramentas de engenharia e homologação; não são a UX final.

## T100A — Post Management Workspace

Contrato: `t100-post-management-workspace-contract-v1.md`.  
ADR: `ADR-004-002-post-centric-management-workspace.md`.

Entregas:
- `Post_Activity_Registry`;
- `Post_Management_Context`;
- `Post_Management_Activities`;
- atividade **Conteúdo** read-only;
- atividade **Inteligência** read-only;
- atividade **Core Blocks** read-only;
- Summary/Classificação/Review/Histórico preservados;
- botão **Gerenciar** e rota por `post_id` preservados;
- Core Blocks mostra readiness/journal/lock sem writer;
- IA registrada sem execução/modelo/rede externa.

## Próximos subgates

- [x] T099C canário real + rollback.
- [x] ADR-004-002 Post-Centric Management Workspace.
- [x] T100A implementação local + lint + invariantes anti-regressão.
- [x] T100B: aceite ambiental/visual da Workspace em homologação — confirmado pelo usuário.
- [x] T100C: implementação local da Core Blocks Activity post-scoped + Authorization Pack read-only.
- [x] T100C ambiental: post 358 ready_for_authorization, journal rolled_back, lock free, Authorization Pack válido.
- [x] T100D autorizado explicitamente para `post_id=358 + authorization_id=17c002d3...a41af0`; build local PASS.
- [x] T100D execução ambiental persistente no post 358: PASS; journal `applied`, rollback não executado, `_elementor_data` intacto.
- [x] T100E-E1 Runtime Inventory baseline.
- [x] T100E-E2 Static Regression Runner v1.1.
- [x] T100E-E3 Runtime classification product/defensive/legacy/engineering.
- [x] T100E-E4 deterministic release builder candidate.
- [ ] T100E-E5 defensive service equivalence/consolidation map.
- [ ] T100E-E6 Workspace regression matrix.
- [ ] T100E-E7 production-readiness exit.
- [ ] T101/T102: próximos gates funcionais somente após T100E.
- [ ] T101: dependência residual Elementor / gate de retirada.
- [ ] G-250 Lifecycle/RC.

## Regras

1. Post é a unidade primária de gerenciamento.
2. Leitura/análise global pode ser massiva; resultados convergem para a Workspace individual.
3. Write editorial permanece post-scoped.
4. Core Blocks são o destino canônico.
5. Plugin Gutenberg não é requisito.
6. Elementor permanece até dependência zero.
7. Nenhum writer `_elementor_data` será implementado como destino.
8. KD não é representação editorial lossless.
9. Mixed exige humano.
10. UX-002 é baseline visual e não pode regredir sem evidência.
11. Trabalho incompleto permanece fora de `main`.
12. O antigo T100 batch não deve ser instalado nem executado.

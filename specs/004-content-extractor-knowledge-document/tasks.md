# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: PASS/CLOSED/main com KD 2.1.0.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — WordPress Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- T087C writer Elementor: CANCELADO / SUPERSEDED antes de implementação.
- T090: PASS LOCAL / READ-ONLY.
- T091: PASS AMBIENTAL.
- T092: PASS LOCAL / READ-ONLY.
- T093: PASS AMBIENTAL.
- T094: PASS AMBIENTAL.
- T095 Migration Fidelity Source v1: PASS LOCAL / READ-ONLY.
- T096 Lossless Core Block Serialization: PASS AMBIENTAL.
- T097 Static Editorial Parity + stale-source: PASS AMBIENTAL.
- T098.1 Block Migration Readiness: FAIL CONTROLADO / SEM MUTAÇÃO.
- T098.2 Block Migration Readiness: PASS AMBIENTAL.
- T099A Journal Store + Lock Smoke: PASS AMBIENTAL.
- T099B Authorization Pack: PASS AMBIENTAL / READ-ONLY.
- T099C canário real: **AUTHORIZED / EXECUTION PENDING**.
- G-250: NOT_RUN.

## T099B — PASS AMBIENTAL
Evidência: `evidence/g245-t099b-authorization-pack-20260917T193757Z.json`.
SHA-256 bruto: `04d47e7839d67b34af6abf8b4ace87238f934f7ca751bc222e427206d813d8ce`.

Candidato congelado:
- post_id 358;
- `LIA | Laboratório de Inteligência Analítica`;
- publish / legacy_html / low risk;
- 1764 bytes, 2 links, 0 imagens, 1 tabela;
- shortcodes 0 / dangerous tags 0 / Core block comments 0;
- `_elementor_data` vazio;
- expected block `core/freeform`;
- authorization_id `1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`.

## T099C — autorizado
Registro: `t099c-authorization-20260917.md`.
Build: `0.4.0-g245-canary-t099c.1`.

Invariantes:
- post 358 somente;
- authorization_id/hashes revalidados antes do write;
- `manage_options`, `edit_post`, `unfiltered_html` obrigatórios;
- journal durável antes do write;
- lock exclusivo;
- stale-source recheck imediatamente antes do write;
- `wp_update_post()` somente para `WP_Post.post_content`;
- `_elementor_data` não é destino de write;
- verificar SHA-256 + `core/freeform` após apply;
- rollback imediato obrigatório;
- exigir post_content e `_elementor_data` iguais ao original após rollback;
- persistir trilha `prepared → applied/partial_failure → rolled_back`;
- liberar lock;
- sem batch.

## Próximos subgates

- [x] T099B Authorization Pack.
- [x] autorização humana específica para post 358 + authorization_id congelado.
- [ ] executar T099C em homologação e versionar evidência.
- [ ] somente se T099C PASS: T100 batch controlado.
- [ ] T101 dependência residual Elementor / gate de retirada.
- [ ] G-250 Lifecycle/RC.

## Regras
1. Core Blocks são o destino canônico.
2. Plugin Gutenberg não é requisito.
3. Elementor permanece até dependência zero.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Write em `post_content` exige gates e autorização explícitos.
6. KD não é representação editorial lossless.
7. Raw payload não sai em runners de corpus.
8. Mixed não é decidido automaticamente.
9. UX-002 não pode regredir.
10. Trabalho incompleto permanece fora de `main`.

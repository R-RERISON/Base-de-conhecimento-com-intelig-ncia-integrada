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
- T099C canário real: **PASS AMBIENTAL / APPLY + VERIFY + ROLLBACK**.
- T100A Batch Authorization Pack (5 low-risk): **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE / READ-ONLY**.
- T100B batch canary: BLOCKED por autorização específica do lote.
- G-250: NOT_RUN.

## T099C — PASS AMBIENTAL

Evidência: `evidence/g245-t099c-canary-pass-20260917T212107Z.json`.  
SHA-256 bruto: `3ddb5f55053675699c9269acfdcb81a03e440be03236389777ddb4144b04ba3a`.

Escopo executado:
- post 358 — `LIA | Laboratório de Inteligência Analítica`;
- authorization_id `1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`;
- `legacy_html -> core/freeform` temporário;
- journal durável + lock exclusivo + stale/auth rechecks;
- `wp_update_post()` somente em `post_content`;
- `_elementor_data` preservado.

Resultado:
- authorization computado = autorizado;
- prepared event 351280;
- applied event 351282;
- rolled_back event 351283;
- apply SHA-256 esperado = observado;
- `core/freeform` validado;
- rollback verificado;
- `post_content` final = original byte-a-byte;
- `_elementor_data` final = original byte-a-byte;
- lock livre após execução;
- latest journal state `rolled_back`;
- errors=[];
- `t099c_canary_pass=true`;
- duração 211 ms.

O journal de auditoria do post 358 permanece intencionalmente persistido; portanto esse post não participa automaticamente do próximo lote.

## T100A — Batch Authorization Pack

Contrato: `t100a-batch-authorization-pack-contract-v1.md`.  
Runtime: `includes/class-block-migration-batch-authorization-pack-smoke.php`.

Objetivo: congelar exatamente 5 artigos `legacy_html` low-risk para o primeiro teste de executor em lote, sem executar qualquer write.

Seleção:
- dry-run ready;
- journal Block Migration = zero;
- lock = free;
- `_elementor_data` vazio;
- 1–30.000 bytes;
- zero shortcodes registrados;
- zero script/iframe/form/object/embed/style;
- zero Core block comments;
- até 10 links, 1 imagem, 1 tabela;
- ordem `risk_score ASC`, `post_id ASC`;
- batch size = 5.

O pack gera `item_authorization_id` por artigo e um `batch_authorization_id` canônico sobre os cinco itens congelados.

## Futuro T100B

Somente após autorização explícita do `batch_authorization_id`:

- exatamente os 5 itens congelados;
- sequencial, concurrency=1;
- stop-on-first-failure;
- journal + lock + stale/auth recheck por item;
- apply + verify + rollback imediato por item;
- nenhum item seguinte roda se o anterior falhar.

## Próximos subgates

- [x] T099C canário real + rollback.
- [ ] executar T100A e versionar Authorization Pack do lote.
- [ ] solicitar autorização explícita do `batch_authorization_id` específico.
- [ ] T100B: batch canary de 5 itens com rollback imediato item a item.
- [ ] definir gate de persistência definitiva após T100B.
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
11. A autorização T099C não autoriza T100B.

# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates
- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097/T098.2/T099A/T099B/T099C: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T100A: IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE / READ-ONLY.
- T100B: BLOCKED por autorização específica do lote.

## T099C — PASS AMBIENTAL
Evidência: `evidence/g245-t099c-canary-pass-20260917T212107Z.json`.  
SHA-256 bruto: `3ddb5f55053675699c9269acfdcb81a03e440be03236389777ddb4144b04ba3a`.

Ambiente: WordPress 6.9.4 / PHP 8.5.10 / build `0.4.0-g245-canary-t099c.1`.

Canário:
- post 358 — `LIA | Laboratório de Inteligência Analítica`;
- source `legacy_html`;
- authorization_id congelado e revalidado;
- capabilities `manage_options`, `edit_post`, `unfiltered_html` PASS;
- journal inicial 0; lock inicial free.

Execução comprovada:
1. prepared journal persistido (event 351280);
2. Core Block serialization aplicada via `wp_update_post()`;
3. SHA-256 aplicado igual ao esperado;
4. `core/freeform` + payload original verificados;
5. `_elementor_data` não alterado;
6. applied journal persistido (event 351282);
7. rollback imediato via `wp_update_post()`;
8. `post_content` final igual ao original byte-a-byte;
9. `_elementor_data` final igual ao original byte-a-byte;
10. rolled_back journal persistido (event 351283);
11. lock livre no final;
12. errors=[];
13. `t099c_canary_pass=true`.

O journal de auditoria permanece intencionalmente no post 358. Efeitos normais de `post_modified`/revisões podem ter ocorrido, conforme contrato, mas o corpo editorial terminou restaurado.

## T100A — Batch Authorization Pack
Contrato: `t100a-batch-authorization-pack-contract-v1.md`.  
Runtime: `includes/class-block-migration-batch-authorization-pack-smoke.php`.

Seleciona deterministicamente 5 artigos low-risk, excluindo posts com journal/lock residual. O post 358 é excluído pelo audit trail do T099C.

T100A é estritamente read-only e gera:
- identidades/hashes atuais por item;
- `item_authorization_id` por post;
- `batch_authorization_id` coletivo;
- ordem de execução congelada;
- plano futuro T100B sequencial, concurrency=1, stop-on-first-failure e rollback imediato de cada item.

Nenhum batch write está autorizado.

## Próximos passos
- instalar/executar T100A em homologação;
- versionar JSON ambiental;
- apresentar os 5 itens + `batch_authorization_id`;
- obter autorização específica antes do T100B;
- T100B: batch canary de 5 posts, apply/verify/rollback por item;
- após T100B PASS, definir gate de persistência definitiva;
- T101 inventário residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails
- UX-002 não pode regredir.
- plugin Gutenberg não é dependência.
- Elementor não é removido antes de dependência zero.
- source mixed continua humano.
- nenhuma autorização anterior é ampliada implicitamente.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

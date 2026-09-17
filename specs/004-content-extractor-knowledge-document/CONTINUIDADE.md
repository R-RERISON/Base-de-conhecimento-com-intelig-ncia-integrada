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
- T091/T093/T094/T096/T097/T098.2/T099A/T099B/T099C: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T100A: IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE / READ-ONLY.
- T100B: BLOCKED até autorização específica do `batch_authorization_id`.

## Evidência T099C
Arquivo: `evidence/g245-t099c-canary-pass-20260917T212107Z.json`.  
SHA-256 bruto: `3ddb5f55053675699c9269acfdcb81a03e440be03236389777ddb4144b04ba3a`.

Canário executado:
- post 358 — `LIA | Laboratório de Inteligência Analítica`;
- authorization_id `1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`;
- `legacy_html -> core/freeform` temporário;
- apply verificado;
- rollback imediato verificado;
- post_content final = snapshot original;
- `_elementor_data` final = snapshot original;
- journal: prepared 351280 -> applied 351282 -> rolled_back 351283;
- lock free após execução;
- errors=[];
- `t099c_canary_pass=true`.

O audit trail permanece intencionalmente persistido no post 358. Esse post é excluído do próximo lote por possuir journal Block Migration residual legítimo.

## T100A — próximo gate exato
Contrato: `t100a-batch-authorization-pack-contract-v1.md`.  
Runtime: `includes/class-block-migration-batch-authorization-pack-smoke.php`.

T100A é read-only e:
1. percorre o corpus atual;
2. restringe a `legacy_html` com dry-run `ready`;
3. exige zero journal/lock residual e `_elementor_data` vazio;
4. aplica o mesmo perfil low-risk conservador do canário;
5. ordena por `risk_score ASC`, `post_id ASC`;
6. congela exatamente 5 artigos;
7. gera `item_authorization_id` por item;
8. gera `batch_authorization_id` coletivo.

## Futuro T100B
Somente com autorização explícita do lote retornado pelo T100A:
- os 5 itens congelados e nenhum outro;
- sequencial (`concurrency=1`);
- stop-on-first-failure;
- journal, lock, stale/auth recheck por item;
- apply + verify + rollback imediato em cada item;
- nunca avança ao próximo item se o anterior falhar.

## Depois do T100B
- se PASS: definir o gate de persistência definitiva em escala;
- se FAIL: parar lote, usar journal e analisar exatamente o item/estágio;
- T101: dependência residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails absolutos
- UX-002 intacta.
- plugin Gutenberg não é dependência.
- Elementor não é removido agora.
- mixed exige humano.
- autorização T099C não autoriza T100B.
- qualquer drift de um dos 5 itens invalida o lote antes de write.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.

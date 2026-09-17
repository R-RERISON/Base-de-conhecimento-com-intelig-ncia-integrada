# T086 — Resumable Batch Plan Contract v1

**Status:** FROZEN — PASS LOCAL / CONTRACTUAL  
**SPEC:** 004 — G-245  
**Build:** `0.4.0-g245-batch.1`

## Objetivo

Particionar candidatos `ready` provenientes do T085 em batches determinísticos, limitados e retomáveis, sem criar executor, sem persistir checkpoint e sem tocar no conteúdo editorial.

T086 organiza uma futura execução; **não executa migração**.

## Elegibilidade

Somente dry-runs com `dry_run_status=ready` entram no cohort.

Cada candidato elegível deve possuir:

- `post_id` positivo;
- `source_hash_before` SHA-256;
- `projection_hash` SHA-256;
- `dry_run_hash` SHA-256;
- `execution_allowed=false`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`.

Candidato `ready` inválido ou unsafe falha fechado. Estados não-ready são excluídos do cohort, não promovidos silenciosamente.

## Determinismo e deduplicação

- candidatos são deduplicados por `post_id`;
- duplicata idêntica é idempotente;
- duplicata conflitante bloqueia o plano;
- ordenação canônica é crescente por `post_id`;
- `cohort_hash` vincula o conjunto normalizado completo;
- `batch_hash` vincula cohort, offset, batch size e items.

A ordem de entrada não altera cohort ou batch.

## Batch size

- default: 25;
- mínimo: 1;
- máximo: 100;
- valor fora do limite falha fechado.

## Cursor/checkpoint

O cursor é versionado e contém:

- `cursor_version`;
- `cohort_hash`;
- `next_offset`;
- `cursor_hash`.

Regras:

1. cursor adulterado bloqueia;
2. cursor de outro cohort é `stale` e bloqueia;
3. resume começa exatamente no `next_offset`;
4. batches consecutivos não repetem IDs;
5. último batch retorna `done=true` e `next_cursor=null`;
6. T086 não persiste checkpoint — `persists_checkpoint=false`.

A persistência durável de estado de execução será decidida apenas quando o executor real for autorizado, respeitando WordPress-first e princípio de negação.

## Safety invariants

Sempre false:

- `execution_allowed`;
- `writer_allowed`;
- `migration_execution_allowed`;
- `persists_checkpoint`;
- `persists_state`;
- `writes_post_content`;
- `writes_elementor_data`;
- `calls_external_network`;
- `executes_shortcodes`.

## Implementação

Runtime: `includes/class-elementor-migration-batch-plan.php`.

Teste: `tests/unit/spec004-migration-batch-plan.php`.

Resultado local:

- PHP lint runtime: PASS;
- PHP lint teste: PASS;
- **37/37 assertions PASS**;
- ordenação/dedupe: PASS;
- resume/cursor: PASS;
- cursor adulterado/stale: PASS;
- zero duplicidade entre batches: PASS;
- cobertura exata do cohort: PASS;
- determinismo: PASS;
- unsafe/invalid candidate fail-closed: PASS;
- safety zero-write: PASS.

## Próximo gate

T087 será o primeiro gate que fala de **canário controlado e rollback comprovado**. A existência de T086 não habilita executor nem writer. Antes de qualquer mutação real continuam obrigatórios, no mínimo:

- journal durável write-ahead;
- stale-source recheck imediatamente antes do write;
- writer version-gated/autorizado;
- canário explícito;
- rollback testável e comprovado;
- autorização humana explícita.

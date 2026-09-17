# T085 — Migration Dry-run Contract v1

**Status:** FROZEN — PASS LOCAL / CONTRACTUAL  
**SPEC:** 004 — G-245  
**Build:** `0.4.0-g245-dryrun.1`

## Objetivo

Simular de forma determinística e zero-write o que uma futura execução de normalização Elementor faria, sem persistir estado, journal ou conteúdo editorial.

T085 fecha o caminho de decisão antes de batches/canário. **Dry-run `ready` não significa autorização de execução.**

## Entradas

O dry-run consome exclusivamente contratos já fechados:

1. `Elementor_Projection_Plan` — plano determinístico e `source_hash_before`;
2. `Elementor_Gateway` — compatibilidade version-gated, writer ainda bloqueado;
3. `Elementor_Stale_Source_Guard` — validação fresh/stale/blocking.

## Estados

### `ready`

Plano `projectable`, source `fresh` e gateway `compatible_read_only`.

A simulação informa que uma execução futura **teria** de:

1. persistir journal write-ahead;
2. revalidar stale-source imediatamente antes do write;
3. obter autorização de writer no gateway;
4. somente então aplicar a projeção.

Mesmo em `ready`:

- `execution_allowed=false`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`.

### `review_required`

Usado quando o Projection Plan exige revisão humana ou a versão Elementor não está homologada.

**Não simula journal nem apply antes da aprovação humana.**

### `noop`

Usado para `native_noop` / `not_applicable`; nenhuma migração é necessária.

### `blocked`

Falha fechada para, entre outros:

- source stale ou hash inválido;
- gateway blocking/desconhecido;
- Projection Plan blocked/desconhecido;
- qualquer violação de safety no plano.

## Determinismo

O resultado possui `dry_run_hash` SHA-256 calculado sobre JSON canônico. Mesmas entradas devem produzir o mesmo hash e o mesmo JSON canônico.

## Safety invariants

Sempre `false` em T085:

- `persists_state`;
- `persists_journal`;
- `writes_post_content`;
- `writes_elementor_data`;
- `calls_external_network`;
- `executes_shortcodes`;
- `execution_allowed`;
- `writer_allowed`;
- `migration_execution_allowed`.

## Implementação

Runtime: `includes/class-elementor-migration-dry-run.php`.

Teste: `tests/unit/spec004-migration-dry-run.php`.

Resultado local:

- PHP lint: PASS;
- **38/38 assertions PASS**;
- determinismo/hash: PASS;
- stale fail-closed: PASS;
- gateway fail-closed/review: PASS;
- Projection Plan unsafe: PASS;
- review não prepara journal: PASS;
- zero-write flags: PASS.

## Relação com próximos gates

T086 poderá particionar apenas resultados de dry-run elegíveis em batches determinísticos e retomáveis, ainda sem executar writer.

T085 PASS **não autoriza** persistência em `post_content` ou `_elementor_data`, nem transforma o journal storage-neutral em storage durável.

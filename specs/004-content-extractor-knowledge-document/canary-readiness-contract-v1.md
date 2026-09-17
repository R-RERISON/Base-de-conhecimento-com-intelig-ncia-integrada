# T087A — Canary Readiness Contract v1

**Status:** PASS LOCAL / CONTRATUAL — READ-ONLY  
**T087 mutável:** BLOCKED / NÃO AUTORIZADO  
**SPEC:** 004 — G-245

## Objetivo

Avaliar, sem executar qualquer mutação, se um único candidato atende às pré-condições técnicas mínimas para um futuro canário controlado.

T087A não é executor e não transforma autorização em write. Mesmo com todas as pré-condições e autorização simulada, `execution_allowed=false` e `writer_allowed=false`.

## Escopo canário

O primeiro canário é obrigatoriamente **1 item**. `canary_scope_size != 1` bloqueia readiness.

## Pré-condições técnicas

Todas devem ser verdadeiras:

1. escopo exatamente 1;
2. journal storage durável disponível;
3. rollback capsule íntegro;
4. dry-run `ready`;
5. stale-source `fresh`;
6. Elementor em versão homologada (`compatible_read_only` no gateway atual);
7. identidade do candidato coincide com `post_id`, `source_hash_before`, `projection_hash` e `dry_run_hash` do dry-run;
8. dry-run permanece zero-write.

Se qualquer item falhar: `status=blocked`.

## Autorização humana

Se todas as pré-condições técnicas passarem, mas não houver autorização explícita: `status=awaiting_explicit_authorization`.

Uma autorização fornecida ao readiness pode produzir `status=ready_for_controlled_canary`, porém ainda:

- `execution_allowed=false`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- `requires_separate_mutable_executor=true`.

A autorização real não será inferida de mensagens genéricas como “continue” ou “vamos em frente”.

## Invariantes adicionais

Antes de qualquer write real, um executor futuro deve obrigatoriamente:

1. persistir journal write-ahead;
2. revalidar stale-source imediatamente antes do write;
3. executar somente o candidato autorizado;
4. verificar resultado pós-write;
5. comprovar caminho de rollback;
6. abortar e restaurar em qualquer divergência.

## Implementação

Runtime: `includes/class-elementor-canary-readiness.php`.
Teste: `tests/unit/spec004-canary-readiness.php`.

Resultado local:

- lint runtime/test: PASS;
- **25/25 assertions PASS**;
- blockers técnicos: PASS;
- autorização explícita: PASS;
- identidade/hashes: PASS;
- determinismo: PASS;
- zero-write: PASS.

## Estado do T087 completo

**NÃO PASS.** O T087 completo exige canário mutável em homologação e rollback real comprovado. Permanecem faltando:

- storage durável do journal;
- executor mutável mínimo e version-gated;
- seleção do artigo canário;
- autorização humana explícita para a mutação;
- evidência before/write/after/rollback;
- comprovação de restauração integral.

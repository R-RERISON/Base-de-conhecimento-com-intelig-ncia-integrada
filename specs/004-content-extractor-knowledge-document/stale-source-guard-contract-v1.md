# T084 — Stale Source Guard Contract v1

**Status:** FROZEN — PASS LOCAL / CONTRACTUAL  
**SPEC:** 004 — G-245  
**Build:** `0.4.0-g245-guards.1`

## Objetivo

Impedir que qualquer futura execução de migração use um Projection Plan produzido sobre uma fonte editorial que mudou depois do planejamento.

## Autoridade

O guard compara:

- `expected_source_hash`: `source_hash_before` congelado no Projection Plan;
- `current_source_hash`: hash atual reconstruído pelo Knowledge Document no instante da validação.

O guard não cria uma nova fonte da verdade. WordPress/Elementor continuam sendo a fonte editorial.

## Estados

### `fresh`

Hashes válidos e idênticos. O guard de stale foi satisfeito, porém **isso não autoriza writer**.

### `stale`

Hashes válidos e diferentes. Razão obrigatória: `SOURCE_CHANGED_SINCE_PROJECTION`.

Execução futura deve ser abortada. Não é permitido escrever e depois tentar reconciliar.

### `blocking`

Hash esperado ou atual ausente/malformado. Falha fechada.

## Invariantes

1. comparação SHA-256 estrita;
2. mismatch bloqueia antes de qualquer write;
3. hash inválido também bloqueia;
4. não há replan automático seguido de write silencioso;
5. stale exige novo planejamento/revisão conforme o caso;
6. o guard não persiste estado;
7. não executa shortcode;
8. não chama rede externa;
9. não escreve `post_content` nem `_elementor_data`;
10. `writer_allowed=false` e `migration_execution_allowed=false` em T084.

## Implementação

Runtime: `includes/class-elementor-stale-source-guard.php`.

`inspect_post()` reconstrói o Knowledge Document atual e usa seu `source_hash` para a comparação read-only.

`assert_fresh()` retorna erro de bloqueio com status lógico 409 quando o source estiver stale ou inválido.

## Aceite local

Cobertura combinada T083/T084: `tests/unit/spec004-journal-stale-guards.php` — **38 assertions PASS**.

Coberto:

- fresh;
- stale;
- hash esperado inválido;
- inspeção read-only por Knowledge Document;
- bloqueio de execução quando stale;
- todas as flags de safety permanecem false.

## Relação com próximos gates

T085 Dry-run deve consumir o stale-source guard e demonstrar que um plano stale é classificado como bloqueado sem persistência. O primeiro caminho realmente mutável continuará proibido até os gates posteriores e autorização explícita.

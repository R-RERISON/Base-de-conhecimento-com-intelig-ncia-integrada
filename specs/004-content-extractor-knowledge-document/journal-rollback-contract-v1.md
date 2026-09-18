# T083 — Journal / Rollback Contract v1

**Status:** FROZEN — PASS LOCAL / CONTRACTUAL  
**SPEC:** 004 — G-245  
**Build:** `0.4.0-g245-guards.1`

## Objetivo

Definir o contrato de write-ahead journal e rollback antes de qualquer futura persistência editorial Elementor.

T083 **não autoriza writer** e não persiste journal em banco nesta fase. Ele congela as invariantes que qualquer storage adapter e qualquer writer futuro deverão obedecer.

## Invariantes

1. Um registro `prepared` deve existir de forma durável **antes** de qualquer write editorial futuro.
2. O registro deve vincular `run_id`, `post_id`, `source_hash_before` e `projection_hash`.
3. O rollback capsule deve conter o snapshot anterior de `post_content` e `_elementor_data`.
4. O capsule deve possuir hash de integridade independente.
5. Exportações/telemetria não podem expor o payload editorial do rollback capsule.
6. Após write futuro, o journal poderá transicionar somente de `prepared` para `applied` ou `partial_failure`.
7. `applied` e `partial_failure` permanecem rollback-eligible.
8. Rollback deve falhar fechado se o alvo foi alterado depois do write (`source_hash_after` diferente do hash atual).
9. Rollback deve falhar fechado se o capsule estiver ausente ou com hash divergente.
10. Rollback repetido depois de `rolled_back` é idempotent noop.
11. `writer_allowed=false` e `migration_execution_allowed=false` em T083.

## Estados

- `prepared`
- `applied`
- `partial_failure`
- `rolled_back`

## Implementação

Runtime: `includes/class-elementor-migration-journal.php`.

A implementação atual é storage-neutral. `journal_persisted=false` é proposital: o storage físico será selecionado antes do primeiro gate mutável, aplicando WordPress-first e princípio de negação.

Nenhum custom table, option ou metadata adicional deve ser criado apenas por conveniência. A escolha de persistência deve considerar atomicidade, volume, auditabilidade, retenção, rollback e produção.

## Dados públicos vs. privados

`public_record()` remove `rollback_payload` e marca `contains_editorial_payload=false`.

O capsule bruto é operacional/sensível e não deve aparecer em downloads de diagnóstico, logs de aplicação ou telemetria.

## Aceite local

Cobertura combinada T083/T084: `tests/unit/spec004-journal-stale-guards.php` — **38 assertions PASS**.

Coberto:

- preparação válida e inválida;
- hashes de integridade;
- transições applied/partial_failure/rolled_back;
- rollback elegível;
- bloqueio de rollback em target stale;
- bloqueio de capsule adulterado;
- idempotência de rollback repetido;
- zero autorização de writer/migration.

## Limites

T083 PASS não comprova storage durável, atomicidade real ou restauração no WordPress. Essas provas pertencem aos gates mutáveis posteriores. Nenhum write em `post_content` ou `_elementor_data` existe aqui.

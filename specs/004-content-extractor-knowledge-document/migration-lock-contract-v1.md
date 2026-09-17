# T087B-prep — Exclusive Migration Lock Contract v1

**Status:** FROZEN — PASS LOCAL  
**SPEC:** 004 — G-245  
**Objetivo:** impedir concorrência entre edição humana e execução de migração no intervalo crítico do canário.

## Storage

Meta key privada e única por artigo:

`_bdc_kb_migration_lock`

Aquisição usa `add_post_meta(..., true)`, portanto duas execuções concorrentes não podem adquirir o mesmo lock.

## Payload

- `schema_version`;
- `run_id`;
- `token`;
- `actor_id`;
- `acquired_at_gmt`;
- `expires_at_gmt`;
- `ttl_seconds`.

TTL padrão: 300s. Limites: 30–900s.

## Regras

1. aquisição exige post existente, usuário autenticado e `manage_options`;
2. lock já existente falha fechado;
3. lock expirado **não é removido automaticamente**;
4. release exige token exato do lock atual;
5. release repetido após sucesso é idempotent noop;
6. lock por si só nunca autoriza writer/migration;
7. recovery de lock expirado será operação explícita, não takeover automático;
8. o executor mutável futuro deve manter o lock desde a releitura final até conclusão do rollback/commit operacional.

## Razão para não auto-recuperar lock expirado

TTL informa suspeita de abandono, mas não prova que o processo anterior deixou de executar. Auto-takeover criaria risco de duas execuções sobre o mesmo artigo. A política v1 é fail-closed e recuperação manual controlada.

## Implementação

Runtime:

`includes/class-elementor-migration-lock.php`

Teste:

`tests/unit/spec004-migration-lock.php`

## Aceite local

- PHP lint: PASS;
- **18/18 assertions PASS**;
- estado inicial free;
- aquisição exclusiva;
- segunda aquisição concorrente bloqueada;
- token incorreto não libera;
- token correto libera;
- release repetido idempotente;
- TTL mínimo/máximo aplicados;
- `manage_options` obrigatório;
- post/run inválidos fail-closed;
- `writer_allowed=false` e `migration_execution_allowed=false`.

## Relação com T087

Este contrato fecha apenas o lock. O canário continua bloqueado até:

- T083B smoke ambiental PASS;
- executor mutável mínimo revisado;
- candidato selecionado;
- Authorization Pack específico;
- autorização humana específica para o canário.

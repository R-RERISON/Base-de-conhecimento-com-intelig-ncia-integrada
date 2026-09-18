# T083B — Journal Durable Storage Contract v1

**Status:** FROZEN — PASS LOCAL / ENVIRONMENTAL SMOKE PENDING  
**SPEC:** 004 — G-245  
**Build:** `0.4.0-g245-journal-store.2`

## Objetivo

Selecionar e implementar o storage durável WordPress-first exigido pelo contrato de Journal/Rollback antes de qualquer caminho editorial mutável.

T083B **não autoriza writer**. Ele prova a camada de persistência do write-ahead journal e prepara um smoke controlado de homologação.

## Decisão de persistência

Storage escolhido: **post metadata privado append-only**.

Meta key canônica:

`_bdc_kb_migration_journal`

Cada estado persistido é um novo evento em `wp_postmeta`; eventos existentes não são atualizados em place.

### Alternativas rejeitadas

1. **Custom table:** rejeitada nesta fase por aumentar schema/migration/rollback operacional sem necessidade demonstrada.
2. **Options API:** rejeitada por escopo global, risco de autoload/volume e baixa aderência ao ownership por artigo.
3. **Comments API:** rejeitada para o journal por semântica inadequada, risco de efeitos em `comment_count` e maior superfície de exposição na administração de comentários.
4. **Arquivo local:** rejeitado por inconsistência entre nós/containers, backup separado e atomicidade inadequada.

Post metadata vence pelo princípio de negação: é nativo, privado por padrão, associado ao artigo, suporta LONGTEXT, fornece `meta_id` como event id e não altera `post_content` ou `_elementor_data`.

## Modelo append-only

Eventos:

- `prepared`;
- `applied`;
- `partial_failure`;
- `rolled_back`.

Campos externos do evento:

- `event_schema`;
- `parent_event_id`;
- `journal_id`;
- `run_id`;
- `state`;
- `actor_id`;
- `created_at_gmt`;
- `record`.

O `record` mantém as invariantes do `Journal / Rollback Contract v1`.

## Rollback capsule

`post_content` e `_elementor_data` são preservados byte-a-byte dentro do evento durável.

Para evitar transformação de HTML, barras, Unicode ou JSON durante serialização, ambos são persistidos em Base64 dentro do envelope de storage. Na leitura, o conteúdo é decodificado antes da validação de integridade.

O hash `rollback_payload_hash` continua calculado sobre o payload editorial original, não sobre Base64.

## Integridade

Antes de persistir:

1. `journal_hash` válido;
2. `rollback_payload_hash` válido;
3. identidade `run_id/post_id/journal_id` válida;
4. `journal_persisted=true` somente após preparação para storage;
5. `writer_allowed=false`;
6. `migration_execution_allowed=false`.

Após `add_post_meta()`:

1. releitura por `meta_id` obrigatória;
2. `journal_id`, `journal_hash`, `rollback_payload_hash`, `parent_event_id` e actor devem coincidir;
3. divergência dispara fail-safe com remoção imediata do evento recém-criado;
4. se a remoção falhar, retorna `PARTIAL_FAILURE_CRITICAL` operacional via `WP_Error` e log crítico.

## Cadeia linear

Uma transição só pode ser anexada quando seu `parent_event_id` é o último evento durável do artigo e pertence ao mesmo `journal_id`.

Isso bloqueia:

- forks;
- retries fora de ordem;
- transições concorrentes sobre estado stale;
- mistura de journals diferentes no mesmo fluxo.

A busca do último `meta_id` usa um único SELECT read-only em `wp_postmeta`, limitado por `post_id` e meta key. A escrita continua exclusivamente pela Metadata API.

## Segurança

Persistência exige:

- usuário autenticado;
- capability `manage_options`;
- post existente;
- payload <= 16 MiB;
- integridade completa do journal.

A meta key é privada (`_` prefix), não é registrada em REST e não é exposta por telemetria/downloads do plugin.

## Implementação

Runtime:

- `includes/class-elementor-migration-journal.php`;
- `includes/class-elementor-migration-journal-store.php`.

Teste local:

- `tests/unit/spec004-journal-durable-store.php`.

Smoke ambiental, **desabilitado por padrão**:

- `includes/class-elementor-migration-journal-smoke.php`;
- feature flag `BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD=false`.

## Aceite local

Resultado local:

- PHP lint: PASS;
- **22/22 assertions PASS**;
- record não persistido não satisfaz requisito durável;
- round-trip byte-exato de `post_content`;
- round-trip byte-exato de `_elementor_data`;
- prepared/applied/rolled_back persistidos e relidos;
- fork/non-linear chain bloqueado;
- parent inexistente bloqueado;
- capsule adulterado bloqueado;
- `journal_hash` adulterado bloqueado;
- `manage_options` obrigatório;
- latest event por post validado.

## Smoke de homologação

O smoke T083B deve:

1. receber um único post de homologação;
2. capturar hashes editoriais before;
3. gerar Projection Plan e prepared journal;
4. persistir um único evento temporário em postmeta privado;
5. reler e validar capsule byte-exato;
6. remover o evento temporário;
7. comprovar contagem de journal restaurada;
8. comprovar `post_content` e `_elementor_data` inalterados;
9. produzir JSON sem conteúdo editorial e sem post ID em claro.

Gate: `gate.t083b_storage_pass=true`.

## Bloqueio restante

Até o smoke ambiental PASS:

- `journal_storage_durable` não deve ser considerado comprovado no T087A;
- canário mutável permanece bloqueado;
- writer/migration permanecem `false`.

# Block Migration Protection Contract v1 — SPEC-004 / G-245 / T098

## Status

`v1.0.0` — implementação defensiva pré-canário.

## Objetivo

Generalizar os mecanismos de proteção anteriormente construídos para a trilha Elementor e torná-los nativos da migração para WordPress Core Blocks, sem autorizar escrita editorial.

## Primitivas

- `Block_Migration_Journal`: rollback capsule, integridade, transitions e fail-closed;
- `Block_Migration_Journal_Store`: postmeta privado append-only, separado da trilha histórica Elementor;
- `Block_Migration_Dry_Run`: simula journal → lock → stale recheck → write, mas nunca executa;
- `Block_Migration_Batch_Plan`: cohort determinístico, cursor versionado, retomada sem duplicidade;
- `Block_Migration_Lock`: lock exclusivo por post com TTL, capability e token;
- `Block_Migration_Stale_Source_Guard`: T097, obrigatório imediatamente antes de qualquer futuro write.

## Invariantes

1. T098 não implementa writer.
2. `execution_allowed`, `writer_allowed` e `migration_execution_allowed` permanecem `false`.
3. Journal deve estar duravelmente persistido antes de qualquer futuro write.
4. Stale-source deve ser revalidado imediatamente antes do write.
5. Lock exclusivo deve estar ativo antes do write.
6. Source `mixed` permanece fora do cohort automático.
7. Gutenberg `native_noop` e `not_applicable` não entram no cohort.
8. Rollback só é permitido se o alvo atual ainda corresponder aos hashes registrados após o write.
9. Rollback restaura `post_content`; `_elementor_data` é preservado e permanece parte da capsule de proteção durante a transição.
10. Nenhum runner exporta conteúdo editorial, URLs ou post IDs.
11. Plugin Gutenberg não é dependência.
12. UX-002 permanece intocada.

## Store

Meta privada: `_bdc_kb_block_migration_journal`.

A store usa WordPress postmeta append-only e Base64 apenas como transporte reversível do payload dentro do evento. O readback deve validar o record antes de aceitar o evento.

## Lock

Meta privada: `_bdc_kb_block_migration_lock`.

- default TTL: 300s;
- max TTL: 900s;
- capability: `manage_options`;
- aquisição e liberação exigem readback;
- T098 smoke não chama `acquire()`.

## T098 environmental gate

O smoke full-corpus é read-only e deve provar em duas passagens:

- zero errors/throwables/safety violations;
- dry-run hash determinístico;
- journal hash determinístico para todo item `ready`;
- journal preparado em memória para todo item `ready`;
- batch size 25, cobertura integral do cohort, zero duplicidade e zero cursor failure;
- fingerprint editorial before/after idêntico;
- journal store não invocada;
- lock não adquirido;
- nenhum writer chamado.

PASS esperado:

`gate_result.t098_block_migration_readiness_pass=true`.

## Depois do T098

T099 só pode iniciar com Authorization Pack específico para **um** artigo de baixo risco, preferencialmente `legacy_html`, sem meta Elementor ativa, com dry-run `ready`, stale `fresh`, rollback capsule e journal/lock smoke comprovados.

# G-520 / T527 — Rollback & Rebuild Contract v1

**Status:** FROZEN

## Princípio

Search é derivado. Falha do índice nunca altera nem bloqueia a edição do WordPress.

## Feature rollback

Enquanto a feature flag/runtime Search estiver OFF:
- Knowledge List atual permanece disponível;
- nenhuma Projection é necessária;
- nenhum conteúdo editorial muda.

Quando Search v1 estiver ON mas Projection não estiver `ready`:
- usar `wordpress_fallback`;
- resposta marcar `degraded`;
- não tentar rebuild no request.

## Rebuild explícito

Estados:
`not_built -> building -> ready`

Falha:
`building -> failed`.

Fluxo:
1. capability + nonce;
2. marcar state=building;
3. obter corpus canônico WordPress;
4. construir Search Documents deterministicamente;
5. upsert por post_id;
6. validar hashes/counts/versions;
7. remover rows stale somente após passagem completa;
8. registrar source_fingerprint;
9. state=ready.

Durante building, read path usa fallback.

## Idempotência

Se `source_hash`, `document_hash`, document_version e normalizer_version não mudaram:
- não reescrever row;
- resultado NO_CHANGE.

Duas passagens sobre a mesma fonte devem produzir os mesmos document_hash/source_hash.

## Falha parcial

- não tocar em WordPress editorial;
- manter Projection como não autoritativa;
- state=failed/degraded;
- Search cai para fallback;
- novo rebuild pode reparar tudo a partir da fonte canônica.

Não é necessário restaurar uma Projection antiga para garantir integridade editorial.

## Schema change

- usar mecanismo versionado próprio BDC;
- migrations aditivas sempre que possível;
- nunca DROP automático em activation/update;
- incompatibilidade de schema => feature degraded/fallback;
- limpeza destrutiva exige gate explícito.

## Deactivation / uninstall

Deactivation:
- não remove tabela;
- não remove fixtures;
- não altera posts.

Uninstall v1:
- retenção por default;
- remoção definitiva somente futura, explícita e autorizada.

## ASI

Rollback nunca usa ASI como fallback.

Fallback permitido é WordPress nativo.

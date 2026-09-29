# G-520 / T524 — Golden Runner Contract v1

**Status:** FROZEN  
**Runner contract version:** `golden-runner-v1.0.0`

## Inputs congelados

Golden Relevance:
- version `golden-relevance-v1.0.0`;
- set_hash `e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4`.

Technical Challenge:
- version `technical-challenge-v1.0.0`;
- set_hash `2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807`.

## Execução

- explícita;
- POST;
- `manage_options`;
- nonce;
- sem execução automática em page load/activation;
- sem rede;
- sem ASI;
- sem persistir query log.

## Runtime resources

As fixtures canônicas de engenharia permanecem em `specs/`.

O runtime G-550 deve empacotar recursos próprios semanticamente equivalentes no plugin e verificar o mesmo `set_hash` antes de executar. Divergência => `STALE`.

## Golden Relevance semantics

Executar todos os itens.

Itens `active=true` + `severity=blocking`:
- expected deve existir;
- actual_rank > 0;
- actual_rank <= max_rank;
- falha => `blocking_failed++`.

Item `AMBIGUOUS_QUARANTINED`:
- executar para observabilidade;
- nunca escolher novo expected;
- não incrementar blocking_failed;
- divergência é warning.

## Technical Challenge semantics

É gate técnico separado, não Golden blocking.

Cada caso da `technical-challenge-v1.0.0` usa `max_rank=3`.

Esse limite pertence ao `golden-runner-v1.0.0`; alterá-lo exige nova runner contract version e torna evidência anterior STALE.

Falha incrementa `technical_failed`.

G-550 exige:
- Projection `ready`; execução em `wordpress_fallback` não pode gerar PASS;
- `blocking_failed=0`;
- `technical_failed=0`;
- suite/hash/version current.

## Stale

Resultado é `STALE` se divergir qualquer:
- Golden suite_version/set_hash;
- Challenge suite_version/set_hash;
- normalizer_version;
- document_version;
- algorithm_version;
- runner contract version.

## Report

Campos mínimos:
- generated_at;
- environment;
- suite versions/hashes;
- runtime versions;
- status;
- blocking_failed;
- warning_failed;
- technical_failed;
- results com expected/max_rank/actual_rank/top_post_ids/matched_signals.

Sem identidade, IP, session ou journey.

## Status

- `PASS`;
- `FAIL`;
- `NOT_CONFIGURED`;
- `NOT_RUN`;
- `STALE`;
- `TECHNICAL_ERROR`.

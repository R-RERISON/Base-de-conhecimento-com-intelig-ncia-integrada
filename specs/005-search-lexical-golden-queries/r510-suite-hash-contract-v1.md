# R-510 — Suite Hash Contract v1

**Status:** FROZEN  
**Aplica-se a:** T515, Golden Relevance Set e Technical Challenge Set.

## Objetivo

Garantir identidade determinística dos datasets independentemente de pretty-print, nome de arquivo ou metadata externa.

## Algoritmo

`set_hash = SHA-256(UTF-8(canonical_json(items)))`

Regras de canonicalização:
1. ordenar `items` por `id` ascendente;
2. preservar ordem declarada dos arrays internos;
3. objetos usam a ordem de campos definida pelo schema de cada suite;
4. JSON compacto, sem whitespace de apresentação;
5. Unicode e slash sem escape desnecessário;
6. o hash cobre somente `items`, não metadata como timestamp/source evidence.

## Golden Relevance — field order

`id, query, query_norm, expected_post_id, max_rank, severity, source, active, disposition, quarantined, rationale, contract_version`

## Technical Challenge — field order

`id, query, query_norm, expected_post_id, origin, declared_classes, source_field, source_kind, summary_dependent, elementor_semantic_gap, document_frequency, active_for_golden_blocking, contract_version`

## Freeze T515

Golden Relevance:
- suite_version: `golden-relevance-v1.0.0`;
- set_hash: `e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4`;
- total: 6;
- active/blocking: 5;
- quarantined: 1.

Technical Challenge:
- suite_version: `technical-challenge-v1.0.0`;
- set_hash: `2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807`;
- count: 7;
- active_for_golden_blocking: false.

## Regra de stale

Qualquer alteração material em item coberto pelo hash produz novo `set_hash`.

Mudança de expectativa, severidade, active/quarantine, query, expected_post_id ou challenge case exige nova versão ou revisão explícita segundo compatibilidade semântica.

Metadata externa pode mudar sem alterar set_hash, mas evidência de execução deve registrar:
- suite_version;
- set_hash;
- algorithm_version;
- normalizer_version.

## Separação obrigatória

Golden Relevance e Technical Challenge jamais compartilham o mesmo set_hash.

Challenge corpus-derived/synthetic nunca pode ser reinterpretado como consulta real de usuário.

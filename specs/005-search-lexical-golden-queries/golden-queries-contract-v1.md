# Golden Queries Contract v1

**Estado:** DRAFT — congelar em R-510/G-520.

## Objetivo

Transformar relevância em contrato reproduzível antes de evolução de ranking.

## Golden Query

Campos mínimos:

```json
{
  "id": "GQ-001",
  "query": "consulta real",
  "query_norm": "consulta real",
  "expected_post_id": 123,
  "max_rank": 3,
  "severity": "blocking",
  "rationale": "por que este é o artigo oficial esperado",
  "source": "human|legacy_validated|incident|search_log_curated",
  "active": true,
  "contract_version": "1.0.0"
}
```

## Suite

Deve registrar:
- suite_version;
- set_hash;
- algorithm_version;
- normalizer_version;
- generated_at;
- count;
- blocking_failed;
- warning_failed;
- results[].

## Semântica

- zero itens ativos -> `NOT_CONFIGURED`;
- blocking fail > 0 -> `FAIL`;
- somente warnings -> `PASS_WITH_WARNINGS` ou PASS com warnings explícitos;
- nenhuma execução atual -> `NOT_RUN`;
- set_hash/algorithm_version divergente -> `STALE`.

## Resultado por caso

- expected_post_id;
- expected max_rank;
- actual_rank;
- pass;
- top_post_ids;
- sinais/ranking versionado quando disponível.

## Governança

Expectativa deve ser revisada por humano. Engine não pode gerar a própria verdade de ranking.

## Privacidade

Golden report não contém identidade, IP, session ou journey. Query só entra após curadoria explícita.

## Gate

Release com Search afetando ranking exige:
- suite não vazia;
- evidence current;
- blocking_failed = 0.

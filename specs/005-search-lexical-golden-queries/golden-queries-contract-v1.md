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

A origem de uma expectativa Golden deve ser humana/curada ou possuir provenance equivalente explicitamente governada. O engine nunca pode gerar a própria verdade de ranking.

Após a expectativa existir, verificações objetivas de continuidade podem ser automatizadas pelo contrato `r510-automated-golden-validation-contract-v1.md`:
- AUTO_PASS confirma expected/max_rank existente quando evidência é inequívoca;
- AMBIGUOUS_QUARANTINED preserva a expectativa, mas a remove do blocking set sem escolher vencedor;
- AUTO_FAIL bloqueia;
- automação não cria nem substitui expected_post_id.

## Privacidade

Golden report não contém identidade, IP, session ou journey. Query só entra após curadoria explícita.

## Gate

Release com Search afetando ranking exige:
- suite não vazia;
- evidence current;
- blocking_failed = 0.


## Candidate seed recuperado do ASI — 2026-09-18

O T510 recuperou 6 expectativas post-level manuais, todas ativas e com expected post existente.

Essas linhas constituem **seed de paridade**, não Golden Suite v1 aceita automaticamente.

O fato de o legado classificá-las como `warning` não obriga a nova severidade. Em T513, AUTO_PASS recomenda `blocking`; ambiguidades vão para `AMBIGUOUS_QUARANTINED`/warning e ficam fora do blocking set.

A suite v1 final deve registrar novo `set_hash` pelo contrato da SPEC-005; hashes históricos do ASI são metadados de proveniência e não devem ser comparados diretamente com o hash candidate do novo schema.


## Golden vs Technical Challenge

ADR-005-002 separa:
- **Golden Relevance Set**: origem humana/curada/histórica; pode bloquear release;
- **Technical Challenge Set**: corpus-derived/synthetic; prova capacidade técnica, nunca intenção real;
- **Real-world Query Enrichment**: typo/alias reais, enriquecidos pela futura Telemetria.

Technical Challenge não entra no `set_hash` da Golden real como se fosse consulta de usuário. Se T515 congelar ambos, deve manter hashes/versionamento separados ou namespaces explícitos.

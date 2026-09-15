# SPEC-003 — Evidência de Profiling S001 / Gate R-001

## Identificação da execução

- arquivo de evidência: `bdc-kb-review-profile-20260915-133951.json`;
- schema: `1.0.0`;
- build executado: `0.3.0-profile.1`;
- WordPress: `6.9.4`;
- PHP: `8.5.10`;
- multisite: não;
- post type: `post`;
- statuses: `publish`, `draft`, `pending`, `private`, `future`;
- corpus observado: **622 posts**;
- meta rows encontradas nos seis stores candidatos: **0**.

## Segurança do profiling

O próprio relatório confirmou:

- `writes_performed=false`;
- `persistent_report=false`;
- capability `manage_options`;
- `reads_editorial_content=false`;
- `exports_review_notes=false`;
- `exports_user_ids=false`;
- `uses_read_only_sql=true`.

Não houve leitura de `post_title`, `post_content` ou `_elementor_data`.

## Resultado por store histórico

| Store | Posts | Rows | Cobertura | Decisão |
|---|---:|---:|---:|---|
| `_kb2ops_review_state` | 0 | 0 | 0% | NO_EVIDENCE |
| `_kb2ops_review_notes` | 0 | 0 | 0% | NO_EVIDENCE |
| `_kb2ops_reviewed_at` | 0 | 0 | 0% | NO_EVIDENCE |
| `_kb2ops_reviewed_by` | 0 | 0 | 0% | NO_EVIDENCE |
| `_kb2ops_include_ai` | 0 | 0 | 0% | NO_EVIDENCE / fora do contrato atual |
| `_kb2ops_review_history` | 0 | 0 | 0% | NO_EVIDENCE |

## Estado histórico

Os valores históricos conhecidos `unreviewed`, `in_review`, `approved` e `excluded` aparecem somente como referência do código legado. A execução real encontrou distribuição vazia e `contract_authorized=false`.

Logo, **nenhum estado histórico possui evidência de uso no banco atual**.

## Actor / timestamp / histórico

- actors válidos: 0;
- actors inválidos: 0;
- users existentes referenciados: 0;
- timestamps parseáveis: 0;
- timestamps inválidos: 0;
- eventos de histórico: 0;
- posts com qualquer dado de review: 0.

Portanto não existe legado real que possa fornecer reviewer, data da decisão ou trilha auditável para migração.

## Relação com `post_status`

Como não existe nenhum dado de Review/Governança nos seis stores candidatos, não há sobreposição real a medir com `post_status` no ambiente atual. Isso reforça a separação conceitual definida pela SPEC: editorial WordPress permanece independente da governança.

## Política de legado

### Migração

**NÃO AUTORIZADA / NÃO NECESSÁRIA.**

Não há rows legadas nos stores candidatos. Criar migration adapter, dual-read, seed ou fallback aumentaria complexidade sem dado para preservar.

### Compatibilidade

- nenhum reader legado será criado para a primeira slice;
- nenhum writer histórico será preservado;
- `include_ai` continua fora do contrato da SPEC-003;
- `AI Ready` não será inferido;
- estados históricos não são importados automaticamente.

## Interpretação do Gate R-001

R-001 mede se conhecemos o estado real o suficiente para decidir sem adivinhação; ele não exige encontrar dados migráveis.

A combinação de:

1. inventário do código histórico;
2. writer/consumer map conhecido;
3. profiler read-only executado no ambiente real;
4. corpus de 622 posts;
5. zero rows em todos os seis stores candidatos;

é suficiente para concluir o current state.

## Decisão

**Gate R-001: PASS.**

A SPEC-003 está autorizada a fechar o Domain Contract R-010 como um domínio **greenfield**, sem passivo de migração de Review/Governança no ambiente analisado.

# Data Model — SPEC-002 Classificação de Conhecimento

## Conceitos lógicos e origens históricas

| Conceito | Stores históricos | Owner futuro | Primitive | Estado |
|---|---|---|---|---|
| Audiência | `_bdc_es_target_audience`, `_kb2ops_target_audience` | Classificação | Taxonomy/Meta | profiling obrigatório |
| Equipe responsável | `_bdc_es_responsible_team` | Classificação | Taxonomy/Meta | profiling obrigatório |
| Item de catálogo | `_bdc_es_catalog_item` | Classificação | Taxonomy/Meta | profiling obrigatório |
| Serviço | `_kb2ops_service` | Classificação | Taxonomy/Meta | distinto de serviço afetado |
| Serviço afetado | `_bdc_es_affected_service` | Classificação | Taxonomy/Meta | distinto de serviço |
| Tecnologias | `_kb2ops_technologies` | Classificação | Taxonomy/Meta | distinto de sistemas |
| Sistemas envolvidos | `_bdc_es_systems_involved` | Classificação | Taxonomy/Meta | distinto de tecnologias |
| Tipo de conhecimento | `_kb2ops_knowledge_type` | Classificação | Taxonomy/Meta | forte candidato a taxonomy |
| Keywords | `_kb2ops_keywords` | Classificação | Taxonomy/Meta | cardinalidade decide |
| Versões | `_kb2ops_versions` | Classificação | Taxonomy/Meta | provável alta cardinalidade |

## Conceitos explicitamente não equivalentes por default

- `service` ≠ `affected_service`;
- `technologies` ≠ `systems_involved`.

## Audiência

Audiência é o único conceito com equivalência lógica preliminar entre GRE e KB2Ops. Ainda assim, a convergência física exige medir:

- cobertura de cada store;
- posts com ambos;
- igualdade de conjuntos normalizados;
- conflitos;
- valores exclusivos por origem.

## Canonicalização

Nenhuma key/taxonomy canônica nova está autorizada nesta fase.

Se Taxonomy for escolhida, a SPEC deverá definir slug estável e policy de termos. Se Meta for escolhida, deverá definir tipo, cardinalidade, serialização proibida/permitida e contrato de empty/remove.

## Compatibilidade

Stores históricos são leitura de origem. Uma futura bridge poderá ser:

- read-through temporário;
- migração explícita com checkpoint;
- cutover para single-writer.

**Dual-write permanente é proibido.**

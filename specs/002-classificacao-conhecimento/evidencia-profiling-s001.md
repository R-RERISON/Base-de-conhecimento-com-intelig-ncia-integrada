# Evidência S001 — Profiling classificatório real

## Execução

- Arquivo: `bdc-kb-classification-profile-20260914-235424.json`
- SHA-256: `f11356632ee2f5720c6399c38af01bb4d0f4342af7e6e57b049fe0e07dda556b`
- WordPress: 6.9.4
- PHP: 8.5.10
- Plugin de profiling: 0.2.0-profile.1
- Modo: `temporary_classification_profile_read_only`
- Resultado: PASS
- Writes: nenhum
- Conteúdo editorial lido: não

## Escopo

- `post_type=post`
- status: publish, draft, pending, private, future
- total de posts: 622
- total de linhas encontradas nos 11 stores classificatórios perfilados: 36

## Cobertura observada

| Store | Posts | Cobertura | Distintos normalizados |
|---|---:|---:|---:|
| `_bdc_es_target_audience` | 6 | 0,96% | 6 |
| `_kb2ops_target_audience` | 0 | 0% | 0 |
| `_bdc_es_responsible_team` | 9 | 1,45% | 9 |
| `_bdc_es_catalog_item` | 8 | 1,29% | 8 |
| `_kb2ops_service` | 0 | 0% | 0 |
| `_bdc_es_affected_service` | 7 | 1,13% | 7 |
| `_kb2ops_technologies` | 0 | 0% | 0 |
| `_bdc_es_systems_involved` | 6 | 0,96% | 6 |
| `_kb2ops_knowledge_type` | 0 | 0% | 0 |
| `_kb2ops_keywords` | 0 | 0% | 0 |
| `_kb2ops_versions` | 0 | 0% | 0 |

## Sinais de qualidade

1. Todos os stores GRE preenchidos apresentam cardinalidade observada 1,0 no conjunto muito pequeno: praticamente um valor distinto por post.
2. Audiência contém ao menos um valor com vírgula, sugerindo texto livre/múltiplos conceitos em scalar.
3. Equipe responsável contém valores com newline e mistura de formatos (`-`, `/`, caminhos organizacionais e texto narrativo), logo não existe vocabulário controlado confiável para importação automática.
4. Item de catálogo, serviço afetado e sistemas envolvidos possuem poucos valores, todos únicos no conjunto observado.
5. Os stores KB2Ops perfilados estão vazios neste ambiente; ausência nesses stores não prova ausência de classificação em outras superfícies, apenas ausência nas chaves candidatas perfiladas.

## Comparações

- audiência GRE ↔ KB2Ops: sem posts com ambos; `merge_authorized=false`;
- service ↔ affected_service: sem sobreposição observável; `merge_authorized=false`;
- technologies ↔ systems_involved: sem sobreposição observável; `merge_authorized=false`.

## Decisão de profiling C-001

**PASS** para conhecimento do estado atual.

O baixo volume e a heterogeneidade tornam o legado inadequado para promoção automática a vocabulário canônico.

### Decisões

- **NO-GO** para migração automática dos valores históricos.
- **NO-GO** para dual-write permanente.
- Stores históricos permanecem somente como referência read-only durante coexistência.
- A nova camada classificatória nasce limpa e canônica.
- `service` continua distinto de `affected_service`.
- `technologies` continua distinto de `systems_involved`.
- Nenhuma chave histórica é reutilizada como owner físico da nova classificação.

## Primeiro vertical slice autorizado para decisão física

1. audiência;
2. equipe responsável;
3. tipo de conhecimento;
4. item de catálogo.

A ausência de dados históricos para `tipo de conhecimento` é tratada como ausência de passivo de migração, não como autorização para inventar vocabulário. O vocabulário canônico deverá ser gerenciado explicitamente.
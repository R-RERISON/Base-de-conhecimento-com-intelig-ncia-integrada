# Matriz de Mutação — SPEC-002

## S001 — profiling concluído

| Superfície | Read | Write | Regra |
|---|---:|---:|---|
| `WP_Post` | SIM, filtro de escopo | NÃO | profiling não altera editorial |
| postmeta histórico | SIM | NÃO | somente evidência |
| `_elementor_data` | NÃO | NÃO | fora do profiling |
| taxonomias | NÃO | NÃO | profiling não registra owner físico |
| options/transients | NÃO | NÃO | relatório não persiste |
| schema/tabelas | NÃO | NÃO | proibido |

## S003 — runtime autorizado

| Superfície | Read | Write | Autoridade/Regra |
|---|---:|---:|---|
| `WP_Post` | SIM | NÃO | validar `post_type=post`, título/contexto somente leitura |
| `post_title` | SIM | NÃO | editorial protegido |
| `post_content` | NÃO necessário | NÃO | protegido |
| `_elementor_data` | NÃO necessário | NÃO | protegido |
| Summary `_bdc_es_objective/_escalation/_important` | SIM pelo módulo Summary | somente handler Summary existente | classificação não escreve |
| `bdc_kb_audience` | SIM | SIM | owner Classificação, Taxonomy API |
| `bdc_kb_responsible_team` | SIM | SIM | owner Classificação, Taxonomy API |
| `bdc_kb_knowledge_type` | SIM | SIM | owner Classificação, single |
| `bdc_kb_catalog_item` | SIM | SIM | owner Classificação, Taxonomy API |
| metas legadas do slice | SIM | NÃO | referência não canônica, output escapado |
| categorias/tags WordPress | NÃO necessário | NÃO | ownership editorial preservado |
| termos canônicos | SIM | gestão via UI nativa WordPress | artigo não cria termos implicitamente |
| options/transients | NÃO | NÃO | não necessários |
| tabela/schema próprio | NÃO | NÃO | proibido neste slice |
| REST/AJAX | NÃO | NÃO | sem consumidor aprovado |

## Handler de assignment

Somente POST autenticado via `admin-post`.

Ordem obrigatória:

1. validar método;
2. validar `post_id` e post type;
3. validar `edit_post` do alvo;
4. validar nonce ligado ao post;
5. validar shape do payload e allowlist;
6. validar todos os term IDs e taxonomy ownership;
7. snapshot de todas as taxonomias presentes no payload;
8. diff mínimo;
9. aplicar `wp_set_object_terms` apenas no diff;
10. read-after-write;
11. em falha, compensar taxonomias já alteradas;
12. reread e classificar `FAIL_SAFE` ou `PARTIAL_FAILURE_CRITICAL`.

## Invariantes

- GET nunca muta;
- field omitido preserva;
- `[]` remove relações do conceito;
- termo inexistente ou de taxonomy errada falha antes do primeiro write;
- unknown field rejeita o payload inteiro;
- nenhum write nos stores GRE/KB2Ops;
- nenhum write editorial;
- nenhum dual-write permanente;
- classificação e Summary usam formulários/handlers separados.
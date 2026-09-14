# Matriz de Evidência — SPEC-002

| Gate | Evidência mínima | Estado |
|---|---|---|
| C-001 Profiling | JSON real, cobertura/cardinalidade/representação/overlaps | **PASS** — `0.2.0-profile.1`, 622 posts, 36 rows, read-only |
| C-010 Primitive | decisão Taxonomy vs Meta por conceito do slice | **PASS** — Taxonomy API para 4 conceitos; sem migração automática |
| G-001 Editorial | regressão SPEC-001 + zero write editorial | **NOT_RUN no runtime SPEC-002** |
| G-030 Classification | contratos single/multi, empty/remove, diff, reread, compensação, legacy advisory | **DESENHADO / NOT_RUN** |
| G-070 Segurança | capability/nonce/IDOR/mass assignment/XSS/scope | **DESENHADO / NOT_RUN** |
| G-110 UI/UX | wp-admin, labels, foco, teclado, viewport, feedback | **DESENHADO / NOT_RUN** |
| G-130 Lifecycle | package limpo, activation/deactivation e retirada de ferramentas temporárias | **NOT_RUN** |
| Regressão SPEC-001 | Summary e seus gates essenciais continuam válidos | **NOT_RUN após código SPEC-002** |

## Evidência C-001

Arquivo coletado: `bdc-kb-classification-profile-20260914-235424.json`.

SHA-256: `f11356632ee2f5720c6399c38af01bb4d0f4342af7e6e57b049fe0e07dda556b`.

Resumo:

- WordPress 6.9.4 / PHP 8.5.10;
- 622 posts no escopo;
- 36 linhas nos 11 stores perfilados;
- nenhum write;
- nenhum conteúdo editorial lido;
- stores KB2Ops candidatos: 0 rows;
- stores GRE observados: cobertura 0,96%–1,45%;
- `service↔affected_service`: merge não autorizado;
- `technologies↔systems_involved`: merge não autorizado;
- audiência GRE↔KB2Ops: sem sobreposição observável.

## Evidência C-010

Primeiro slice:

- `bdc_kb_audience` — multi;
- `bdc_kb_responsible_team` — multi;
- `bdc_kb_knowledge_type` — single;
- `bdc_kb_catalog_item` — multi.

Todas via WordPress Taxonomy API, namespaced, `public=false`, `show_in_rest=false`, sem meta box/quick edit de assignment.

### Compatibilidade

Legado é somente referência visual read-only. Não existe:

- seed automático;
- auto-map textual;
- fallback canônico;
- dual-write;
- migração destrutiva.

## Regra

Nenhum estado `NOT_RUN`, `FAIL`, `STALE` ou `NOT_CONFIGURED` pode ser promovido por inferência. C-001/C-010 liberam implementação; não promovem G-001/G-030/G-070/G-110/G-130.
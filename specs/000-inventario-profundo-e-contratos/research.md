# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões documentais resultantes do cruzamento das três baselines. Hipótese não vira fato sem runtime/evidência; design futuro não apaga necessidade de cutover.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Fatos estruturais já consolidados

### KB2Ops

- Content Extractor Elementor-aware read-only;
- workflow de revisão/AI READY;
- Search provisória com `WP_Query/meta LIKE`;
- DS/server-rendered como referência de UX;
- activation/uninstall reversíveis;
- ausência de suite executável versionada equivalente ao release audit;
- gap de extractor parcial em custom widgets;
- `save_review()` pode emitir approval sem comprovar todos os writes.

### ASI

- lexical/FULLTEXT + fallback;
- QueryContext/ranking explicáveis;
- post/item projections, vocabulary/bindings/rules;
- Golden Queries;
- events/interactions/outcomes;
- queue/migrations/legacy com complexidade histórica;
- GAC ambiental;
- vários parsers sobre `post_content`;
- robustez de Search maior que KB2Ops, mas storage não deve ser copiado literalmente.

### GRE

- oito metas via Metadata API;
- `post_title` canônico;
- read side-effect free, allowlist, sanitização, `edit_post`, nonce;
- sem tabela/REST/AJAX/cron próprios;
- Coverage sem bound;
- ausência de `Objective_Provider` e `bdc_es_objective_updated` esperados pelo ASI.

## 3. Cruzamento T050–T053

### Ownership

- Editorial WP/Elementor: conteúdo/publicação.
- Resumo: objective/escalation/important.
- Classificação: team/catalog/audience/services/systems/technologies/type/keywords/versions.
- Revisão/Governança: review state/notes/reviewer/time/include_ai/history.
- Search Knowledge: vocabulary/bindings/rules.
- Search Indexing: projections.
- Search Quality: Golden/evidência.
- Analytics: events/interactions/outcomes.

### Regras de persistência

- um conceito canônico = um owner;
- projection não é canônico;
- dual-write permanente proibido;
- adapters/dual-read somente temporários;
- chaves/stores antigos são origem/compatibilidade, não arquitetura futura.

### Regras de integração

- persistir -> confirmar -> emitir;
- consumers idempotentes;
- Analytics non-fatal;
- admin-post/server-rendered baseline;
- AJAX só por live UX;
- REST sem consumidor negado;
- queue ainda não autorizada.

## 4. T054 — mapa final de drifts e compatibilidade

Artefato canônico: `mapa-contratos-quebrados.md`.

### D-001 — Objective Provider

- histórico: quebrado;
- futuro: Summary Store interno;
- compat: adapter somente se ASI legado coexistir;
- gate: remover após ASI legado/consumidores externos deixarem de existir.

### D-002 — evento Objective

- histórico: ASI espera action que GRE nunca emite;
- futuro: evento pós-write confirmado;
- compat: tradução temporária para hook legado apenas se coexistência exigir.

### D-003 — post_content versus Elementor

- direção: extractor único;
- estado: **BLOCKER técnico para Search/RAG final** até provar completude em corpus Elementor real/custom widgets.

### D-004 — múltiplos parsers

- parsers duplicados descartados;
- Word Cloud/anchors só sobrevivem por decisão de produto/preflight;
- qualquer feature sobrevivente consome extractor/projections canônicos.

### D-005 — GAC

- fora do core;
- adapter só se requisito institucional/consumidor for comprovado;
- caso contrário, descartar no T095.

### D-006 — Classificação GRE/KB2Ops

- ownership resolvido;
- audiência = conceito único;
- service/affected_service e technologies/systems continuam distintos;
- profiling é obrigatório antes de migração física;
- dual-read pode existir no cutover, dual-write permanente não.

### D-007 — UI fragmentada

- DS único futuro corrige arquitetura;
- CSS/menus antigos não são contrato;
- shortcodes são compatibilidade separada e dependem de preflight.

### D-008 — AI READY

- baseline histórica: publish + approved + 8/8 + include_ai;
- futuro: regra única/testada em Revisão/Governança;
- não exige adapter complexo.

## 5. Compatibilidade histórica — política

Hooks/aliases/stores antigos não são portados por inércia.

Todo compat temporário precisa de:

1. consumidor comprovado;
2. owner canônico;
3. modo limitado — read-only/dual-read/tradução/alias;
4. observabilidade de uso;
5. rollback;
6. gate de remoção;
7. regressão de equivalência.

### Shortcodes em preflight

- `[asi_search_form]`;
- `[bdc_word_cloud]`;
- `[bdc_resumo_executivo]`;
- `[kb2ops_search]`;
- `[kb2ops_portal]`.

Nenhum alias está aprovado ainda.

## 6. Blockers B-001–B-007

| ID | Capacidade afetada | Estado |
|---|---|---|
| B-001 | Search/RAG/Indexing | extractor precisa provar completude |
| B-002 | migração de classificação | profiling de valores/cardinalidade/uso obrigatório |
| B-003 | retirada de plugins/aliases | preflight de consumidores externos obrigatório |
| B-004 | Analytics detalhado | privacy/query retention ainda precisa decisão |
| B-005 | Item Knowledge/deep-link | estratégia de anchors ainda aberta |
| B-006 | writes compostos Summary/Classificação | semântica de falha tardia precisa ser definida/testada |
| B-007 | async indexing/queue | stale/diagnóstico obrigatório se esse modo existir |

Blocker é contextual: B-005, por exemplo, não impede um slice inicial sem deep-link.

## 7. Dívidas postergáveis

- Word Cloud;
- GAC sem requisito;
- side panel GRE;
- quality_daily;
- vector/embeddings/Foundry;
- queue quando não necessária;
- rollups sem benchmark;
- SPA/REST.

## 8. Próximo passo — T056

Aplicar WordPress-first por conceito/capacidade, sem implementar:

- postmeta vs taxonomy;
- revisions/histórico;
- options/settings;
- admin-post/AJAX;
- Site Health;
- transients/object cache;
- WP-Cron;
- users/capabilities;
- native search versus projection própria.

Somente o que WordPress não atender com evidência segue para T057 como candidato a infraestrutura própria.

## Decisões ainda proibidas

- runtime/bootstrap;
- schema/tabelas;
- migrations reais;
- Foundry/vector;
- aliases de compatibilidade;
- nomes finais de hooks/rotas;
- iniciar SPEC-001.

## Estado

T050–T054 concluídas documentalmente. Próximo: **T056**.
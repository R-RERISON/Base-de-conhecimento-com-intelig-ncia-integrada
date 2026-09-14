# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões documentais resultantes do cruzamento das três baselines. Hipótese não vira fato sem runtime/evidência; design futuro não apaga necessidade de cutover.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Fatos estruturais consolidados

### KB2Ops

- Content Extractor Elementor-aware read-only;
- workflow de revisão/AI READY;
- Search provisória com `WP_Query/meta LIKE`;
- DS/server-rendered como referência de UX;
- activation/uninstall reversíveis;
- gap de extractor parcial em custom widgets;
- `save_review()` pode emitir approval sem comprovar todos os writes;
- corpus documentado na ordem de **~700 posts**;
- scans integrais/meta LIKE não são contrato de escala.

### ASI

- lexical/FULLTEXT + fallback;
- QueryContext/ranking explicáveis;
- post/item projections, vocabulary/bindings/rules;
- Golden Queries;
- events/interactions/outcomes;
- queue com lease/retry/dead/recovery;
- migrations/legacy com complexidade histórica;
- GAC ambiental;
- vários parsers sobre `post_content`;
- 12 tabelas próprias no schema 4.6.8, cuja existência histórica **não** prova necessidade no novo produto.

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
- queue não nasce por herança.

## 4. T054 — drifts e compatibilidade

Artefato canônico: `mapa-contratos-quebrados.md`.

- D-001 Objective Provider: futuro Summary Store interno; adapter só por coexistência comprovada.
- D-002 evento Objective: futuro evento pós-write confirmado; bridge legado somente se necessário.
- D-003 `post_content` versus Elementor: extractor único; B-001 permanece blocker de Search/RAG final.
- D-004 múltiplos parsers: descartados; downstream consome extractor/projections canônicos.
- D-005 GAC: fora do core; adapter somente por requisito real.
- D-006 classificação: owner único; profiling obrigatório antes de cutover.
- D-007 UI fragmentada: Design System único futuro.
- D-008 AI READY: baseline `publish + approved + 8/8 + include_ai`.

Compatibilidade nunca escolhe arquitetura permanente.

## 5. Blockers B-001–B-007

| ID | Capacidade afetada | Estado |
|---|---|---|
| B-001 | Search/RAG/Indexing | extractor precisa provar completude |
| B-002 | migração de classificação | profiling de valores/cardinalidade/uso obrigatório |
| B-003 | retirada de plugins/aliases | preflight de consumidores externos obrigatório |
| B-004 | Analytics detalhado | privacy/query retention/finalidade ainda precisam decisão |
| B-005 | Item Knowledge/deep-link | estratégia de anchors ainda aberta |
| B-006 | writes compostos Summary/Classificação | semântica de falha tardia precisa ser definida/testada |
| B-007 | async indexing/queue | stale/diagnóstico obrigatório se esse modo existir |

Blockers são contextuais.

## 6. T056 — WordPress-first

Artefato canônico: `matriz-wordpress-first.md`.

### Decisões

- `WP_Post`/Elementor continuam fonte editorial.
- Resumo narrativo permanece em Metadata API.
- Revisão/Governança permanece em Metadata API + WP Users; Revisions é primitive disponível quando snapshot for requisito.
- classificação não ganhou tabela própria;
- audience, knowledge type, service e technologies têm Taxonomy API como primitive preferida;
- responsible team, catalog item, affected service e systems involved permanecem entre Metadata/Taxonomy até B-002;
- keywords e versions permanecem Metadata no baseline;
- Search Knowledge (`vocabulary`, `bindings`, `rules`) e Golden Queries ficam em `WP_Post` interno + Metadata/Revisions enquanto o conjunto governado permanecer de baixa/moderada cardinalidade;
- Settings/Options, Users/Roles/Capabilities, Nonces, `admin-post`, Site Health, Transients/Object Cache e WP-Cron foram confirmados como primitives nativas;
- AJAX é somente enhancement de live UX;
- REST continua negado sem consumidor formal;
- WP-Cron é trigger, não durable queue;
- native search permanece fallback, mas não entrega sozinho a paridade Search Elementor-aware/Item Knowledge/ranking composto.

### Candidatos enviados a T057

1. F-057-01 — Search Retrieval Projection;
2. F-057-02 — Analytics Facts, condicional;
3. F-057-03 — Durable Job State, condicional.

## 7. Evidência adicional analisada em T057

### Search/index ASI

O schema ASI separava `search_index` e `search_items`, ambos com FULLTEXT. `PostIndex` combinava título, Objective, headings, taxonomy, canonical terms e corpo em documento lexical e possuía fallback bounded. `ItemKnowledge` mantinha identidade estável, generation, hash, reconciliação e texto bounded por item.

**Conclusão:** comportamento de documento lexical + item é comprovado, mas duas tabelas não são requisito. Ambos são a mesma família semântica: **documentos de retrieval derivados**.

### Queue ASI

A fila histórica implementava claim/lease, retry/backoff, attempts, dead state, recuperação de processing e worker bounded, com WP-Cron apenas como trigger.

**Conclusão:** ASI prova o contrato de uma fila durável caso ela seja necessária; não prova que o futuro extractor/index precisa dela. Não há benchmark do novo pipeline que compre essa complexidade.

### Analytics ASI

ASI demonstrava valor gerencial em volume, zero-result, p95, termos frequentes/emergentes, gaps, interactions e outcomes. Também exigia limites operacionais e declarava que seus guardrails estruturais não substituíam benchmark; o runbook exigia benchmark sintético de 100k buscas/200k interações antes de produção para aquele desenho.

**Conclusão:** existe valor de produto, mas B-004 e workload do novo produto continuam sem resposta. A escala histórica ASI não deve ser transformada em requisito artificial.

### WordPress

A documentação oficial do WordPress recomenda Post Meta quando prática, mas admite tabelas próprias para dados do plugin quando a modelagem/volume justificarem. Essa regra é consistente com o projeto: tabela própria é exceção comprovada, não default.

## 8. T057 — infraestrutura própria mínima

Artefato canônico: `infraestrutura-propria-minima.md`.

### F-057-01 — Search Retrieval Projection

**APROVADA E REDUZIDA.**

Direção documental:

- um único store lógico/tabela própria futura para documentos derivados `post|item`;
- evitar `search_index` + `search_items` separados enquanto um store unificado atender;
- identidade estável de documento/item;
- texto derivado pelo Content Extractor;
- hash/version/generation/freshness suficientes para rebuild/NO_CHANGE;
- FULLTEXT quando suportado, com fallback lexical bounded;
- WordPress continua autoridade de status/scope/capability;
- resultados da projection não podem vazar post despublicado/sem permissão;
- projection é reconstruível e nunca fonte da verdade.

Gates futuros: B-001, Golden, benchmark representativo, FULLTEXT/fallback no ambiente real, rebuild idempotente, stale/degraded observável.

### F-057-02 — Analytics Facts

**NÃO APROVADA NO BASELINE / POSTERGADA.**

Razões:

- B-004 aberto;
- finalidade/query text/minimização/retenção/acesso não definidos;
- taxa de eventos e perguntas do novo produto não versionadas;
- interactions/outcomes ainda não provaram necessidade no primeiro slice.

Consequências:

- zero tabela de events/interactions/outcomes agora;
- query text não é coleta implícita do baseline;
- telemetria histórica não migra automaticamente;
- Search funciona sem Analytics.

### F-057-03 — Durable Job State

**NÃO APROVADA NO BASELINE / POSTERGADA.**

Razões:

- sem benchmark do futuro extractor/index;
- corpus atual conhecido (~700 posts) não prova sozinho necessidade de worker durável;
- SLA de freshness e taxa de mudanças não estão versionados.

Primeiro testar indexação por post síncrona/bounded, rebuild explícito em lotes e WP-Cron apenas como trigger opcional. Se lease/retry/dead se provar necessário, reabrir com B-007. Não improvisar queue em Options/Transients.

### Resultado líquido

Das 12 tabelas históricas ASI, apenas **uma família de persistência própria** está aprovada para desenho futuro: a Search Retrieval Projection unificada.

Não foram aprovadas tabelas próprias para:

- Summary;
- Classification;
- Review;
- Search Knowledge;
- Golden;
- Analytics;
- queue;
- audit genérico;
- quality rollup;
- migrations permanentes.

## 9. Próximo passo — T055

T055 deve consolidar regressões e Golden com base em T056/T057.

Contratos prioritários:

- extractor único/read-only e B-001;
- projection unificada post/item;
- identidade/hash/generation/rebuild idempotente;
- FULLTEXT + fallback bounded;
- scope/status/capability recheck no WordPress;
- estado stale/degraded explícito;
- Golden como gate de ranking;
- Search funciona sem Analytics;
- baseline não registra query text silenciosamente;
- baseline não depende de durable queue;
- activation não dispara rebuild massivo.

## Decisões ainda proibidas

- runtime/bootstrap;
- DDL/schema físico final;
- migrations reais;
- Foundry/vector;
- aliases de compatibilidade;
- nomes finais de hooks/rotas;
- iniciar SPEC-001.

## Estado

T050–T054, T056 e **T057 concluídas documentalmente**. Próximo: **T055**.
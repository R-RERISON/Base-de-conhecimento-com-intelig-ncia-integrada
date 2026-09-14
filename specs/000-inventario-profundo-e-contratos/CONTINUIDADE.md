# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## 1. Prompt pronto para colar em novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".

Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

ANTES DE QUALQUER ALTERAÇÃO
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md.
4. Leia todos os artefatos de specs/000-inventario-profundo-e-contratos/.
5. Leia docs/DEFINITION-OF-DONE.md.
6. Leia este CONTINUIDADE.md inteiro.
7. Confirme HEAD/branch no GitHub.
8. Se baseline/estado divergir, investigue antes de escrever.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- HEAD confirmado antes do bloco T057: d8c5b068d2c8969d86b2e53f6c225442bd13e3bf
- O commit que contém esta versão representa o fechamento documental de T057; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários individuais + T050/T051/T052/T053/T054/T056/T057 concluídos documentalmente; nenhum runtime novo.

BASELINES FIXADAS
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

TAREFAS CONCLUÍDAS
- T000–T002 preparação/governança.
- T010–T019 KB2Ops.
- T020–T034 ASI.
- T040–T047 GRE.
- T050 persistência consolidada.
- T051 integrações consolidadas.
- T052 ownership.
- T053 sobreposição.
- T054 contratos quebrados/drifts/compatibilidade/blockers.
- T056 matriz WordPress-first.
- T057 infraestrutura própria mínima.

ARTEFATOS CENTRAIS
- mapa-ownership-dados.md
- matriz-sobreposicoes.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- mapa-contratos-quebrados.md
- matriz-wordpress-first.md
- infraestrutura-propria-minima.md
- catalogo-testes-regressao.md
- riscos-e-drifts.md
- matriz-paridade-futura.md
- research.md

INVARIANTES
- WordPress-first.
- Princípio de negação.
- WordPress/Elementor são fonte editorial absoluta.
- Nunca escrever _elementor_data por pipeline derivado.
- Nunca reescrever post_content silenciosamente.
- Um conceito canônico = um owner lógico.
- Projection/index/cache/vector nunca é fonte da verdade.
- Um Content Extractor canônico alimenta downstream.
- Qualidade da extração precede Search/RAG.
- Persistência confirmada precede evento.
- Consumers de eventos são idempotentes.
- Dual-write permanente é proibido.
- Adapter/dual-read é temporário e possui gate de remoção.
- Compatibilidade nunca vira owner.
- IA sugere; humano decide; owner persiste.
- Retrieval precede síntese.
- IA/vetor são opcionais/degradáveis.
- Lexical funciona sem IA.
- Nenhum runtime antes de T097 autorizar SPEC-001.

OWNERS LÓGICOS
- Editorial WP/Elementor: título, conteúdo, _elementor_data, publicação.
- Resumo: objective, escalation, important.
- Classificação: responsible_team, catalog_item, audience, service, affected_service, technologies, systems_involved, knowledge_type, keywords, versions.
- Revisão/Governança: review state, notes, reviewer/time, include_ai, history.
- Content Extraction: texto/estrutura/hash derivados.
- Search Knowledge: vocabulary/bindings/relevance rules.
- Search Indexing: projections lexical/item/deep-link/vector.
- Search Quality: Golden/evidências.
- Analytics: events/interactions/outcomes, somente se futura política autorizar.
- Core Configuration: settings/runtime version.
- Operations: rebuild/migration/purge e queue somente se workload futuro justificar.
- AI Assist: sugestões não canônicas.

T050/T051 — CONTRATOS CONSOLIDADOS
- persistência por owner/conceito, não por plugin antigo;
- chaves/stores históricos são compatibilidade/origem;
- dual-write permanente proibido;
- evento = persistir -> confirmar -> emitir;
- admin-post/server-rendered baseline;
- AJAX só por live UX;
- REST negado sem consumidor;
- shortcodes históricos não têm alias aprovado sem preflight.

T054 — DRIFTS E COMPATIBILIDADE
Arquivo canônico: mapa-contratos-quebrados.md.

D-001 Objective_Provider
- futuro: Summary Store interno;
- adapter temporário somente se ASI legado coexistir.

D-002 bdc_es_objective_updated
- futuro: evento pós-write confirmado;
- bridge temporário somente se ASI legado precisar de invalidação.

D-003 post_content vs Elementor
- futuro: Content Extraction único;
- B-001: completude do extractor é blocker para Search/RAG final.

D-004 múltiplos parsers
- parsers duplicados descartados;
- Word Cloud/anchors dependem de produto/preflight, sem parser próprio.

D-005 GAC
- fora do core;
- adapter apenas se requisito/consumidor comprovado.

D-006 Classificação GRE/KB2Ops
- ownership resolvido;
- audiência é um conceito;
- service != affected_service e technologies != systems sem profiling;
- dual-read temporário possível; dual-write permanente proibido.

D-007 UI/CSS fragmentados
- DS único futuro;
- CSS/menus antigos não são contrato.

D-008 AI READY
- baseline: publish + approved + 8/8 + include_ai;
- regra futura única/testada e derivada.

T056 — WORDPRESS-FIRST
Arquivo canônico: matriz-wordpress-first.md.

DECISÕES CORE
- WP_Post/Elementor continuam fonte editorial.
- Summary objective/escalation/important -> Metadata API.
- Review state/notes/reviewer/include_ai/history bounded -> Metadata API + Users; Revisions quando snapshot for requisito.
- Settings -> Settings/Options API.
- Authorization -> Users/Roles/Capabilities.
- CSRF -> Nonces + capability.
- Admin mutations -> admin-post baseline.
- AJAX -> somente live UX.
- REST -> nenhum consumidor atual; não criar.
- Health -> Site Health.
- Cache -> Transients/Object Cache; sempre reconstruível.
- Scheduling -> WP-Cron como trigger, nunca como durable job store.
- Search Knowledge vocabulary/bindings/rules -> WP_Post interno + Metadata/Revisions inicialmente.
- Golden Queries -> WP_Post interno + Metadata/Revisions inicialmente.

CLASSIFICAÇÃO T056
- audience -> Taxonomy API; cutover B-002.
- knowledge_type -> Taxonomy API; cutover B-002.
- service -> Taxonomy API; separado de affected_service.
- technologies -> Taxonomy API; separado de systems_involved.
- keywords -> Metadata API no baseline.
- versions -> Metadata API no baseline.
- responsible_team -> AINDA_NAO_SABEMOS entre Metadata/Taxonomy; B-002/D-005.
- catalog_item -> AINDA_NAO_SABEMOS entre Metadata/Taxonomy; B-002.
- affected_service -> AINDA_NAO_SABEMOS entre Metadata/Taxonomy; B-002.
- systems_involved -> AINDA_NAO_SABEMOS entre Metadata/Taxonomy; B-002.

Os quatro unknowns classificatórios permanecem dentro de primitives WordPress; não justificam tabela própria.

T057 — INFRAESTRUTURA PRÓPRIA MÍNIMA
Arquivo canônico: infraestrutura-propria-minima.md.

EVIDÊNCIA
- corpus KB2Ops documentado: ~700 posts; não é NFR futuro.
- item count/QPS/p95/custo do extractor/rebuild ainda não têm medição versionada.
- ASI possuía 12 tabelas, FULLTEXT post/item, queue durável e telemetria correlacionada; comportamento é referência, schema não.
- ASI declarava seus performance bounds como guardrails, não benchmark produtivo.

F-057-01 — Search Retrieval Projection
STATUS: APROVADA E REDUZIDA.

Direção:
- uma única infraestrutura própria de baseline: store lógico/tabela futura de documentos de retrieval derivados;
- o mesmo store deve representar document_kind=post|item antes de considerar tabelas separadas;
- identidade estável/document_key, post_id, item_key quando aplicável, texto/título lexical derivado, hashes/version/generation/freshness;
- FULLTEXT dedicado quando suportado + fallback lexical bounded;
- Content Extractor é a única fonte textual derivada;
- WordPress continua autoridade de status/scope/capability; revalidar candidatos antes de exposição;
- projection é reconstruível; falha/stale não altera canônico;
- B-001 + Golden + benchmark + FULLTEXT/fallback no ambiente real antes de produção;
- B-005 bloqueia deep-link completo, não item lexical sem link público.

NÃO PORTAR do ASI por inércia:
- search_index e search_items como duas tabelas separadas;
- tables de vocabulary/bindings/rules/Golden/audit/quality/migrations;
- parsers próprios.

F-057-02 — Analytics Facts
STATUS: NÃO APROVADA NO BASELINE / POSTERGADA.

- B-004 permanece aberto;
- não criar events/interactions/outcomes agora;
- não persistir query text por default enquanto finalidade/minimização/retenção/acesso não forem definidos;
- não migrar telemetria histórica automaticamente;
- Search deve funcionar sem Analytics;
- se voltar, desenhar uma família coerente de facts a partir das perguntas, não copiar schema ASI.

F-057-03 — Durable Job State
STATUS: NÃO APROVADA NO BASELINE / POSTERGADA.

- ASI prova semântica de lease/retry/backoff/dead/recovery caso fila exista;
- novo produto ainda não provou que indexação/rebuild precisa de worker durável;
- primeiro medir indexação síncrona/bounded e rebuild manual/batched;
- WP-Cron pode ser trigger opcional, não durable state;
- não improvisar queue em Options/Transients;
- reabrir somente com benchmark, SLA/freshness/concorrência e B-007.

RESULTADO T057
- 12 stores ASI históricos não renascem.
- 1 família própria aprovada documentalmente: Search Retrieval Projection unificada.
- 0 stores Analytics aprovados no baseline.
- 0 queue stores aprovados no baseline.
- nenhum DDL/schema/runtime/migration foi criado.

COMPATIBILIDADE DE HOOKS/SHORTCODES
- bdc_es_loaded: preflight.
- kb2ops_loaded: preflight.
- bdc_es_objective_updated: compat temporário condicional.
- kb2ops_post_approved: não portar literalmente; futuro evento confirmado.
- extractor error hooks: manter intenção, nomes finais abertos.
- save_post: trigger WP válido; processamento pesado precisa ser redesenhado.
- the_content anchors: depende de produto/preflight.
- site_status_tests: primitive WP preferida para health.

Shortcodes em preflight:
- [asi_search_form]
- [bdc_word_cloud]
- [bdc_resumo_executivo]
- [kb2ops_search]
- [kb2ops_portal]

Nenhum alias está aprovado.

BLOCKERS
B-001 — Content Extractor representativo antes de Search/RAG final e F-057-01 produtiva.
B-002 — profiling classificatório antes de migração/cutover.
B-003 — preflight de consumidores antes de retirar plugins/aliases.
B-004 — política de Analytics/query text; mantém F-057-02 postergada.
B-005 — deep-link/anchors antes de paridade completa Item Knowledge.
B-006 — semântica de falha multi-campo antes do write path composto definitivo.
B-007 — stale/observabilidade + necessidade real antes de reabrir F-057-03/async queue.

Blockers são contextuais. Não bloquear slice que não use a capacidade.

DÍVIDAS POSTERGÁVEIS
- Word Cloud;
- GAC sem requisito;
- side panel GRE;
- quality_daily;
- Analytics detalhado;
- queue durável;
- vetor/embeddings/Foundry;
- rollups/materializações sem benchmark;
- SPA/REST.

DADOS QUE NÃO PODEM SER PERDIDOS SEM DECISÃO
- WP_Post/Elementor/taxonomias editoriais;
- oito valores GRE históricos;
- review/include_ai/notas/revisor/histórico válidos;
- classificações KB2Ops efetivamente usadas;
- vocabulary/bindings/rules/Golden ASI manuais quando houver dados reais.

Telemetria histórica não é automaticamente migrada; depende de política explícita.

O QUE AINDA NÃO FOI DECIDIDO
- profiling/cutover dos campos classificatórios e termos finais;
- nomes/chaves/cardinalidade finais;
- quais metas usam Revisions além do histórico explícito;
- DDL/nome físico/índices finais da Search Retrieval Projection;
- NFRs p95/QPS/rebuild/item cardinality do Search futuro;
- política de Analytics/query text;
- necessidade futura de durable queue;
- deep-link/anchors;
- preflight real dos consumidores;
- chunks/vector/embeddings;
- Foundry/provider;
- runtime/layout final.

O QUE NÃO DEVE SER FEITO AGORA
- não criar bootstrap/runtime;
- não registrar taxonomy/CPT/meta final;
- não criar DDL/schema físico;
- não implementar Search table ainda;
- não criar Analytics tables;
- não criar queue table;
- não implementar migration/adapters/aliases;
- não integrar Foundry;
- não criar vector/embeddings;
- não copiar classes dos legados;
- não alterar ASI/GRE/KB2Ops;
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T055
Consolidar Catálogo de Regressão e Golden Queries conforme as decisões T056/T057.

T055 DEVE PROTEGER NO MÍNIMO
1. Content Extractor único/read-only e fixtures B-001.
2. Projection conceitual unificada post/item; identidade/hash/generation determinísticos.
3. Rebuild idempotente; falha/stale não altera canônico.
4. Despublicação/permissão não vaza por índice stale.
5. FULLTEXT + fallback lexical bounded e estado degradado.
6. Ranking explicável + Golden Queries; suíte vazia nunca PASS.
7. Search scope/detail revalidado no WordPress.
8. Search funciona sem Analytics e sem IA/vetor.
9. Baseline não grava query text silenciosamente.
10. Baseline não depende de durable queue.
11. Se fila futura surgir: lease/retry/dead/idempotência/B-007 obrigatórios.
12. Activation não dispara rebuild massivo/destrutivo.
13. Package/build/rollback/coexistência continuam gates.

CRITÉRIO PARA FECHAR T055
- contratos críticos T050–T057 têm teste/gate futuro mapeado;
- Golden dataset/execução/versionamento/falha bloqueante estão explicitados;
- gaps de teste das baselines têm destino;
- performance possui benchmark definido como gate de runtime, sem inventar resultado;
- security/privacy regressions estão mapeadas;
- nenhum teste depende de implementação antiga por acidente;
- nenhum runtime foi criado.

ORDEM RESTANTE
T055 -> T058 -> T059 -> T090–T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat. Não transformar intenção em conclusão sem evidência versionada.
```

## 2. Estado resumido

- SPEC-000 ativa.
- HEAD antes de T057: `d8c5b068d2c8969d86b2e53f6c225442bd13e3bf`.
- T050–T054 + T056 + T057 concluídas documentalmente.
- Próximo: T055.
- Infra própria aprovada: somente Search Retrieval Projection unificada, ainda sem DDL/runtime.
- Analytics detalhado: postergado por B-004.
- Durable queue: postergada até benchmark/B-007.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## 3. Regra de atualização

Atualizar este arquivo ao concluir T055. Não acumular estados contraditórios.
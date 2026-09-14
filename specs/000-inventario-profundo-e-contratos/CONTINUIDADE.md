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
- HEAD confirmado antes do bloco T055: c68b10644f247e07866d26f08654137be0253eb7
- O commit que contém esta versão representa o fechamento documental de T055; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários + T050/T051/T052/T053/T054/T055/T056/T057 concluídos documentalmente; nenhum runtime novo.

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
- T054 drifts/compatibilidade/blockers.
- T056 WordPress-first.
- T057 infraestrutura própria mínima.
- T055 regressão e Golden Queries.

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
- WordPress-first + princípio de negação.
- WP_Post/Elementor são fonte editorial absoluta.
- nunca escrever _elementor_data por pipeline derivado.
- nunca reescrever post_content silenciosamente.
- um conceito canônico = um owner lógico.
- projection/index/cache/vector nunca é fonte da verdade.
- um Content Extractor canônico alimenta downstream.
- qualidade da extração precede Search/RAG.
- persistência confirmada precede evento.
- consumers de eventos são idempotentes.
- dual-write permanente proibido.
- adapter/dual-read temporário possui gate de remoção.
- IA sugere; humano decide; owner persiste.
- retrieval precede síntese.
- IA/vetor opcionais/degradáveis.
- lexical funciona sem IA/vetor.
- nenhum runtime antes de T097 autorizar SPEC-001.

OWNERS LÓGICOS
- Editorial WP/Elementor: título, conteúdo, _elementor_data, publicação.
- Resumo: objective, escalation, important.
- Classificação: responsible_team, catalog_item, audience, service, affected_service, technologies, systems_involved, knowledge_type, keywords, versions.
- Revisão/Governança: review state, notes, reviewer/time, include_ai, history.
- Content Extraction: texto/estrutura/hash derivados.
- Search Knowledge: vocabulary/bindings/relevance rules.
- Search Indexing: projection lexical/item/deep-link/vector.
- Search Quality: Golden/evidências.
- Analytics: somente se política futura autorizar.
- Operations: rebuild/migration/purge; queue somente se workload provar necessidade.
- AI Assist: sugestões não canônicas.

T054 — BLOCKERS
B-001 — extractor representativo antes de Search/RAG final.
B-002 — profiling classificatório antes de cutover.
B-003 — preflight de consumidores antes de remover plugins/aliases.
B-004 — política de Analytics/query text antes de telemetria detalhada.
B-005 — deep-link/anchors antes de paridade pública completa de item.
B-006 — semântica de falha multi-campo antes do write path composto definitivo.
B-007 — stale/observabilidade + necessidade real antes de async queue.

T056 — WORDPRESS-FIRST
- Editorial -> WP_Post/Elementor.
- Summary -> Metadata API.
- Review -> Metadata + Users + Revisions quando aplicável.
- Settings -> Settings/Options.
- Security -> Users/Roles/Capabilities + Nonces.
- admin mutations -> admin-post.
- AJAX só por live UX; REST sem consumidor não criar.
- Health -> Site Health.
- Cache -> Transients/Object Cache reconstruíveis.
- WP-Cron -> trigger, nunca durable store.
- Search Knowledge/Golden -> WP_Post interno + Metadata/Revisions inicialmente.
- audience/knowledge_type/service/technologies -> Taxonomy preferida sob B-002.
- responsible_team/catalog_item/affected_service/systems_involved -> ainda Metadata vs Taxonomy; não são infra própria.

T057 — INFRAESTRUTURA PRÓPRIA
Arquivo canônico: infraestrutura-propria-minima.md.

F-057-01 Search Retrieval Projection: APROVADA E REDUZIDA.
- um store lógico/tabela futura inicial para documentos document_kind=post|item;
- identity/document_key, post_id/item_key, texto derivado, hash/version/generation/freshness;
- FULLTEXT quando suportado + fallback bounded;
- Content Extractor único;
- WordPress revalida status/scope/capability;
- projection reconstruível e não-canônica;
- B-001 + Golden + benchmark antes de produção.

F-057-02 Analytics Facts: POSTERGADA.
- zero events/interactions/outcomes no baseline;
- zero query text persistida por default;
- reabrir somente após B-004.

F-057-03 Durable Job State: POSTERGADA.
- zero queue table no baseline;
- primeiro medir sync/bounded + rebuild manual/batched;
- WP-Cron só trigger;
- não usar Options/Transients como fila;
- reabrir somente após benchmark + B-007.

T055 — REGRESSÃO E GOLDEN
Arquivo canônico: catalogo-testes-regressao.md.

CLASSES DE GATE
- MUST: falha ou falta de evidência = NO-GO.
- CONDICIONAL: obrigatório quando feature/slice ativa a capacidade.
- POSTERGADO: capacidade não deve nascer silenciosamente.
- N/A: somente com justificativa explícita.

GATES
- G-001 Editorial/Elementor.
- G-010 Content Extractor/B-001.
- G-020 Summary/Metadata/B-006.
- G-030 Classificação/B-002.
- G-040 Review/governança/eventos.
- G-050 Search Retrieval Projection.
- G-060 QueryContext/ranking.
- Golden Queries.
- G-070 Scope/security/exposição.
- G-080 Analytics baseline negativo.
- G-090 Queue baseline negativo.
- G-100 Compatibilidade/B-003.
- G-110 UI/UX/a11y.
- G-120 Performance/benchmark.
- G-130 Lifecycle/build/rollback.
- G-140 IA/vetor reservado a T058.

GOLDEN QUERIES — DECISÕES
- Golden é QA governada, não telemetria de usuário.
- storage baseline: WP_Post interno + Metadata/Revisions; nenhuma Golden table autorizada.
- expectation conceitual: query curada, expected_post_id, expected_item_key opcional, max_rank, severity blocking|warning, active, origem/notas, autor/revisão.
- suíte vazia = NOT_CONFIGURED, nunca PASS.
- não executada/stale = NO-GO para release/mudança de Search.
- blocking failure = NO-GO.
- warning exige decisão/waiver versionado ou correção; não some em PASS.
- execução explícita/read-only; dashboard lê status sem executar ranking.
- evidência liga set hash/conjunto, ranker, item-ranker/identity, extractor/index e dataset/projection do release candidate.
- mudança material invalida evidência antiga.
- Golden não guarda identity/session/journey e não depende de Analytics.
- cobertura por famílias de comportamento, não número artificial mínimo: exato, sigla, acento/case, multi-token, natural language, sinônimo, sinais Summary/Classificação, item, ambiguidade relevante e caso blocking quando aplicável.
- Golden PASS não fecha B-001, security ou performance.

PERFORMANCE T055
- não inventar p95/QPS.
- futura SPEC define thresholds antes do GO.
- benchmark registra corpus, item cardinality, fixtures leves/pesadas, p50/p95, wall time, DB queries, memória, rebuild, FULLTEXT/fallback e configuração do DB.
- guardrail estrutural não substitui benchmark real.

COMPATIBILIDADE
Shortcodes ainda em preflight:
- [asi_search_form]
- [bdc_word_cloud]
- [bdc_resumo_executivo]
- [kb2ops_search]
- [kb2ops_portal]
Nenhum alias aprovado automaticamente.

DÍVIDAS/POSTERGADOS
- Word Cloud;
- GAC sem requisito;
- side panel GRE;
- quality_daily;
- Analytics detalhado;
- durable queue;
- vetor/embeddings/Foundry;
- rollups/materializações sem benchmark;
- SPA/REST.

O QUE AINDA NÃO FOI DECIDIDO
- profiling/cutover final das classificações;
- meta revisions finais;
- DDL/nome/índices da Search Projection;
- NFRs reais Search/index/rebuild;
- Analytics/query policy;
- necessidade futura de queue;
- anchors/deep-link;
- preflight real de consumidores;
- chunks/embeddings/vector;
- Foundry/provider;
- runtime/layout final.

O QUE NÃO DEVE SER FEITO AGORA
- não criar runtime/bootstrap.
- não criar schema/tabela/migration.
- não registrar taxonomies/CPT/meta finais.
- não criar Golden dataset real ainda.
- não criar Analytics/queue.
- não implementar aliases/adapters.
- não integrar Foundry.
- não criar embeddings/vectors/chunks.
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T058
Identificar e priorizar candidatos a IA/vetor.

T058 DEVE
1. listar capacidades candidatas: classificação assistida, Summary assistido, embeddings, semantic retrieval, reranking, RAG/síntese, agentes/Foundry;
2. aplicar princípio de negação por capacidade;
3. separar valor de produto de provider/tecnologia;
4. preservar IA assistiva e decisão humana;
5. preservar lexical independente;
6. tratar custo/quota/timeout/falha/degradação;
7. definir rastreabilidade de prompt/model/provider/version;
8. exigir hash/NO_CHANGE antes de recomputar em massa;
9. decidir o que fica fora do primeiro runtime;
10. completar G-140 com gates específicos.

CRITÉRIO PARA FECHAR T058
- cada candidato aprovado/postergado/descartado com razão;
- custo e fallback tratados;
- nenhuma IA vira owner editorial;
- lexical permanece funcional sem IA/vetor;
- vector/embedding não nasce por modernidade;
- nenhuma implementação/runtime criada.

ORDEM RESTANTE
T058 -> T059 -> T090–T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat. Não transformar intenção em conclusão sem evidência versionada.
```

## 2. Estado resumido

- SPEC-000 ativa.
- HEAD antes de T055: `c68b10644f247e07866d26f08654137be0253eb7`.
- T050–T057, incluindo T055, concluídas documentalmente.
- Próximo: T058.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## 3. Regra de atualização

Atualizar este arquivo ao concluir T058. Não acumular estados contraditórios.

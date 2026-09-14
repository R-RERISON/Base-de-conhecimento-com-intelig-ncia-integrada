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
- HEAD confirmado antes do bloco T058: 6802d7bb75297dc8f3e403b57113c73170c78061
- O commit que contém esta versão representa o fechamento documental de T058; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários + T050/T051/T052/T053/T054/T055/T056/T057/T058 concluídos documentalmente; nenhum runtime novo.

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
- T058 IA/vetor priorizados.

ARTEFATOS CENTRAIS
- mapa-ownership-dados.md
- matriz-sobreposicoes.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- mapa-contratos-quebrados.md
- matriz-wordpress-first.md
- infraestrutura-propria-minima.md
- catalogo-testes-regressao.md
- matriz-ia-vetor.md
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
- provider externo é adapter, nunca owner de domínio.
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
- AI Assist: sugestões/síntese não canônicas; nunca persiste owner sem humano.

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
- um store lógico futuro inicial para documentos document_kind=post|item;
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

T055 — REGRESSÃO/GOLDEN
Arquivo canônico: catalogo-testes-regressao.md.

CLASSES
- MUST: falha ou falta de evidência = NO-GO.
- CONDICIONAL: obrigatório quando feature ativa a capacidade.
- POSTERGADO: capacidade não nasce silenciosamente.
- N/A: somente com justificativa explícita.

GATES PREEXISTENTES
- G-001 Editorial/Elementor.
- G-010 Content Extractor/B-001.
- G-020 Summary/B-006.
- G-030 Classificação/B-002.
- G-040 Review/eventos.
- G-050 Search Retrieval Projection.
- G-060 QueryContext/ranking.
- Golden Queries.
- G-070 Scope/security.
- G-080 Analytics negativo.
- G-090 Queue negativo.
- G-100 Compatibilidade/B-003.
- G-110 UI/UX/a11y.
- G-120 Performance.
- G-130 Lifecycle/build/rollback.

GOLDEN
- QA governada, não telemetria.
- storage inicial WP_Post interno + meta/revisions.
- vazia = NOT_CONFIGURED, nunca PASS.
- não executada/stale = NO-GO quando Search é afetada.
- blocking fail = NO-GO.
- warning exige decisão explícita.
- evidence liga conjunto, rankers, extractor/index e dataset/projection.
- execução explícita; status read não rerun.
- Golden não depende de identity/session/journey.
- PASS não fecha B-001/performance/security.

T058 — IA/VETOR
Arquivo canônico: matriz-ia-vetor.md.

PRIORIDADES
P0 — determinístico/core obrigatório.
P1 — IA assistiva sob demanda.
P2 — RAG/síntese opcional sobre retrieval confiável.
P3 — embeddings/semantic/hybrid/reranking somente após evidência de ganho.
P4 — agentes/tools somente após caso multi-step comprovado.

DECISÕES T058
- pré-análise determinística -> MANTER P0.
- Assistente de Classificação -> APROVADO OPCIONAL P1.
- Assistente de Summary -> APROVADO OPCIONAL P1.
- primeiro slice futuro escolhe uma das duas jornadas, não ambas automaticamente.
- LLM em toda query -> DESCARTAR baseline.
- RAG/síntese -> APROVADO OPCIONAL POSTERIOR P2; pode nascer lexical-first.
- chunking adicional -> POSTERGADO condicional; não cria parser paralelo.
- embeddings -> POSTERGADO COM GATE P3.
- semantic/hybrid -> POSTERGADO COM GATE P3; lexical fallback obrigatório.
- model rerank -> POSTERGADO COM GATE P3; top-K bounded/fail-open.
- Microsoft Foundry -> provider preferencial candidato, não domínio.
- Foundry Agent File Search -> DESCARTAR como Search/RAG core; somente projection eventual de agente específico.
- agentes/tools -> POSTERGADOS/NEGADOS no baseline P4.
- primeiro runtime pode ter ZERO IA externa.

AI READY x ASSISTÊNCIA
- AI READY permanece publish + approved + 8/8 + include_ai quando vigente.
- include_ai NÃO autoriza assistência editorial.
- eventual AI Assist Allowed é conceito separado que futura SPEC deve definir por capability/política.

IA P1 — CONTRATO
- ação explícita por usuário autorizado.
- contexto do Content Extractor + dados/vocabulário canônicos.
- saída estruturada, validada, com evidência/incerteza.
- Generate != Apply.
- IA não persiste owner.
- Apply humano usa handler canônico + capability/nonce/read-after-write.
- erro/timeout não muda estado canônico.

RAG P2 — CONTRATO
- query -> retrieval -> evidências -> síntese opcional -> fontes.
- vetor não é requisito.
- corpus produtivo respeita AI READY quando gate ativo.
- scope/status/capability revalidado no WordPress.
- abstenção quando evidência insuficiente.
- falha provider pode degradar para retrieval sem síntese.
- conteúdo recuperado é dado, não instrução/tool authority.

EMBEDDING/SEMANTIC P3
- somente após lexical operacional/medido + B-001 + Golden/lacuna semântica.
- raw Elementor/JSON nunca é fonte de embedding.
- fingerprint inclui origem/source hash/extractor/chunk contract/provider/model/deployment/dimensão/config.
- mudança incompatível invalida embedding.
- re-embed total sem diff/budget é NO-GO.
- T058 não escolheu MariaDB VECTOR/Azure AI Search/Foundry vector store.
- hybrid é hipótese preferida; critério de sucesso deve ser fixado antes do experimento.

FOUNDRY
- provider seam mínimo só nasce junto com caso real.
- WordPress HTTP API é primeira opção quando adequada.
- provider/model/deployment/prompt/config versionados.
- timeout/retry/quota/auth/errors explícitos.
- secrets nunca em logs/export.
- failover/provider switch não silencioso.
- preços/defaults atuais do provider não são contrato arquitetural.

CUSTO/NO_CHANGE
- toda chamada externa futura gera AI Operation Receipt conceitual: operação, provider/model/deployment, prompt/config version, objeto/contexto, input fingerprint, volume, tokens/unidades, custo estimado/real quando possível, duração, status/error, responsável quando aplicável, timestamp.
- T058 não escolheu storage para receipts.
- batch exige preview/estimativa/budget/limite/stop condition/confirmação.
- activation/publicação/page load não dispara batch por default.
- se batch exigir worker durável, reabrir F-057-03/B-007.

G-140 APÓS T058
Canônico em matriz-ia-vetor.md:
- G-140A independência/degradação.
- G-140B provider/rastreabilidade/data egress.
- G-140C human-in-the-loop.
- G-140D custo/budget/NO_CHANGE.
- G-140E embedding/semantic/hybrid.
- G-140F RAG/síntese.
- G-140G agentes/tools.
- G-140H provider-managed knowledge/File Search.

O QUE CONTINUA POSTERGADO
- Word Cloud.
- GAC sem requisito.
- side panel GRE.
- quality_daily.
- Analytics detalhado.
- durable queue.
- embeddings/semantic/rerank.
- agentes/tools.
- SPA/REST sem consumidor.
- rollups/materializações sem benchmark.

SHORTCODES AINDA EM PREFLIGHT
- [asi_search_form]
- [bdc_word_cloud]
- [bdc_resumo_executivo]
- [kb2ops_search]
- [kb2ops_portal]
Nenhum alias aprovado automaticamente.

O QUE AINDA NÃO FOI DECIDIDO
- profiling/cutover final de classificações.
- meta revisions finais.
- DDL/nome/índices da Search Projection.
- NFRs reais Search/index/rebuild.
- Analytics/query policy.
- necessidade futura de queue.
- anchors/deep-link.
- preflight real de consumidores.
- primeiro caso concreto de IA P1: Classificação versus Summary.
- provider/deployment/model/prompt reais.
- strategy institucional de secrets/data egress.
- necessidade real de embeddings/chunking/vector storage.
- runtime/layout final.

O QUE NÃO DEVE SER FEITO AGORA
- não criar runtime/bootstrap.
- não criar schema/tabela/migration.
- não registrar taxonomies/CPT/meta finais.
- não criar Golden dataset real ainda.
- não criar Analytics/queue.
- não implementar aliases/adapters.
- não integrar Foundry.
- não criar provider SDK/adapter runtime.
- não criar prompts runtime.
- não chamar LLM.
- não criar chunks/embeddings/vector store.
- não habilitar MariaDB VECTOR/Azure AI Search/File Search.
- não criar agentes/tools.
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T059
Consolidar a Matriz de Paridade Futura FINAL.

T059 DEVE
1. ler T054/T056/T057/T055/T058 como conjunto.
2. criar uma visão única por capacidade/conceito.
3. registrar owner lógico.
4. registrar primitive/storage aprovado ou estado postergado.
5. classificar temporalmente: PRIMEIRO_RUNTIME | POSTERIOR | POSTERGADO | COMPAT_CUTOVER.
6. mapear gates G-001–G-140 e blockers B-001–B-007 aplicáveis.
7. mapear dados históricos que não podem ser perdidos.
8. mapear fallback/degradação.
9. mapear compatibilidade/remoção.
10. eliminar contradições residuais dos artefatos incrementais.
11. preparar entrada objetiva para revisões T090–T094 e fechamento T095.
12. não criar runtime.

CRITÉRIO PARA FECHAR T059
- toda capacidade relevante aparece uma única vez como decisão final de paridade.
- owners não conflitam.
- first runtime não inclui itens postergados por acidente.
- todos os itens de IA refletem T058.
- gates/blockers/fallback/cutover estão visíveis.
- unknowns restantes estão explicitamente destinados a T095 ou SPEC futura.
- nenhum runtime foi criado.

ORDEM RESTANTE
T059 -> T090 -> T091 -> T092 -> T093 -> T094 -> T095 -> T096 -> T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat. Não transformar intenção em conclusão sem evidência versionada.
```

## 2. Estado resumido

- SPEC-000 ativa.
- HEAD antes de T058: `6802d7bb75297dc8f3e403b57113c73170c78061`.
- T050–T058 concluídas documentalmente.
- Próximo: T059.
- Runtime/IA/vector: inexistentes.
- SPEC-001: bloqueada até T097.

## 3. Regra de atualização

Atualizar este arquivo ao concluir T059. Não acumular estados contraditórios.

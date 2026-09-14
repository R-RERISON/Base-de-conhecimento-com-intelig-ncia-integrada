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
- HEAD confirmado antes do bloco T056: fe40f8a56e2efb440f748963ef58c3b69cba4470
- O commit que contém esta versão representa o fechamento documental de T056; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários individuais + T050/T051/T052/T053/T054/T056 concluídos documentalmente; nenhum runtime novo.

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

ARTEFATOS CENTRAIS
- mapa-ownership-dados.md
- matriz-sobreposicoes.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- mapa-contratos-quebrados.md
- matriz-wordpress-first.md
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
- Search Indexing: post/item/deep-link/vector projections.
- Search Quality: Golden/evidências.
- Analytics: events/interactions/outcomes.
- Core Configuration: settings/runtime version.
- Operations: queue/migration/rebuild/purge.
- AI Assist: sugestões não canônicas.

T050/T051 — CONTRATOS CONSOLIDADOS
- persistência por owner/conceito, não por plugin antigo;
- chaves/stores históricos são compatibilidade/origem;
- dual-write permanente proibido;
- evento = persistir -> confirmar -> emitir;
- admin-post/server-rendered baseline;
- AJAX só por live UX;
- REST negado sem consumidor;
- queue não nasce por herança;
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
- Word Cloud/anchors dependem de produto/preflight, mas não podem criar parser próprio.

D-005 GAC
- fora do core;
- adapter apenas se requisito/consumidor comprovado; caso contrário descartar.

D-006 Classificação GRE/KB2Ops
- ownership resolvido;
- audiência é um conceito;
- service != affected_service e technologies != systems sem profiling;
- dual-read temporário possível; dual-write permanente proibido.

D-007 UI/CSS fragmentados
- DS único futuro;
- CSS/menus antigos não são contrato;
- shortcodes são preflight separado.

D-008 AI READY
- baseline: publish + approved + 8/8 + include_ai;
- futuro: regra única/testada; derivada, não fonte canônica.

T056 — WORDPRESS-FIRST
Arquivo canônico: matriz-wordpress-first.md.

DECISÕES CORE
- WP_Post/Elementor continuam fonte editorial.
- Summary objective/escalation/important -> Metadata API.
- Review state/notes/reviewer/include_ai/history bounded -> Metadata API + Users; Revisions avaliadas quando snapshot for requisito.
- Settings -> Settings/Options API.
- Authorization -> Users/Roles/Capabilities.
- CSRF -> Nonces; nonce nunca substitui capability.
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

IMPORTANTE
- os quatro unknowns classificatórios NÃO seguem para T057; continuam dentro de primitives WordPress.
- taxonomy não foi escolhida só por ser classificação; somente onde reutilização/filtro/faceta têm evidência.
- nenhum CPT/taxonomy/meta foi registrado no runtime; a decisão é documental.

SEARCH T056
- WP_Query/native search permanece fallback.
- native search é insuficiente para paridade completa porque opera sobre title/excerpt/content e não fornece por si só documento Elementor extraído + Summary/Classificação + Item Knowledge + ranker composto.
- meta LIKE/_elementor_data KB2Ops continua rejeitado como arquitetura futura.

ÚNICOS CANDIDATOS PARA T057
F-057-01 — Search Retrieval Projection
- lexical por post + itens/identidade pesquisável;
- sem schema/tabela escolhidos;
- T057 deve provar corpus, consultas, latência, rebuild e índices mínimos.

F-057-02 — Analytics Facts, CONDICIONAL
- events/interactions/outcomes somente se requisito sobreviver;
- B-004 obrigatoriamente antes da aprovação;
- pode morrer em T057 sem implementação.

F-057-03 — Durable Job State, CONDICIONAL
- somente se index/rebuild assíncrono exigir lease/retry/dead/recovery;
- WP-Cron continua sendo trigger;
- pode morrer se processamento bounded/manual atender.

ITENS RETIRADOS DE T057
- Summary;
- Classification;
- Review/history bounded;
- Settings/Options;
- Users/Capabilities/Nonces;
- admin-post/AJAX/REST transportes;
- Search Knowledge/Golden, enquanto governados/baixo volume;
- Site Health;
- caches;
- Coverage/read models simples;
- Revisions;
- audit/evidência operacional de baixa frequência.

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
B-001 — Content Extractor representativo para Search/RAG.
B-002 — profiling classificatório antes de migração/cutover.
B-003 — preflight de consumidores antes de retirar plugins/aliases.
B-004 — política de Analytics/query text antes de telemetria detalhada/F-057-02.
B-005 — deep-link/anchors antes de paridade completa Item Knowledge.
B-006 — semântica de falha multi-campo antes do write path composto definitivo.
B-007 — stale/observabilidade antes de async indexing/queue em produção/F-057-03.

Blockers são contextuais. Não bloquear um slice que não usa a capacidade correspondente.

DÍVIDAS POSTERGÁVEIS
- Word Cloud;
- GAC sem requisito;
- side panel GRE;
- quality_daily;
- vetor/embeddings/Foundry;
- queue quando slice não exigir;
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
- schema mínimo de F-057-01, se aprovado;
- política/schema mínimo de F-057-02, se aprovado;
- necessidade/schema mínimo de F-057-03, se aprovado;
- deep-link/anchors;
- preflight real dos consumidores;
- chunks/vector/embeddings;
- Foundry/provider;
- runtime/layout final.

O QUE NÃO DEVE SER FEITO AGORA
- não criar bootstrap/runtime;
- não registrar taxonomy/CPT/meta final;
- não criar tabela/schema;
- não implementar migration/adapters/aliases;
- não integrar Foundry;
- não criar vector/embeddings;
- não copiar classes dos legados;
- não alterar ASI/GRE/KB2Ops;
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T057
Identificar infraestrutura própria mínima justificada, restrita inicialmente a F-057-01..03.

PARA CADA FAMÍLIA
1. reafirmar comportamento e owner;
2. demonstrar limitação da primitive WordPress já avaliada em T056;
3. estimar/registrar volume, cardinalidade, taxa de escrita e consultas;
4. definir latência/SLA e concorrência/durabilidade;
5. separar canônico de projection/operacional;
6. verificar se uma única infraestrutura mínima cobre mais de um comportamento sem acoplamento indevido;
7. comparar NÃO CONSTRUIR / síncrono bounded / WP Core / extensão própria;
8. somente se próprio vencer, documentar requisitos de storage/índices — ainda sem runtime;
9. preservar fallback/degradação/rollback;
10. registrar testes/evidência que T055 precisará proteger.

CRITÉRIO PARA FECHAR T057
- cada F-057 foi aprovada, reduzida ou descartada por evidência;
- nenhuma das 12 tabelas ASI renasceu por inércia;
- nenhum schema existe sem workload/consulta explícitos;
- Analytics não avança sem B-004;
- queue não avança sem necessidade real + B-007;
- Search projection permanece reconstruível e nunca owner editorial;
- nenhum runtime foi criado.

ORDEM RESTANTE
T057 -> T055 -> T058 -> T059 -> T090–T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat. Não transformar intenção em conclusão sem evidência versionada.
```

## 2. Estado resumido

- SPEC-000 ativa.
- HEAD antes de T056: `fe40f8a56e2efb440f748963ef58c3b69cba4470`.
- T050–T054 + T056 concluídas documentalmente.
- Próximo: T057.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## 3. Regra de atualização

Atualizar este arquivo ao concluir T057. Não acumular estados contraditórios.

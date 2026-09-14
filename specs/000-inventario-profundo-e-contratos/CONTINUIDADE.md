# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## Prompt pronto para colar em novo chat

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
- HEAD confirmado antes do bloco T059: c79bf04ebc3587721464c39a58d9c328108a2482
- O commit que contém esta versão representa o fechamento documental de T059; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários + T050–T059 concluídos documentalmente; nenhum runtime novo.

BASELINES FIXADAS
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

ARTEFATOS CANÔNICOS
- mapa-ownership-dados.md
- matriz-sobreposicoes.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- mapa-contratos-quebrados.md
- matriz-wordpress-first.md
- infraestrutura-propria-minima.md
- catalogo-testes-regressao.md
- matriz-ia-vetor.md
- matriz-paridade-futura.md  <-- visão executiva final T059
- riscos-e-drifts.md
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

T059 — MATRIZ DE PARIDADE FUTURA FINAL
Arquivo canônico: matriz-paridade-futura.md.

CLASSES TEMPORAIS
- PRIMEIRO_RUNTIME: primeira onda de vertical slices; NÃO big-bang.
- POSTERIOR: capacidade aprovada depois das dependências/gates.
- POSTERGADO: complexidade não comprada; reabrir com evidência.
- COMPAT_CUTOVER: apenas se consumidor/coexistência real for comprovado.
- DESCARTADO: não transportar ao baseline.

PRIMEIRO_RUNTIME — DIREÇÃO
- Core/Settings/Security/Design System mínimo.
- Summary em Metadata API.
- Review/Governança em Metadata + WP Users.
- Classificação por conceitos já resolvidos em primitives WP.
- Content Extractor canônico.
- Site Health/diagnóstico mínimo.
- native WP search pode existir como fallback, não paridade final.

POSTERIOR
- Search Knowledge e Golden.
- Search Retrieval Projection única `post|item`.
- QueryContext/ranking explicável.
- item identity e, separadamente, deep-link sob B-005.
- IA P1: uma jornada estreita Classificação OU Summary.
- RAG P2 após retrieval confiável.

POSTERGADO
- Analytics detalhado/query logging.
- durable queue.
- vector store/embeddings.
- semantic/hybrid/rerank.
- agentes/tools.
- Foundry File Search como core.
- Word Cloud.
- rollups/audit genérico.
- REST/SPA sem consumidor.

INFRA PRÓPRIA APROVADA
Somente Search Retrieval Projection futura e reconstruível, um store lógico inicial `document_kind=post|item`. DDL/nome/índices físicos continuam abertos à SPEC concreta.

DADOS QUE NÃO PODEM SER PERDIDOS
1. WP_Post/Elementor/taxonomias editoriais.
2. Oito valores GRE.
3. Review/include_ai/notas/revisor/histórico KB2Ops válidos.
4. Classificações KB2Ops efetivamente usadas.
5. vocabulary/bindings/relevance rules ASI manuais reais, quando houver.
6. Golden Queries/expectativas ASI reais/úteis.

NÃO MIGRAR AUTOMATICAMENTE COMO CANÔNICO
- índices/search_items antigos;
- caches;
- queue;
- quality_daily/rollups;
- telemetria histórica;
- migrations registry;
- embeddings/vectors;
- outras projections reconstruíveis.

BLOCKERS B-001–B-007 — LEITURA T059
- B-001 bloqueia Search/RAG/embedding produtivos; não Core/Summary/Review.
- B-002 bloqueia profiling/cutover classificatório; não todo o produto.
- B-003 bloqueia retirada de plugins/aliases; não coexistência.
- B-004 bloqueia Analytics/query logging; não Search lexical.
- B-005 bloqueia deep-link público de item; não Search post-level/item não navegável.
- B-006 bloqueia write composto definitivo; não leituras.
- B-007 bloqueia durable queue/async; não sync/bounded/manual rebuild.

T095 deve fechar ou postergar blocker POR SLICE. T097 pode autorizar SPEC-001 com blockers contextuais abertos se a SPEC autorizada não depender deles e declarar seus gates.

FALLBACKS
- domínio: falha preserva estado anterior; sem sucesso falso.
- Search projection: degraded + native/bounded fallback + rebuild.
- IA P1: provider falhou -> sem sugestão/sem write.
- RAG: provider falhou -> retrieval/evidências sem síntese.
- semantic futuro: lexical fallback.
- sem queue: sync/bounded/manual batches; não improvisar store.
- compat: plugins antigos/coexistência em homologação até cutover.

IA/VETOR T058 CONTINUA VÁLIDA
- P0 determinístico primeiro.
- P1 assistivo opcional; Generate != Apply.
- P2 RAG retrieval-first e pode ser lexical-first.
- P3 embeddings/semantic/rerank postergados.
- P4 agentes/tools postergados.
- Foundry é provider preferencial candidato, não domínio.
- File Search não é Search/RAG canônico.
- custo/budget/NO_CHANGE/data egress/rastreabilidade continuam obrigatórios quando IA existir.

SHORTCODES EM PREFLIGHT B-003
- [asi_search_form]
- [bdc_word_cloud]
- [bdc_resumo_executivo]
- [kb2ops_search]
- [kb2ops_portal]
Nenhum alias aprovado automaticamente.

UNKNOWNS LEGÍTIMOS APÓS T059
- responsible_team/catalog_item/affected_service/systems_involved: Metadata vs Taxonomy sob B-002.
- meta revisions finais.
- DDL/nome/índices da Search Projection.
- NFRs reais Search/index/rebuild.
- Analytics/query policy.
- necessidade futura de queue.
- anchors/deep-link.
- preflight real de consumidores.
- primeiro caso IA P1.
- provider/deployment/model/prompt reais.
- secrets/data egress institucionais.
- necessidade/tecnologia de embeddings/vector.
- runtime/layout físico final.

O QUE NÃO DEVE SER FEITO AGORA
- não criar runtime/bootstrap.
- não criar schema/tabela/migration.
- não registrar taxonomies/CPT/meta finais.
- não criar Golden dataset real.
- não criar Analytics/queue.
- não implementar aliases/adapters.
- não integrar Foundry.
- não chamar LLM.
- não criar chunks/embeddings/vector store.
- não criar agentes/tools.
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T090
Executar a Revisão do Arquiteto WordPress sobre a matriz final T059.

T090 DEVE
1. reler Constituição, Manifesto, T056, T057 e matriz-paridade-futura.md.
2. revisar cada primitive/storage da matriz.
3. procurar infraestrutura própria que Core ainda possa eliminar.
4. revisar Metadata vs Taxonomy vs Options/Revisions.
5. revisar admin-post/AJAX/REST e negar endpoint sem consumidor.
6. revisar Site Health/Transients/Object Cache/WP-Cron.
7. garantir que projection nunca virou owner.
8. classificar findings como APROVADO | SIMPLIFICAR | BLOQUEAR | INVESTIGAR.
9. registrar qualquer alteração documental necessária.
10. não criar runtime.

CRITÉRIO PARA FECHAR T090
- toda família da matriz final foi revisada sob WordPress-first;
- nenhum uso desnecessário de infraestrutura própria ficou sem finding;
- findings possuem severidade, evidência, decisão e impacto em T091–T097;
- divergências são corrigidas documentalmente;
- próximo passo T091 fica explícito.

ORDEM RESTANTE
T090 -> T091 -> T092 -> T093 -> T094 -> T095 -> T096 -> T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat. Não transformar intenção em conclusão sem evidência versionada.
```

## Estado resumido

- SPEC-000 ativa.
- HEAD antes de T059: `c79bf04ebc3587721464c39a58d9c328108a2482`.
- T050–T059 concluídas documentalmente.
- Próximo: T090.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## Regra de atualização

Atualizar este arquivo ao concluir T090. Não acumular estados contraditórios.

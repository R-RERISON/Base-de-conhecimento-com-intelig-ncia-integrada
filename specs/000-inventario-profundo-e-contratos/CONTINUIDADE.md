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
- HEAD confirmado antes do bloco T054: c4f671f43fe41f2a5e8ee9db706223b52d5a27eb
- O commit que contém esta versão representa o fechamento documental de T054; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários individuais + T050/T051/T052/T053/T054 concluídos documentalmente; nenhum runtime novo.

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

ARTEFATOS CENTRAIS
- mapa-ownership-dados.md
- matriz-sobreposicoes.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- mapa-contratos-quebrados.md
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
- queue ainda não autorizada;
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

BLOCKERS T054
B-001 — Content Extractor representativo para Search/RAG.
B-002 — profiling classificatório antes de migração/cutover.
B-003 — preflight de consumidores antes de retirar plugins/aliases.
B-004 — política de Analytics/query text antes de telemetria detalhada.
B-005 — deep-link/anchors antes de paridade completa Item Knowledge.
B-006 — semântica de falha multi-campo antes do write path composto definitivo.
B-007 — stale/observabilidade antes de async indexing/queue em produção.

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
- taxonomy versus postmeta por classificação;
- nomes/chaves/cardinalidade finais;
- revisions/history;
- schema Search Index/Items;
- storage Search Knowledge/Golden;
- necessidade concreta de queue;
- Analytics facts/schema/retention;
- deep-link/anchors;
- preflight real dos consumidores;
- chunks/vector/embeddings;
- Foundry/provider;
- runtime/layout final.

O QUE NÃO DEVE SER FEITO AGORA
- não criar bootstrap/runtime;
- não registrar taxonomy;
- não criar tabela/schema;
- não implementar migration/adapters/aliases;
- não integrar Foundry;
- não criar vector/embeddings;
- não copiar classes dos legados;
- não alterar ASI/GRE/KB2Ops;
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T056
Construir a matriz WordPress-first por conceito/capacidade.

Para cada necessidade:
1. listar requisito/comportamento;
2. owner lógico;
3. primitive WordPress candidata;
4. por que atende ou não atende;
5. constraints de cardinalidade/consulta/volume/segurança;
6. compatibilidade/migração afetada;
7. decisão: SUBSTITUIR_POR_WORDPRESS | MANTER WP | CANDIDATO_INFRA_PROPRIA_T057 | AINDA_NAO_SABEMOS;
8. evidência necessária para resolver unknowns.

Avaliar obrigatoriamente:
- WP_Post/Elementor;
- Metadata API;
- Taxonomy API;
- Options/Settings API;
- Users/Roles/Capabilities;
- Nonces;
- admin-post;
- AJAX somente quando necessário;
- Site Health;
- Revisions;
- Transients/Object Cache;
- WP-Cron como trigger;
- native search antes de projection própria.

CRITÉRIO PARA FECHAR T056
- cada dado/capacidade tem primitive WP avaliada;
- taxonomia não é escolhida só porque campo é classificatório;
- tabela própria não é escolhida em T056;
- tudo que WordPress atender é retirado da lista T057;
- somente limitações comprovadas seguem para infraestrutura própria;
- compatibilidade T054 não distorce a arquitetura;
- T057 começa com uma lista pequena e justificada.

ORDEM RESTANTE
T056 -> T057 -> T055 -> T058 -> T059 -> T090–T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat. Não transformar intenção em conclusão sem evidência versionada.
```

## 2. Estado resumido

- SPEC-000 ativa.
- HEAD antes de T054: `c4f671f43fe41f2a5e8ee9db706223b52d5a27eb`.
- T050–T054 concluídas documentalmente.
- Próximo: T056.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## 3. Regra de atualização

Atualizar este arquivo ao concluir T056. Não acumular estados contraditórios.
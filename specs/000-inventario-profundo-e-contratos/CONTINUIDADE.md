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
- HEAD confirmado antes do bloco T050/T051: b42cffc6090f4c66857113738e1838c93f0e3c48
- O commit que contém esta versão de CONTINUIDADE.md representa o fechamento documental T050/T051; confirme seu SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários individuais + T050/T051/T052/T053 concluídos documentalmente; runtime novo bloqueado.

BASELINES FIXADAS
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

TAREFAS CONCLUÍDAS
- T000–T002 preparação/governança.
- T010–T019 KB2Ops.
- T020–T034 ASI.
- T040–T047 GRE.
- T050 catálogo unificado de persistência.
- T051 catálogo unificado de integrações/hooks/superfícies.
- T052 ownership de dados.
- T053 sobreposição funcional.

ARTEFATOS CENTRAIS DO CRUZAMENTO
- mapa-ownership-dados.md
- matriz-sobreposicoes.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- riscos-e-drifts.md
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
- Persistência confirmada precede evento de domínio.
- Consumers de eventos devem ser idempotentes.
- Dual-write permanente é proibido.
- Adapter/dual-read só pode ser temporário e ter gate de remoção.
- IA sugere; humano decide; owner persiste.
- Retrieval precede síntese.
- IA/vetor opcionais/degradáveis.
- Lexical continua funcional sem IA.
- Nenhum runtime antes de T097 autorizar SPEC-001.

OWNERS LÓGICOS JÁ RESOLVIDOS — T052
- Editorial WP/Elementor: post_title, post_content, _elementor_data, publicação.
- Resumo Executivo: objective, escalation, important.
- Classificação: responsible_team, catalog_item, audience, service, affected_service, technologies, systems_involved, knowledge_type, keywords, versions.
- Revisão/Governança: review state, notes, reviewer/time, include_ai, history.
- Content Extraction: texto/estrutura/hash derivados.
- Search Knowledge: vocabulary/bindings/relevance rules.
- Search Indexing: post/item/deep-link/vector projections.
- Search Quality: Golden Queries/evidências.
- Analytics: search events/interactions/outcomes.
- Core Configuration: settings/runtime version.
- Operations: queue/migration/rebuild/purge state.
- AI Assist: sugestões não canônicas.

DECISÕES T053 — SOBREPOSIÇÃO
- um Summary Store/Resumo;
- uma Search;
- um domínio de Classificação;
- um Analytics/Search Intelligence;
- um Design System/shell;
- uma navegação de Insights/Settings/Operations.

NÃO FUNDIR
- aprovação do artigo != Apply de Search Knowledge;
- qualidade de conteúdo != Search Quality;
- service != affected_service sem profiling;
- technologies != systems_involved sem profiling;
- categorias/tags editoriais != eventual classificação sistêmica.

T050 — PERSISTÊNCIA CONSOLIDADA
Arquivo canônico: catalogo-persistencia.md.

Decisões:
1. persistência está organizada por owner/conceito, não por plugin antigo;
2. chaves _bdc_es_*, _kb2ops_* e stores asi_* são rastreabilidade histórica/compatibilidade;
3. canônico, derivado, observacional, operacional, configuração e compat são classes separadas;
4. dual-write permanente proibido;
5. dual-read/adapter temporário apenas com consumidor + gate de remoção;
6. Resumo não justifica tabela própria;
7. Search Index/Items são capacidades necessárias, mas schema/tabela dependem de T057;
8. quality_daily/migrations históricas/tracking option-viewcount KB2Ops não nascem automaticamente;
9. Search Knowledge/Golden storage segue T056/T057;
10. query text/retention continua bloqueado até política explícita.

Dados que não podem ser perdidos sem decisão:
- WP_Post/Elementor/taxonomias editoriais;
- oito valores GRE históricos;
- review/include AI/notas/revisor/histórico válidos;
- classificações KB2Ops efetivamente usadas;
- vocabulary/bindings/rules/Golden manuais ASI quando houver dados reais;
- telemetria somente quando política/requisito justificar retenção.

T051 — INTEGRAÇÕES CONSOLIDADAS
Arquivo canônico: catalogo-integracoes.md.

Contratos documentais:
- EVT-001 Summary/Classificação changed -> Indexing/cache invalidation.
- EVT-002 Review State Confirmed -> scope/readiness/Insights/AI opcional.
- EVT-003 Classification Changed -> Indexing/Insights/filtros.
- EVT-004 Search Knowledge Applied -> Search/cache/Golden stale.
- EVT-005 Content source changed/invalidation -> projections.
- EVT-006 Search Fact -> Analytics non-fatal.

Nomes EVT-* são IDs documentais, NÃO nomes finais de hooks WordPress.

Regras:
- evento somente após write confirmado;
- failure de projection não desfaz dado canônico;
- admin-post/server-rendered é baseline;
- AJAX apenas se live Search/tracking exigir;
- REST negado sem consumidor;
- Search pública aplica scope antes de exposição e revalida detail;
- tracking, se existir: nonce + rate-limit + HMAC + server authority + idempotência;
- WP-Cron pode disparar trabalho, mas queue própria ainda não foi aprovada;
- rebuild massivo nunca ocorre silenciosamente em activation/save.

SHORTCODES/COMPATIBILIDADE
- [asi_search_form] — compat a provar.
- [bdc_word_cloud] — opcional/compat a provar.
- [bdc_resumo_executivo] — compat a provar; current-post-only se alias existir.
- [kb2ops_search] — compat a provar.
- [kb2ops_portal] — compat a provar.
- side panel GRE — não canônico.

Nenhum alias deve ser portado sem preflight de uso real.

DRIFTS D-001–D-008 — ESTADO ANTES DE T054
- D-001 Objective_Provider: quebrado histórico; futuro Summary Store interno.
- D-002 bdc_es_objective_updated: quebrado histórico; contrato pós-write futuro definido.
- D-003 raw post_content vs Elementor: direção resolvida por extractor único.
- D-004 múltiplos parsers: direção resolvida; downstream não reparseia.
- D-005 GAC: fora do core; adapter opcional/preflight.
- D-006 classificação GRE/KB2Ops: ownership resolvido; primitive/profiling/migration abertos.
- D-007 UI fragmentada: DS único; runtime ainda inexistente.
- D-008 AI READY docs/runtime: drift confirmado; baseline runtime = publish+approved+8/8+include_ai.

RISCOS PRIORITÁRIOS
- R-KB-001: extractor pode omitir custom widgets quando saída parcial não vazia; blocker técnico para Search/RAG final.
- X-001: adapter/dual-read virar permanente.
- X-002: evento antes de consistência.
- X-003: índice correto sobre extração incompleta.
- X-004: Analytics super/subdimensionado.
- X-005: migration virar arquitetura permanente.
- X-007: aprovação de artigo confundida com Search Apply.
- X-008: dashboard único virar scans ilimitados.
- X-010: shortcodes/aliases portados sem consumidor.
- X-011: tempestade de invalidações/eventos; consumers precisam ser idempotentes/coalescíveis.
- X-012: hook interno virar API pública acidental.
- X-013: projection stale sem observabilidade.

O QUE AINDA NÃO FOI DECIDIDO
- taxonomy versus postmeta final;
- nomes/chaves finais e cardinalidade;
- profiling/migração de classificações;
- schema Search Index/Items;
- storage Search Knowledge/Golden;
- queue durável no primeiro slice;
- Analytics facts/schema/query retention;
- revisions/history;
- preflight de shortcodes/consumidores;
- anchors/deep links finais;
- chunks/vector/embeddings;
- Foundry/provider;
- layout/runtime final.

O QUE NÃO DEVE SER FEITO AGORA
- não criar bootstrap/runtime;
- não registrar taxonomy;
- não criar tabela/schema;
- não implementar migration;
- não integrar Foundry;
- não criar chunks/vectors/embeddings;
- não copiar classes legadas;
- não alterar ASI/GRE/KB2Ops;
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T054
Criar o mapa final de contratos quebrados/drifts/compatibilidade.

Para cada D-001–D-008 e cada compatibilidade relevante:
1. descrever contrato histórico e evidência;
2. indicar produtor/consumidor atual;
3. classificar como: CORRIGIDO PELA ARQUITETURA FUTURA | COMPAT TEMPORÁRIO | DESCARTADO | DEPENDE DE PREFLIGHT/PROFILING | BLOCKER;
4. definir risco se ignorado;
5. definir mecanismo de coexistência, quando necessário, sem escolher schema prematuramente;
6. definir gate de remoção do adapter/alias;
7. identificar regressão futura necessária;
8. listar blockers que precisam estar fechados até T095.

T054 não cria migration/runtime. Ela transforma drift em decisão de compatibilidade e gate.

CRITÉRIO PARA FECHAR T054
- D-001–D-008 têm classificação final clara;
- shortcodes/hooks/bridges/adapters históricos têm status explícito;
- todo compat temporário possui consumidor/preflight e gate de remoção;
- blockers reais para SPEC-001 estão separados de dívidas postergáveis;
- nenhuma divergência é escondida como “resolvida” apenas porque o design futuro é melhor;
- T056 pode começar sem dúvida sobre o que precisa coexistir.

ORDEM RESTANTE
T054 -> T056 -> T057 -> T055 -> T058 -> T059 -> T090–T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat. Não transformar intenção em conclusão sem evidência versionada.
```

## 2. Estado resumido para humanos

- **SPEC:** SPEC-000.
- **HEAD antes do bloco T050/T051:** `b42cffc6090f4c66857113738e1838c93f0e3c48`.
- **T050:** concluída documentalmente.
- **T051:** concluída documentalmente.
- **T052/T053:** já concluídas.
- **Próximo:** T054.
- **Runtime novo:** inexistente.
- **SPEC-001:** bloqueada até T097.

## 3. Regra de atualização

Atualizar este arquivo ao concluir T054. Substituir estado obsoleto; não acumular handoffs contraditórios.
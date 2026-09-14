# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## 1. Prompt pronto para colar em novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".

Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

Antes de qualquer alteração:
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md.
4. Leia todos os artefatos de specs/000-inventario-profundo-e-contratos/.
5. Leia docs/DEFINITION-OF-DONE.md.
6. Leia este CONTINUIDADE.md inteiro.
7. Confirme o HEAD atual no GitHub.
8. Se qualquer baseline ou estado divergir, investigue antes de modificar documentos ou runtime.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- HEAD confirmado na entrada do bloco T052/T053: d03ecce4d3618ea0eddbf23d03f7fe65a8901dec
- O commit que contém esta versão de CONTINUIDADE.md representa o fechamento documental de T052/T053; confirme seu SHA atual no GitHub antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventários individuais + ownership T052 + sobreposição T053 concluídos documentalmente; runtime novo continua bloqueado.

BASELINES CONFIRMADAS
- ASI: R-RERISON/Advanced-search-Intelligence 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE: R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops: R-RERISON/KB2Ops-Operational-Knowledge-Engine 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

TAREFAS CONCLUÍDAS
- T000–T002: preparação/governança.
- T010–T019: KB2Ops.
- T020–T034: Advanced Search Intelligence.
- T040–T047: Gerenciador de Resumo Executivo.
- T052: mapa de ownership de dados.
- T053: matriz de sobreposição funcional.

ARTEFATOS NOVOS DO CRUZAMENTO
- specs/000-inventario-profundo-e-contratos/mapa-ownership-dados.md
- specs/000-inventario-profundo-e-contratos/matriz-sobreposicoes.md

PRÓXIMO BLOCO EXATO
T050 + T051 — consolidar catálogo de persistência e catálogo de integrações usando ownership e sobreposição já resolvidos.

ORDEM RESTANTE OBRIGATÓRIA
1. T050 + T051 — persistência/hooks/rotas/integrações consolidados.
2. T054 — consolidar contratos quebrados/drifts e sua política de compatibilidade.
3. T056 — matriz WordPress-first: escolher primitive nativa campo/capacidade por campo/capacidade.
4. T057 — justificar somente a infraestrutura própria que realmente sobrar.
5. T055 — consolidar regressão/Golden/gates já alinhados às decisões de arquitetura.
6. T058 — priorizar IA/vetor como extensão opcional, com dependências/custo/risco.
7. T059 — matriz de paridade futura final.
8. T090–T097 — revisões/gate final e autorização ou bloqueio da SPEC-001.

Não inverter essa ordem para começar por tabela, taxonomia, vetor, Foundry ou runtime.

INVARIANTES VIGENTES
- WordPress-first.
- Princípio de negação antes de complexidade.
- WordPress/Elementor são fonte editorial absoluta.
- O plugin não faz manutenção editorial dos posts.
- Nunca escrever _elementor_data por pipeline derivado.
- Nunca reescrever post_content silenciosamente.
- Um conceito canônico possui um único owner lógico.
- Um Content Extractor canônico alimenta todos os consumidores derivados.
- Qualidade da extração precede qualidade de Search/RAG.
- Persistência confirmada precede qualquer evento de domínio.
- Projection/cache/index/vector nunca substitui fonte canônica.
- IA sugere; humano decide; owner do domínio persiste.
- Retrieval precede síntese.
- IA/vetor são opcionais/degradáveis.
- Lexical continua funcional sem IA.
- Golden blockers são obrigatórios quando Search muda.
- Compatibilidade/migração não pode virar ownership permanente.
- Nenhum runtime antes de T097 autorizar SPEC-001.

CONCLUSÕES ASI QUE NÃO DEVEM SER PERDIDAS
1. Não copiar as 12 tabelas como arquitetura futura.
2. Preservar lexical degradável, QueryContext limitado, explicabilidade e Golden Queries.
3. Preservar Item Knowledge/identity/deep-link, reconstruindo sobre extractor único.
4. PostIndex, Item Knowledge, Structural Audit e Word Cloud não podem ter parsers independentes.
5. Vocabulary/bindings/rules têm valor e pertencem a Search Knowledge; storage final continua aberto.
6. Queue só nasce se durabilidade/workload justificarem.
7. Migrations/Reconciler/Orchestrator ASI carregam história e não pertencem automaticamente ao greenfield.
8. Events/Interactions/Outcomes têm valor, mas privacy/query retention precisam ser redesenhadas.
9. Tracking público: nonce + rate limit + HMAC + server authority + idempotência.
10. Golden suite vazia não é PASS.
11. Site Health é primitive preferida para diagnostics.
12. GAC fica fora do core.

CONCLUSÕES GRE
1. Oito _bdc_es_* são contrato de compatibilidade conhecido.
2. post_title é título canônico; _bdc_es_title proibida.
3. Metadata API + edit_post + nonce + allowlist + sanitização são baseline.
4. Reads são side-effect free; vazio remove meta.
5. Multi-campo não tem atomicidade compensatória comprovada.
6. Coverage Dashboard atual é read-only mas sem workload bound.
7. Não há evidência para tabela/REST/AJAX/cron próprios de Resumo.
8. Objective_Provider e bdc_es_objective_updated esperados pelo ASI não existem.
9. Futuro: Summary Store interno único + evento após write confirmado.

CONCLUSÕES KB2OPS
1. Content Extractor é principal contrato técnico: _elementor_data read-only -> parser determinístico -> render fallback -> post_content fallback.
2. Gap crítico: extração parcialmente não vazia pode omitir custom widget sem acionar render fallback.
3. Knowledge Studio/review states/checklist/suggestions são fortes contratos de produto.
4. AI READY runtime = publish + approved + Resumo 8/8 + include_ai.
5. save_review() emite approval sem comprovar todos os writes; não portar esse comportamento.
6. Summary_Bridge é read-only/duplicado e deve desaparecer no bounded context unificado.
7. Search atual é provisória e não deve ser motor futuro.
8. Analytics option/view count são leves mas insuficientes para privacy/concurrency/outcomes.
9. Design System KB2Ops é principal referência visual.
10. Activation/migration/uninstall são reversíveis/não destrutivos por default; preservar princípio, não hardcodes.
11. Build determinístico é valioso; contratos documentados apenas no audit precisam virar testes executáveis.

T052 — OWNERSHIP RESOLVIDO
Arquivo canônico: mapa-ownership-dados.md.

Owners lógicos futuros:
- WP_Post/Elementor -> Editorial WordPress/Elementor.
- objective/escalation/important -> Resumo Executivo.
- responsible_team/catalog_item/audience/services/systems/technologies/knowledge_type/keywords/versions -> Classificação de Conhecimento.
- review_state/notes/reviewer/time/include_ai/history -> Revisão e Governança.
- texto/estrutura derivados -> Content Extraction (projection).
- vocabulary/bindings/rules -> Search Knowledge.
- post/item/vector indexes -> Search Indexing (projections).
- Golden Queries -> Search Quality.
- events/interactions/outcomes -> Analytics/Search Intelligence.
- settings -> Core Configuration.
- queue/migration -> Operations, estado operacional/transitório.
- sugestões de IA -> AI Assist, nunca canônicas sem aprovação humana.

Decisões críticas T052:
- audiência GRE/KB2Ops é um único conceito canônico.
- service e affected_service ficam no mesmo domínio, mas NÃO foram declarados equivalentes.
- technologies e systems_involved ficam no mesmo domínio, mas NÃO foram declarados equivalentes.
- Search não é owner de classificação.
- Summary UI pode compor classificações sem possuir semanticamente esses dados.
- GAC é owner externo; adapter opcional somente se requisito real.
- storage físico permanece aberto.

T053 — SOBREPOSIÇÃO RESOLVIDA
Arquivo canônico: matriz-sobreposicoes.md.

Convergências funcionais:
1. um único Summary Store/Resumo Executivo;
2. uma única experiência de Search;
3. um único domínio de Classificação;
4. um único Analytics/Search Intelligence;
5. um único Design System/shell;
6. uma única navegação de Insights/Reports/Settings.

Combinação de referências:
- motor/qualidade de Search: contratos ASI;
- Content Extraction/UX/Design System: contratos KB2Ops;
- Summary Store/WordPress-first: contratos GRE;
- release futuro: integração WordPress real GRE + regressão/Golden ASI + package determinístico KB2Ops/GRE.

RESPONSABILIDADES QUE NÃO DEVEM SER FUNDIDAS
- review approval != Apply de Search Knowledge;
- qualidade de conteúdo != Search Quality;
- service != affected_service sem profiling;
- technologies != systems_involved sem profiling;
- categorias/tags editoriais != eventual taxonomia sistêmica.

DRIFTS ESTADO ATUAL
- D-001 ASI -> Objective_Provider: QUEBRADO CONFIRMADO.
- D-002 ASI -> bdc_es_objective_updated: QUEBRADO CONFIRMADO.
- D-003 ASI post_content vs Elementor: direção funcional resolvida por extractor único.
- D-004 múltiplos extractors: direção funcional resolvida; downstream não reparseia fonte.
- D-005 GAC: dependência ambiental, fora do core.
- D-006 classificação GRE/KB2Ops: ownership resolvido; storage/migração abertos.
- D-007 UI fragmentada: owner visual resolvido em DS único; runtime ainda não existe.
- D-008 AI READY docs/runtime: drift interno confirmado; runtime baseline é referência histórica.

RISCOS CROSS-MODULE IMPORTANTES
- X-002: evento antes de consistência.
- X-003: índice correto construído sobre extração incompleta.
- X-004: Analytics super ou subdimensionado.
- X-005: migração virar arquitetura permanente.
- X-007: confundir aprovação de artigo com curadoria de Search.
- X-008: dashboard único virar scan/mega agregador caro.
- X-009: DS compartilhado gerar acoplamento lateral de domínio.
- X-010: compatibility adapters virarem duplicação permanente.

O QUE NÃO FOI DECIDIDO
- taxonomy versus postmeta por classificação;
- nomes/chaves finais;
- cardinalidade mono/multivalor;
- profiling de valores atuais;
- schema de search_index/search_items;
- storage de vocabulary/bindings/rules/Golden;
- necessidade concreta de queue;
- analytics facts/schema/retention;
- revisions/histórico;
- shortcodes/aliases de compatibilidade;
- deep-link/anchors finais;
- coexistência/migração detalhada;
- chunks/MariaDB Vector/embeddings;
- Foundry/provider contract;
- layout final de runtime.

O QUE NÃO DEVE SER FEITO AGORA
- Não criar bootstrap/runtime.
- Não registrar taxonomy.
- Não criar tabela.
- Não implementar migration.
- Não integrar Foundry.
- Não criar chunks/vectors/embeddings.
- Não copiar classes dos plugins de referência.
- Não alterar ASI/GRE/KB2Ops.
- Não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T050/T051
1. Abrir catalogo-persistencia.md e substituir o formato “por plugin” por uma visão consolidada por conceito/owner, preservando rastreabilidade de origem.
2. Para cada dado, registrar: owner lógico, fonte histórica, canônico/derivado/observacional/operacional, leitores, escritores, reconstruibilidade, compatibilidade e decisão preliminar de primitive sem ainda fechar T056 onde faltar evidência.
3. Abrir catalogo-integracoes.md e consolidar hooks/rotas/superfícies por responsabilidade futura, não por plugin histórico.
4. Marcar bridges/actions/shortcodes como canonical, compat temporário, descartado ou ainda dependente de preflight.
5. Formalizar contratos cross-module mínimos: Summary changed -> invalidation; Review approved -> readiness/downstream; Content Extractor -> Search; Search facts -> Analytics; Search Knowledge Apply separado de Review.
6. Não escolher infraestrutura própria antes de terminar T056/T057.
7. Atualizar research/tasks/checklist/CONTINUIDADE ao concluir o bloco.

CRITÉRIO PARA FECHAR T050/T051
- catálogos deixam de ser apenas inventário por plugin e passam a representar o produto unificado;
- toda persistência tem owner lógico definido ou unknown explicitamente justificado;
- nenhuma projection é tratada como dado canônico;
- toda integração cross-module tem produtor/consumidor/falha/idempotência preliminar;
- bridges/aliases têm status de compatibilidade explícito;
- nenhum schema/taxonomy/runtime é criado por antecipação;
- próximos passos T054/T056 ficam pequenos, verificáveis e sem adivinhação estrutural.

REGRA DE CONTINUIDADE
Repositório, Constituição, Manifesto e SPEC prevalecem sobre memória de chat. Se houver divergência, pare a decisão correspondente e investigue. Não transformar intenção em estado concluído sem evidência versionada.
```

## 2. Estado resumido para humanos

- **SPEC:** SPEC-000 — Inventário Profundo e Contratos.
- **HEAD confirmado antes de T052/T053:** `d03ecce4d3618ea0eddbf23d03f7fe65a8901dec`.
- **Inventários individuais:** concluídos.
- **T052:** concluída — mapa de ownership criado.
- **T053:** concluída — matriz de sobreposição criada.
- **Próximo bloco:** T050/T051.
- **SPEC-001:** bloqueada.
- **Runtime novo:** inexistente por decisão arquitetural.

## 3. Regra de atualização

Atualizar este arquivo ao concluir T050/T051. Substituir estado obsoleto; não acumular versões contraditórias no mesmo handoff.

# Pesquisa e Consolidação — SPEC-000

## Objetivo

Registrar fatos comprovados nas três baselines e decisões documentais do cruzamento. O inventário detalhado permanece nos arquivos `inventario-*`; este documento acompanha a evolução das hipóteses até contratos do produto futuro.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Fatos estruturais que permanecem válidos

### KB2Ops

- WordPress-first sem tabelas/REST/AJAX/cron novos no runtime atual;
- Content Extractor Elementor-aware, read-only, com parser determinístico + render fallback + `post_content` fallback;
- Knowledge Studio/review/AI READY e Design System são referências de produto;
- Search atual é provisória (`WP_Query/meta LIKE` + score simples);
- Analytics option/view count é leve porém insuficiente para privacy/outcomes/concurrency;
- lifecycle/uninstall preserva reversibilidade;
- risco de extração parcial de custom widgets permanece crítico;
- `save_review()` histórico pode emitir aprovação sem comprovar todos os writes;
- release audit não equivale a suíte executável versionada.

### ASI

- contratos maduros de lexical/FULLTEXT+fallback, QueryContext, ranking, Item Knowledge, vocabulary/bindings/rules, simulation, Golden, telemetria e segurança;
- 12 tabelas não são arquitetura futura automática;
- pipelines diretos sobre `post_content` devem desaparecer em favor de extractor único;
- queue tem semântica valiosa, mas storage só nasce por evidência;
- migration/reconciler/orchestrator e legacy carregam história;
- GAC é dependência ambiental, fora do core;
- `quality_daily` é otimização antecipada no greenfield.

### GRE

- oito metas privadas, `post_title` canônico, Metadata API e autorização `edit_post`;
- Summary Store side-effect free, allowlist/sanitização/read-after-write;
- nenhuma tabela/REST/AJAX/cron de domínio;
- Coverage é read-only mas sem bound de corpus;
- integração WordPress real e build determinístico são referências fortes;
- `Objective_Provider` e `bdc_es_objective_updated` esperados pelo ASI não existem.

## 3. T052 — Ownership

`mapa-ownership-dados.md` definiu owners lógicos sem escolher storage físico:

- Editorial WordPress/Elementor — fonte editorial absoluta;
- Resumo Executivo — objetivo/escalonamento/importante;
- Classificação — team/catalog/audience/services/systems/technologies/type/keywords/versions;
- Revisão/Governança — state/notes/reviewer/time/include AI/history;
- Content Extraction — projections textuais/estruturais;
- Search Knowledge — vocabulary/bindings/rules;
- Search Indexing — post/item/deep-link/vector projections;
- Search Quality — Golden/evidências;
- Analytics — facts observacionais;
- Core Configuration — settings;
- Operations — queue/migration;
- AI Assist — sugestões não canônicas.

Audiência GRE/KB2Ops virou um único conceito lógico. `service`/`affected_service` e `technologies`/`systems_involved` permanecem distintos até profiling.

## 4. T053 — Sobreposição

`matriz-sobreposicoes.md` consolidou:

- um Summary Store;
- uma Search;
- um domínio de Classificação;
- um Analytics/Search Intelligence;
- um Design System/shell;
- uma navegação de Insights/Settings/Operations.

Não foram fundidos:

- aprovação de artigo e Apply de Search Knowledge;
- qualidade de conteúdo e Search Quality;
- serviço e serviço afetado sem prova;
- tecnologia e sistema envolvido sem prova;
- taxonomias editoriais e futuras classificações sistêmicas.

## 5. T050 — Persistência consolidada

`catalogo-persistencia.md` deixou de ser inventário por plugin e passou a representar o produto futuro por conceito/owner.

### Decisões novas

1. chaves/tabelas antigas são **origem de compatibilidade**, não design futuro;
2. canônicos, projections, observacionais, operacionais e configuração são classes distintas;
3. projection stale nunca altera fonte canônica;
4. dual-write permanente é proibido;
5. dual-read/adapter só pode existir temporariamente e com gate de remoção;
6. não há justificativa para tabela de Resumo Executivo;
7. `quality_daily`, migrations históricas e tracking KB2Ops option/view-count não ganham direito automático de nascer;
8. Search Index/Items são capacidades necessárias, mas schema/tabela são decisão T057;
9. Search Knowledge/Golden precisam de primitive/storage em T056/T057;
10. query text/retention permanecem bloqueados até política explícita.

### Dados de cutover que não podem ser perdidos sem decisão explícita

- conteúdo WordPress/Elementor;
- oito valores GRE históricos;
- decisões humanas de review/include AI/notas/revisor/histórico válidas;
- classificações KB2Ops efetivamente utilizadas;
- vocabulary/bindings/rules/Golden manuais do ASI quando existirem dados reais;
- telemetria histórica apenas se requisito/política justificar retenção.

## 6. T051 — Integrações consolidadas

`catalogo-integracoes.md` passou a organizar integrações por contrato futuro.

### Contratos definidos

- Summary/Classificação changed -> projection invalidation;
- Review confirmed -> scope/readiness/Insights;
- Search Knowledge Applied -> cache/Golden/runtime Search;
- fonte editorial alterada -> invalidation, sem reprocessamento massivo síncrono;
- Search facts -> Analytics non-fatal;
- Content Extraction -> Search/Review/IA, sem parser paralelo.

### Regras de transporte

- admin-post/server rendering é baseline administrativo;
- AJAX é enhancement somente para live Search/tracking quando UX justificar;
- REST é negado enquanto não houver consumidor real;
- WP-Cron pode ser trigger, mas não substitui queue durável se T057 provar necessidade.

### Compatibilidade

Shortcodes `[asi_search_form]`, `[bdc_word_cloud]`, `[bdc_resumo_executivo]`, `[kb2ops_search]` e `[kb2ops_portal]` estão classificados como **compatibilidade a provar por preflight**, não APIs obrigatórias do novo produto.

Hooks `bdc_es_loaded`/`kb2ops_loaded` não têm direito automático de sobreviver; `bdc_es_objective_updated` é substituído conceitualmente por evento pós-write confirmado; `kb2ops_post_approved` precisa ser endurecido para o mesmo princípio.

## 7. Drifts D-001–D-008 — estado antes de T054

| ID | Estado atual |
|---|---|
| D-001 Objective Provider | quebrado histórico; arquitetura futura usa store interno |
| D-002 Objective event | quebrado histórico; contrato futuro pós-write definido |
| D-003 raw post_content vs Elementor | direção resolvida por Content Extraction único |
| D-004 múltiplos parsers | direção resolvida; downstream não reparseia |
| D-005 GAC | externo/adapter opcional |
| D-006 classificação duplicada | ownership resolvido; primitive/migração abertas |
| D-007 UI fragmentada | owner visual resolvido; runtime DS futuro ainda não existe |
| D-008 AI READY docs/runtime | drift confirmado; runtime baseline é referência histórica |

T054 deve agora classificar formalmente cada drift em: corrigido pela arquitetura futura, compat temporário, descartado, dependente de preflight/profiling ou blocker.

## 8. Decisões ainda proibidas

- taxonomy versus postmeta final;
- schema/tabela de Search/Analytics/Queue;
- chunks/vector/embeddings;
- Foundry/provider;
- migration de dados;
- aliases/shortcodes sem preflight;
- runtime novo.

## 9. Sequência restante

`T054 -> T056 -> T057 -> T055 -> T058 -> T059 -> T090–T097`.

A ordem impede que schema/infra seja escolhido antes de sabermos quais contratos quebrados precisam de compatibilidade e quais primitives WordPress já atendem.
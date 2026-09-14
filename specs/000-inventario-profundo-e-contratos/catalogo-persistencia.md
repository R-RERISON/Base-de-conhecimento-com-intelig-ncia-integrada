# Catálogo de Persistência — SPEC-000

> Documento incremental. Os três blocos de referência estão inventariados. **T052 definiu ownership lógico**, registrado em `mapa-ownership-dados.md`. Este catálogo ainda **não** fecha schema/taxonomias nem está consolidado por conceito; essa consolidação é T050.

## 1. Advanced Search Intelligence 4.6.8

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

### Tabelas próprias

| Chave lógica | Papel | Natureza | Owner futuro lógico após T052 | Futuro preliminar |
|---|---|---|---|---|
| `search_index` | documento lexical derivado por post | derivado/reconstruível | Search Indexing | MANTER comportamento; REDESENHAR |
| `search_items` | projection de trechos | derivado/reconstruível | Search Indexing | MANTER comportamento; REDESENHAR |
| `term_bindings` | curadoria termo→alvo | domínio/manual | Search Knowledge | MANTER; storage T056/T057 |
| `vocabulary` | canonical/variants | domínio/manual | Search Knowledge | MANTER; storage T056/T057 |
| `relevance_rules` | promote/demote | domínio/manual | Search Knowledge | MANTER; storage T056/T057 |
| `index_queue` | jobs assíncronos duráveis | operacional | Search Operations | semântica só se workload justificar |
| `audit_log` | trilha operacional/mutação | governança | Governança/Audit | MANTER mínimo; REDESENHAR |
| `search_events` | execução de consultas | telemetria | Analytics / Search Intelligence | facts mínimos; privacy/retention |
| `search_interactions` | clicks/ações correlacionadas | telemetria | Analytics / Search Intelligence | MANTER comportamento se necessário |
| `golden_queries` | expectativas de ranking | QA/governança | Search Quality | MANTER; storage T056/T057 |
| `quality_daily` | agregados diários | projection | Analytics projection | DESCARTAR inicialmente |
| `migrations` | registry/checkpoints ASI | operacional/histórico | Operations/Migration | DESCARTAR histórico; mecanismo futuro mínimo |

### Options/transients

- `asi4_settings` e opções de activation/bootstrap/Golden/migrations/orchestration;
- conjunto `asi4_word_cloud_*` para settings/snapshot/state/history/retention;
- transients de busca, schema metadata, rate-limit, evidência temporária e módulos auxiliares.

**Direção:** preservar semântica de settings/cache quando necessária, não a quantidade de stores. Estados operacionais grandes ou de alta mutação devem passar pelo princípio de negação.

### Natureza dos dados

- **canônicos/manual:** vocabulary, bindings, relevance rules, Golden expectations, settings/decisões humanas;
- **derivados:** post/item index, caches, rollups;
- **observacionais:** search events/interactions/audit;
- **operacionais:** queue/migration/orchestration.

---

## 2. Gerenciador de Resumo Executivo 0.6.0

Baseline: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`.

### Oito metas históricas e ownership lógico após T052

| Campo lógico | Meta key | Owner futuro lógico | Observação de storage |
|---|---|---|---|
| `objective` | `_bdc_es_objective` | Resumo Executivo | postmeta é baseline forte; confirmar T056 |
| `responsible_team` | `_bdc_es_responsible_team` | Classificação de Conhecimento | storage/cardinalidade T056 |
| `catalog_item` | `_bdc_es_catalog_item` | Classificação de Conhecimento | storage/cardinalidade T056 |
| `affected_service` | `_bdc_es_affected_service` | Classificação de Conhecimento | manter conceito distinto de `service` até profiling |
| `systems_involved` | `_bdc_es_systems_involved` | Classificação de Conhecimento | manter distinto de `technologies` até profiling |
| `target_audience` | `_bdc_es_target_audience` | Classificação de Conhecimento | mesmo conceito lógico de audiência KB2Ops |
| `escalation` | `_bdc_es_escalation` | Resumo Executivo | postmeta é baseline forte; confirmar T056 |
| `important` | `_bdc_es_important` | Resumo Executivo | postmeta é baseline forte; confirmar T056 |

As metas históricas são `string`, `single`, default vazio, `show_in_rest=false`, `revisions_enabled=false`, sanitizer central e `edit_post` por objeto.

`post_title` é título canônico; `_bdc_es_title` é proibida.

### Infraestrutura ausente por design

Não há tabela própria, options/transients de domínio, cron, REST, AJAX ou migration/schema próprio.

Leitura é side-effect free; vazio sanitizado remove a meta. Cobertura (`empty/partial/complete`) é derivada sob demanda.

**Conclusão:** não existe justificativa observada para tabela de Resumo Executivo.

---

## 3. KB2Ops 0.2.1

Baseline: `R-RERISON/KB2Ops-Operational-Knowledge-Engine@f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.

### 3.1 Post metadata de curadoria/uso

| Meta key | Papel | Registro explícito? | Owner futuro lógico | Futuro preliminar |
|---|---|---:|---|---|
| `_kb2ops_review_state` | estado de revisão | SIM | Revisão e Governança | MANTER semântica |
| `_kb2ops_knowledge_type` | tipo de conhecimento | SIM | Classificação de Conhecimento | primitive T056 |
| `_kb2ops_technologies` | tecnologias/contexto | SIM | Classificação de Conhecimento | distinto de systems até profiling |
| `_kb2ops_review_notes` | notas humanas | SIM | Revisão e Governança | Metadata API forte |
| `_kb2ops_reviewed_at` | data da revisão | SIM | Revisão e Governança | MANTER semântica |
| `_kb2ops_reviewed_by` | usuário revisor | SIM | Revisão e Governança | MANTER semântica |
| `_kb2ops_target_audience` | audiência | SIM | Classificação de Conhecimento | mesmo conceito do GRE target audience |
| `_kb2ops_service` | serviço | SIM | Classificação de Conhecimento | distinto de affected_service até profiling |
| `_kb2ops_keywords` | keywords | SIM | Classificação de Conhecimento | storage T056 |
| `_kb2ops_versions` | versões | SIM | Classificação de Conhecimento | storage T056 |
| `_kb2ops_include_ai` | opt-in humano para base IA | SIM | Revisão e Governança | MANTER intenção |
| `_kb2ops_review_history` | histórico bounded 50 | NÃO | Revisão e Governança | REDESENHAR/contrato explícito |
| `_kb2ops_view_count` | contador aproximado de views | NÃO | Analytics / Search Intelligence | store atual não deve continuar como fonte principal |

O Meta Contract KB2Ops é incompleto em relação ao runtime porque history/view count ficam fora do registro explícito.

### 3.2 Resumo Executivo como dependência de dados

`Summary_Bridge` lê diretamente as oito `_bdc_es_*` e não escreve. O KB2Ops replica o mapa de chaves/labels em seu próprio código.

**Valor histórico:** compatibilidade read-only quando GRE não está ativo.

**Decisão T053:** no produto unificado não existe bridge interna permanente; um Summary Store único e o domínio de Classificação fornecem os valores. Adapter só pode existir no cutover com consumidor/gate de remoção.

### 3.3 Options ativas

| Option | Papel | Owner futuro | Futuro preliminar |
|---|---|---|---|
| `kb2ops_options` | settings de Studio/Search | Core Configuration | Settings/Options API, reorganizar por domínio |
| `kb2ops_search_analytics` | até 500 queries normalizadas/count/last | Analytics / Search Intelligence | não manter como store paralelo |
| `kb2ops_runtime_version` | versão instalada | Core Lifecycle | manter se upgrade exigir |
| `kb2ops_legacy_cleanup_v1` | relatório de retirada legado | Operations/Migration | DESCARTAR no greenfield |
| `kb2ops_migration_verification` | último preflight manual | Operations/Migration | somente se coexistência exigir |
| `kb2ops_legacy_file_cleanup_warning` | aviso de cópia antiga | Operations/Migration | DESCARTAR histórico |
| `kb2ops_legacy_purge_report` | evidência de purge | Operations/Migration | princípio útil; mecanismo futuro próprio |

Options antigas apenas preservadas para migração: `kb2ops_db_version`, `kb2ops_settings`, `kb2ops_secure_secrets`.

### 3.4 Tabelas, cron e transients

O runtime novo não cria tabelas nem agenda cron. Installer/Migration apenas detectam/preservam/purgam explicitamente tabelas e hooks legados.

Nenhum transient ativo foi identificado. A ativação remove transients legados por prefixo.

**Direção:** não importar essas estruturas históricas. Activation/purge reversível é o comportamento a preservar.

### 3.5 Taxonomias nativas/custom

- nenhuma taxonomia KB2Ops própria registrada;
- categorias WordPress nativas entram no checklist;
- filtros atuais usam string postmeta para tipo/tecnologia/serviço/audiência.

**Após T052:** owner lógico desses conceitos é Classificação de Conhecimento. **Ainda não implica Taxonomy API.** T056 deve avaliar cardinalidade, reutilização, filtros, governança, migração e consultas antes de escolher primitive.

### 3.6 Dados derivados versus canônicos

**Canônicos editoriais:** `WP_Post` + Elementor.  
**Canônicos de Resumo:** objective/escalation/important.  
**Canônicos classificatórios:** team/catalog/audience/services/systems/technologies/type/keywords/versions, com forma física ainda aberta.  
**Canônicos de revisão:** review state, include_ai, notes/reviewer/time e política de histórico.  
**Derivados:** AI READY, scores, checklist, suggestions, facts/structure, excerpts, métricas de cobertura.  
**Observacionais:** queries/views históricos; futuro owner Analytics.  
**Históricos/transitórios:** review history e relatórios de migração/purge, cada um sujeito à política específica.

---

## 4. Sobreposições de persistência — estado após T052

| Conceito | GRE histórico | KB2Ops histórico | Owner lógico futuro | Estado |
|---|---|---|---|---|
| audiência | `_bdc_es_target_audience` | `_kb2ops_target_audience` | Classificação de Conhecimento | **um conceito; storage/migração T056** |
| serviço | `_bdc_es_affected_service` | `_kb2ops_service` | Classificação de Conhecimento | conceitos relacionados, não fundidos |
| sistemas/tecnologias | `_bdc_es_systems_involved` | `_kb2ops_technologies` | Classificação de Conhecimento | eixos relacionados, não fundidos |
| histórico de revisão | nenhum | `_kb2ops_review_history` | Revisão e Governança | mecanismo final aberto |
| inclusão IA | nenhum | `_kb2ops_include_ai` | Revisão e Governança | decisão humana útil |
| analytics | tabelas ASI | option/meta KB2Ops | Analytics / Search Intelligence | store mínimo será T057 |

Nenhuma migração/conversão deve ser feita antes de T050/T056/T057.

## 5. WordPress-first: evidência combinada

Os três projetos indicam que:

- posts/Elementor permanecem fonte editorial;
- metadata funciona bem para atributos locais por post;
- taxonomy deve ser considerada para classificação compartilhada antes de tabela própria;
- Options API atende configuração/evidência pequena;
- tabelas próprias são justificáveis sobretudo para projections de busca, telemetria relacional, Golden e jobs duráveis — nunca por default;
- dados derivados devem ser reconstruíveis sempre que possível;
- uninstall/purge deve ser não destrutivo por default.

## 6. Próxima consolidação — T050

T050 deve reescrever este catálogo de uma visão predominantemente **por plugin histórico** para uma visão **por conceito/owner futuro**, preservando a rastreabilidade das chaves antigas.

T050 ainda precisa decidir/documentar, sem implementar:

- canonical/derived/observational/operational por conceito;
- leitores/writers futuros;
- compatibilidade/dual-read temporário;
- o que pode ser eliminado antes de T056;
- quais decisões de primitive precisam permanecer explicitamente abertas.

Não fazem parte de T050: criar taxonomy/tabela/schema/runtime.

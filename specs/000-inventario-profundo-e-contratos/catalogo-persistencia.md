# Catálogo de Persistência — SPEC-000

> Documento incremental. Os três blocos de referência — ASI, Gerenciador de Resumo Executivo e KB2Ops — estão inventariados. Este catálogo ainda **não** fecha schema/taxonomias: essas decisões pertencem ao cruzamento T050–T059.

## 1. Advanced Search Intelligence 4.6.8

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

### Tabelas próprias

| Chave lógica | Papel | Natureza | Ownership observado | Futuro preliminar |
|---|---|---|---|---|
| `search_index` | documento lexical derivado por post | derivado/reconstruível | Search/Indexing | MANTER comportamento; REDESENHAR |
| `search_items` | projection de trechos | derivado/reconstruível | Item Knowledge | MANTER comportamento; REDESENHAR |
| `term_bindings` | curadoria termo→alvo | domínio/manual | Search & Knowledge | MANTER; storage AINDA NÃO SABEMOS |
| `vocabulary` | canonical/variants | domínio/manual | Search & Knowledge | MANTER; storage AINDA NÃO SABEMOS |
| `relevance_rules` | promote/demote | domínio/manual | Search & Knowledge | MANTER; storage AINDA NÃO SABEMOS |
| `index_queue` | jobs assíncronos duráveis | operacional | Indexing | MANTER semântica se justificada; REDESENHAR |
| `audit_log` | trilha operacional/mutação | governança | transversal | MANTER mínimo; REDESENHAR |
| `search_events` | execução de consultas | telemetria | Analytics | MANTER fatos mínimos; REDESENHAR privacy/retention |
| `search_interactions` | clicks/ações correlacionadas | telemetria | Analytics | MANTER comportamento; REDESENHAR |
| `golden_queries` | expectativas de ranking | QA/governança | Search Quality | MANTER; storage AINDA NÃO SABEMOS |
| `quality_daily` | agregados diários | projection | Analytics | DESCARTAR inicialmente; derivar de eventos |
| `migrations` | registry/checkpoints ASI | operacional/histórico | Migration | DESCARTAR histórico; mecanismo futuro próprio mínimo |

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

### Oito metas canônicas

| Campo lógico | Meta key | Observação | Futuro preliminar |
|---|---|---|---|
| `objective` | `_bdc_es_objective` | texto executivo | MANTER; postmeta é baseline forte |
| `responsible_team` | `_bdc_es_responsible_team` | ownership/classificação | MANTER valor; storage cruzar |
| `catalog_item` | `_bdc_es_catalog_item` | classificação/catalogação | MANTER valor; storage cruzar |
| `affected_service` | `_bdc_es_affected_service` | serviço afetado | MANTER valor; storage cruzar |
| `systems_involved` | `_bdc_es_systems_involved` | sistemas relacionados | MANTER valor; storage cruzar |
| `target_audience` | `_bdc_es_target_audience` | público-alvo | MANTER valor; storage cruzar |
| `escalation` | `_bdc_es_escalation` | orientação de escalonamento | MANTER semântica |
| `important` | `_bdc_es_important` | informação crítica | MANTER semântica |

Todas: `string`, `single`, default vazio, `show_in_rest=false`, `revisions_enabled=false`, sanitizer central e `edit_post` por objeto.

`post_title` é o título canônico; `_bdc_es_title` é proibida.

### Infraestrutura ausente por design

Não há:

- tabela própria;
- options de domínio;
- transients próprios;
- cron;
- REST;
- AJAX;
- migration/schema próprio.

Leitura é side-effect free; vazio sanitizado remove a meta. Cobertura (`empty/partial/complete`) é derivada sob demanda.

**Conclusão:** não existe justificativa observada para tabela de Resumo Executivo.

---

## 3. KB2Ops 0.2.1

Baseline: `R-RERISON/KB2Ops-Operational-Knowledge-Engine@f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.

### 3.1 Post metadata de curadoria/uso

| Meta key | Papel | Registro explícito? | Futuro preliminar |
|---|---|---:|---|
| `_kb2ops_review_state` | estado de revisão | SIM | MANTER semântica |
| `_kb2ops_knowledge_type` | tipo de conhecimento | SIM | MANTER; candidato a taxonomia |
| `_kb2ops_technologies` | tecnologias/contexto | SIM | REDESENHAR storage |
| `_kb2ops_review_notes` | notas humanas | SIM | MANTER via WP metadata |
| `_kb2ops_reviewed_at` | data da revisão | SIM | MANTER semântica |
| `_kb2ops_reviewed_by` | usuário revisor | SIM | MANTER semântica |
| `_kb2ops_target_audience` | audiência | SIM | sobreposição GRE; ownership aberto |
| `_kb2ops_service` | serviço | SIM | sobreposição GRE; ownership aberto |
| `_kb2ops_keywords` | keywords | SIM | MANTER valor; storage aberto |
| `_kb2ops_versions` | versões | SIM | MANTER valor; storage aberto |
| `_kb2ops_include_ai` | opt-in humano para base IA | SIM | MANTER intenção |
| `_kb2ops_review_history` | histórico bounded 50 | NÃO | REDESENHAR/registrar contrato |
| `_kb2ops_view_count` | contador aproximado de views | NÃO | REDESENHAR analytics |

O Meta Contract KB2Ops é incompleto em relação ao runtime porque history/view count ficam fora do registro explícito.

### 3.2 Resumo Executivo como dependência de dados

`Summary_Bridge` lê diretamente as oito `_bdc_es_*` e não escreve. O KB2Ops replica o mapa de chaves/labels em seu próprio código.

**Valor:** compatibilidade read-only quando GRE não está ativo.

**Dívida:** contrato duplicado pode divergir. No produto unificado, usar um único store/contract interno e manter adapter apenas para coexistência/migração.

### 3.3 Options ativas

| Option | Papel | Futuro preliminar |
|---|---|---|
| `kb2ops_options` | settings de Studio/Search | MANTER via Settings/Options API |
| `kb2ops_search_analytics` | até 500 queries normalizadas/count/last | REDESENHAR privacy/concurrency/retention |
| `kb2ops_runtime_version` | versão instalada | MANTER se upgrade exigir |
| `kb2ops_legacy_cleanup_v1` | relatório de retirada legado | DESCARTAR no greenfield |
| `kb2ops_migration_verification` | último preflight manual | mecanismo futuro só se coexistência exigir |
| `kb2ops_legacy_file_cleanup_warning` | aviso de cópia antiga | DESCARTAR histórico |
| `kb2ops_legacy_purge_report` | evidência de purge | princípio útil; implementação futura própria |

Options antigas apenas preservadas para migração: `kb2ops_db_version`, `kb2ops_settings`, `kb2ops_secure_secrets`.

### 3.4 Tabelas, cron e transients

O runtime novo não cria tabelas nem agenda cron. Installer/Migration apenas detectam/preservam/purgam explicitamente tabelas e hooks **legados**.

Nenhum transient ativo foi identificado. A ativação remove transients legados por prefixo.

**Direção:** não importar essas estruturas históricas. Activation/purge reversível é o comportamento a preservar.

### 3.5 Taxonomias nativas/custom

- nenhuma taxonomia KB2Ops própria registrada;
- categorias WordPress nativas entram no checklist;
- filtros atuais usam string postmeta para tipo/tecnologia/serviço/audiência.

O uso recorrente desses campos em filtro, agrupamento e relatórios torna `knowledge_type`, tecnologias, serviço e audiência candidatos fortes a taxonomias. A decisão será fechada em T052/T056, não aqui.

### 3.6 Dados derivados versus canônicos

**Canônicos/editoriais:** `WP_Post`, Elementor e oito GRE existentes.

**Canônicos de curadoria:** review state, decisão include_ai, notas/reviewer e classificações humanas.

**Derivados:** AI READY, scores, checklist, suggestions, facts/structure, excerpts, métricas de cobertura.

**Observacionais:** queries agregadas e view count.

**Históricos:** review history bounded e relatórios de migração/purge.

---

## 4. Sobreposições de persistência a resolver no cruzamento

| Conceito | GRE | KB2Ops | Problema |
|---|---|---|---|
| audiência | `_bdc_es_target_audience` | `_kb2ops_target_audience` | duplicidade de ownership |
| serviço | `_bdc_es_affected_service` | `_kb2ops_service` | semântica próxima, não necessariamente idêntica |
| sistemas/tecnologias | `_bdc_es_systems_involved` | `_kb2ops_technologies` | sobreposição parcial |
| histórico de revisão | nenhum | `_kb2ops_review_history` | política final aberta |
| inclusão IA | nenhum | `_kb2ops_include_ai` | decisão de governança útil |
| analytics | tabelas ASI | option/meta KB2Ops | ASI é mais robusto; storage final aberto |

Nenhuma migração/conversão deve ser feita antes de definir semanticamente cada conceito.

## 5. WordPress-first: evidência combinada

Os três projetos indicam que:

- posts/Elementor permanecem fonte editorial;
- metadata funciona bem para atributos locais por post;
- categories/taxonomies devem ser consideradas antes de criar tabelas para classificação compartilhada;
- Options API atende configuração/evidência pequena;
- tabelas próprias são justificáveis sobretudo para projections de busca, telemetria relacional, Golden e jobs duráveis — nunca por default;
- dados derivados devem ser reconstruíveis sempre que possível;
- uninstall/purge deve ser não destrutivo por default.

## 6. Decisões explicitamente adiadas para T050–T059

- mapa final de ownership GRE ↔ KB2Ops;
- taxonomia versus postmeta campo a campo;
- storage de vocabulary/bindings/rules/Golden;
- schema do índice lexical e de trechos;
- necessidade final de queue;
- histórico/revisions;
- retenção/minimização de query text;
- coexistência/migração `_kb2ops_*`, `_bdc_es_*`, `asi_*`;
- chunks/MariaDB Vector/embeddings.

O inventário agora fornece evidência suficiente para iniciar o cruzamento, mas nenhuma dessas decisões é autorizada por este catálogo isoladamente.
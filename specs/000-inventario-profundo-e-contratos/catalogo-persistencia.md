# Catálogo de Persistência — SPEC-000

> Documento incremental. Nesta atualização, os blocos ASI e Gerenciador de Resumo Executivo (GRE) estão concluídos. KB2Ops ainda precisa ser incorporado antes de qualquer decisão final de schema/taxonomia.

## Advanced Search Intelligence 4.6.8

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

### Options canônicas observadas

- `asi4_settings`: configuração principal; autoload desabilitado.
- `asi4_activation_pending`: marcador de preparação.
- `asi4_bootstrap_failure`: diagnóstico de falha de bootstrap.
- `asi4_golden_last_run`: evidência cacheada da última execução Golden.
- opções de estado de Migration/Reconciler/PostInstallOrchestrator.
- opções `asi4_word_cloud_*`: settings, allowlist, blocklist, snapshot, timestamps, quality report, generation state, history, retention e anonymization.
- option de versão do contrato de anchors/itens.

**Leitura futura:** settings compactos e evidência pequena combinam com Options API; estados operacionais grandes ou de alta mutação devem ser reavaliados pelo princípio de negação.

### Transients observados

- cache de busca por query/categoria/versões do algoritmo;
- cache de metadados de tabela/schema;
- rate-limit buckets;
- revalidação temporária de gaps;
- evidência curta de antes/depois de Apply da curadoria;
- caches de auditoria/estrutura e módulos auxiliares.

**Decisão preliminar:** MANTER semântica de cache efêmero. REDESENHAR invalidação por version token/namespace; evitar DELETE por prefixo em `wp_options` quando houver alternativa mais segura.

### WordPress Core usado como persistência/domínio

- Posts/pages continuam sendo conteúdo canônico.
- Categorias/tags são sinais nativos de busca.
- Users/roles/capabilities são usados para autorização/contexto.
- Post metadata participa indiretamente de integrações, mas o ASI não deve ser tomado como referência editorial.

### Dados derivados versus canônicos

**Canônicos/manual:** vocabulary, bindings manuais, relevance rules, Golden expectations, settings e decisões humanas.

**Derivados/reconstruíveis:** search index, item index, caches, parte dos relatórios/rollups.

**Observacionais:** search events/interactions/audit. Não são reconstruíveis e exigem política de retenção/privacidade.

**Operacionais:** queue, migration/orchestrator state. Devem existir apenas se necessários ao lifecycle real.

---

## Gerenciador de Resumo Executivo 0.6.0

Baseline: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`.

### Persistência canônica observada

O GRE não cria tabela própria. O domínio central é persistido em oito post metas privadas, registradas pela Metadata API:

| Campo lógico | Meta key | Observação | Futuro preliminar |
|---|---|---|---|
| `objective` | `_bdc_es_objective` | texto executivo do post | MANTER semântica; post meta é baseline forte |
| `responsible_team` | `_bdc_es_responsible_team` | ownership/classificação | MANTER valor; storage final cruzar com taxonomias KB2Ops |
| `catalog_item` | `_bdc_es_catalog_item` | classificação/catalogação | MANTER valor; storage final cruzar |
| `affected_service` | `_bdc_es_affected_service` | serviço afetado | MANTER valor; storage final cruzar |
| `systems_involved` | `_bdc_es_systems_involved` | sistemas relacionados | MANTER valor; storage final cruzar |
| `target_audience` | `_bdc_es_target_audience` | público-alvo | MANTER valor; storage final cruzar |
| `escalation` | `_bdc_es_escalation` | orientação de escalonamento | MANTER semântica; storage final cruzar |
| `important` | `_bdc_es_important` | informação crítica | MANTER semântica; post meta é baseline forte |

### Contrato de registro

Todas as oito metas usam:

- `type = string`;
- `single = true`;
- `default = ''`;
- `show_in_rest = false`;
- `revisions_enabled = false`;
- sanitizer centralizado;
- auth por `edit_post` do objeto.

O título **não** é meta: `post_title` é a fonte canônica. `_bdc_es_title` é explicitamente proibida pela suíte arquitetural.

### Sem tabela própria

Busca por runtime confirmou ausência de:

- `$wpdb` no plugin;
- `CREATE TABLE` / `dbDelta`;
- migrations de banco;
- queue/audit table própria.

**Decisão:** não existe evidência para criar tabela de Resumo Executivo no novo produto. WordPress-first prevalece.

### Options

Não foram encontradas options próprias de domínio do GRE. O único `get_option()` relevante no runtime lido consulta a option nativa `date_format` para apresentação de datas no Coverage Dashboard.

### Transients/cache

Não foram encontrados transients próprios no runtime GRE.

### Cron

Não foram encontrados WP-Cron ou schedules próprios.

### Semântica de vazio

`Summary_Store::read()` projeta ausência de meta como `''` sem criar linha. `Summary_Store::update()` remove fisicamente a meta quando o valor sanitizado é vazio.

**Decisão:** MANTER esse comportamento para evitar linhas vazias e writes colaterais em leitura.

### Revisions/histórico

As metas atuais registram `revisions_enabled = false`. Isso descreve o baseline, mas **não resolve** a decisão do novo produto sobre histórico de revisão/curadoria. Esse tema permanece aberto para o cruzamento e SPEC futura.

### Uninstall

Não há `uninstall.php` no baseline. Logo não existe rotina própria que remova as metas na desinstalação. Isso reduz risco de deleção automática, mas a política formal de retenção/uninstall do plugin unificado ainda deve ser definida.

### Dados canônicos versus derivados

**Canônicos:** `post_title` e os oito valores do Resumo Executivo.

**Derivados:** estado `empty/partial/complete`, percentuais de cobertura e lista de prioridades. O GRE calcula esses dados sob demanda; não os persiste.

**Implicação:** o Coverage Dashboard não justifica uma tabela agregada por si só. Só materializar rollup se benchmark provar necessidade.

---

## Decisões explicitamente adiadas

A SPEC-000 ainda não decide:

- quais campos GRE classificatórios devem permanecer post meta versus virar taxonomia no plugin unificado;
- se vocabulary deve ser tabela, taxonomia ou outro mecanismo WP;
- se bindings/rules devem ser post meta, tabela ou storage híbrido;
- se Golden Queries devem ficar em tabela própria;
- schema final do índice lexical;
- tabela de chunks;
- MariaDB Vector;
- persistência de embeddings;
- retenção definitiva de queries/telemetria;
- mecanismo final de histórico de revisão;
- compatibilidade final com `_kb2ops_*`, `_bdc_es_*` e stores `asi_*` existentes;
- necessidade final de fila própria.

Essas decisões só serão fechadas depois do inventário KB2Ops e do cruzamento T050–T059.
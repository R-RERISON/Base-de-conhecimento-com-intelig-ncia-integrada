# Catálogo de Persistência — SPEC-000

> Documento incremental. Nesta atualização, o bloco ASI foi concluído. KB2Ops e Gerenciador de Resumo Executivo serão adicionados nos respectivos blocos antes de qualquer decisão final de schema.

## Advanced Search Intelligence 4.6.8

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

### Tabelas próprias

| Chave lógica | Papel | Natureza | Ownership observado | Futuro preliminar |
|---|---|---|---|---|
| `search_index` | projection lexical por post | derivado/reconstruível | Search/Indexing | MANTER comportamento; REDESENHAR |
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

## Decisões explicitamente adiadas

A SPEC-000 ainda não decide:

- se vocabulary deve ser tabela, taxonomia ou outro mecanismo WP;
- se bindings/rules devem ser post meta, tabela ou storage híbrido;
- se Golden Queries devem ficar em tabela própria;
- schema final do índice lexical;
- tabela de chunks;
- MariaDB Vector;
- persistência de embeddings;
- retenção definitiva de queries/telemetria;
- compatibilidade final com stores `asi_*` existentes.

Essas decisões só serão fechadas depois do cruzamento com KB2Ops, Gerenciador de Resumo Executivo, WordPress-first e volumetria.
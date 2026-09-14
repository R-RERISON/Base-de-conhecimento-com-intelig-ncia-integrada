# Infraestrutura Própria Mínima — SPEC-000 — T057

> Estado: **T057 concluída documentalmente**.  
> Baseline de entrada: `main @ d8c5b068d2c8969d86b2e53f6c225442bd13e3bf`.  
> Baselines de referência: ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`, KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este documento identifica **a menor infraestrutura própria justificável após T056**. Ele não cria tabela, schema, migration, classe, queue, endpoint ou runtime. Nenhum nome físico abaixo está congelado. T097 continua sendo o único gate que pode autorizar SPEC-001.

## 1. Princípio aplicado

T057 não pergunta “quais tabelas do ASI precisamos?”. Pergunta:

> **Qual comportamento comprovado não pode ser atendido adequadamente pelas primitives WordPress já avaliadas em T056, e qual é a menor extensão capaz de atendê-lo?**

Para cada candidato foram comparadas quatro alternativas:

1. **NÃO CONSTRUIR**;
2. **WordPress Core / processamento síncrono bounded**;
3. **WordPress Core + extensão mínima**;
4. **infraestrutura própria persistente**.

A opção mais simples vence enquanto preservar confiabilidade, segurança, performance, regressão e produto.

## 2. Evidência disponível e limites

### 2.1 Corpus conhecido

A baseline KB2Ops documenta corpus da ordem de **~700 posts**. Isso é evidência do ambiente atual, não contrato de escala futura.

Ainda não há evidência versionada no novo projeto para:

- número total de itens/trechos extraídos por post;
- QPS/pico de Search;
- p50/p95/p99 de Search futura;
- custo médio/p95 do Content Extractor futuro;
- taxa de alteração de posts;
- duração do rebuild completo futuro;
- taxa de eventos de Analytics;
- SLA de freshness da projection.

**Regra:** T057 não inventa números para preencher lacunas. Esses NFRs serão medidos na SPEC/runtime que implementar a capacidade.

### 2.2 Evidência ASI que pode ser reutilizada como comportamento

ASI prova que o Search maduro precisou de:

- documento lexical normalizado por post;
- FULLTEXT com fallback bounded;
- ranking por sinais explícitos;
- itens/trechos pesquisáveis com identidade estável;
- hashes/generation para reconciliação;
- índices orientados a consultas reais;
- Golden Queries;
- estado `ready/partial/degraded`;
- fila com lease/retry/dead apenas porque seu workload foi desenhado assíncrono;
- events/interactions/outcomes porque sua Search Intelligence requeria correlação temporal.

Isso comprova **necessidades semânticas**, não comprova que as 12 tabelas ASI devam existir no greenfield.

### 2.3 Evidência WordPress

T056 já demonstrou que Core cobre domínio, configuração, segurança, governança, cache e scheduling como trigger. A documentação oficial do WordPress recomenda Post Meta quando prática, mas admite tabela própria quando dados crescentes/requisitos do plugin justificam armazenamento separado. Em eventual implementação, criação/upgrade deve seguir mecanismos WordPress (`$wpdb->prefix`, `dbDelta` quando adequado), nunca DDL improvisado em cada request.

## 3. Resultado executivo

| Família T056 | Resultado T057 | Infra própria aprovada agora? |
|---|---|---:|
| **F-057-01 — Search Retrieval Projection** | **APROVADA E REDUZIDA** a um único store lógico de documentos de busca derivados | **SIM — uma única tabela/projection candidata de implementação** |
| **F-057-02 — Analytics Facts** | **NÃO APROVADA NO BASELINE / POSTERGADA** | **NÃO** |
| **F-057-03 — Durable Job State** | **NÃO APROVADA NO BASELINE / POSTERGADA** | **NÃO** |

Resultado do princípio de negação:

> **12 tabelas ASI históricas → 1 família de persistência própria aprovada para desenho futuro, exclusivamente como projection reconstruível de Search.**

Não significa que uma tabela foi criada. Significa que T057 autoriza a SPEC futura de Search a desenhar **no máximo um store relacional próprio inicial** para documentos lexicais de post e item, sujeito aos gates abaixo.

---

## 4. F-057-01 — Search Retrieval Projection

### 4.1 Comportamento obrigatório

Owner lógico: **Search Indexing**.

A projection precisa suportar, sem assumir autoridade editorial:

- um documento lexical derivado por post;
- zero ou mais documentos derivados de itens/trechos do mesmo post;
- identidade estável de documento/item;
- texto produzido pelo Content Extractor canônico;
- sinais lexicais necessários ao ranking explicável;
- hash/version/generation suficientes para `NO_CHANGE`, rebuild e reconciliação;
- lookup por post para invalidação/rebuild;
- consulta lexical transversal bounded;
- FULLTEXT quando disponível, com fallback lexical funcional e bounded quando indisponível;
- estado de freshness/diagnóstico suficiente para não apresentar projection silenciosamente como atual quando não estiver.

### 4.2 Por que WordPress Core sozinho não atende a paridade

`WP_Query`/native search continua útil como **fallback**, mas não entrega por si só:

1. documento textual canônico Elementor-aware, porque o conteúdo pesquisável não é apenas `post_content`;
2. documentos separados por item/trecho com identidade estável;
3. FULLTEXT dedicado ao documento derivado com campos/pesos próprios;
4. ranking composto e explicável sem reconstruir uma engine sobre consultas ad hoc/meta LIKE;
5. reconciliação eficiente de vários itens derivados quando a fonte muda.

Postmeta também não é adequado para fazer busca lexical transversal por grandes blobs/itens. O caminho KB2Ops de `meta_query LIKE`, inclusive sobre `_elementor_data`, já está classificado como provisório e não escalável.

Um CPT/WP_Post interno por item foi considerado em T056, mas faria a projection disputar o store editorial genérico, multiplicaria objetos `wp_posts` para dados reconstruíveis e ainda não ofereceria os índices lexicais dedicados exigidos sem alterar a query/índices do Core. Para o workload de retrieval, a separação física mínima é mais simples e mais honesta.

### 4.3 Redução do desenho ASI

**Não aprovar duas tabelas `search_index` + `search_items` por herança.**

Posts e itens são ambos **documentos de retrieval**. O baseline futuro deve tentar uma única relação/store com um discriminador de tipo.

Modelo conceitual mínimo — nomes finais permanecem abertos:

- identificador técnico;
- `document_key` estável e único;
- `document_kind`: `post | item`;
- `post_id` canônico de origem;
- `item_key` somente quando `document_kind=item`;
- ordem do item quando aplicável;
- título normalizado;
- texto lexical derivado;
- anchor/anchor_state somente se B-005 aprovar deep-link;
- `source_hash`/`content_hash`;
- versão/generation do extractor/indexer quando necessária;
- `indexed_at_gmt`;
- flag/estado derivado mínimo para reconciliação/freshness, se a implementação provar necessidade.

**Não duplicar como autoridade:** `post_status`, capabilities, classificação canônica, Summary ou conteúdo editorial. Valores podem ser incorporados ao texto/sinais derivados para retrieval, mas a exposição final deve revalidar o estado canônico WordPress.

### 4.4 Índices mínimos a avaliar na SPEC de implementação

A implementação futura deve justificar cada índice pela query que o usa. Baseline conceitual:

- unique de identidade do documento;
- índice por `post_id` + tipo/estado para rebuild/reconciliação;
- índice por `post_id` + ordem quando Item Knowledge exigir ordenação;
- FULLTEXT sobre título/texto lexical quando MariaDB/MySQL alvo suportar adequadamente;
- índice de freshness somente se houver query operacional correspondente.

Não portar índices de `bindings`, `rules`, Analytics, queue ou migrations para este store.

### 4.5 FULLTEXT e fallback

A projection lexical deve:

- preferir FULLTEXT dedicado quando disponível e validado no ambiente;
- possuir fallback lexical bounded para degradação, manutenção ou ausência de FULLTEXT;
- expor estado degradado em diagnóstico;
- nunca cair para `LIKE` ilimitado em `_elementor_data`;
- nunca depender de IA/vetor para funcionar.

### 4.6 Freshness e segurança de exposição

A projection é derivada e pode ficar stale. Portanto:

- Search não usa a projection como autoridade de publicação/permissão;
- candidatos retornados pelo índice devem ser revalidados contra WordPress antes da exposição quando scope/permissão puder ter mudado;
- post despublicado/excluído não pode permanecer acessível porque uma linha do índice ainda existe;
- mismatch de hash/version deve ser diagnosticável;
- falha de rebuild não altera WP/Elementor.

B-007 permanece obrigatório **se** o modo assíncrono for adotado. Freshness básica também precisa de teste no modo síncrono/manual.

### 4.7 Gates antes de implementação produtiva

**B-001 continua BLOCKER:** não faz sentido otimizar um índice sobre extração incompleta.

Antes de considerar F-057-01 pronta para produção:

1. Content Extractor aprovado em corpus Elementor representativo;
2. item identity/reconciliation testados;
3. Golden Queries ativas e versionadas;
4. benchmark com corpus representativo medindo wall time, queries DB, memória e p95 de Search/rebuild;
5. FULLTEXT/fallback testados no MariaDB/MySQL do ambiente real;
6. scope/detail revalidation comprovados;
7. rebuild idempotente e projection reconstruível;
8. stale/degraded observável;
9. nenhuma escrita editorial pelo indexer.

### 4.8 Rollback/degradação

Se a projection própria falhar:

- fonte editorial permanece intacta;
- Search deve poder entrar em estado `degraded`;
- native search/um fallback lexical bounded pode preservar descoberta básica, mesmo sem paridade completa;
- rebuild recria a projection a partir dos canônicos;
- remover/recriar projection não deve exigir restaurar dados editoriais de backup.

### Decisão F-057-01

**APROVADA COMO INFRAESTRUTURA PRÓPRIA MÍNIMA**, reduzida a **um único store lógico/tabela de documentos lexicais derivados** no baseline de implementação.

Aprovação é de arquitetura documental, não de DDL.

---

## 5. F-057-02 — Analytics Facts

### 5.1 Valor de produto comprovado

ASI demonstra valor em perguntas como:

- volume de buscas;
- zero-result rate;
- termos frequentes/emergentes;
- latência/cache/ranking source;
- engagement/outcome;
- lacunas atuais versus histórico resolvido.

KB2Ops demonstra que uma option de termos é simples, porém inadequada como store concorrente/temporal definitivo.

### 5.2 Por que não aprovar agora

B-004 permanece aberto. O problema principal não é “qual tabela usar”, mas:

- finalidade da coleta;
- se query text pode ser persistido;
- minimização;
- acesso;
- retenção;
- necessidade de identidade/journey;
- necessidade real de interactions/outcomes;
- volume/taxa de escrita.

A baseline ASI tinha política própria e até exigia benchmark sintético de 100k buscas/200k interações para aceite de performance. Isso é evidência de complexidade e custo operacional, **não um requisito automático do novo produto**.

Sem política e workload do novo produto, criar events/interactions/outcomes agora seria arquitetura especulativa.

### 5.3 Baseline até decisão futura

- **não criar** tabela de events;
- **não criar** tabela de interactions;
- **não criar** tabela de outcomes/rollup;
- **não migrar** telemetria histórica automaticamente;
- **não persistir query text por default** enquanto B-004 estiver aberto;
- métricas operacionais agregadas estritamente necessárias podem usar Options/Site Health bounded, sem construir Search Intelligence completa.

Se Analytics detalhado for aprovado depois, reabrir F-057-02 como **uma única família de fatos**, evitando stores paralelos por origem histórica. O desenho deverá começar pelas perguntas e retenção, não pelo schema ASI.

### Decisão F-057-02

**NÃO APROVADA NO BASELINE / POSTERGADA.**

Não bloqueia o Search lexical nem a SPEC-001 se o slice inicial não depender de Analytics detalhado. B-004 continua ativo para qualquer slice que o habilite.

---

## 6. F-057-03 — Durable Job State

### 6.1 O que ASI prova

A fila ASI demonstra corretamente a semântica necessária quando existe worker assíncrono durável:

- claim atômico;
- lease;
- retry/backoff;
- attempts budget;
- dead state;
- recuperação de processing inválido/expirado;
- reabertura explícita;
- worker bounded;
- WP-Cron como trigger/fallback, não como durable store.

Esses contratos devem ser preservados **se uma fila nascer**.

### 6.2 O que ainda não foi provado no novo produto

Não existe benchmark do futuro Content Extractor + projection única demonstrando que:

- indexar um post não cabe com segurança em operação síncrona/bounded;
- rebuild manual/batched não atende o corpus atual;
- freshness exige SLA que dependa de background worker;
- concorrência real exige lease;
- retry automático é requisito do primeiro slice.

Com corpus atual documentado na ordem de ~700 posts, criar imediatamente uma fila durável seria uma aposta sem medição.

### 6.3 Baseline até benchmark

Primeira opção de implementação a testar:

- invalidação barata após mudança confirmada;
- indexação de um único post síncrona quando orçamento de request permitir;
- rebuild explícito e bounded por lotes;
- WP-Cron apenas como trigger opcional de manutenção/retry leve, se necessário;
- hashes/freshness na própria projection para descobrir `NO_CHANGE`/stale;
- Operations UI somente para ação humana real.

**Proibição:** não improvisar fila em options/transients. Se a semântica de lease/retry/dead for necessária, então uma durable queue própria deve ser redesenhada explicitamente.

### 6.4 Gate de reabertura

F-057-03 só volta a `APROVADA` quando uma SPEC/runtime medir e registrar pelo menos:

- custo p50/p95 de indexação por post;
- custo de posts pesados/custom widgets;
- duração do rebuild representativo;
- timeout/memory budget;
- taxa de alteração e backlog possível;
- SLA de freshness;
- necessidade de concorrência/lease;
- B-007: stale, diagnóstico, retry e recuperação.

### Decisão F-057-03

**NÃO APROVADA NO BASELINE / POSTERGADA.**

Não existe queue table autorizada nesta fase.

---

## 7. Infraestrutura explicitamente proibida após T057

T057 **não autoriza**:

- replicar as 12 tabelas ASI;
- tabelas separadas de post index e item index sem benchmark/prova que o store único falha;
- tabela de vocabulary;
- tabela de bindings;
- tabela de relevance rules;
- tabela de Golden Queries;
- tabela de audit genérico;
- tabela de analytics/events/interactions/outcomes;
- `quality_daily`;
- tabela de queue;
- registry permanente de migrations;
- tabela para Summary/Classificação/Review;
- embeddings/vectors/chunks — pertencem a T058/Specs futuras.

## 8. Relação com blockers T054

| Blocker | Efeito após T057 |
|---|---|
| B-001 extractor | continua BLOCKER para implementar F-057-01 Search final |
| B-002 classificação | não altera infraestrutura própria; continua profiling/cutover WP |
| B-003 consumidores | não altera store Search; continua gate de aliases/cutover |
| B-004 Analytics | mantém F-057-02 não aprovada |
| B-005 anchors | bloqueia deep-link completo; não impede documento lexical/item sem link público |
| B-006 write composto | não justifica tabela; continua no domínio Summary/Classificação |
| B-007 stale/async | obrigatório somente se async/queue for reaberto; stale básico de Search continua observável |

## 9. Segurança e privacidade

### Search projection

- não conter secrets;
- não se tornar bypass de capability/status;
- conteúdo derivado só pode ser exposto quando o post canônico puder ser exposto no scope da Search;
- queries SQL futuras devem usar `$wpdb->prepare`/APIs seguras;
- diagnóstico/export não inclui conteúdo sensível além do necessário;
- rebuild/purge exige capability operacional + nonce + intenção explícita.

### Analytics

Nenhuma persistência detalhada é aprovada até B-004. Isso é decisão de minimização por default, não falta de funcionalidade acidental.

## 10. Custos e operação

T057 não introduz serviço externo nem custo de IA.

A única infraestrutura própria aprovada é local ao banco WordPress/MariaDB e reconstruível. Mesmo assim, sua implementação futura deve medir:

- crescimento de linhas por post/item;
- tamanho do FULLTEXT;
- tempo de rebuild;
- carga de query;
- impacto em backup/restore;
- custo de migration/schema upgrade;
- efeito de ausência de persistent object cache.

Nenhum rebuild massivo ocorre automaticamente em activation.

## 11. Contratos que T055 deve proteger

T055 deve consolidar regressões para, no mínimo:

### Search projection

- zero write em `post_content`/`_elementor_data`;
- Content Extractor único;
- mesma fonte/version gera projection determinística;
- um post + N itens podem coexistir no mesmo store lógico;
- `document_key`/item identity estáveis;
- rebuild idempotente;
- remoção/despublicação não vaza resultado stale;
- FULLTEXT e fallback bounded;
- ranking explicável;
- Golden Queries;
- scope/detail recheck;
- estado degraded/stale explícito;
- falha da projection não altera canônico.

### Analytics

- baseline sem Analytics detalhado não registra query text silenciosamente;
- Search funciona mesmo sem Analytics.

### Queue

- baseline não depende de queue;
- se fila futura surgir, lease/retry/dead/idempotência e B-007 tornam-se gates obrigatórios.

## 12. Revisão pelos papéis da SPEC

### Arquiteto WordPress

**APROVA COM LIMITE:** tabela própria é justificada somente para a projection lexical/item. Core continua owner de editorial, domínio, segurança e lifecycle.

### Arquiteto de Conhecimento

**APROVA:** documentos de Search são projections; Item Knowledge não ganha autoridade editorial. Search Knowledge/Golden permanecem governados em primitives WP.

### Especialista em Busca/Retrieval

**APROVA:** um único document store reduz duplicação de `search_index`/`search_items` e mantém possibilidade de FULLTEXT/ranking/item retrieval.

### Especialista em MariaDB/Dados

**APROVA COMO DESENHO CONCEITUAL:** índices/DDL finais dependem de benchmark e ambiente real. Não copiar schema ASI.

### Especialista de Segurança/Privacidade

**APROVA COM GATES:** revalidação WP impede projection stale de virar bypass; Analytics fica bloqueado por B-004.

### Especialista de Performance/Observabilidade

**APROVA COM BENCHMARK FUTURO:** ~700 posts é baseline, não NFR. Não foram inventadas metas de latência. Queue fica postergada até medição.

### Crítico de Simplicidade

**APROVA:** 12 stores históricos foram reduzidos a uma única persistência própria realmente justificada; duas famílias condicionais foram recusadas no baseline.

## 13. Critério de fechamento T057

- [x] F-057-01 foi aprovada e reduzida a um store lógico único, sem DDL/runtime;
- [x] F-057-02 foi postergada, sem criar Analytics antes de B-004;
- [x] F-057-03 foi postergada, sem criar queue sem benchmark/B-007;
- [x] nenhuma das 12 tabelas ASI renasceu por inércia;
- [x] Search projection permanece reconstruível e não-canônica;
- [x] WordPress continua autoridade de scope/status/permissão;
- [x] fallback lexical sem IA/vetor permanece obrigatório;
- [x] T055 recebeu contratos objetivos de regressão;
- [x] nenhum runtime/schema/migration foi criado.

## 14. Próximo passo autorizado

**T055 — consolidar catálogo de regressão e Golden Queries de acordo com as decisões T056/T057.**

T055 deve transformar os contratos desta matriz em testes/gates futuros, sem iniciar runtime.
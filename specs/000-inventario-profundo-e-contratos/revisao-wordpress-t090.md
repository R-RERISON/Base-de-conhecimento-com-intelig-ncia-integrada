# T090 — Revisão do Arquiteto WordPress

> Estado: **CONCLUÍDA — APROVADA COM SIMPLIFICAÇÕES/INVESTIGAÇÕES NÃO BLOQUEANTES**.  
> Baseline revisada: `main @ bc5594c48bdabd22f3575477509fb83f53ad2176` (fechamento T059).  
> Objeto principal: `matriz-paridade-futura.md`.

## 1. Objetivo

Revisar a arquitetura final T059 sob o princípio WordPress-first e tentar remover infraestrutura, storage, endpoint, JavaScript, scheduler ou abstração que o WordPress Core já atenda adequadamente.

Método obrigatório:

`necessidade -> API nativa avaliada -> limitação comprovada -> extensão mínima escolhida`.

Nenhum runtime foi criado nesta revisão.

## 2. Fontes WordPress verificadas

Snapshot consultado em 2026-09-14:

- `register_meta()` / revisions de meta: https://developer.wordpress.org/reference/functions/register_meta/
- Taxonomy API: https://developer.wordpress.org/plugins/taxonomies/
- WP-Cron: https://developer.wordpress.org/plugins/cron/
- Site Health custom tests: https://developer.wordpress.org/reference/hooks/site_status_tests/
- HTTP API `wp_remote_post()`: https://developer.wordpress.org/reference/functions/wp_remote_post/

A documentação confirma:

- meta registrada suporta tipo, sanitização, auth callback e `revisions_enabled` para post meta;
- Taxonomy API é a primitive nativa para classificação/relação reutilizável;
- WP-Cron é scheduler acionado por tráfego, não worker durável com lease/retry/dead-letter;
- Site Health aceita checks próprios diretos/assíncronos;
- HTTP externo pode usar WordPress HTTP API e retorna `WP_Error` em falha.

## 3. Resumo executivo

**Resultado T090: PASS WordPress-first.**

Não foi encontrada infraestrutura própria adicional a remover da matriz final. A única família persistente própria aprovada — Search Retrieval Projection — continua justificada por limitação objetiva do Core para o workload de busca derivada Elementor-aware por post/item.

Findings:

- **8 APROVADO**;
- **2 SIMPLIFICAR**;
- **3 INVESTIGAR**;
- **0 BLOQUEAR**.

Nenhum finding altera a ordem T091–T097 nem autoriza runtime.

## 4. Findings

### WP-001 — Fonte editorial

**Classificação:** APROVADO.

`WP_Post` + Elementor continuam owner absoluto de título/corpo/estrutura/publicação. Content Extractor permanece read-only. Nenhuma API própria substitui editor, roteamento ou CMS.

**Gate:** G-001.

### WP-002 — Summary e estado de Review

**Classificação:** APROVADO.

Metadata API é suficiente para Summary e estado local de Review. `register_meta()` suporta contrato de tipo/sanitização/auth e, quando aplicável, revisions de meta.

Tabela própria continua injustificada.

**Gate:** G-020/G-040/B-006.

### WP-003 — Classificações reutilizáveis

**Classificação:** APROVADO.

Taxonomy API permanece a primitive preferida para `audience`, `knowledge_type`, `service` e `technologies`, porque há reutilização/filtro/faceta comprovados.

B-002 continua necessário para mapping/cutover. Os quatro unknowns restantes continuam estritamente entre Metadata e Taxonomy; nenhum ganha direito a tabela própria.

**Gate:** G-030/B-002.

### WP-004 — Histórico de revisão

**Classificação:** SIMPLIFICAR.

Evitar manter **duas histórias concorrentes** sem requisito explícito:

- array bounded `_review_history` equivalente; e
- revisions de todas as metas de review.

Baseline recomendada: histórico bounded do workflow continua suficiente enquanto não houver requisito transversal/imutável. `revisions_enabled` deve ser aplicado apenas aos campos cuja recuperação de snapshot gere valor de produto real.

**Regra:** não duplicar histórico “por segurança”.

### WP-005 — Search Knowledge/Golden em `WP_Post`

**Classificação:** SIMPLIFICAR.

Quando implementados, usar primitive WordPress de entidade interna/não pública (por exemplo post type interno apropriado) em vez de criar tabela/admin CRUD próprio.

Não congelar agora slug, UI ou `show_ui`; a SPEC concreta deve escolher a menor configuração. REST permanece `false`/não exposto salvo consumidor real.

**Gate:** G-060/Golden/G-070.

### WP-006 — Search Retrieval Projection própria

**Classificação:** APROVADO.

A exceção T057 continua comprada:

- `WP_Query` pesquisa campos editoriais nativos, mas não representa sozinho texto Elementor extraído + Summary/Classificação + itens derivados;
- postmeta não é store adequado para busca lexical transversal de blobs/itens;
- transformar cada item derivado em `WP_Post` multiplicaria objetos reconstruíveis e ainda não eliminaria a necessidade de índice lexical dedicado.

Portanto uma única projection/store `post|item` permanece a **menor extensão própria justificável**.

**Condições:** B-001 + Golden + benchmark + G-050/G-060/G-070/G-120/G-130.

### WP-007 — Site Health

**Classificação:** APROVADO.

Checks técnicos devem preferir Site Health. Checks caros devem ser assíncronos quando apropriado; não criar dashboard técnico paralelo só para reproduzir health status.

**Gate:** G-130.

### WP-008 — WP-Cron

**Classificação:** APROVADO.

WP-Cron continua apenas scheduler/trigger. A documentação oficial confirma que sua execução depende de page load e pode atrasar. Isso não fornece lease, retry budget ou dead-letter.

Logo T057/T059 estão corretos em não tratá-lo como durable queue.

**Gate:** G-090/B-007.

### WP-009 — versão mínima do WordPress para meta revisions

**Classificação:** INVESTIGAR.

`revisions_enabled` foi introduzido em WordPress 6.4. A baseline KB2Ops referenciada exige WP 6.6, mas o produto greenfield ainda não congelou oficialmente sua versão mínima.

**Decisão:** não bloquear T090. T095 ou a SPEC que usar meta revisions deve fixar a versão mínima ou definir fallback sem depender do recurso.

### WP-010 — exposição pública de taxonomias

**Classificação:** INVESTIGAR.

A necessidade de classificação/faceta não implica automaticamente archive/rewrite público. Na SPEC de Classificação, começar fail-closed para exposição pública e habilitar rewrite/archive somente se existir jornada pública real.

**Risco evitado:** criar superfície/URL pública por conveniência técnica.

### WP-011 — admin-post, AJAX e REST

**Classificação:** APROVADO.

`admin-post` continua baseline de mutações administrativas server-rendered. AJAX somente quando live UX comprar o custo. REST continua negado sem consumidor formal.

Nenhum endpoint adicional é necessário pela arquitetura T059.

**Gate:** G-070/G-110.

### WP-012 — integração HTTP de IA/Foundry

**Classificação:** APROVADO com ressalva de segurança para T092.

WordPress HTTP API continua primeira opção adequada para provider seam mínimo. SDK específico não ganha direito automático de existir.

Timeout, `WP_Error`, resposta HTTP e retries bounded pertencem ao adapter. Secrets/data egress permanecem T092/G-140B.

### WP-013 — endpoint/provider configurável

**Classificação:** INVESTIGAR em T092/SPEC de IA.

Se URL de provider/deployment puder ser configurada administrativamente, validar esquema/host e avaliar `wp_safe_remote_post()`/allowlist conforme modelo de ameaça. Configuração administrativa não deve permitir SSRF arbitrário.

Não bloqueia o baseline sem IA externa.

## 5. Decisões que permanecem inalteradas

T090 **não** reabre:

- uma única Search Retrieval Projection própria;
- Analytics detalhado postergado;
- durable queue postergada;
- embeddings/semantic/rerank postergados;
- agentes postergados;
- Foundry como provider candidato e não domínio;
- File Search fora do core;
- dual-write permanente proibido;
- shortcodes/aliases sob B-003.

## 6. Ajustes obrigatórios para futuras SPECs

1. Não combinar histórico bounded + revisions de meta sem necessidade comprovada.
2. Search Knowledge/Golden devem começar em primitives internas do WordPress, não tabela própria.
3. Taxonomias sistêmicas não ganham rota/archive público automaticamente.
4. A SPEC que depender de meta revisions deve declarar versão mínima WordPress compatível.
5. Provider endpoint configurável deve passar revisão SSRF/host allowlist em T092/SPEC de IA.

## 7. Gate T090

- WordPress-first revisado: **PASS**.
- infraestrutura própria sem justificativa encontrada: **NENHUMA adicional**.
- finding bloqueante: **ZERO**.
- runtime criado: **NÃO**.
- SPEC-001 autorizada: **NÃO**; T097 continua obrigatório.

## 8. Próximo passo

**T091 — Revisão do Crítico de Simplicidade.**

T091 deve assumir que toda capacidade é culpada até provar valor e tentar remover:

- classes/camadas futuras;
- stores;
- endpoints;
- telas;
- schedulers;
- compatibilidade;
- Search features;
- IA/vetor;
- qualquer abstração antecipada.

Classificar findings como `MANTER | SIMPLIFICAR | POSTERGAR | DESCARTAR | BLOQUEAR` e garantir que a matriz T059 continue representando a menor arquitetura suficiente.

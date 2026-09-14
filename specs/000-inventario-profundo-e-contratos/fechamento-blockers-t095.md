# T095 — Fechamento de Unknowns e Blockers por Slice

> Estado: **CONCLUÍDO — SPEC-001 candidata sem blocker global aberto; B-006 fechado conceitualmente para Summary; demais blockers encaminhados ao slice correto**.  
> Baseline revisada: `main @ 64339854c5d50f358c60b7220a95041978695d91` (fechamento T094).  
> Objeto: candidato `Core mínimo + Summary narrativo`.

## 1. Objetivo

Eliminar ambiguidade antes do gate final, separando blocker real da SPEC-001 de blocker pertencente a Search, Classificação, Review, Analytics, IA ou cutover futuro.

Classificação:

- **FECHAR_AGORA** — resolver documentalmente antes de T097;
- **NÃO_APLICÁVEL_A_SPEC001** — não pertence ao escopo da SPEC-001;
- **POSTERGAR_PARA_SLICE_CORRETO** — válido, mas pertence a uma futura SPEC;
- **BLOCKER_SPEC001** — impede a SPEC-001 candidata de avançar após T097.

Nenhum runtime foi criado.

---

## 2. Resultado executivo

**Resultado T095: nenhum `BLOCKER_SPEC001` permanece aberto.**

Dos blockers B-001–B-007:

- **B-006** é o único aplicável ao candidato Summary e foi **FECHADO_AGORA conceitualmente**;
- **B-001, B-002, B-004, B-005, B-007** são `NÃO_APLICÁVEL_A_SPEC001` e seguem para seus slices;
- **B-003** não bloqueia criação/homologação da SPEC-001 porque nenhum plugin/alias será removido; permanece para cutover/compatibilidade.

T095 não autoriza runtime. Ele produz a Definition of Ready documental para T097 avaliar.

---

## 3. B-001 — Content Extractor representativo

**Classificação:** NÃO_APLICÁVEL_A_SPEC001 / POSTERGAR_PARA_SLICE_CORRETO.

SPEC-001 Summary não lê `_elementor_data` nem precisa interpretar corpo do artigo.

B-001 volta a ser MUST na SPEC de Search/RAG/qualidade que consuma conteúdo extraído.

**Não bloquear:** CRUD dos três metadados Summary.

---

## 4. B-002 — Profiling classificatório

**Classificação:** NÃO_APLICÁVEL_A_SPEC001 / POSTERGAR_PARA_CLASSIFICAÇÃO.

SPEC-001 não registra taxonomy, não migra classificação e não toca nos cinco campos classificatórios históricos do GRE.

B-002 governa SPEC-002 e cutovers posteriores.

---

## 5. B-003 — Preflight de consumidores/aliases/plugins

**Classificação:** NÃO_APLICÁVEL_A_SPEC001 para desenvolvimento/homologação; POSTERGAR para cutover/removal.

SPEC-001:

- não remove GRE/KB2Ops/ASI;
- não cria alias de shortcode;
- não desativa plugin legado automaticamente;
- não promete cutover de produção.

Portanto B-003 não é blocker para criar/testar o novo Summary.

### Coexistência

Os três valores Summary históricos já existem em postmeta GRE. T095 escolhe **preservar e reutilizar essas mesmas três chaves como storage inicial canônico da SPEC-001**, porque a semântica é equivalente e isso evita migration/dual-write:

- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

Isso é uma decisão de simplicidade e preservação, não autorização para dois writers concorrentes indefinidos.

**Gate de produção/cutover:** antes de habilitar dois fluxos de escrita simultâneos em ambiente real, a SPEC/cutover deve comprovar coexistência segura ou estabelecer single-writer. Nenhum plugin legado é alterado por SPEC-001.

---

## 6. B-004 — Analytics/query text

**Classificação:** NÃO_APLICÁVEL_A_SPEC001.

SPEC-001 não cria Analytics, query logging, journey, IP/UA/session nem telemetria detalhada.

Homologação usa evidência QA + feedback humano.

B-004 permanece intacto para futura Analytics/Search Intelligence.

---

## 7. B-005 — Deep-link/anchors

**Classificação:** NÃO_APLICÁVEL_A_SPEC001.

Nenhum item index/deep-link/anchor é criado no Summary.

Volta no slice de item-level Search.

---

## 8. B-006 — Falha multi-campo

**Classificação:** FECHAR_AGORA.

### Problema

`objective`, `escalation`, `important` são apresentados como um único save. WordPress Metadata API não fornece uma transação relacional conjunta entre três metas.

Sucesso falso ou estado parcialmente alterado sem diagnóstico é proibido.

### Estratégia final para SPEC-001

Aplicar **validação antecipada + snapshot + write mínimo + read-after-write + compensação best-effort**.

Fluxo obrigatório:

1. autorizar usuário/objeto;
2. validar nonce/método;
3. aplicar allowlist dos três campos;
4. normalizar/validar todos os inputs antes de qualquer write;
5. reler e guardar snapshot dos três valores anteriores;
6. calcular diff; campo `NO_CHANGE` não é regravado;
7. aplicar apenas updates/deletes necessários;
8. reler os três campos após writes;
9. comparar estado final com estado esperado;
10. se todos coincidirem -> **SUCCESS**;
11. se houver mismatch -> **FAIL**, nunca sucesso parcial silencioso;
12. tentar compensar os campos alterados restaurando o snapshot anterior;
13. reler após compensação;
14. se snapshot foi restaurado -> retornar falha segura/retry possível;
15. se compensação não restaurou integralmente -> estado **PARTIAL_FAILURE_CRITICAL**, com diagnóstico administrativo claro e valores finais relidos; nunca esconder divergência.

### Por que esta estratégia

- mantém WordPress-first;
- evita tabela/transação própria só para três metas;
- trata `update_post_meta()` retornar `false` em `NO_CHANGE` sem confundir com falha, porque sucesso é definido pelo **estado relido**, não pelo booleano isolado;
- oferece comportamento previsível ao usuário;
- permite fault-injection testável em T093/SPEC-001.

### Gate B-006

**FECHADO conceitualmente para SPEC-001.**

A futura implementação precisa provar a estratégia com testes de falha/compensação antes do GO; falha desses testes reabre o gate da implementação, não a decisão arquitetural.

---

## 9. B-007 — Stale/async/queue

**Classificação:** NÃO_APLICÁVEL_A_SPEC001.

Summary é síncrono e não cria queue/rebuild/background worker.

B-007 permanece para Search assíncrona/queue caso benchmark futuro justifique.

---

## 10. Unknowns T090–T094

### U-001 — versão mínima WordPress / meta revisions

**Decisão:** NÃO_APLICÁVEL_A_SPEC001.

SPEC-001 não depende de `revisions_enabled`; não criar meta revisions no primeiro slice.

A versão mínima geral do plugin deve ser declarada na futura SPEC com base nas APIs realmente usadas, mas não há dependência específica que bloqueie planejamento agora.

### U-002 — taxonomias públicas

**Decisão:** NÃO_APLICÁVEL_A_SPEC001.

Sem taxonomy no Summary.

### U-003 — provider endpoint/SSRF

**Decisão:** NÃO_APLICÁVEL_A_SPEC001.

Sem IA/integrador externo.

### U-004 — post type/scope suportado

**Decisão:** FECHAR NA BASELINE DA SPEC-001 ANTES DO CÓDIGO; não é blocker de criação da SPEC.

Regra já suficiente para T097:

- a SPEC-001 só atua sobre tipos de post comprovadamente pertencentes à Base de Conhecimento atual;
- o inventário inicial da SPEC enumera explicitamente os `post_type` reais do ambiente/baseline antes de registrar tela/handler;
- nenhum `post_type` genérico ou arbitrário é aceito por request;
- capability continua por objeto.

Se o inventário da futura SPEC não conseguir comprovar o target, implementação permanece `NOT_READY`.

### U-005 — capability

**Decisão:** FECHAR_AGORA.

Baseline de autorização: `current_user_can( 'edit_post', $post_id )` no objeto alvo, mais qualquer restrição de post type/scope definida pela SPEC. Não criar role/capability custom sem necessidade comprovada.

### U-006 — superfície técnica

**Decisão:** FECHAR_AGORA.

- server-rendered wp-admin;
- GET somente leitura;
- POST via `admin-post` ou handler WordPress equivalente mínimo;
- nonce específico;
- sem AJAX/REST/SPA.

### U-007 — settings

**Decisão:** FECHAR_AGORA — **nenhuma tela/option de settings é necessária para SPEC-001**, exceto se uma necessidade concreta surgir durante a SPEC e passar pelo princípio de negação.

### U-008 — events/hooks

**Decisão:** FECHAR_AGORA — nenhum evento próprio é necessário para cumprir SPEC-001. Se não existir consumidor real, não emitir hook novo por antecipação.

### U-009 — lifecycle/version option

**Decisão:** FECHAR_AGORA — activation mínima; nenhuma migration/rebuild; não criar runtime version option sem necessidade de upgrade real.

### U-010 — histórico/audit/revisions

**Decisão:** FECHAR_AGORA — fora da SPEC-001. Logs/diagnóstico apenas para erro necessário; não criar histórico de Summary ou audit genérico.

---

## 11. Dados e rollback da SPEC-001

### Dados canônicos

- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

### O que não muda

- `post_title`;
- `post_content`;
- `_elementor_data`;
- cinco classificações GRE;
- Review/KB2Ops;
- Search/ASI.

### Rollback

Como SPEC-001 reutiliza os meta keys já existentes e não cria migration/schema:

- desativar o novo plugin não exige rollback de dados;
- dados permanecem postmeta WordPress;
- nenhuma limpeza automática é executada;
- purge fica fora de escopo.

Isso reduz fortemente risco de cutover.

---

## 12. Definition of Ready documental da SPEC-001 candidata

T095 considera suficientes para T097:

- problema/usuário: Analista de Conhecimento precisa gerir três campos Summary;
- owner: Resumo Executivo;
- storage: três postmeta GRE preservadas/reutilizadas;
- UI: uma tela wp-admin server-rendered mínima;
- auth: `edit_post` por objeto;
- mutation: POST + nonce + allowlist/validation/sanitization;
- consistency: estratégia B-006 definida;
- escaping: contextual;
- QA: Matriz T093 definida;
- editorial boundary: G-001;
- no schema/migration/queue/Search/Analytics/IA;
- rollback: deactivate sem destruir meta;
- coexistência/cutover: não remover legado; B-003 permanece para produção/removal;
- post types: enumerar no baseline da própria SPEC antes do código.

---

## 13. Mapa final dos blockers

| Blocker | T095 para SPEC-001 | Destino |
|---|---|---|
| B-001 | NÃO APLICÁVEL | Search/RAG |
| B-002 | NÃO APLICÁVEL | Classificação/cutover |
| B-003 | NÃO APLICÁVEL ao dev/homolog; posterior | cutover/aliases/removal |
| B-004 | NÃO APLICÁVEL | Analytics |
| B-005 | NÃO APLICÁVEL | item/deep-link |
| B-006 | **FECHADO AGORA** | estratégia Summary definida |
| B-007 | NÃO APLICÁVEL | async/queue |

**BLOCKER_SPEC001 aberto: ZERO.**

---

## 14. Gate T095

- blockers aplicáveis identificados: **SIM**;
- B-006 fechado conceitualmente: **SIM**;
- demais blockers encaminhados por slice: **SIM**;
- meta keys/storage inicial definidos: **SIM**;
- security/QA/rollback conhecidos: **SIM**;
- runtime criado: **NÃO**;
- SPEC-001 autorizada: **AINDA NÃO — T097**.

## 15. Próximo passo

**T096 — Relatório Final da SPEC-000.**

T096 deve sintetizar inventário, decisões, riscos, reviews, blockers e recomendação objetiva para T097, sem criar novas decisões arquiteturais salvo correção de contradição.
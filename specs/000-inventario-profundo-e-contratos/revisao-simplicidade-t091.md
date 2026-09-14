# T091 — Revisão do Crítico de Simplicidade

> Estado: **CONCLUÍDA — APROVADA COM SIMPLIFICAÇÕES E POSTERGAÇÕES, ZERO BLOQUEIOS**.  
> Baseline revisada: `main @ 875be15d50425b71903568e3cac153dd6a67f798` (fechamento T090).  
> Objeto principal: `matriz-paridade-futura.md` + `revisao-wordpress-t090.md`.

## 1. Objetivo

Atacar a arquitetura consolidada sob o princípio de negação e remover tudo que possa ser adiado, reduzido ou eliminado sem perda material de produto, segurança, integridade, regressão ou reversibilidade.

Pergunta aplicada a cada família:

> **Se não construirmos isto agora, qual resultado real deixa de existir?**

Nenhum runtime foi criado nesta revisão.

## 2. Resultado executivo

**PASS de simplicidade.** A arquitetura T059 permanece válida, mas sua execução futura deve ser mais incremental do que a leitura ampla de `PRIMEIRO_RUNTIME` poderia sugerir.

Contagem de findings:

- **6 MANTER**;
- **8 SIMPLIFICAR**;
- **6 POSTERGAR**;
- **4 DESCARTAR**;
- **0 BLOQUEAR**.

A principal conclusão é:

> **SPEC-001, se autorizada em T097, não deve construir plataforma, busca, IA ou “fundação completa”. Deve entregar um único vertical slice WordPress-first e homologável.**

O candidato mais simples para primeiro slice continua sendo **Core mínimo + Summary narrativo**, por ser o domínio menor, já comprovado no GRE e sem depender de B-001/B-002/B-003/B-004/B-005/B-007.

T091 não autoriza SPEC-001; essa recomendação será confrontada em T094/T095/T097.

---

## 3. Findings

### S-001 — Primeiro runtime

**Classificação:** SIMPLIFICAR.

`PRIMEIRO_RUNTIME` deve significar apenas “elegível para a primeira onda”, nunca “construir juntos”.

**Alternativa mínima recomendada:**

`bootstrap mínimo -> capability/nonce -> tela server-rendered -> ler Summary -> editar 1–3 metas -> salvar -> read-after-write -> feedback`

Não incluir no mesmo slice:

- classificação completa;
- review completo;
- extractor;
- Search;
- Golden;
- Analytics;
- queue;
- Foundry;
- IA;
- migration framework genérico.

### S-002 — Content Extractor

**Classificação:** POSTERGAR até o primeiro consumidor real.

O contrato permanece obrigatório para Search/RAG/qualidade que dependam de conteúdo Elementor, mas não há benefício em implementá-lo numa SPEC de Summary puro.

**Regra:** extractor nasce junto do primeiro slice que realmente o consome e B-001 passa a ser gate desse slice, não da plataforma inteira.

### S-003 — Design System

**Classificação:** SIMPLIFICAR.

Não criar biblioteca de componentes completa antes das telas.

Baseline:

- tokens mínimos;
- layout/shell necessário ao slice;
- componentes apenas quando usados por uma tela real;
- server-rendered primeiro;
- sem framework SPA.

O DS continua contrato visual; a simplificação é de escopo, não de qualidade.

### S-004 — Settings/Core Configuration

**Classificação:** SIMPLIFICAR.

Não criar uma grande tela de configurações vazia. Criar apenas options/settings efetivamente consumidos pelo slice.

`runtime_version` só existe se upgrade/lifecycle real precisar.

### S-005 — Summary

**Classificação:** MANTER.

É o menor bounded context com valor de produto comprovado e implementação WordPress-first simples.

Primeiro slice deve começar apenas com `objective`, `escalation`, `important`; campos classificatórios históricos exibidos no GRE continuam pertencendo ao owner Classificação e não precisam entrar no primeiro fluxo.

### S-006 — Review

**Classificação:** POSTERGAR para slice próprio após um owner de conteúdo sistêmico estável.

Review não precisa existir para provar Summary CRUD. Quando nascer:

- state/notes/reviewer/time/include_ai;
- histórico bounded somente se requerido;
- sem revisions duplicadas por default;
- AI READY apenas quando Summary/Classificação necessários estiverem disponíveis.

### S-007 — Classificação

**Classificação:** SIMPLIFICAR.

Não registrar todas as taxonomias/metas de classificação na mesma SPEC.

Começar por um eixo com maior valor/filtro comprovado e expandir por slice. B-002 continua necessário para cutover, mas não impede construir domínio novo sem migração.

Os quatro unknowns (`responsible_team`, `catalog_item`, `affected_service`, `systems_involved`) continuam fora até profiling real.

### S-008 — Histórico/Revisions

**Classificação:** SIMPLIFICAR.

Reforça T090: um mecanismo de histórico por requisito. Não criar “auditabilidade total” genérica antecipadamente.

### S-009 — Eventos de domínio

**Classificação:** DESCARTAR como framework genérico.

Persistência confirmada antes de evento continua invariante, mas isso não exige event bus próprio.

Usar actions/hooks WordPress específicos quando houver consumidor concreto. Não criar `EventDispatcher`, broker ou abstração de eventos antecipada.

### S-010 — Repository/Service Container genéricos

**Classificação:** DESCARTAR no baseline.

Não criar camada `Repository` sobre Metadata/Taxonomy/Options apenas para “arquitetura limpa”. Funções/classes coesas podem chamar APIs WordPress diretamente.

Também não criar service locator/container de DI antes de dependências concretas justificarem.

### S-011 — Cache abstraction

**Classificação:** DESCARTAR no baseline.

Usar request cache/Object Cache/Transients diretamente quando um benchmark mostrar necessidade. Não criar cache service genérico antes de workload.

### S-012 — Site Health

**Classificação:** SIMPLIFICAR.

Site Health permanece primitive correta, mas somente para checks que existem no runtime atual.

Não criar checks para:

- vector store inexistente;
- Foundry não configurado por design;
- queue inexistente;
- Analytics postergado;
- Search projection ainda não implementada.

Health cresce junto das capacidades reais.

### S-013 — Search lexical customizada

**Classificação:** MANTER como capacidade posterior, não antecipar.

A Search Retrieval Projection própria continua justificada, mas só deve nascer quando native WP search + requisitos do resolvedor demonstrarem insuficiência no slice de Search.

B-001 + Golden + benchmark continuam obrigatórios antes do GO produtivo.

### S-014 — Search post-level antes de item-level

**Classificação:** SIMPLIFICAR.

Mesmo com store conceitual `post|item`, implementar primeiro documentos `post` se eles satisfizerem a primeira jornada de Search.

Itens/trechos entram somente quando:

- Golden/casos de produto comprovarem ganho;
- identidade de item for necessária;
- custo de cardinalidade estiver medido.

Isso reduz acoplamento inicial a B-005.

### S-015 — Deep-link/anchors

**Classificação:** POSTERGAR.

Busca por post pode existir sem deep-link de item. Não escrever conteúdo editorial para criar anchors.

B-005 só entra no slice de item navegável.

### S-016 — Search Knowledge

**Classificação:** POSTERGAR para depois do ranker lexical mínimo, salvo caso Golden provar necessidade imediata.

Primeira Search pode operar com normalização/ranking determinístico simples. Vocabulary/bindings/relevance rules entram quando corrigem problema observado, não por paridade histórica.

Dados ASI manuais reais continuam preserváveis para cutover quando a feature nascer.

### S-017 — Golden Queries

**Classificação:** MANTER contrato, SIMPLIFICAR produto.

Golden continua obrigatória antes do release de Search, mas não exige imediatamente uma UI administrativa sofisticada.

A SPEC de Search deve criar a menor forma governada capaz de:

- versionar expectations;
- executar suite;
- produzir evidência;
- invalidar PASS stale.

CRUD visual completo só nasce se o fluxo de curadoria exigir.

### S-018 — Operations UI / migration framework

**Classificação:** DESCARTAR como framework genérico.

Não criar “Operations Center”, migration orchestrator ou painel de jobs antes de existir operação real.

Cada migração/cutover futuro deve ser idempotente e pequena; option de versão/checkpoint apenas quando necessário.

### S-019 — Compatibilidade/aliases

**Classificação:** POSTERGAR até B-003 comprovar consumidor.

Nenhum adapter framework. Cada compatibilidade, se necessária, é específica, temporária e removível.

### S-020 — Analytics/queue

**Classificação:** MANTER POSTERGADO.

Nenhuma evidência nova compra Search Analytics detalhado ou durable queue.

### S-021 — IA P1

**Classificação:** POSTERGAR até o owner assistido estar estável.

Não criar provider seam, Prompt Registry, AI settings, receipts storage ou abstração multi-provider antes do primeiro caso real.

Quando IA nascer, começar com **um** caso P1 e um adapter mínimo. Interface/factory multi-provider só se existir segundo provider/problema concreto.

### S-022 — RAG P2

**Classificação:** POSTERGAR.

Sem retrieval produtivo e evidência de necessidade de síntese, RAG não compra valor agora.

### S-023 — embeddings/semantic/rerank/agentes

**Classificação:** MANTER POSTERGADO.

T058 já está no nível mínimo aceitável; nenhuma reabertura.

### S-024 — REST/AJAX/JS

**Classificação:** MANTER política atual.

Server-rendered/admin-post primeiro; AJAX apenas se live UX real; REST/SPA sem consumidor continuam fora.

---

## 4. Sequenciamento mínimo recomendado após eventual T097

T091 recomenda, sujeito a T094/T095/T097:

1. **SPEC-001 — Core mínimo + Summary narrativo**;
2. **SPEC-002 — Classificação mínima por eixo priorizado**;
3. **SPEC-003 — Review/Governança**;
4. **SPEC de Content Extractor + Search post-level** quando produto priorizar Search;
5. item-level/deep-link somente depois;
6. IA P1 somente com owner estável;
7. demais capacidades por evidência.

Os números finais de SPEC ainda não estão congelados; a ordem serve como regra de simplicidade.

## 5. Complexidades explicitamente proibidas no primeiro slice

- tabela própria;
- migration framework genérico;
- Search index;
- queue;
- Analytics;
- REST;
- SPA/React;
- agent/tool framework;
- provider abstraction multi-vendor;
- Prompt Registry genérico;
- cache service genérico;
- event bus próprio;
- repository layer genérico;
- Operations dashboard;
- batch/rebuild;
- adapters/aliases sem consumidor.

Exceção somente por requisito direto da SPEC e ADR correspondente.

## 6. Riscos de simplificar demais

Simplicidade não autoriza remover garantias:

- capability + nonce + sanitização + escaping;
- read-after-write;
- B-006 no write composto;
- rollback/coexistência quando houver cutover;
- Golden/benchmark quando Search nascer;
- B-001 quando extractor for consumidor crítico;
- logs/diagnóstico suficientes para a capacidade implementada;
- Design System e acessibilidade da tela real.

## 7. Gate T091

- princípio de negação aplicado: **PASS**;
- complexidade não justificada bloqueante: **ZERO** após postergações/simplificações;
- infraestrutura própria adicional: **ZERO**;
- capacidades avançadas reabertas: **ZERO**;
- runtime criado: **NÃO**;
- SPEC-001 autorizada: **NÃO**.

## 8. Entrada para T092

A revisão de Segurança deve focar especialmente em:

1. capability model por owner/slice;
2. nonce/método/CSRF;
3. sanitização/escaping;
4. IDOR/scope de post;
5. taxonomias internas e exposição pública;
6. shortcodes/aliases e superfícies legadas;
7. provider endpoint/SSRF/secrets/data egress;
8. prompt injection futuro;
9. Search detail/scope fail-closed;
10. migration/purge/destructive actions.

## 9. Próximo passo

**T092 — Revisão de Segurança.**

T092 não deve criar runtime; deve produzir threat model/gates objetivos por capacidade e identificar qualquer blocker de segurança que T095/T097 precise resolver.
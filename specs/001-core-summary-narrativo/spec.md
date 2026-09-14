# SPEC-001 — Core mínimo + Summary narrativo

**Status:** Pronta  
**Dono:** Orquestrador + Arquiteto WordPress + Produto/Conhecimento + Segurança/Regressão + Crítico de Simplicidade  
**Data:** 2026-09-14  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## 1. Problema

O Analista de Conhecimento precisa gerir um Summary narrativo mínimo de um artigo da Base de Conhecimento sem alterar conteúdo editorial, sem introduzir infraestrutura desnecessária e sem repetir os problemas de consistência do writer legado.

A primeira jornada homologável é deliberadamente pequena:

`selecionar artigo -> ler objective/escalation/important -> editar -> salvar -> reler -> confirmar estado`.

## 2. Baseline — onde estamos

Baseline de entrada: `main @ fced4a6015638b585d8817485fce8ef0fb8d7ccb` (`spec-000: autorizar SPEC-001 T097`).

Estado comprovado:

- SPEC-000 concluída por T097;
- nenhum runtime do novo plugin existe;
- GRE 0.6.0 é a referência WordPress-first do Summary;
- GRE registra metadata para `post` e rejeita `post_type` diferente de `post`;
- KB2Ops e ASI tratam o corpus de conhecimento como posts vinculados por post ID;
- três metas narrativas foram autorizadas para este slice:
  - `objective` -> `_bdc_es_objective`;
  - `escalation` -> `_bdc_es_escalation`;
  - `important` -> `_bdc_es_important`;
- B-006 foi fechado conceitualmente, mas precisa de implementação e teste;
- B-003 não bloqueia desenvolvimento/homologação, mas volta antes de produção/cutover/coexistência não controlada de writers.

### Post type suportado

**Somente `post`.**

A decisão é baseada no baseline versionado do GRE 0.6.0, cujo Meta Contract fixa `POST_TYPE = 'post'` e cujo store rejeita tipos diferentes. Nenhum `page` ou CPT ganha suporte por inferência. Novo tipo exige evidência e alteração formal da SPEC.

Esta comprovação é suficiente para desenvolvimento/homologação. Não substitui o preflight B-003 exigido antes de produção/cutover.

### Divergência de roadmap encontrada

O repositório continha placeholders antigos `001-core-shell-design-system` e `002-resumo-executivo-integrado`. T097 é decisão posterior e canônica. Os placeholders são preservados apenas para rastreabilidade e marcados como supersedidos; não podem ser executados como SPECs ativas.

## 3. Usuários e jornada

Usuário primário: **Analista de Conhecimento**.

Fluxo:

1. abrir a superfície administrativa da Base de Conhecimento;
2. selecionar um `post` que o usuário possa editar;
3. ler os três campos atuais sem side effect;
4. editar um ou mais campos;
5. submeter por POST com nonce;
6. validar todo o payload antes de qualquer write;
7. persistir conforme B-006;
8. reler os três campos;
9. redirecionar para GET e apresentar o estado confirmado.

## 4. Resultado esperado — para onde queremos ir

Um vertical slice WordPress-first, server-rendered e reversível que permita gerenciar apenas os três campos narrativos autorizados, com segurança por objeto, consistência explícita e zero alteração editorial.

## 5. Invariantes constitucionais afetados

- Art. I — WordPress-first;
- Art. II — editorial pertence a WordPress/Elementor;
- Art. III — princípio de negação;
- Art. V — vertical slice;
- Art. VI — baseline e regressão;
- Art. IX — Design System como contrato;
- Art. XIII — segurança WordPress;
- Art. XIV — rollback/preservação;
- Art. XVI — governança de Specs;
- Art. XVIII — continuidade entre chats.

## 6. Avaliação WordPress-first

| Necessidade | Recurso nativo avaliado | Atende? | Justificativa |
|---|---|---:|---|
| storage | Post Metadata API | Sim | três strings por post; tabela própria não agrega valor |
| autorização | `current_user_can('edit_post', $post_id)` | Sim | controle por objeto sem role custom |
| CSRF | nonce WordPress | Sim | mutação browser tradicional |
| mutação | `admin-post.php`/handler WP equivalente | Sim | não há consumidor REST/AJAX |
| UI | wp-admin server-rendered | Sim | fluxo administrativo simples |
| consulta/listagem | `WP_Query`/APIs de post | Sim | escopo limitado a `post` e paginação |
| feedback | redirect + admin notice | Sim | padrão POST-Redirect-GET |
| persistência consistente | Metadata API + read-after-write + compensação | Sim | evita tabela/transação própria |

## 7. Princípio de negação

### Solução inicialmente possível

SPA/REST, store próprio, tabela transacional, framework de repositories/events e Design System completo antes do fluxo real.

### Alternativa mais simples

wp-admin server-rendered + Metadata API + POST/nonce + capability por objeto + B-006.

### O que pode ser removido?

REST, AJAX, SPA, tabela, queue, settings, IA, vetor, event bus, audit genérico, migration e dependência dos plugins legados.

### Decisão

Implementar somente o mínimo necessário à jornada autorizada.

## 8. Escopo

### Dentro

- bootstrap mínimo necessário ao slice;
- superfície integrada ao wp-admin;
- listagem/seleção paginada de `post`;
- leitura e edição de `objective`, `escalation`, `important`;
- POST + nonce + capability por objeto;
- allowlist estrita;
- validação, sanitização e escaping;
- Metadata API;
- POST-Redirect-GET;
- B-006;
- feedback de sucesso/falha crítica;
- Design System mínimo usado pela tela;
- testes e fixtures do slice.

### Fora

Classificação; Review/AI READY; Content Extractor; Search/Golden/Search Knowledge; Analytics/query logging; queue; tabela/schema/migration; REST/AJAX/SPA; Foundry/LLM; embeddings/vector/semantic/rerank; agentes; aliases/shortcodes de compatibilidade; remoção/desativação de GRE/KB2Ops/ASI; cutover produtivo; cinco campos classificatórios restantes do GRE.

## 9. Contrato funcional

### Leitura

- GET é estritamente read-only;
- ID deve resolver um `post` existente;
- leitura de meta inexistente projeta `''` sem criar linha;
- direct URL para objeto sem `edit_post` falha fechado;
- `post_title`, `post_content` e `_elementor_data` nunca são alterados.

### Escrita

- somente POST autenticado;
- nonce específico do fluxo e vinculado ao post alvo;
- `current_user_can('edit_post', $post_id)` no ponto de mutação;
- allowlist lógica exata: `objective`, `escalation`, `important`;
- campo desconhecido rejeita o request inteiro antes de write;
- valores devem ser strings;
- `wp_unslash()` antes da validação/sanitização de request;
- limite: **32 KiB (32768 bytes UTF-8) por campo antes da sanitização**;
- excesso de limite é erro; nunca truncar silenciosamente;
- sanitização canônica: `trim( sanitize_textarea_field( $value ) )`;
- vazio sanitizado remove a meta;
- campo omitido é preservado;
- valor idêntico é `NO_CHANGE` e não é regravado;
- writes usam Metadata API e slashing compatível;
- sucesso é definido pelo estado relido, não pelo booleano isolado de `update_post_meta()`.

### B-006

`authorize -> method/nonce -> allowlist -> validate all -> sanitize all -> snapshot -> diff -> write changes -> read-after-write -> compare`.

Se houver mismatch:

`FAIL -> compensação best-effort -> reread`.

Resultados:

- estado esperado: `SUCCESS`;
- snapshot restaurado: `FAIL_SAFE`;
- restauração incompleta: `PARTIAL_FAILURE_CRITICAL`, com valores finais relidos e diagnóstico administrativo explícito.

## 10. Modelo de dados

Owner: **Resumo Executivo**.

| Campo | Meta key | Tipo | Single | Vazio | Limite |
|---|---|---|---:|---|---:|
| objective | `_bdc_es_objective` | string multiline | sim | delete | 32768 bytes |
| escalation | `_bdc_es_escalation` | string multiline | sim | delete | 32768 bytes |
| important | `_bdc_es_important` | string multiline | sim | delete | 32768 bytes |

Título permanece `post_title`; nenhuma `_bdc_es_title`.

Não existe schema/tabela/migration nesta SPEC.

## 11. Integrações e eventos

APIs utilizadas: posts, Metadata API, capabilities, nonces, admin-post/handler nativo, escaping e wp-admin.

Nenhum hook/evento de domínio novo será criado sem consumidor real. Nenhum serviço externo.

## 12. Segurança

A Matriz de Mutação em `matriz-mutacao.md` é normativa para este slice.

Regras mínimas:

- capability por objeto;
- POST para qualquer mutação;
- nonce não substitui capability;
- fail-closed para ID inexistente/tipo fora de `post`;
- zero mass assignment;
- validação integral antes do primeiro write;
- escaping contextual (`esc_html`, `esc_attr`, `esc_textarea`, `esc_url` conforme contexto);
- mensagens não podem refletir payload cru;
- nenhuma query SQL própria.

## 13. UI/UX

- wp-admin é o shell;
- nenhuma segunda sidebar;
- uma superfície simples com listagem/seleção e editor do Summary;
- labels associados aos textareas;
- título do post exibido como contexto read-only;
- feedback textual de sucesso/erro, nunca somente por cor;
- foco de erro previsível e navegação por teclado;
- layout utilizável em viewport administrativo estreito;
- POST-Redirect-GET evita reenvio acidental.

Browser acceptance está detalhado em `baseline-definition-of-ready.md` e `matriz-evidencia.md`.

## 14. Observabilidade e custo

Sem IA, vetor, query logging ou telemetria de usuário.

Diagnóstico somente para falhas necessárias, especialmente `PARTIAL_FAILURE_CRITICAL`. Não criar audit framework genérico.

## 15. Performance

- listagem paginada; proibido `posts_per_page = -1`;
- no máximo três leituras de meta por snapshot lógico, admitindo caches nativos;
- writes somente no diff;
- nenhum benchmark obrigatório para Summary simples antes de evidência de gargalo.

## 16. Migração e compatibilidade

As três meta keys existentes são reutilizadas. Não há dual-write nem migration.

Desenvolvimento/homologação pode coexistir com plugins legados desde que eles não sejam removidos. Produção com writers concorrentes continua bloqueada por B-003 até preflight e decisão de single-writer/coexistência.

## 17. Testes

### Unitários

Validação de allowlist/tipos/limites, sanitização, diff, classificação dos resultados B-006.

### Integração WordPress

Metadata API, capabilities, nonce/handler, empty-delete, partial update, no-op, read-after-write, post type e fault injection B-006.

### Regressão

G-001, G-020, G-070, G-110 e G-130 aplicáveis.

### Segurança

IDOR, capability, nonce ausente/inválido, GET sem mutação, mass assignment, XSS e tipo inválido.

### Performance

N/A nesta fase salvo regressão evidente; listagem deve ser paginada.

### Browser/manual

Seleção -> leitura -> edição -> save -> redirect -> releitura; empty/delete; erro; permissão; foco/teclado; viewport estreito.

### Golden Queries

N/A — Search está fora de escopo.

## 18. Critérios de aceite

- [ ] runtime atua somente em `post`;
- [ ] jornada ponta a ponta funciona;
- [ ] GET não escreve;
- [ ] os três campos persistem e são relidos corretamente;
- [ ] vazio remove meta;
- [ ] no-op não é falha;
- [ ] B-006 passa em fault injection;
- [ ] capability/nonce/allowlist/escaping passam;
- [ ] editorial permanece byte/logicamente inalterado nos campos protegidos;
- [ ] UI/browser acceptance passa;
- [ ] rollback é comprovado;
- [ ] Matriz de Evidência não possui gate ativo em FAIL/NOT_RUN/NOT_CONFIGURED/STALE no momento do GO de homologação/release.

## 19. Critérios de NÃO aceite

- write em `_elementor_data`;
- alteração silenciosa de `post_content` ou `post_title`;
- suporte arbitrário a `page`/CPT;
- capability apenas no menu;
- mutação por GET;
- nonce ausente;
- mass assignment;
- truncamento silencioso;
- sucesso falso após write parcial;
- XSS/escaping incorreto;
- tabela/schema/migration;
- REST/AJAX/SPA sem nova decisão;
- IA/Search/Analytics/Classificação/Review introduzidos por conveniência;
- limpeza automática de legado/dados.

## 20. Rollback

Desativar o novo plugin. As três metas permanecem em postmeta. Nenhuma limpeza automática, migration ou schema precisa ser revertido. Se uma mutação individual falhar, B-006 tenta restaurar o snapshot; compensação incompleta é crítica e explícita.

## 21. Evidências de conclusão

Serão registradas na Matriz de Evidência, resultados de testes, browser acceptance, build/package quando aplicável, commit e handoff.

## 22. Continuidade entre chats

`CONTINUIDADE.md` é obrigatório e deve refletir o último estado comprovado.

### Definition of Ready

O bloco documental inicial está **PASS** conforme `baseline-definition-of-ready.md`. Isso autoriza o início futuro do runtime mínimo da SPEC-001, mas este commit documental não implementa runtime, não homologa e não autoriza produção/cutover.

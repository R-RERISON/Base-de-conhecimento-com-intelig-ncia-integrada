# Research — SPEC-001 Core mínimo + Summary narrativo

## Pergunta

Qual é a menor solução comprovável para editar o Summary narrativo sem alterar editorial e sem importar a dívida dos plugins anteriores?

## Evidências do baseline

1. T097 autoriza exatamente `objective`, `escalation` e `important`, em wp-admin server-rendered, GET read-only e POST+nonce.
2. GRE 0.6.0 usa apenas Metadata API para o domínio e não precisa de tabela, REST, AJAX, cron ou JavaScript.
3. O Meta Contract do GRE fixa `POST_TYPE = 'post'`.
4. O Summary Store do GRE valida post existente, exige `post_type = post`, aplica `edit_post`, allowlist, sanitização e read-after-write.
5. A lacuna do GRE é a falha tardia multi-campo sem compensação; T095 define B-006 para o novo slice.
6. KB2Ops confirma o valor de wp-admin como shell, server rendering e Design System, mas seu domínio de classificação/review está fora desta SPEC.
7. ASI não é necessário para o Summary; Search/Analytics/queue permanecem postergados.

## Decisões

### Target

`post` somente. Não aceitar `page` ou CPT por request, filtro ou configuração nesta SPEC.

### Persistência

Reutilizar `_bdc_es_objective`, `_bdc_es_escalation`, `_bdc_es_important`.

### Sanitização

`wp_unslash` na entrada HTTP; tipo string; limite 32768 bytes por campo; `trim(sanitize_textarea_field())`; vazio sanitizado remove meta. Sem truncamento silencioso.

### UI

Server-rendered, PRG, listagem paginada, editor simples, sem JavaScript obrigatório.

### Consistência

Snapshot + diff + writes mínimos + read-after-write + compensação best-effort.

## Alternativas rejeitadas

- tabela própria para obter transação: custo/complexidade desproporcionais para três metas;
- REST/AJAX: nenhum consumidor real;
- SPA/React: não agrega ao primeiro slice;
- oito campos GRE: viola escopo T097;
- role/capability custom: `edit_post` por objeto resolve;
- live production inventory como pré-requisito absoluto do desenvolvimento: baseline versionado já comprova `post`; inventário/preflight de produção volta no B-003.

## Unknowns remanescentes

Nenhum unknown bloqueia o início do runtime de desenvolvimento/homologação. Produção/cutover permanece explicitamente não autorizada.
